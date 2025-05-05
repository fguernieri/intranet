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

  <div class="w-full max-w-6xl mx-auto bg-gray-800 p-8 rounded-lg shadow-lg">

    <!-- Voltar -->
    <div class="mb-6">
      <a href="consulta.php"
         class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded-lg shadow no-underline font-semibold">
        ⬅️ Voltar para Consulta
      </a>
    </div>

    <h1 class="text-3xl font-bold text-cyan-400 mb-8 text-center">
      Cadastrar Nova Ficha Técnica
    </h1>

    <form action="salvar_ficha.php" method="POST" enctype="multipart/form-data"
          class="grid grid-cols-1 md:grid-cols-2 gap-6">

      <!-- Dados principais -->
      <div>
        <label class="block text-cyan-300 mb-2 font-medium">Nome do Prato</label>
        <input type="text" name="nome_prato" required
               class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
      </div>

      <div>
        <label class="block text-cyan-300 mb-2 font-medium">Rendimento</label>
        <input type="text" name="rendimento" required
               class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
      </div>

      <div>
        <label class="block text-cyan-300 mb-2 font-medium">Imagem (opcional)</label>
        <input type="file" name="imagem" accept=".jpg,.jpeg,.png"
               class="w-full p-2 bg-gray-800 border border-gray-700 rounded file:text-white file:bg-cyan-500 file:rounded file:px-4 file:py-1 file:font-semibold">
      </div>

      <div>
        <label class="block text-cyan-300 mb-2 font-medium">Responsável</label>
        <input type="text" name="usuario" required
               class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
      </div>

      <!-- Busca de Insumo -->
      <div class="col-span-1 md:col-span-2 bg-gray-700 p-4 rounded-lg">
        <label for="busca_insumo" class="block text-sm font-semibold text-white mb-2">
          Buscar insumo por nome:
        </label>
        <input id="busca_insumo" type="text" oninput="buscarInsumo()"
               class="w-full p-3 rounded bg-white text-gray-900 border border-gray-500"
               placeholder="Digite pelo menos 2 caracteres">

        <div id="tabela_resultados" class="mt-4 hidden overflow-x-auto">
          <table class="w-full bg-gray-500 border border-gray-400 rounded-lg">
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

      <!-- Ingredientes -->
      <div class="col-span-1 md:col-span-2">
        <h2 class="text-xl font-bold text-cyan-300 mb-4">Ingredientes</h2>

        <div id="ingredientesContainer" class="grid grid-cols-1 md:grid-cols-4 gap-4">
          <!-- Template de ingrediente -->
          <template id="ingredienteTemplate">
            <div>
              <label class="block text-cyan-300 mb-1">Código (opcional)</label>
              <input type="text" name="codigo[]"
                     class="w-full p-2 rounded bg-gray-800 border border-gray-700">
            </div>
            <div>
              <label class="block text-cyan-300 mb-1">Descrição</label>
              <input type="text" name="descricao[]" required
                     class="w-full p-2 rounded bg-gray-800 border border-gray-700">
            </div>
            <div>
              <label class="block text-cyan-300 mb-1">Quantidade</label>
              <input type="number" step="0.01" name="quantidade[]" required
                     class="w-full p-2 rounded bg-gray-800 border border-gray-700">
            </div>
            <div>
              <label class="block text-cyan-300 mb-1">Unidade</label>
              <select name="unidade[]" required
                      class="w-full p-2 rounded bg-gray-800 border border-gray-700 text-white">
                <option value="">Selecione</option>
                <option value="g">g</option>
                <option value="kg">kg</option>
                <option value="ml">ml</option>
                <option value="l">l</option>
                <option value="unidade">unidade</option>
              </select>
            </div>
          </template>

          <!-- Primeira linha padrão -->
          <script>
            document.addEventListener('DOMContentLoaded', () => {
              const container = document.getElementById('ingredientesContainer');
              const tpl = document.getElementById('ingredienteTemplate');
              container.appendChild(tpl.content.cloneNode(true));
            });
          </script>

        </div>

        <div class="mt-4">
          <button type="button" onclick="addIngrediente()"
                  class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded-lg shadow font-semibold">
            ➕ Adicionar Ingrediente
          </button>
        </div>
      </div>

      <!-- Modo de preparo -->
      <div class="col-span-1 md:col-span-2">
        <label class="block text-cyan-300 mb-2 font-medium">Modo de Preparo</label>
        <textarea name="modo_preparo" rows="6" required
                  class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500"></textarea>
      </div>

      <!-- Botão salvar -->
      <div class="col-span-1 md:col-span-2 flex justify-center">
        <button type="submit"
                class="bg-cyan-500 hover:bg-cyan-600 text-white px-8 py-3 rounded-lg shadow font-semibold">
          💾 Cadastrar Ficha
        </button>
      </div>
    </form>

  </div>

  <script>
    // Função de busca por insumo
    function buscarInsumo() {
      const termo = document.getElementById('busca_insumo').value;
      const tabela = document.getElementById('tabela_resultados');
      const corpo = document.getElementById('corpo_tabela');

      if (termo.length < 2) {
        tabela.classList.add('hidden');
        return;
      }

      fetch('buscar_insumos.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'termo=' + encodeURIComponent(termo)
      })
      .then(res => res.json())
      .then(dados => {
        const seen = new Set();
        const resultados = dados.filter(item => {
          if (seen.has(item.codigo)) return false;
          seen.add(item.codigo);
          return true;
        });

        corpo.innerHTML = '';
        if (resultados.length) {
          resultados.forEach(insumo => {
            const tr = document.createElement('tr');
            tr.className = 'border-t';
            tr.innerHTML = `
              <td class="px-4 py-2 text-gray-900">${insumo.Insumo}</td>
              <td class="px-4 py-2 text-gray-900">${insumo.codigo}</td>
              <td class="px-4 py-2 text-gray-900">${insumo.unidade}</td>
            `;
            corpo.appendChild(tr);
          });
          tabela.classList.remove('hidden');
        } else {
          tabela.classList.add('hidden');
        }
      });
    }

    // Adicionar novo ingrediente
    function addIngrediente() {
      const container = document.getElementById('ingredientesContainer');
      const tpl = document.getElementById('ingredienteTemplate');
      const clone = tpl.content.cloneNode(true);
      container.appendChild(clone);
      aplicarBuscaPorCodigo();
    }

    // Busca por código de insumo em campos dinâmicos
    function aplicarBuscaPorCodigo() {
      document.querySelectorAll("input[name='codigo[]']").forEach(input => {
        if (!input.dataset.listener) {
          input.addEventListener('blur', function() {
            const codigoDiv   = this.parentElement;
            const codigoValor = this.value.trim();
            if (!codigoValor) return;

            fetch('buscar_insumos.php', {
              method: 'POST',
              headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
              body: 'codigo=' + encodeURIComponent(codigoValor)
            })
            .then(res => res.json())
            .then(dados => {
              if (!dados.length) return;

              const descDiv    = codigoDiv.nextElementSibling;
              const unidadeDiv = descDiv.nextElementSibling.nextElementSibling;

              descDiv.querySelector("input[name='descricao[]']").value = dados[0].Insumo;
              unidadeDiv.querySelector("select[name='unidade[]']").value = dados[0].unidade;
            });
          });
          input.dataset.listener = true;
        }
      });
    }

    document.addEventListener('DOMContentLoaded', aplicarBuscaPorCodigo);
  </script>
</body>
</html>
