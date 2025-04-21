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
        <input type="file" name="imagem" 
               class="w-full p-3 bg-gray-800 border border-gray-700 rounded file:text-white file:bg-cyan-600 file:border-none file:rounded file:px-4 file:py-2">
      </div>

      <!-- Responsável -->
      <div>
        <label class="text-cyan-300 block mb-2 font-medium">Responsável</label>
        <input type="text" name="usuario" required class="w-full p-3 rounded bg-gray-800 border border-gray-700 focus:outline-none focus:ring-2 focus:ring-cyan-500">
      </div>

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
