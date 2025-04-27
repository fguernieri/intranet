<?php
/**
 * Módulo dash_comercial_v1.2.php
 *
 * Dashboard Comercial v1.2 com ApexCharts:
 * - Filtros: Vendedores (Todos), Intervalo de Datas (mês atual padrão)
 * - KPIs: Total Faturado, Total de Pedidos, Clientes Únicos, Estados com Pedido
 * - Gráficos: Total por Vendedor, Forma de Pagamento, Faturamento por Data,
 *   Pedidos por Dia, Clientes por Vendedor, Pedidos por Estado
 */
declare(strict_types=1);

// Autenticação e sessão
require_once __DIR__ . '/../../auth.php';

// 1) Permissões e nomes de vendedores permitidos
$permissoes = $_SESSION['vendedores_permitidos'] ?? [];
$nomesPermitidos = [];
if (!empty($permissoes)) {
    $ph = implode(',', array_fill(0, count($permissoes), '?'));
    $stmt = $pdo->prepare("SELECT nome FROM vendedores WHERE id IN ($ph)");
    $stmt->execute($permissoes);
    $nomesPermitidos = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// 2) Carregar JSON de pedidos
$jsonPath = __DIR__ . '/pedidos_hrx.json';
if (!file_exists($jsonPath)) {
    die('Arquivo de pedidos não encontrado.');
}
$pedidos = json_decode(file_get_contents($jsonPath), true) ?: [];

// 3) Filtros via GET ou padrão
$selectedVendedores = $_GET['vendedores'] ?? ['ALL'];
if (!is_array($selectedVendedores)) {
    $selectedVendedores = [$selectedVendedores];
}
$startDate = $_GET['start_date'] ?? date('Y-m-01'); // primeiro dia do mês atual
$endDate   = $_GET['end_date']   ?? date('Y-m-t'); // último dia do mês atual

// 4) Filtrar dados
$dadosFiltrados = array_filter($pedidos, function($p) use ($nomesPermitidos, $selectedVendedores, $startDate, $endDate) {
    // Apenas vendedores permitidos
    if (!in_array($p['Vendedor'], $nomesPermitidos, true)) return false;
    // Se não selecionou ALL, filtra pelo vendedor
    if (!in_array('ALL', $selectedVendedores, true)
        && !in_array($p['Vendedor'], $selectedVendedores, true)) {
        return false;
    }
    // Data do pedido
    $dia = date('Y-m-d', (int)$p['Data Pedido'] / 1000);
    if ($dia < $startDate || $dia > $endDate) return false;
    return true;
});

// 5) Calcular KPIs
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

// 6) Agrupamentos para gráficos
$porVendedor     = [];
$porPagamento    = [];
$porData         = [];
$pedidosPorDia   = [];
$clientesPorV    = [];
$pedidosPorEstado= [];
foreach ($dadosFiltrados as $p) {
    $ven = $p['Vendedor'] ?: 'N/A';
    $fp  = $p['Forma Pagamento'] ?: 'N/A';
    $val = (float)$p['R$ Faturado'];
    $d   = date('Y-m-d', (int)$p['Data Pedido'] / 1000);
    $est = $p['Estado'] ?: 'N/A';
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
// Converter clientesPorV para contagem
$clientesCount = array_map('count', $clientesPorV);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard Comercial v1.2</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
</head>
<body class="bg-gray-100 text-gray-900">
  <div class="flex h-screen">
    <!-- Sidebar -->
    <?php include __DIR__ . '/../../sidebar.php'; ?>

    <main class="flex-1 p-6 overflow-auto">
      <!-- Boas-vindas e Título -->
      <h1 class="text-2xl sm:text-3xl font-bold mb-2">Bem-vindo, <?= htmlspecialchars($_SESSION['usuario_nome']); ?></h1>
      <h2 class="text-xl font-semibold mb-6">Dashboard Comercial v1.2 (ApexCharts)</h2>

      <!-- Filtros -->
      <form method="get" class="bg-white rounded-lg shadow p-4 mb-8 grid grid-cols-1 md:grid-cols-3 gap-4">
        <!-- Vendedores -->
        <div>
          <label class="block mb-1 font-medium">🧑‍💼 Vendedores</label>
          <select name="vendedores[]" multiple class="w-full border rounded p-2 h-24 overflow-auto">
            <option value="ALL" <?= in_array('ALL', $selectedVendedores, true) ? 'selected' : '' ?>>Todos</option>
            <?php foreach ($nomesPermitidos as $n): ?>
              <option value="<?= htmlspecialchars($n) ?>" <?= in_array($n, $selectedVendedores, true) ? 'selected' : '' ?>><?= htmlspecialchars($n) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <!-- Data Início -->
        <div>
          <label class="block mb-1 font-medium">📆 Data Início</label>
          <input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="w-full border rounded p-2" />
        </div>
        <!-- Data Fim -->
        <div>
          <label class="block mb-1 font-medium">📆 Data Fim</label>
          <input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="w-full border rounded p-2" />
        </div>
        <!-- Botão -->
        <div class="md:col-span-3 text-right">
          <button type="submit" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">Aplicar Filtros</button>
        </div>
      </form>

      <!-- KPIs Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white rounded-lg shadow p-5">
          <p class="text-sm text-gray-500">💵 Total Faturado</p>
          <p class="text-2xl font-bold text-blue-600">R$ <?= number_format($totalFaturado, 2, ',', '.') ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
          <p class="text-sm text-gray-500">📦 Total de Pedidos</p>
          <p class="text-2xl font-bold text-green-600"><?= $totalPedidos ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
          <p class="text-sm text-gray-500">👥 Clientes Únicos</p>
          <p class="text-2xl font-bold text-purple-600"><?= $totalClientes ?></p>
        </div>
        <div class="bg-white rounded-lg shadow p-5">
          <p class="text-sm text-gray-500">🌎 Estados com Pedido</p>
          <p class="text-2xl font-bold text-yellow-600"><?= $totalEstados ?></p>
        </div>
      </div>

      <!-- Gráficos ApexCharts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <div class="bg-white p-5 rounded-lg shadow">
          <p class="font-medium mb-2">Total por Vendedor</p>
          <div id="chartVendedor"></div>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
          <p class="font-medium mb-2">Total por Forma de Pagamento</p>
          <div id="chartPagamento"></div>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
          <p class="font-medium mb-2">Faturamento por Data</p>
          <div id="chartData"></div>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
          <p class="font-medium mb-2">Pedidos por Dia</p>
          <div id="chartPedidosDia"></div>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
          <p class="font-medium mb-2">Clientes Únicos por Vendedor</p>
          <div id="chartClientes"></div>
        </div>
        <div class="bg-white p-5 rounded-lg shadow">
          <p class="font-medium mb-2">Pedidos por Estado</p>
          <div id="chartEstado"></div>
        </div>
      </div>
    </main>
  </div>

  <script>
    // Preparar dados JS a partir de PHP
    const categoriesVendedor  = <?= json_encode(array_keys($porVendedor)) ?>;
    const dataVendedor        = <?= json_encode(array_values($porVendedor)) ?>;
    const categoriesPagamento = <?= json_encode(array_keys($porPagamento)) ?>;
    const dataPagamento       = <?= json_encode(array_values($porPagamento)) ?>;
    const categoriesData      = <?= json_encode(array_keys($porData)) ?>;
    const dataData            = <?= json_encode(array_values($porData)) ?>;
    const categoriesDia       = <?= json_encode(array_keys($pedidosPorDia)) ?>;
    const dataDia             = <?= json_encode(array_values($pedidosPorDia)) ?>;
    const categoriesClientes  = <?= json_encode(array_keys($clientesCount)) ?>;
    const dataClientes        = <?= json_encode(array_values($clientesCount)) ?>;
    const categoriesEstado    = <?= json_encode(array_keys($pedidosPorEstado)) ?>;
    const dataEstado          = <?= json_encode(array_values($pedidosPorEstado)) ?>;

    function renderApex(selector, options) {
      const chart = new ApexCharts(document.querySelector(selector), options);
      chart.render();
    }

    window.addEventListener('load', () => {
      renderApex('#chartVendedor', {
        chart: { type: 'bar', height: 300 },
        series: [{ name: 'Total por Vendedor', data: dataVendedor }],
        xaxis: { categories: categoriesVendedor }
      });

      renderApex('#chartPagamento', {
        chart: { type: 'donut', height: 300 },
        series: dataPagamento,
        labels: categoriesPagamento
      });

      renderApex('#chartData', {
        chart: { type: 'line', height: 300 },
        series: [{ name: 'Faturamento', data: dataData }],
        xaxis: { categories: categoriesData }
      });

      renderApex('#chartPedidosDia', {
        chart: { type: 'bar', height: 300 },
        series: [{ name: 'Pedidos por Dia', data: dataDia }],
        xaxis: { categories: categoriesDia }
      });

      renderApex('#chartClientes', {
        chart: { type: 'bar', height: 300 },
        series: [{ name: 'Clientes Únicos', data: dataClientes }],
        xaxis: { categories: categoriesClientes }
      });

      renderApex('#chartEstado', {
        chart: { type: 'bar', height: 300 },
        series: [{ name: 'Pedidos por Estado', data: dataEstado }],
        xaxis: { categories: categoriesEstado }
      });
    });
  </script>
</body>
</html>
