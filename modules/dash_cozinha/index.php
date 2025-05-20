<?php include '../../sidebar.php'; ?>
<?php include '../../config/db.php'; ?>
<?php include '../../config/db_dw.php'; ?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard da Cozinha</title>
  <link rel="stylesheet" href="../../assets/style.css">
  <script src="https://cdn.jsdelivr.net/npm/apexcharts"></script>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
</head>
<body class="bg-gray-900 text-white">
<main class="p-4 md:ml-64">
  <h1 class="text-2xl font-bold mb-4">Dashboard da Cozinha</h1>

  <!-- KPIs -->
  <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
    <div class="card1 text-center">
      <p>Total de Pratos</p>
      <p id="kpi-total">--</p>
    </div>
    <div class="card1 text-center">
      <p>Custo Médio</p>
      <p id="kpi-custo">--</p>
    </div>
    <div class="card1 text-center">
      <p>Preço Médio</p>
      <p id="kpi-preco">--</p>
    </div>
    <div class="card1 text-center">
      <p>CMV Médio</p>
      <p id="kpi-cmv">--</p>
    </div>
  </div>

  <hr class="divider_yellow">

  <!-- Gráficos lado a lado -->
  <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mt-6">
    <!-- Gráfico CMV por prato -->
    <div>
      <h2 class="text-xl font-semibold mb-2">CMV por Prato</h2>
      <div id="chart-cmv" class="rounded-xl p-4"></div>
    </div>

    <!-- Gráfico de pizza por grupo -->
    <div>
      <h2 class="text-xl font-semibold mb-2">Distribuição por Grupo</h2>
      <div id="chart-grupo" class="rounded-xl p-4"></div>
    </div>
  </div>

  <!-- Tabela de pratos -->
  <div class="mt-6">
    <h2 class="text-xl font-semibold mb-2">Detalhamento</h2>
    <div class="overflow-x-auto">
      <table id="tabela-sortable" class="min-w-full text-sm text-left cursor-pointer">
        <thead>
          <tr class="bg-yellow-600 text-white">
            <th class="p-2" onclick="sortTable(0)">Prato</th>
            <th class="p-2" onclick="sortTable(1)">Grupo</th>
            <th class="p-2" onclick="sortTable(2)">Custo</th>
            <th class="p-2" onclick="sortTable(3)">Preço</th>
            <th class="p-2" onclick="sortTable(4)">CMV (%)</th>
            <th class="p-2" onclick="sortTable(5)">Margem (R$)</th>
            <th class="p-2" onclick="sortTable(6)">Margem (%)</th>
          </tr>
        </thead>
        <tbody id="tabela-pratos"></tbody>
      </table>
    </div>
  </div>
</main>

<script>
  async function carregarDashboard() {
    const res = await fetch('dash_data.php');
    const data = await res.json();

    document.getElementById('kpi-total').textContent = data.kpis.total;
    document.getElementById('kpi-custo').textContent = 'R$ ' + data.kpis.custo.toFixed(2);
    document.getElementById('kpi-preco').textContent = 'R$ ' + data.kpis.preco.toFixed(2);
    document.getElementById('kpi-cmv').textContent = data.kpis.cmv.toFixed(1) + '%';

    Apex.chart = {
      ...Apex.chart,
      background: '#1f2937',
      foreColor: '#f3f4f6'
    };

    new ApexCharts(document.querySelector("#chart-cmv"), data.chartCmv).render();
    new ApexCharts(document.querySelector("#chart-grupo"), data.chartGrupo).render();

    const tabela = document.getElementById('tabela-pratos');
    tabela.innerHTML = data.tabela.map(p => {
      const margemReais = p.preco - p.custo;
      const margemPct = (margemReais / p.preco) * 100;
      return `
        <tr class="border-b border-gray-700">
          <td class="p-2">${p.nome}</td>
          <td class="p-2">${p.grupo}</td>
          <td class="p-2">R$ ${p.custo.toFixed(2)}</td>
          <td class="p-2">R$ ${p.preco.toFixed(2)}</td>
          <td class="p-2">${p.cmv.toFixed(1)}%</td>
          <td class="p-2">R$ ${margemReais.toFixed(2)}</td>
          <td class="p-2">${margemPct.toFixed(1)}%</td>
        </tr>
      `;
    }).join('');
  }

  function sortTable(n) {
    const table = document.getElementById("tabela-sortable");
    let switching = true;
    let dir = "asc";
    let switchcount = 0;

    while (switching) {
      switching = false;
      const rows = table.rows;
      for (let i = 1; i < (rows.length - 1); i++) {
        let shouldSwitch = false;
        const x = rows[i].getElementsByTagName("TD")[n];
        const y = rows[i + 1].getElementsByTagName("TD")[n];
        const xVal = parseFloat(x.innerText.replace(/[R$%]/g, '')) || x.innerText;
        const yVal = parseFloat(y.innerText.replace(/[R$%]/g, '')) || y.innerText;

        if ((dir == "asc" && xVal > yVal) || (dir == "desc" && xVal < yVal)) {
          shouldSwitch = true;
          break;
        }
      }
      if (shouldSwitch) {
        rows[i].parentNode.insertBefore(rows[i + 1], rows[i]);
        switching = true;
        switchcount++;
      } else {
        if (switchcount == 0 && dir == "asc") {
          dir = "desc";
          switching = true;
        }
      }
    }
  }

  carregarDashboard();
</script>
</body>
</html>
