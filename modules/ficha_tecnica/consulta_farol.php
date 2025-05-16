<?php
require_once '../../config/db.php';
require_once '../../config/db_dw.php';

header('Content-Type: application/json');

$id = intval($_GET['prato_id'] ?? 0);
$status = 'cinza';

if ($id > 0) {
    $stmt1 = $pdo->prepare("SELECT id FROM ficha_tecnica WHERE id = ?");
    $stmt1->execute([$id]);
    $hasIntranet = $stmt1->fetch();

    $stmt2 = $pdo_dw->prepare("SELECT id FROM insumos_bastards WHERE id_ficha = ?");
    $stmt2->execute([$id]);
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
