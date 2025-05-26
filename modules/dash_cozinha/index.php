<?php
include __DIR__ . '/../../sidebar.php';
include __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../config/db_dw.php';
?>
<!DOCTYPE html>
<html lang="pt-br">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard da Cozinha</title>

  <link rel="stylesheet" href="../../assets/css/style.css">
  <link rel="icon" href="/assets/favicon.ico">
  <script src="https://cdn.tailwindcss.com"></script>

  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>

<body class="bg-gray-900 text-white">
<main class="p-4 md:ml-64">
  <h1 class="text-2xl font-bold mb-4">Dashboard da Cozinha</h1>

  <!-- KPIs -->
  <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-5">
    <div class="card1 text-center"><p>Total de Pratos</p><p id="kpi-total">--</p></div>
    <div class="card1 text-center"><p>Custo Médio</p><p id="kpi-custo">--</p></div>
    <div class="card1 text-center"><p>Preço Médio</p><p id="kpi-preco">--</p></div>
    <div class="card1 text-center"><p>CMV Médio</p><p id="kpi-cmv">--</p></div>
    <div class="card1 text-center"><p>Margem Média (%)</p><p id="kpi-margem">--</p></div>
  </div>

  <!-- Gráficos -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
    <div>
      <h2 class="text-xl font-semibold mb-2">CMV por Prato</h2>
      <div id="chart-cmv" class="rounded-xl p-4"></div>
    </div>
    <div>
      <h2 class="text-xl font-semibold mb-2">Distribuição por Grupo</h2>
      <div id="chart-grupo" class="rounded-xl p-4"></div>
    </div>
  </div>

  <!-- Tabela -->
  <div class="mt-6">
    <h2 class="text-xl font-semibold mb-2">Detalhamento</h2>
    <div class="overflow-x-auto">
      <table id="tabela-sortable" class="min-w-full text-sm text-left">
        <thead>
          <tr class="bg-yellow-600 text-white">
            <th class="p-2 cursor-pointer" onclick="sortTable(0)" scope="col">Prato</th>
            <th class="p-2 cursor-pointer" onclick="sortTable(1)" scope="col">Grupo</th>
            <th class="p-2 cursor-pointer" onclick="sortTable(2)" scope="col">Custo</th>
            <th class="p-2 cursor-pointer" onclick="sortTable(3)" scope="col">Preço</th>
            <th class="p-2 cursor-pointer" onclick="sortTable(4)" scope="col">CMV&nbsp;(%)</th>
            <th class="p-2 cursor-pointer" onclick="sortTable(5)" scope="col">Margem&nbsp;(R$)</th>
            <th class="p-2 cursor-pointer" onclick="sortTable(6)" scope="col">Margem&nbsp;(%)</th>
          </tr>
        </thead>
        <tbody id="tabela-pratos" data-sort-dir="asc"></tbody>
      </table>
    </div>
  </div>
</main>

<script>
async function carregarDashboard() {
  try {
    const res  = await fetch('dash_data.php');
    const data = await res.json();

    /* KPIs ------------------------------------------------------------ */
    document.getElementById('kpi-total').textContent = data.kpis.total ?? '--';
    document.getElementById('kpi-custo').textContent = `R$ ${(data.kpis.custo ?? 0).toFixed(2)}`;
    document.getElementById('kpi-preco').textContent = `R$ ${(data.kpis.preco ?? 0).toFixed(2)}`;
    document.getElementById('kpi-cmv').textContent   = `${(data.kpis.cmv   ?? 0).toFixed(1)}%`;
    /* margem média calculada aqui mesmo */
    const margemMedia = 100 - (data.kpis.cmv ?? 0);
    document.getElementById('kpi-margem').textContent = `${margemMedia.toFixed(1)}%`;

    /* Gráficos -------------------------------------------------------- */
    new ApexCharts(document.querySelector('#chart-cmv'),   data.chartCmv  ).render();
    new ApexCharts(document.querySelector('#chart-grupo'), data.chartGrupo).render();

    /* Tabela ---------------------------------------------------------- */
    const tbody = document.getElementById('tabela-pratos');
    tbody.innerHTML = data.tabela.map(p => {
      const margemR = p.preco - p.custo;
      const margemP = (margemR / p.preco) * 100;
      return `
        <tr class="border-b border-gray-700 hover:bg-gray-800">
          <td class="p-2">${p.nome}</td>
          <td class="p-2">${p.grupo}</td>
          <td class="p-2">R$ ${p.custo.toFixed(2)}</td>
          <td class="p-2">R$ ${p.preco.toFixed(2)}</td>
          <td class="p-2">${p.cmv.toFixed(1)}%</td>
          <td class="p-2">R$ ${margemR.toFixed(2)}</td>
          <td class="p-2">${margemP.toFixed(1)}%</td>
        </tr>`;
    }).join('');
  } catch (e) {
    console.error('Erro ao carregar dashboard:', e);
  }
}

/* Ordenação ---------------------------------------------------------- */
function sortTable(col) {
  const table = document.getElementById('tabela-sortable');
  let dir = table.tBodies[0].getAttribute('data-sort-dir') === 'asc' ? 'asc' : 'desc';
  let switching = true;

  while (switching) {
    switching = false;
    const rows = table.rows;
    for (let i = 1; i < rows.length - 1; i++) {
      const xText = rows[i].cells[col].textContent;
      const yText = rows[i + 1].cells[col].textContent;
      const xVal  = parseFloat(xText.replace(/[R$%]/g, '').replace(',', '.')) || xText.toLowerCase();
      const yVal  = parseFloat(yText.replace(/[R$%]/g, '').replace(',', '.')) || yText.toLowerCase();
      const mustSwitch = dir === 'asc' ? xVal > yVal : xVal < yVal;
      if (mustSwitch) {
        rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
        switching = true;
        break;
      }
    }
    if (!switching) dir = dir === 'asc' ? 'desc' : 'asc';
  }
  table.tBodies[0].setAttribute('data-sort-dir', dir);
}

carregarDashboard();
</script>
</body>
</html>