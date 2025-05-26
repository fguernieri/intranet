<?php
require_once '../../config/db.php';      // Intranet
require_once '../../config/db_dw.php';   // Cloudify

header('Content-Type: application/json');

$pratos = [];
$totalCusto = 0;
$totalPreco = 0;

// Passo 1: buscar os pratos da Intranet
$sql = "SELECT nome_prato, codigo_cloudify FROM ficha_tecnica WHERE farol = 'verde' AND codigo_cloudify IS NOT NULL";
$stmt = $pdo->prepare($sql);
$stmt->execute();
$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach ($fichas as $ficha) {
  $codigo = $ficha['codigo_cloudify'];
  $sqlPB = "SELECT Grupo, `Custo médio` AS custo, Valor AS preco FROM ProdutosBares WHERE `Cód. Ref.` = ? AND `Custo médio` > 0 AND Valor > 0";
  $stmtPB = $pdo_dw->prepare($sqlPB);
  $stmtPB->execute([$codigo]);
  $pb = $stmtPB->fetch(PDO::FETCH_ASSOC);

  if ($pb) {
    $cmv = ($pb['custo'] / $pb['preco']) * 100;
    $pratos[] = [
      'nome' => $ficha['nome_prato'],
      'grupo' => $pb['Grupo'],
      'custo' => (float)$pb['custo'],
      'preco' => (float)$pb['preco'],
      'cmv' => $cmv
    ];
    $totalCusto += $pb['custo'];
    $totalPreco += $pb['preco'];
  }
}

$kpis = [
  'total' => count($pratos),
  'custo' => $totalCusto / max(1, count($pratos)),
  'preco' => $totalPreco / max(1, count($pratos)),
  'cmv' => ($totalCusto / max(1, $totalPreco)) * 100
];

// Configuração global de tema escuro
$themeDark = [
  'background' => '#1f2937',
  'foreColor' => '#f3f4f6'
];

// Chart de barras (CMV por prato)
$chartCmv = [
  'chart' => array_merge(['type' => 'bar'], $themeDark),
  'series' => [[
    'name' => 'CMV (%)',
    'data' => array_map(fn($p) => round($p['cmv'], 1), $pratos)
  ]],
  'xaxis' => ['categories' => array_column($pratos, 'nome')],
  'colors' => ['#f59e0b']

];

// Chart de pizza (por grupo)
$grupos = [];
foreach ($pratos as $p) {
  $grupos[$p['grupo']] = ($grupos[$p['grupo']] ?? 0) + 1;
}
$chartGrupo = [
  'chart' => array_merge(['type' => 'donut'], $themeDark),
  'labels' => array_keys($grupos),
  'series' => array_values($grupos),
  'colors' => ['#facc15', '#f59e0b', '#eab308', '#d97706']
];

echo json_encode([
  'kpis' => $kpis,
  'chartCmv' => $chartCmv,
  'chartGrupo' => $chartGrupo,
  'tabela' => $pratos
]);
