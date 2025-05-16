<?php
require_once '../../config/db.php';
require_once '../../config/db_dw.php';

header('Content-Type: application/json');

$cod = $_GET['cod_cloudify'] ?? '';
$status = 'cinza';

if ($cod !== '') {
    $stmt1 = $pdo->prepare("SELECT id FROM ficha_tecnica WHERE codigo_cloudify = ?");
    $stmt1->execute([$cod]);
    $hasIntranet = $stmt1->fetch();

    $stmt2 = $pdo_dw->prepare("SELECT id FROM insumos_bastards WHERE codigo_cloudify = ?");
    $stmt2->execute([$cod]);
    $hasCloud = $stmt2->fetch();

    if ($hasIntranet && $hasCloud) {
        $status = 'verde';
    } elseif ($hasIntranet || $hasCloud) {
        $status = 'amarelo';
    } else {
        $status = 'vermelho';
    }
}

echo json_encode(['status' => $status]);
