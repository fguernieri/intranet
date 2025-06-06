<?php
// === modules/compras/exportar_pedido.php ===
// ATENÇÃO: não deve haver nenhum output (nem espaço em branco) antes desta tag

session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
if (empty($_SESSION['usuario_id'])) {
    header('Location: /login.php');
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');
if ($conn->connect_error) {
    error_log("Conexão falhou: " . $conn->connect_error);
    die("Erro ao conectar ao banco de dados.");
}

// 1) Lê filtros com validação e sanitização
$selFilial   = filter_input(INPUT_GET, 'filial', FILTER_SANITIZE_STRING);
$dataInicio  = filter_input(INPUT_GET, 'data_inicio', FILTER_SANITIZE_STRING);
$dataFim     = filter_input(INPUT_GET, 'data_fim', FILTER_SANITIZE_STRING);
$action      = filter_input(INPUT_GET, 'export', FILTER_SANITIZE_STRING);

// Valida datas
if ($dataInicio && $dataFim) {
    $dtIni = DateTime::createFromFormat('Y-m-d', $dataInicio) ? $dataInicio . ' 00:00:00' : null;
    $dtFim = DateTime::createFromFormat('Y-m-d', $dataFim) ? $dataFim . ' 23:59:59' : null;
    if (!$dtIni || !$dtFim) {
        die("Datas inválidas.");
    }
}

// Consulta SQL (usada tanto no CSV quanto no preview)
$sql = "
  SELECT
    t.INSUMO,
    t.CATEGORIA,
    t.UNIDADE,
    SUM(t.QUANTIDADE) AS QUANTIDADE,
    GROUP_CONCAT(NULLIF(t.OBSERVACAO, '') SEPARATOR ' - ') AS OBSERVACAO
  FROM (
    SELECT INSUMO, CATEGORIA, UNIDADE, QUANTIDADE, OBSERVACAO, DATA_HORA
      FROM pedidos
     WHERE FILIAL = ? AND DATA_HORA BETWEEN ? AND ?
    UNION ALL
    SELECT INSUMO, CATEGORIA, UNIDADE, QUANTIDADE, OBSERVACAO, DATA_HORA
      FROM novos_insumos
     WHERE FILIAL = ? AND DATA_HORA BETWEEN ? AND ?
  ) AS t
  GROUP BY t.INSUMO, t.CATEGORIA, t.UNIDADE
  ORDER BY t.INSUMO
";

// 2) Exportação CSV — roda antes de qualquer include/HTML
if ($action === 'csv' && $selFilial && $dataInicio && $dataFim) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Erro ao preparar consulta: " . $conn->error);
        die("Erro ao preparar consulta.");
    }
    $stmt->bind_param(
        'ssssss',
        $selFilial, $dtIni, $dtFim,
        $selFilial, $dtIni, $dtFim
    );
    $stmt->execute();
    $res2 = $stmt->get_result();

    if (!$res2) {
        error_log("Erro ao executar consulta: " . $stmt->error);
        die("Erro ao executar consulta.");
    }

    $filename = sprintf("pedidos_%s_%s_a_%s.csv", $selFilial, $dataInicio, $dataFim);
    header('Content-Type: text/csv; charset=UTF-8');
    header("Content-Disposition: attachment; filename=\"{$filename}\"");
    echo "\xEF\xBB\xBF"; // BOM UTF-8

    $out = fopen('php://output', 'w');
    // Cabeçalho
    fputcsv($out, ['Insumo', 'Categoria', 'Unidade', 'Quantidade', 'Observação'], ';');

    // Linhas
    while ($row = $res2->fetch_assoc()) {
        $q = number_format((float)$row['QUANTIDADE'], 2, ',', '.');
        fputcsv($out, [
            $row['INSUMO'],
            $row['CATEGORIA'],
            $row['UNIDADE'],
            $q,
            $row['OBSERVACAO'] ?? '',
        ], ';');
    }

    fclose($out);
    exit; // interrompe antes de qualquer saída de template
}

// 3) Busca lista de filiais para o filtro (para o <select>) 
$resFiliais = $conn->query("
    SELECT DISTINCT FILIAL FROM pedidos
    UNION
    SELECT DISTINCT FILIAL FROM novos_insumos
    ORDER BY FILIAL
");
$filiais = $resFiliais ? $resFiliais->fetch_all(MYSQLI_ASSOC) : [];

// 4) Se forem fornecidos filtros, faz o preview em HTML
$dataRows = [];
if ($selFilial && $dataInicio && $dataFim) {
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        error_log("Erro ao preparar consulta: " . $conn->error);
        die("Erro ao preparar consulta.");
    }
    $stmt->bind_param(
        'ssssss',
        $selFilial, $dtIni, $dtFim,
        $selFilial, $dtIni, $dtFim
    );
    $stmt->execute();
    $dataRows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

$conn->close();

// 5) A partir daqui pode vir o template e HTML
require_once __DIR__ . '/../../sidebar.php';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width,initial-scale=1">
  <title>Exportar Pedido</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link href="/assets/css/style.css" rel="stylesheet">
</head>
<body class="bg-gray-900 text-gray-100 flex min-h-screen">
  <main class="flex-1 p-6 bg-gray-900">
    <h1 class="text-3xl font-bold text-yellow-400 text-center mb-8">
      Exportar Pedido
    </h1>

    <!-- Formulário de filtros -->
    <form method="get" class="max-w-md mx-auto bg-gray-800 p-6 rounded-lg shadow space-y-4">
      <div>
        <label class="block text-sm font-semibold mb-1 text-white">Filial:</label>
        <select name="filial" required
                class="w-full bg-gray-700 border border-gray-600 text-white p-2 rounded">
          <option value="">— Selecione —</option>
          <?php foreach ($filiais as $f):
            $v = htmlspecialchars($f['FILIAL'], ENT_QUOTES);
            $s = $v === $selFilial ? ' selected' : '';
          ?>
            <option value="<?= $v ?>"<?= $s ?>><?= $v ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1 text-white">Data Início:</label>
        <input type="date" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>" required
               class="w-full bg-gray-700 border border-gray-600 text-white p-2 rounded">
      </div>

      <div>
        <label class="block text-sm font-semibold mb-1 text-white">Data Fim:</label>
        <input type="date" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>" required
               class="w-full bg-gray-700 border border-gray-600 text-white p-2 rounded">
      </div>

      <button type="submit"
              class="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-3 rounded">
        Aplicar Filtro
      </button>
    </form>

    <?php if (!empty($dataRows)): ?>
      <!-- Botões de exportação -->
      <div class="max-w-4xl mx-auto flex justify-end space-x-2 mt-6">
        <a href="?<?= http_build_query([
              'filial'      => $selFilial,
              'data_inicio' => $dataInicio,
              'data_fim'    => $dataFim,
              'export'      => 'csv'
            ]) ?>"
           class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-2 px-4 rounded">
          Exportar Para Excel
        </a>
        <button id="btn-pdf"
                class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded">
          Exportar PDF
        </button>
      </div>

      <!-- Preview de resultados -->
      <div id="pdf-content"
           class="overflow-x-auto bg-gray-800 rounded-lg shadow mt-4 max-w-4xl mx-auto">
        <table class="min-w-full text-xs text-gray-100">
          <thead class="bg-gray-700 text-yellow-400">
            <tr>
              <th class="p-2 text-left">Insumo</th>
              <th class="p-2 text-left">Categoria</th>
              <th class="p-2 text-left">Unidade</th>
              <th class="p-2 text-center">Qtde</th>
              <th class="p-2 text-left">Observação</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($dataRows as $row):
              $q = number_format((float)$row['QUANTIDADE'], 2, ',', '.');
            ?>
            <tr class="border-b border-gray-700">
              <td class="p-2"><?= htmlspecialchars($row['INSUMO'], ENT_QUOTES) ?></td>
              <td class="p-2"><?= htmlspecialchars($row['CATEGORIA'], ENT_QUOTES) ?></td>
              <td class="p-2"><?= htmlspecialchars($row['UNIDADE'], ENT_QUOTES) ?></td>
              <td class="p-2 text-center"><?= $q ?></td>
              <td class="p-2"><?= htmlspecialchars($row['OBSERVACAO'], ENT_QUOTES) ?></td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </main>

  <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.9.3/html2pdf.bundle.min.js"></script>
  <script>
    document.getElementById('btn-pdf')?.addEventListener('click', () => {
      const element = document.getElementById('pdf-content');
      if (!element) return;
      html2pdf()
        .set({
          margin: 0.5,
          filename: `pedido_<?= $selFilial ?>_<?= $dataInicio ?>_a_<?= $dataFim ?>.pdf`,
          html2canvas: { scale: 2 },
          jsPDF: { unit: 'in', format: 'letter', orientation: 'portrait' }
        })
        .from(element)
        .save();
    });
  </script>
</body>
</html>