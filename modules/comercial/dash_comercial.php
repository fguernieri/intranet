<?php
declare(strict_types=1);

// 1) Autenticação e sessão — carrega o $pdo principal
require_once __DIR__ . '/../../auth.php';
$pdoMain = $pdo; // conexão principal (intranet)

// 2) Conexão DW — para dados de pedidos
require_once '../../config/db_dw.php';

// 3) Permissões de vendedores vindas da sessão
$permissoes = $_SESSION['vendedores_permitidos'] ?? [];

// 4) Busca nomes desses vendedores (para select e filtro)
$nomesPermitidos = [];
if (!empty($permissoes)) {
    $ph = implode(',', array_fill(0, count($permissoes), '?'));
    $stmt = $pdoMain->prepare("SELECT nome FROM vendedores WHERE id IN ($ph)");
    $stmt->execute($permissoes);
    $nomesPermitidos = $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// 5) Parâmetros de filtro vindos da UI (vendedores e datas)
$selectVendedores = $_GET['vendedores'] ?? ['ALL'];
$selectVendedores = is_array($selectVendedores)
    ? $selectVendedores
    : [$selectVendedores];

$startDate = $_GET['start_date'] ?? date('Y-m-01');
$endDate   = $_GET['end_date']   ?? date('Y-m-t');

// 6) Define quais vendedores realmente filtrar
if (!in_array('ALL', $selectVendedores, true)) {
    $filteredVend = array_values(
        array_intersect($nomesPermitidos, $selectVendedores)
    );
} else {
    $filteredVend = $nomesPermitidos;
}

// 7) Monta cláusulas SQL dinâmicas e parâmetros
$whereClauses = [];
$queryParams = [];

if (!empty($filteredVend)) {
    $ph2 = implode(',', array_fill(0, count($filteredVend), '?'));
    $whereClauses[] = "Vendedor IN ($ph2)";
    $queryParams   = array_merge($queryParams, $filteredVend);
}
// filtro de data
$whereClauses[] = "DATE(DataPedido) BETWEEN ? AND ?";
$queryParams[]  = $startDate;
$queryParams[]  = $endDate;

// 8) Consulta na view/tabela DB DW
$sql = "
SELECT
  Empresa,
  NumeroPedido    AS NumeroPedido,
  CodCliente      AS CodCliente,
  Estado,
  DataPedido      AS DataPedido,
  Vendedor,
  DataFaturamento AS DataFaturamento,
  ValorFaturado   AS ValorFaturado,
  FormaPagamento  AS FormaPagamento
FROM PedidosComercial
" . ($whereClauses ? ' WHERE ' . implode(' AND ', $whereClauses) : '');

$stmt  = $pdo_dw->prepare($sql);
$stmt->execute($queryParams);
$pedidos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// 9) Totais para cards
$totalPedidos  = count($pedidos);
$totalFaturado = array_sum(array_column($pedidos, 'ValorFaturado'));
$totalEstados  = count(array_unique(array_column($pedidos, 'Estado')));

// total de clientes únicos direto do result
$allClients    = array_column($pedidos, 'CodCliente');
$allClients    = array_filter($allClients, fn($c) => $c !== null && $c !== '');
$totalClientes = count(array_unique($allClients));

// 10) Calcula datas mínima e máxima
$data_inicial = null;
$data_final   = null;
foreach ($pedidos as $p) {
    $ts = strtotime($p['DataPedido']);
    if ($data_inicial === null || $ts < $data_inicial) {
        $data_inicial = $ts;
    }
    if ($data_final === null || $ts > $data_final) {
        $data_final = $ts;
    }
}

// 11) Processa estatísticas para charts
$porVendedor      = [];
$porPagamento     = [];
$porData          = [];
$pedidosPorDia    = [];
$pedidosPorEstado = [];
$clientesPorV     = [];

foreach ($pedidos as $p) {
    $ven = $p['Vendedor'];
    $fp  = $p['FormaPagamento'] ?? 'N/A';
    $val = (float) $p['ValorFaturado'];
    $d   = substr($p['DataPedido'], 0, 10);
    $est = $p['Estado'] ?? 'N/A';
    $cli = $p['CodCliente'];

    $porVendedor[$ven]      = ($porVendedor[$ven]      ?? 0) + $val;
    $porPagamento[$fp]      = ($porPagamento[$fp]      ?? 0) + $val;
    $porData[$d]            = ($porData[$d]            ?? 0) + $val;
    $pedidosPorDia[$d]      = ($pedidosPorDia[$d]      ?? 0) + 1;
    $pedidosPorEstado[$est] = ($pedidosPorEstado[$est] ?? 0) + 1;

    if ($cli) {
        if (!in_array($cli, $clientesPorV[$ven] ?? [], true)) {
            $clientesPorV[$ven][] = $cli;
        }
    }
}
$clientesCount = array_map('count', $clientesPorV);

$sql = "SELECT MAX(data_hora) AS UltimaAtualizacao FROM fAtualizacoes";
$stmt = $pdo_dw->query($sql);
$UltimaAtualizacao = $stmt->fetchColumn();

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
      <h2 class="text-xl font-semibold mb-6 text-center text-white">Dashboard Comercial</h2>
      
      <!-- Formulário de filtros -->
      <form method="get" class="bg-gray-800 rounded-lg p-6 grid grid-cols-1 md:grid-cols-2 gap-6 mb-8 text-white">
        <div>
          <label class="block mb-2 text-sm font-semibold">🧑‍💼 Vendedores</label>
          <select name="vendedores[]" multiple class="w-full h-32 bg-gray-700 border border-gray-600 rounded-md text-sm p-2">
            <option value="ALL" <?= in_array('ALL',$selectVendedores,true)? 'selected':'' ?>>Todos</option>
            <?php foreach($nomesPermitidos as $nome): ?>
              <option value="<?= htmlspecialchars($nome) ?>" <?= in_array($nome,$selectVendedores,true)? 'selected':'' ?>><?= htmlspecialchars($nome) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
        <div class="grid-cols-2 gap-6">
          <div><label class="block mb-2 text-sm font-semibold">📅 Data Início</label><input type="date" name="start_date" value="<?= htmlspecialchars($startDate) ?>" class="w-full bg-gray-700 border border-gray-600 rounded-md p-2 text-sm"></div>
          <div><label class="block mb-2 text-sm font-semibold">📅 Data Fim</label><input type="date" name="end_date" value="<?= htmlspecialchars($endDate) ?>" class="w-full bg-gray-700 border border-gray-600 rounded-md p-2 text-sm"></div>
          <div class="p-2"><p><strong>Período disponível:</strong> <?= $data_inicial? date('d/m/Y',$data_inicial):'' ?> a <?= $data_final? date('d/m/Y',$data_final):'' ?></p></div>
          <div><p class="p-2 text-sm text-gray-400 mt-auto">Última Atualização em: <?=date('d/m/Y H:i:s', strtotime($UltimaAtualizacao))?></p></div>
          <div class="flex justify-end"><button type="submit" class="btn-acao">Aplicar Filtros</button></div>
        </div>
      </form>
      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php 
          $charts = [
            'chartVendedor' => 'Total por Vendedor',
            'chartPagamento' => 'Total por Forma de Pagamento',
            'chartData' => 'Faturamento por Data',
            'chartPedidosDia' => 'Pedidos por Dia',
            'chartClientes' => 'Clientes Únicos por Vendedor',
            'chartEstado' => 'Pedidos por Estado'
          ];

          $sortableCharts = ['chartVendedor', 'chartClientes', 'chartEstado', 'chartPedidosDia', 'chartData'];

          foreach ($charts as $id => $label): ?>
            <div class="rounded-xl bg-white/5 p-4 shadow-md">
              <div class="flex justify-between items-center mb-2">
                <p class="font-medium text-white"><?= $label ?></p>
                <?php if (in_array($id, $sortableCharts)): ?>
                  <select 
                    data-target="<?= $id ?>" 
                    class="sort-dropdown bg-gray-800 text-white text-xs rounded px-2 py-1 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                    <option value="default">Original</option>
                    <option value="asc">A-Z</option>
                    <option value="desc">Z-A</option>
                    <option value="value_asc">↑ Valor</option>
                    <option value="value_desc">↓ Valor</option>
                  </select>
                <?php endif; ?>
              </div>
              <div id="<?= $id ?>"></div>
            </div>
        <?php endforeach; ?>
      </div>
      <script>
        // 💸 Arredondamento para valores inteiros
        const arredondar = arr => arr.map(v => parseFloat(parseFloat(v).toFixed(0)));

        // 📊 Mapa com dados dos gráficos categóricos
        const chartDataMap = {
          chartVendedor: {
            labels: <?= json_encode(array_keys($porVendedor)) ?>.map(v => v.split(' ')[0]),
            values: arredondar(<?= json_encode(array_values($porVendedor)) ?>),
            type: 'bar'
          },
          chartClientes: {
            labels: <?= json_encode(array_keys($clientesCount)) ?>.map(v => v.split(' ')[0]),
            values: <?= json_encode(array_values($clientesCount)) ?>,
            type: 'bar'
          },
          chartEstado: {
            labels: <?= json_encode(array_keys($pedidosPorEstado)) ?>,
            values: <?= json_encode(array_values($pedidosPorEstado)) ?>,
            type: 'bar'
          },
          chartPedidosDia: {
            labels: <?= json_encode(array_keys($pedidosPorDia)) ?>,
            values: <?= json_encode(array_values($pedidosPorDia)) ?>,
            type: 'bar'
          },
          chartData: {
            labels: <?= json_encode(array_keys($porData)) ?>,
            values: arredondar(<?= json_encode(array_values($porData)) ?>),
            type: 'line'
          }
        };

        // 🎯 Ordenação padrão por gráfico
        const defaultSorts = {
          chartVendedor: 'value_desc',
          chartClientes: 'value_desc',
          chartEstado: 'value_desc',
          chartPedidosDia: 'asc',
          chartData: 'asc'
        };

        // 🍩 Donut chart (forma de pagamento) - sem ordenação
        const donutChart = {
          id: 'chartPagamento',
          type: 'donut',
          labels: <?= json_encode(array_keys($porPagamento)) ?>,
          values: <?= json_encode(array_values($porPagamento)) ?>
        };

        // 📈 Renderiza qualquer gráfico
        function renderApex(selector, options) {
          const el = document.querySelector(selector);
          if (el) {
            el.innerHTML = ''; // limpar antes de re-renderizar
            new ApexCharts(el, options).render();
          }
        }

        // 🔁 Ordena e renderiza gráficos com eixo X categórico
        function sortAndRenderChart(chartId, sortBy) {
          const { labels, values, type } = chartDataMap[chartId];
          let combined = labels.map((label, i) => ({ label, value: values[i] }));

          switch (sortBy) {
            case 'asc':
              combined.sort((a, b) => a.label.localeCompare(b.label));
              break;
            case 'desc':
              combined.sort((a, b) => b.label.localeCompare(a.label));
              break;
            case 'value_asc':
              combined.sort((a, b) => a.value - b.value);
              break;
            case 'value_desc':
              combined.sort((a, b) => b.value - a.value);
              break;
            default:
              // ordem original
              combined = labels.map((label, i) => ({ label, value: values[i] }));
          }

          const sortedLabels = combined.map(x => x.label);
          const sortedValues = combined.map(x => x.value);

          renderApex(`#${chartId}`, {
            chart: { type, height: 300, background: 'transparent' },
            theme: { mode: 'dark' },
            series: [{ name: 'Valor', data: sortedValues }],
            xaxis: { categories: sortedLabels },
            tooltip: {
              y: {
                formatter: val => new Intl.NumberFormat('pt-BR', {
                  style: 'currency',
                  currency: 'BRL'
                }).format(val)
              }
            }
          });
        }

        // 🚀 Inicializa gráficos ao carregar a página
        window.addEventListener('load', () => {
          // Gráficos com ordenação dinâmica
          Object.keys(chartDataMap).forEach(chartId => {
            const defaultSort = defaultSorts[chartId] || 'default';
            sortAndRenderChart(chartId, defaultSort);

            // Atualiza o <select> com o valor padrão
            const dropdown = document.querySelector(`.sort-dropdown[data-target="${chartId}"]`);
            if (dropdown) dropdown.value = defaultSort;
          });

          // Donut chart
          renderApex(`#${donutChart.id}`, {
            chart: { type: 'donut', height: 300, background: 'transparent' },
            theme: { mode: 'dark' },
            series: donutChart.values,
            labels: donutChart.labels,
            tooltip: {
              y: {
                formatter: val => new Intl.NumberFormat('pt-BR', {
                  style: 'currency',
                  currency: 'BRL'
                }).format(val)
              }
            }
          });
        });

        // 🎯 Escuta mudanças em todos os dropdowns
        document.addEventListener('DOMContentLoaded', () => {
          document.querySelectorAll('.sort-dropdown').forEach(dropdown => {
            dropdown.addEventListener('change', e => {
              const chartId = e.target.dataset.target;
              const sortBy = e.target.value;
              sortAndRenderChart(chartId, sortBy);
            });
          });
        });
    </script>

</body>
</html>