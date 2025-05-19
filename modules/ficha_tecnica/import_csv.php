<?php
// import_insumos.php

// === Configurações de conexão ===
$host      = '162.241.2.239';
$db        = 'basta920_dw_fabrica';
$user      = 'basta920_lucas';
$pass      = 'C;f.(7(2K+D%';
$charset   = 'utf8mb4';
$table     = 'insumos_bastards';

// chave composta
$keyCols   = ['Cód. ref.', 'Cód. ref..1'];
// todas as colunas da tabela, na ordem
$dbColumns = [
    'Cód. ref.', 'Produto', 'Unidade', 'Tipo', 'Grupo',
    'Rendimento', 'Custo unit.', 'Preço venda', 'Markup atual(%)',
    'Markup desejado(%)', 'Preço sugerido', 'Cód. ref..1',
    'Insumo', 'Qtde.', 'Und.', 'Custo unit..1',
    'Custo total', 'Principal'
];
// colunas numéricas (tratadas como floats)
$numericCols = [
    'Rendimento','Custo unit.','Preço venda','Markup atual(%)',
    'Markup desejado(%)','Preço sugerido','Qtde.','Custo unit..1','Custo total'
];

// novo nome do log
$logFile = __DIR__ . '/import_csv.log';

// gera nome seguro para parâmetros PDO
function paramName(string $col): string {
    return preg_replace('/[^a-zA-Z0-9_]/', '_', $col);
}

// exibe formulário se não for POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_FILES['csv_file'])) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-br">
    <head>
      <meta charset="UTF-8">
      <title>Importar CSV de Insumos</title>
      <style>
        body { font-family: Arial, sans-serif; padding: 2rem; background: #f5f5f5; }
        form { background: #fff; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); }
        input, button { font-size: 1rem; margin-top: 0.5rem; }
      </style>
    </head>
    <body>
      <h1>Importar CSV de Insumos</h1>
      <form method="POST" enctype="multipart/form-data">
        <label>
          Selecione o arquivo CSV:<br>
          <input type="file" name="csv_file" accept=".csv,.txt" required>
        </label><br><br>
        <button type="submit">Importar</button>
      </form>
    </body>
    </html>
    <?php
    exit;
}

// conexão PDO
try {
    $dsn = "mysql:host=$host;dbname=$db;charset=$charset";
    $pdo = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]);
} catch (PDOException $e) {
    die("Falha na conexão: " . $e->getMessage());
}

// valida upload
if ($_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
    die("Erro no upload do CSV: código {$_FILES['csv_file']['error']}");
}
$csvFile = $_FILES['csv_file']['tmp_name'];

// inicia log (limpa conteúdo anterior)
file_put_contents($logFile,
    date('Y-m-d H:i:s')." - Início importação {$_FILES['csv_file']['name']}\n",
    LOCK_EX
);

// 1) SELECT para verificar existência
$chkSql = "
  SELECT 1 FROM `$table`
   WHERE `{$keyCols[0]}` = :".paramName($keyCols[0])."
     AND `{$keyCols[1]}` = :".paramName($keyCols[1])."
   LIMIT 1
";
$chkStmt = $pdo->prepare($chkSql);

// 2) UPDATE
$updCols   = array_diff($dbColumns, $keyCols);
$setParts  = array_map(fn($c)=> "`$c` = :".paramName($c), $updCols);
$whereParts= array_map(fn($k)=> "`$k` = :".paramName($k), $keyCols);
$sqlUpdate = "UPDATE `$table` SET ".implode(', ', $setParts)
           . " WHERE ".implode(' AND ', $whereParts);
$stmtUpdate = $pdo->prepare($sqlUpdate);

// 3) INSERT
$colList    = implode(', ', array_map(fn($c)=> "`$c`", $dbColumns));
$paramList  = implode(', ', array_map(fn($c)=> ":".paramName($c), $dbColumns));
$sqlInsert  = "INSERT INTO `$table` ($colList) VALUES ($paramList)";
$stmtInsert = $pdo->prepare($sqlInsert);

// abre CSV com fgetcsv (tab + aspas)
$handle = fopen($csvFile, 'r');
$header = fgetcsv($handle, 0, "\t", '"');

// corrige "Cód. ref.Produto"
if (isset($header[0]) && $header[0] === 'Cód. ref.Produto') {
    array_splice($header, 0, 1, ['Cód. ref.', 'Produto']);
}
// remove coluna vazia final
if (end($header) === '') {
    array_pop($header);
}
// valida colunas
if (count($header) !== count($dbColumns)) {
    die("Colunas CSV (".count($header).") diferentes do esperado (".count($dbColumns).")");
}

// contadores
$proc = $up = $in = $err = 0;
$line = 1;

// processa cada linha
while (($cells = fgetcsv($handle, 0, "\t", '"')) !== false) {
    $line++;
    if (count($cells) < count($dbColumns)) {
        $err++; continue;
    }
    $cells = array_slice($cells, 0, count($dbColumns));
    $row   = array_combine($dbColumns, $cells);

    // limpa whitespace nas chaves
    foreach ($keyCols as $k) {
        $row[$k] = trim(preg_replace('/\x{00a0}/u', '', $row[$k]));
    }

    // prepara parâmetros (trata decimais BR)
    $params = [];
    foreach ($dbColumns as $col) {
        $val = $row[$col];
        if (in_array($col, $numericCols, true)) {
            $clean = str_replace('.', '', $val);
            $clean = str_replace(',', '.', $clean);
            $params[paramName($col)] = ($clean === '' ? null : (float)$clean);
        } else {
            $params[paramName($col)] = ($val === '' ? null : $val);
        }
    }

    // verifica existência
    $chkStmt->execute([
        paramName($keyCols[0]) => $params[paramName($keyCols[0])],
        paramName($keyCols[1]) => $params[paramName($keyCols[1])],
    ]);
    $exists = $chkStmt->fetch();

    try {
        if ($exists) {
            // tenta UPDATE
            $stmtUpdate->execute($params);
            if ($stmtUpdate->rowCount() > 0) {
                $up++;
                file_put_contents(
                    $logFile,
                    "Linha $line: UPDATE | keys={$row[$keyCols[0]]},{$row[$keyCols[1]]}\n",
                    FILE_APPEND | LOCK_EX
                );
            }
        } else {
            // INSERT
            $stmtInsert->execute($params);
            $in++;
            file_put_contents(
                $logFile,
                "Linha $line: INSERT | keys={$row[$keyCols[0]]},{$row[$keyCols[1]]}\n",
                FILE_APPEND | LOCK_EX
            );
        }
        $proc++;
    } catch (PDOException $e) {
        $err++;
        // opcional: log de erro detalhado
    }
}

fclose($handle);

// resumo final no log
file_put_contents(
    $logFile,
    date('Y-m-d H:i:s')." - Fim: proc=$proc; upd=$up; ins=$in; err=$err\n",
    FILE_APPEND | LOCK_EX
);

// exibe ao usuário
?>
<!DOCTYPE html>
<html lang="pt-br">
<head><meta charset="UTF-8"><title>Importação Concluída</title></head>
<body>
  <h1>Import OK!</h1>
  <p>Processados: <?= $proc ?>; Atualizados: <?= $up ?>; Inseridos: <?= $in ?>; Erros: <?= $err ?></p>
  <p>Confira o log em <code><?= htmlspecialchars($logFile) ?></code></p>
  <p><a href="<?= $_SERVER['PHP_SELF'] ?>">Importar outro CSV</a></p>
</body>
</html>
