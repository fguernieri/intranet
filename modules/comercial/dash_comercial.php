<?php
require_once '../../sidebar.php';

?>
<!DOCTYPE html>
<html lang="pt-BR" class="dark transition-colors duration-300">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Dashboard de Pedidos - HRX</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
    };
	// Carregar JSON padrão ao abrir a página
	window.addEventListener('DOMContentLoaded', async () => {
	  try {
		const response = await fetch('pedidos_hrx.json');
		if (response.ok) {
		  pedidos = await response.json();
		  renderFiltros(pedidos);
		  atualizarDashboard();
		  console.log("✅ pedidos_hrx.json carregado automaticamente.");
		} else {
		  console.warn("⚠️ Não foi possível carregar pedidos_hrx.json.");
		}
	  } catch (err) {
		console.error("🚨 Erro ao carregar JSON padrão:", err);
	  }
	});
  </script>
</head>
<body class="flex min-h-screen bg-gray-100 text-gray-900 dark:bg-slate-900 dark:text-gray-100 transition-colors duration-300">

  <!-- 🎯 Dashboard Principal -->
  <div id="dashboardApp">
    <div class="max-w-7xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
      <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold">📊 Dashboard Comercial</h1>
      </div>

      <!-- Cards KPI -->
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">💵 Total Faturado</p>
          <p id="kpiFaturamento" class="text-2xl font-bold text-blue-600 dark:text-blue-400">R$ 0,00</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">📦 Total de Pedidos</p>
          <p id="kpiPedidos" class="text-2xl font-bold text-green-600 dark:text-green-400">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">👥 Clientes Únicos</p>
          <p id="kpiClientes" class="text-2xl font-bold text-purple-600 dark:text-purple-400">0</p>
        </div>
        <div class="bg-white dark:bg-gray-800 shadow-md rounded-lg p-4">
          <p class="text-sm text-gray-500 dark:text-gray-400">🌎 Estados com Pedido</p>
          <p id="kpiEstados" class="text-2xl font-bold text-yellow-600 dark:text-yellow-400">0</p>
        </div>
      </div>

      <!-- Filtros -->
      <div class="grid md:grid-cols-3 gap-6 mb-8">
        <div>
          <label class="block mb-2 font-semibold">📁 Carregar JSON</label>
          <input type="file" id="jsonFile" accept=".json" class="w-full text-sm file:mr-4 file:py-2 file:px-4 file:border-0 file:bg-blue-600 file:text-white file:rounded-md" />
        </div>
        <div>
          <label class="block mb-2 font-semibold">🧑‍💼 Vendedores</label>
          <select id="vendedorSelect" multiple size="6" class="w-full h-32 p-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-md"></select>
        </div>
        <div>
          <label class="block mb-2 font-semibold">📆 Intervalo de Datas</label>
          <input type="date" id="startDate" class="w-full mb-2 p-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-md" />
          <input type="date" id="endDate" class="w-full p-2 border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 rounded-md" />
        </div>
      </div>

      <!-- Gráficos -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-10 mb-10">
        <div><canvas id="totalPorVendedor"></canvas></div>
        <div><canvas id="porPagamento"></canvas></div>
      </div>
      <div class="mb-10">
        <p class="text-lg font-semibold mb-1">📈 Faturamento por Data:</p>
        <span id="totalFaturamento" class="text-blue-500 dark:text-blue-300 block mb-2"></span>
        <canvas id="faturamentoPorData"></canvas>
      </div>
      <div class="mb-10">
        <p class="text-lg font-semibold mb-1">🗓️ Pedidos por Dia:</p>
        <span id="totalPedidosDia" class="text-blue-500 dark:text-blue-300 block mb-2"></span>
        <canvas id="pedidosPorDia"></canvas>
      </div>
      <div class="mb-10">
        <div class="flex justify-between items-center mb-2">
          <p class="text-lg font-semibold">👥 Clientes únicos por Vendedor:</p>
          <select id="ordemClientes" class="border rounded-md px-2 py-1 dark:bg-gray-800">
            <option value="desc">Maior → Menor</option>
            <option value="asc">Menor → Maior</option>
          </select>
        </div>
        <span id="totalClientes" class="text-blue-500 dark:text-blue-300 block mb-2"></span>
        <canvas id="clientesPorVendedor"></canvas>
      </div>
      <div class="mb-10">
        <div class="flex justify-between items-center mb-2">
          <p class="text-lg font-semibold">🗺️ Pedidos por Estado:</p>
          <select id="ordemEstados" class="border rounded-md px-2 py-1 dark:bg-gray-800">
            <option value="desc">Maior → Menor</option>
            <option value="asc">Menor → Maior</option>
          </select>
        </div>
        <span id="totalEstados" class="text-blue-500 dark:text-blue-300 block mb-2"></span>
        <canvas id="pedidosPorEstado"></canvas>
      </div>
    </div>
  </div>

  <!-- Scripts do Dashboard -->
  <script>
    // ⚙️ Inicialização
    let pedidos = [];
    const chartRefs = {};
    const colors = ['#3b82f6','#10b981','#f59e0b','#ef4444','#8b5cf6','#14b8a6','#ec4899','#eab308','#6366f1','#0ea5e9'];

    
    document.getElementById('jsonFile').addEventListener('change', async (e) => {
      const file = e.target.files[0];
      if (file) {
        const text = await file.text();
        pedidos = JSON.parse(text);
        renderFiltros(pedidos);
        atualizarDashboard();
      }
    });

    document.getElementById('vendedorSelect').addEventListener('change', atualizarDashboard);
    document.getElementById('startDate').addEventListener('change', atualizarDashboard);
    document.getElementById('endDate').addEventListener('change', atualizarDashboard);
    document.getElementById('ordemClientes').addEventListener('change', atualizarDashboard);
    document.getElementById('ordemEstados').addEventListener('change', atualizarDashboard);

    function renderFiltros(data) {
      const todosVendedores = [...new Set(data.map(p => p.Vendedor).filter(Boolean))];
      const select = document.getElementById('vendedorSelect');
      select.innerHTML = '';
      todosVendedores.forEach(v => {
        const option = document.createElement('option');
        option.value = v;
        option.text = v;
        select.appendChild(option);
      });
    }

    function getSelectedVendedores() {
      return Array.from(document.getElementById('vendedorSelect').selectedOptions).map(opt => opt.value);
    }

    function filtrarPedidos() {
      const vendedoresSelecionados = getSelectedVendedores();
      return pedidos.filter(p => {
        const dataPedido = new Date(Number(p["Data Pedido"]));
        const inicio = startDate.value ? new Date(startDate.value) : null;
        const fim = endDate.value ? new Date(endDate.value) : null;
        const dataOK = (!inicio || dataPedido >= inicio) && (!fim || dataPedido <= fim);
        const vendedorOK = vendedoresSelecionados.length === 0 || vendedoresSelecionados.includes(p.Vendedor);
        return dataOK && vendedorOK;
      });
    }

    function atualizarDashboard() {
      const data = filtrarPedidos();
      const porVendedor = {}, porPagamento = {}, porData = {};
      const pedidosPorDia = {}, clientesPorVendedor = {}, pedidosPorEstado = {};
      let totalFaturamento = 0;

      data.forEach(p => {
        const vendedor = p.Vendedor || 'N/A';
        const forma = p["Forma Pagamento"] || 'N/A';
        const valor = parseFloat(p["R$ Faturado"]);
        const dataPedido = new Date(Number(p["Data Pedido"]));
        const labelData = dataPedido.toISOString().split('T')[0];
        const estado = p.Estado || 'N/A';
        const cliente = p["Cód Cliente"];

        porVendedor[vendedor] = (porVendedor[vendedor] || 0) + valor;
        porPagamento[forma] = (porPagamento[forma] || 0) + valor;
        porData[labelData] = (porData[labelData] || 0) + valor;
        pedidosPorDia[labelData] = (pedidosPorDia[labelData] || 0) + 1;
        pedidosPorEstado[estado] = (pedidosPorEstado[estado] || 0) + 1;

        if (!clientesPorVendedor[vendedor]) clientesPorVendedor[vendedor] = new Set();
        if (cliente) clientesPorVendedor[vendedor].add(cliente);

        totalFaturamento += valor;
      });

      const clientesPorVendedorCount = {};
      Object.keys(clientesPorVendedor).forEach(v => {
        clientesPorVendedorCount[v] = clientesPorVendedor[v].size;
      });

      const ordemClientes = document.getElementById('ordemClientes').value;
      const ordemEstados = document.getElementById('ordemEstados').value;
      const clientesOrdenados = ordenarObjeto(clientesPorVendedorCount, ordemClientes);
      const estadosOrdenados = ordenarObjeto(pedidosPorEstado, ordemEstados);

      // KPIs
      document.getElementById('kpiFaturamento').textContent = `R$ ${totalFaturamento.toFixed(2)}`;
      document.getElementById('kpiPedidos').textContent = `${data.length}`;
      document.getElementById('kpiClientes').textContent = `${new Set(data.map(p => p["Cód Cliente"])).size}`;
      document.getElementById('kpiEstados').textContent = `${new Set(data.map(p => p.Estado)).size}`;

      document.getElementById('totalFaturamento').textContent = `R$ ${totalFaturamento.toFixed(2)}`;
      document.getElementById('totalPedidosDia').textContent = `${data.length} pedidos`;
      document.getElementById('totalClientes').textContent = `${Object.values(clientesPorVendedorCount).reduce((a, b) => a + b, 0)} clientes únicos`;
      document.getElementById('totalEstados').textContent = `${Object.values(pedidosPorEstado).reduce((a, b) => a + b, 0)} pedidos`;

      gerarGrafico('totalPorVendedor', 'Total por Vendedor (R$)', porVendedor, 'bar');
      gerarGrafico('porPagamento', 'Total por Forma de Pagamento (R$)', porPagamento, 'doughnut');
      gerarGrafico('faturamentoPorData', 'Faturamento ao Longo do Tempo (R$)', porData, 'line');
      gerarGrafico('pedidosPorDia', 'Total de Pedidos por Dia', pedidosPorDia, 'bar');
      gerarGrafico('clientesPorVendedor', 'Clientes Únicos por Vendedor', clientesOrdenados, 'bar');
      gerarGrafico('pedidosPorEstado', 'Total de Pedidos por Estado', estadosOrdenados, 'bar');
    }

    function ordenarObjeto(obj, direcao = 'desc') {
      return Object.fromEntries(Object.entries(obj).sort(([, a], [, b]) => direcao === 'asc' ? a - b : b - a));
    }

    function gerarGrafico(id, titulo, dados, tipo) {
      const ctx = document.getElementById(id).getContext('2d');
      if (chartRefs[id]) chartRefs[id].destroy();
      chartRefs[id] = new Chart(ctx, {
        type: tipo,
        data: {
          labels: Object.keys(dados),
          datasets: [{
            label: titulo,
            data: Object.values(dados),
            backgroundColor: tipo === 'line' ? 'rgba(59,130,246,0.2)' : colors,
            borderColor: 'transparent',
            borderWidth: 0,
            fill: tipo === 'line',
            tension: 0.3
          }]
        },
        options: {
          responsive: true,
          plugins: {
            legend: { display: tipo !== 'bar', position: 'bottom' },
            title: { display: true, text: titulo }
          },
          scales: tipo === 'line' || tipo === 'bar' ? {
            x: { title: { display: true, text: 'Categoria' }},
            y: { beginAtZero: true, title: { display: true, text: 'Valor' }}
          } : {}
        }
      });
    }
  </script>

</body>
</html>
