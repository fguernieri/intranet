<?php

include __DIR__ . '/../../sidebar.php';

?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Cadastrar Ficha Técnica</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../../assets/css/style.css">

</head>
<body class="bg-gray-900 text-white min-h-screen flex">

  <div class="max-w-6xl mx-auto bg-gray-800 p-8 rounded shadow">

    <!-- Botão voltar -->
    <div class="mb-6">
      <a href="consulta.php"
         class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded shadow no-underline min-w-[170px] text-center font-semibold">
        ⬅️ Voltar para Consulta
      </a>
    </div>

    <h1 class="text-3xl font-bold text-cyan-400 mb-8 text-center">Cadastrar Nova Ficha Técnica</h1>

    <form action="salvar_ficha.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- Nome do prato -->
      <div>
        <label class="text-cyan-300 block mb-2 font-medium">Nome do Prato</label>
        <input type="text" name="nome_prato" required class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
      </div>

      <!-- Rendimento -->
      <div>
        <label class="text-cyan-300 block mb-2 font-medium">Rendimento</label>
        <input type="text" name="rendimento" required class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
      </div>

      <!-- Imagem -->
      <div>
        <label class="text-cyan-300 block mb-2 font-medium">Imagem (opcional)</label>
        <input type="file" name="imagem" accept=".jpg,.jpeg,.png"
               class="w-full p-3 bg-gray-800 border border-gray-700 rounded file:text-white file:bg-cyan-600 file:border-none file:rounded file:px-4 file:py-2">
      </div>

      <!-- Responsável -->
      <div>
        <label class="text-cyan-300 block mb-2 font-medium">Responsável</label>
        <input type="text" name="usuario" required class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
      </div>

      <!-- Card com fundo amarelo claro para busca de insumo -->
      <div class="card1 no-hover col-span-2">
        <label for="busca_insumo" class="block text-sm font-semibold text-white mb-1">Buscar insumo por nome:</label>
        <div class="mb-4">
          <input type="text" id="busca_insumo" oninput="buscarInsumo()" class="border border-gray-500 rounded px-3 py-2 w-full text-gray-900 bg-white" placeholder="Digite o nome do insumo">
        </div>

        <!-- Tabela de resultados -->
        <div id="tabela_resultados" class="hidden overflow-x-auto">
          <table class="w-full bg-gray-500 border border-gray-300">
            <thead>
              <tr class="bg-gray-200 text-gray-800">
                <th class="px-4 py-2 text-left">Descrição</th>
                <th class="px-4 py-2 text-left">Código</th>
                <th class="px-4 py-2 text-left">Unidade</th>
              </tr>
            </thead>
            <tbody id="corpo_tabela"></tbody>
          </table>
        </div>
      </div>

      <script>
      function buscarInsumo() {
        const termo = document.getElementById('busca_insumo').value;
        if (termo.length < 2) {
          document.getElementById('tabela_resultados').classList.add('hidden');
          return;
        }
        fetch('buscar_insumos.php', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'termo=' + encodeURIComponent(termo)
        })
        .then(res => res.json())
        .then(dados => {
          const codigosUnicos = new Set();
          const resultados = dados.filter(insumo => {
            if (codigosUnicos.has(insumo.codigo)) return false;
            codigosUnicos.add(insumo.codigo);
            return true;
          });

          const tabela = document.getElementById('tabela_resultados');
          const corpo = document.getElementById('corpo_tabela');
          corpo.innerHTML = '';

          if (resultados.length > 0) {
            resultados.forEach(insumo => {
              const row = document.createElement('tr');
              row.classList.add('border-t');
              row.innerHTML = `
                <td class="px-4 py-2 text-gray-900">${insumo.Insumo}</td>
                <td class="px-4 py-2 text-gray-900">${insumo.codigo}</td>
                <td class="px-4 py-2 text-gray-900">${insumo.unidade}</td>
              `;
              corpo.appendChild(row);
            });
            tabela.classList.remove('hidden');
          } else {
            tabela.classList.add('hidden');
          }
        });
      }
      </script>
      
      
      <!-- Ingredientes -->
      <div class="col-span-1 md:col-span-2">
        <h2 class="text-xl font-bold text-cyan-300 mb-4">Ingredientes</h2>

        <div id="ingredientesContainer" class="space-y-4">
          <!-- Ingrediente inicial -->
          <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
            <div>
              <label class="text-cyan-300 block mb-1">Código (opcional)</label>
              <input type="text" name="codigo[]" class="w-full p-2 rounded bg-gray-800 border border-gray-700">
            </div>
            <div>
              <label class="text-cyan-300 block mb-1">Descrição</label>
              <input type="text" name="descricao[]" required class="w-full p-2 rounded bg-gray-800 border border-gray-700">
            </div>
            <div>
              <label class="text-cyan-300 block mb-1">Quantidade</label>
              <input type="number" step="0.01" name="quantidade[]" required class="w-full p-2 rounded bg-gray-800 border border-gray-700">
            </div>
            <div>
              <label class="text-cyan-300 block mb-1">Unidade</label>
              <select name="unidade[]" required class="w-full p-2 rounded bg-gray-800 border border-gray-700 text-white">
                <option value="">Selecione</option>
                <option value="g">g</option>
                <option value="kg">kg</option>
                <option value="ml">ml</option>
                <option value="l">l</option>
                <option value="unidade">unidade</option>
              </select>
            </div>
            <div></div>
          </div>
        </div>

        <!-- Botão adicionar ingrediente -->
        <div class="mt-4">
          <button type="button" onclick="addIngrediente()"
                  class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded shadow font-semibold">
            ➕ Adicionar Ingrediente
          </button>
        </div>
      </div>

      <!-- Modo de preparo -->
      <div class="col-span-1 md:col-span-2">
        <label class="text-cyan-300 block mb-2 font-medium">Modo de Preparo</label>
        <textarea name="modo_preparo" rows="6" required
                  class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
      </div>

      <!-- Botão salvar -->
      <div class="col-span-1 md:col-span-2 flex justify-center">
        <button type="submit"
                class="bg-cyan-500 hover:bg-cyan-600 text-white px-8 py-3 rounded shadow font-semibold min-w-[170px]">
          💾 Cadastrar Ficha
        </button>
      </div>
    </form>
  </div>

  <!-- Template JS -->
  <template id="ingredienteTemplate">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
      <div>
        <label class="text-cyan-300 block mb-1">Código (opcional)</label>
        <input type="text" name="codigo[]" class="w-full p-2 rounded bg-gray-800 border border-gray-700">
      </div>
      <div>
        <label class="text-cyan-300 block mb-1">Descrição</label>
        <input type="text" name="descricao[]" required class="w-full p-2 rounded bg-gray-800 border border-gray-700">
      </div>
      <div>
        <label class="text-cyan-300 block mb-1">Quantidade</label>
        <input type="number" step="0.01" name="quantidade[]" required class="w-full p-2 rounded bg-gray-800 border border-gray-700">
      </div>
      <div>
        <label class="text-cyan-300 block mb-1">Unidade</label>
        <select name="unidade[]" required class="w-full p-2 rounded bg-gray-800 border border-gray-700 text-white">
          <option value="">Selecione</option>
          <option value="g">g</option>
          <option value="kg">kg</option>
          <option value="ml">ml</option>
          <option value="l">l</option>
          <option value="unidade">unidade</option>
        </select>
      </div>
      <div class="flex justify-center items-end pb-2">
        <button type="button" onclick="this.closest('.grid').remove()"
                class="text-red-400 hover:text-red-600 font-bold text-sm">🗑️</button>
      </div>
    </div>
  </template>

  <!-- JS para adicionar ingrediente -->
  <script>
    function addIngrediente() {
      const template = document.getElementById('ingredienteTemplate');
      const container = document.getElementById('ingredientesContainer');
      container.appendChild(template.content.cloneNode(true));
    }
  </script>

</body>
</html>
