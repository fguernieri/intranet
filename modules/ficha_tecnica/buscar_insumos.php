<?php
require_once '../../config/db_dw.php';

$codigo = $_POST['codigo'] ?? null;
$termo = $_POST['termo'] ?? '';

if ($codigo) {
    $stmt = $pdo_dw->prepare("SELECT `Insumo`, `Cód. ref..1` AS codigo, `Und.` AS unidade
                              FROM insumos_bastards
                              WHERE `Cód. ref..1` = :codigo
                              LIMIT 1");
    $stmt->execute([':codigo' => $codigo]);
    echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));
    exit;
}

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
