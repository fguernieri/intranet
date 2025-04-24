<?php
declare(strict_types=1);
require_once __DIR__ . '/../../auth.php';

$permissoes = $_SESSION['vendedores_permitidos'] ?? [];
$nomesPermitidos = [];

if (!empty($permissoes)) {
    $ph = implode(',', array_fill(0, count($permissoes), '?'));
    $stmt = $pdo->prepare("SELECT nome FROM vendedores WHERE id IN ($ph)");
    $stmt->execute($permissoes);
    $nomesPermitidos = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

$jsonPath = __DIR__ . '/pedidos_hrx.json';
if (!file_exists($jsonPath)) die('Arquivo de pedidos não encontrado.');
$pedidos = json_decode(file_get_contents($jsonPath), true) ?: [];

$selectedVendedores = $_GET['vendedores'] ?? ['ALL'];
$selectedVendedores = is_array($selectedVendedores) ? $selectedVendedores : [$selectedVendedores];
$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date']   ?? date('Y-m-t');

$dadosFiltrados = array_filter($pedidos, function($p) use ($nomesPermitidos, $selectedVendedores, $startDate, $endDate) {
    if (!in_array($p['Vendedor'], $nomesPermitidos, true)) return false;
    if (!in_array('ALL', $selectedVendedores, true) && !in_array($p['Vendedor'], $selectedVendedores, true)) return false;
    $dia = date('Y-m-d', (int)$p['Data Pedido'] / 1000);
    return ($dia >= $startDate && $dia <= $endDate);
});

$totalFaturado = 0;
$clientes = [];
$estados = [];
foreach ($dadosFiltrados as $p) {
    $valor = (float)$p['R$ Faturado'];
    $totalFaturado += $valor;
    if (!empty($p['Cód Cliente'])) $clientes[$p['Cód Cliente']] = true;
    if (!empty($p['Estado']))     $estados[$p['Estado']] = true;
}
$totalPedidos  = count($dadosFiltrados);
$totalClientes = count($clientes);
$totalEstados  = count($estados);

$porVendedor = $porPagamento = $porData = $pedidosPorDia = $clientesPorV = $pedidosPorEstado = [];
foreach ($dadosFiltrados as $p) {
    $ven = $p['Vendedor'] ?? 'N/A';
    $fp  = $p['Forma Pagamento'] ?? 'N/A';
    $val = (float)$p['R$ Faturado'];
    $d   = date('Y-m-d', (int)$p['Data Pedido'] / 1000);
    $est = $p['Estado'] ?? 'N/A';
    $cli = $p['Cód Cliente'];

    $porVendedor[$ven]     = ($porVendedor[$ven] ?? 0) + $val;
    $porPagamento[$fp]     = ($porPagamento[$fp] ?? 0) + $val;
    $porData[$d]           = ($porData[$d] ?? 0) + $val;
    $pedidosPorDia[$d]     = ($pedidosPorDia[$d] ?? 0) + 1;
    $pedidosPorEstado[$est]= ($pedidosPorEstado[$est] ?? 0) + 1;

    if ($cli) {
        $clientesPorV[$ven] = $clientesPorV[$ven] ?? [];
        if (!in_array($cli, $clientesPorV[$ven], true)) {
            $clientesPorV[$ven][] = $cli;
        }
    }
}
$clientesCount = array_map('count', $clientesPorV);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Comercial v1.2</title>
  <link rel="stylesheet" href="../../assets/css/style.css" />
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="body bg-gray-900 text-white">
  <div class="flex h-screen">
    <?php include __DIR__ . '/../../sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-auto">
      <h1 class="text-3xl font-bold mb-2 text-yellow-400 text-center">Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']); ?></h1>
      <h2 class="text-xl font-semibold mb-6 text-center text-white">Dashboard Comercial v1.2 (ApexCharts)</h2>

      <form method="get" class="bg-gray-800 rounded-lg p-6 grid grid-cols-1 md:grid-cols-4 gap-6 mb-8 text-white">
        <!-- Vendedores -->
        <div>
          <label class="block mb-2 text-sm font-semibold">🧑‍💼 Vendedores</label>
          <select name="vendedores[]" multiple class="w-full h-32 bg-gray-700 border border-gray-600 rounded-md text-sm p-2">
            <option value="ALL" <?= in_array('ALL', $selectedVendedores, true) ? 'selected' : '' ?>>Todos</option>
            <?php foreach ($nomesPermitidos as $n): ?>
              <option value="<?= htmlspecialchars($n) ?>" <?= in_array($n, $selectedVendedores, true) ? 'selected' : '' ?>>
                <?= htmlspecialchars($n) ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>

        <!-- Data Início -->
        <div>
          <label class="block mb-2 text-sm font-semibold">📅 Data Início</label>
          <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="w-full bg-gray-700 border border-gray-600 rounded-md p-2 text-sm">
        </div>

        <!-- Data Fim -->
        <div>
          <label class="block mb-2 text-sm font-semibold">📅 Data Fim</label>
          <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="w-full bg-gray-700 border border-gray-600 rounded-md p-2 text-sm">
        </div>

        <!-- Botão -->
        <div class="flex items-end justify-end">
          <button type="submit" class="btn-acao">Aplicar Filtros</button>
        </div>
      </form>


      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="card1">
          <p>💵 Total Faturado</p>
          <p>R$ <?= number_format($totalFaturado, 2, ',', '.') ?></p>
        </div>
        <div class="card1">
          <p> 📦 Total de Pedidos</p>
          <p> <?= $totalPedidos ?></p>
        </div>
        <div class="card1">
          <p>🏪 Clientes Únicos</p>
          <p><?= $totalClientes ?></p>
        </div>
        <div class="card1">
          <p>🌎 Estados com Pedido</p>
          <p><?= $totalEstados ?></p>
        </div>
      </div>

      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php
        $charts = [
          'chartVendedor' => 'Total por Vendedor',
          'chartPagamento' => 'Total por Forma de Pagamento',
          'chartData' => 'Faturamento por Data',
          'chartPedidosDia' => 'Pedidos por Dia',
          'chartClientes' => 'Clientes Únicos por Vendedor',
          'chartEstado' => 'Pedidos por Estado',
        ];
        foreach ($charts as $id => $label): ?>
          <div class="rounded-xl bg-white/5 p-4 shadow-md">
            <p class="font-medium mb-2"><?= $label ?></p>
            <div id="<?= $id ?>"></div>
          </div>
        <?php endforeach; ?>
      </div>
    </main>
  </div>

  <script>
    const arredondar = arr => arr.map(v => parseFloat(parseFloat(v).toFixed(2)));
    const categoriesVendedor  = <?= json_encode(array_keys($porVendedor)) ?>.map(Vendedor => Vendedor.split(' ')[0]);
    const dataVendedor        = <?= json_encode(array_values($porVendedor)) ?>;
    const categoriesPagamento = <?= json_encode(array_keys($porPagamento)) ?>;
    const dataPagamento       = <?= json_encode(array_values($porPagamento)) ?>;
    const categoriesData      = <?= json_encode(array_keys($porData)) ?>;
    const dataData            = arredondar(<?= json_encode(array_values($porData)) ?>);
    const categoriesDia       = <?= json_encode(array_keys($pedidosPorDia)) ?>;
    const dataDia             = <?= json_encode(array_values($pedidosPorDia)) ?>;
    const categoriesClientes  = <?= json_encode(array_keys($clientesCount)) ?>.map(Vendedor => Vendedor.split(' ')[0]);
    const dataClientes        = <?= json_encode(array_values($clientesCount)) ?>;
    const categoriesEstado    = <?= json_encode(array_keys($pedidosPorEstado)) ?>;
    const dataEstado          = <?= json_encode(array_values($pedidosPorEstado)) ?>;
    
    function renderApex(selector, options) {
      const chart = new ApexCharts(document.querySelector(selector), options);
      chart.render();
    }
    
  
    window.addEventListener('load', () => {
      renderApex('#chartVendedor', {
        chart: { type: 'bar', height: 300, background: 'transparent'},
        theme: { mode: 'dark'},
        series: [{ name: 'Total por Vendedor', data: dataVendedor }],
        xaxis: { categories: categoriesVendedor }
      });
      renderApex('#chartPagamento', {
        chart: { type: 'donut', height: 300, background: 'transparent' },
        theme: { mode: 'dark'},
        series: dataPagamento,
        labels: categoriesPagamento
      });
      renderApex('#chartData', {
        chart: { type: 'line', height: 300, background: 'transparent' },
        theme: { mode: 'dark'},
        series: [{ name: 'Faturamento', data: dataData }],
        xaxis: { categories: categoriesData }
      });
      renderApex('#chartPedidosDia', {
        chart: { type: 'bar', height: 300, background: 'transparent' },
        theme: { mode: 'dark'},
        series: [{ name: 'Pedidos por Dia', data: dataDia }],
        xaxis: { categories: categoriesDia }
      });
      renderApex('#chartClientes', {
        chart: { type: 'bar', height: 300, background: 'transparent' },
        theme: { mode: 'dark'},
        series: [{ name: 'Clientes Únicos', data: dataClientes }],
        xaxis: { categories: categoriesClientes }
      });
      renderApex('#chartEstado', {
        chart: { type: 'bar', height: 300, background: 'transparent' },
        theme: { mode: 'dark'},
        series: [{ name: 'Pedidos por Estado', data: dataEstado }],
        xaxis: { categories: categoriesEstado }
      });
    });
  </script>
</body>
</html>
