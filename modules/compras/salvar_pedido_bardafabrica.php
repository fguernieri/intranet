<?php
// === modules/compras/salvar_pedido_7tragos.php ===
// Salva o pedido só para 7TRAGOS (comportamento igual ao salvar_pedido.php original).

require_once $_SERVER['DOCUMENT_ROOT'] . '/auth.php';
session_start();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: insumos_bardafabrica.php');
    exit;
}

// fixa a filial
$filial  = 'BAR DA FABRICA';
$usuario = $_SESSION['usuario_nome'] ?? '';

// conexão + transação
require_once $_SERVER['DOCUMENT_ROOT'] . '/db_config.php';
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');
$conn->begin_transaction();

try {
    // novo número
    $row = $conn->query("SELECT COALESCE(MAX(numero_pedido),0)+1 AS novo FROM pedidos")->fetch_assoc();
    $numeroPedido = (int)$row['novo'];
    $dataHora = date('Y-m-d H:i:s');

    // prepara statements
    $stmtInfo = $conn->prepare("
        SELECT INSUMO_CLOUDFY, CODIGO
          FROM insumos
         WHERE INSUMO = ? AND FILIAL = ?
    ");
    $stmtInfo->bind_param('ss', $insumoNome, $filial);

    $stmtInsert = $conn->prepare("
        INSERT INTO pedidos (
          numero_pedido, INSUMO_CLOUDFY, INSUMO, CODIGO,
          CATEGORIA, UNIDADE, FILIAL, QUANTIDADE,
          OBSERVACAO, USUARIO, DATA_HORA
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
    ");
    $stmtInsert->bind_param(
        'issssssdsss',
        $numeroPedido,
        $insumoCloudfy,
        $insumoNome,
        $insumoCodigo,
        $categoria,
        $unidade,
        $filial,
        $quantidade,
        $observacao,
        $usuario,
        $dataHora
    );

    // lê arrays do POST
    $insumosArr     = $_POST['insumo']     ?? [];
    $categoriasArr  = $_POST['categoria']  ?? [];
    $unidadesArr    = $_POST['unidade']    ?? [];
    $quantidadesArr = $_POST['quantidade'] ?? [];
    $obsArr         = $_POST['observacao'] ?? [];

    // loop existentes
    foreach ($insumosArr as $i => $_) {
        $insumoNome = trim($insumosArr[$i] ?? '');
        $categoria  = substr(trim($categoriasArr[$i] ?? ''), 0, 50);
        $unidade    = substr(trim($unidadesArr[$i]   ?? ''), 0, 20);
        $quantidade = floatval(str_replace(',', '.', $quantidadesArr[$i] ?? '0'));
        $observacao = substr(trim($obsArr[$i]         ?? ''), 0, 200);

        if ($insumoNome === '' || $quantidade <= 0) continue;

        $stmtInfo->execute();
        $stmtInfo->bind_result($insumoCloudfy, $insumoCodigo);
        if (! $stmtInfo->fetch()) {
            $insumoCloudfy = '';
            $insumoCodigo  = '';
        }
        $stmtInfo->free_result();

        $stmtInsert->execute();
    }

    $conn->commit();
    header('Location: insumos_bardafabrica.php?status=ok&pedido='.$numeroPedido);
    exit;

} catch (mysqli_sql_exception $e) {
    $conn->rollback();
    error_log("salvar_pedido_bardafabrica.php erro: ".$e->getMessage());
    die("Erro ao salvar o pedido. Consulte o administrador.");
}
