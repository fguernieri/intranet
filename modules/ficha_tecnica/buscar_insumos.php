<?php
require_once '../../config/db_dw.php';

$termo = $_POST['termo'] ?? '';

if (strlen($termo) < 2) {
    echo json_encode([]);
    exit;
}

$stmt = $pdo_dw->prepare("SELECT `Insumo`, `Cód. ref..1` AS codigo, `Und.` AS unidade
                           FROM insumos_bastards
                           WHERE `Insumo` LIKE :termo
                           LIMIT 10");
$stmt->execute([':termo' => '%' . $termo . '%']);
$resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

echo json_encode($resultados);
