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
          <div class="mb-4 p-2"><p><strong>Período disponível:</strong> <?= $data_inicial? date('d/m/Y',$data_inicial):'' ?> a <?= $data_final? date('d/m/Y',$data_final):'' ?></p></div>
          <div class="flex justify-end"><button type="submit" class="btn-acao">Aplicar Filtros</button></div>
        </div>
      </form>
      <!-- Cards de resumo -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
              <div class="card1"><p>💵 Total Faturado</p><p>R$ <?= number_format($totalFaturado,2,',','.') ?></p></div>
              <div class="card1"><p>📦 Total de Pedidos</p><p><?= $totalPedidos ?></p></div>
              <div class="card1"><p>🏪 Clientes Únicos</p><p><?= $totalClientes ?></p></div>
              <div class="card1"><p>🌎 Estados com Pedido</p><p><?= $totalEstados ?></p></div>
            </div>

      <!-- Charts -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <?php $charts=[ 'chartVendedor'=>'Total por Vendedor','chartPagamento'=>'Total por Forma de Pagamento','chartData'=>'Faturamento por Data','chartPedidosDia'=>'Pedidos por Dia','chartClientes'=>'Clientes Únicos por Vendedor','chartEstado'=>'Pedidos por Estado' ];
        foreach($charts as $id=>$label):?>
          <div class="rounded-xl bg-white/5 p-4 shadow-md"><p class="font-medium mb-2"><?= $label ?></p><div id="<?= $id ?>"></div></div>
        <?php endforeach;?>
      </div>
    </main>
  </div>
  <script>
    const arredondar=arr=>arr.map(v=>parseFloat(parseFloat(v).toFixed(2)));
    const categoriesVendedor=<?= json_encode(array_keys($porVendedor))?>.map(v=>v.split(' ')[0]);
    const dataVendedor=<?= json_encode(array_values($porVendedor))?>;
    const categoriesPagamento=<?= json_encode(array_keys($porPagamento))?>;
    const dataPagamento=<?= json_encode(array_values($porPagamento))?>;
    const categoriesData=<?= json_encode(array_keys($porData))?>;
    const dataData=arredondar(<?= json_encode(array_values($porData))?>);
    const categoriesDia=<?= json_encode(array_keys($pedidosPorDia))?>;
    const dataDia=<?= json_encode(array_values($pedidosPorDia))?>;
    const categoriesClientes=<?= json_encode(array_keys($clientesCount))?>.map(v=>v.split(' ')[0]);
    const dataClientes=<?= json_encode(array_values($clientesCount))?>;
    const categoriesEstado=<?= json_encode(array_keys($pedidosPorEstado))?>;
    const dataEstado=<?= json_encode(array_values($pedidosPorEstado))?>;
    function renderApex(s,o){new ApexCharts(document.querySelector(s),o).render();}
    window.addEventListener('load',()=>{
      renderApex('#chartVendedor',{chart:{type:'bar',height:300,background:'transparent'},theme:{mode:'dark'},series:[{name:'Total por Vendedor',data:dataVendedor}],xaxis:{categories:categoriesVendedor}});
      renderApex('#chartPagamento',{chart:{type:'donut',height:300,background:'transparent'},theme:{mode:'dark'},series:dataPagamento,labels:categoriesPagamento});
      renderApex('#chartData',{chart:{type:'line',height:300,background:'transparent'},theme:{mode:'dark'},series:[{name:'Faturamento',data:dataData}],xaxis:{categories:categoriesData}});
      renderApex('#chartPedidosDia',{chart:{type:'bar',height:300,background:'transparent'},theme:{mode:'dark'},series:[{name:'Pedidos por Dia',data:dataDia}],xaxis:{categories:categoriesDia}});
      renderApex('#chartClientes',{chart:{type:'bar',height:300,background:'transparent'},theme:{mode:'dark'},series:[{name:'Clientes Únicos',data:dataClientes}],xaxis:{categories:categoriesClientes}});
      renderApex('#chartEstado',{chart:{type:'bar',height:300,background:'transparent'},theme:{mode:'dark'},series:[{name:'Pedidos por Estado',data:dataEstado}],xaxis:{categories:categoriesEstado}});
    });
  </script>
</body>
</html>