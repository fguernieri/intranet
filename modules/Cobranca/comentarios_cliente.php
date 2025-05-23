<?php
declare(strict_types=1);
header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'].'/auth.php';
session_start();
if (empty($_SESSION['usuario_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Não autenticado']);
    exit;
}

// sempre usamos o nome do cliente para buscar nos comentários
$cliente = trim($_GET['cliente'] ?? '');
if ($cliente === '') {
    http_response_code(400);
    echo json_encode(['error' => 'Parâmetro cliente faltando']);
    exit;
}

require_once $_SERVER['DOCUMENT_ROOT'].'/db_config.php';
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
$conn->set_charset('utf8mb4');

try {
    $sql = "
        SELECT 
            comentario,
            usuario,
            DATE_FORMAT(criado_em, '%d/%m/%Y %H:%i') AS datahora_fmt
        FROM cobrancas_comentarios
        WHERE cliente = ?
        ORDER BY criado_em DESC
    ";
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        throw new Exception('Prepare falhou: ' . $conn->error);
    }
    $stmt->bind_param('s', $cliente);
    $stmt->execute();
    $res = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    echo json_encode($res, JSON_UNESCAPED_UNICODE | JSON_NUMERIC_CHECK);
} catch (Throwable $e) {
    http_response_code(500);
    error_log("comentarios_cliente.php: " . $e->getMessage());
    echo json_encode([
        'error'  => 'Falha interna',
        'detail' => $e->getMessage()
    ], JSON_UNESCAPED_UNICODE);
} finally {
    $stmt?->close();
    $conn->close();
}
