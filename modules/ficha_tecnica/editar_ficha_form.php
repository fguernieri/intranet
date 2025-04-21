<?php
require_once '../../config/db.php';
include '../../sidebar.php';


$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID inválido.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM ficha_tecnica WHERE id = :id");
$stmt->execute([':id' => $id]);
$ficha = $stmt->fetch();

if (!$ficha) {
    echo "Ficha não encontrada.";
    exit;
}

// Ingredientes
$stmtIng = $pdo->prepare("SELECT * FROM ingredientes WHERE ficha_id = :id");
$stmtIng->execute([':id' => $id]);
$ingredientes = $stmtIng->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Editar: <?= htmlspecialchars($ficha['nome_prato']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex">

  <div class="max-w-5xl mx-auto bg-gray-800 rounded shadow p-8">

    <!-- Botão voltar -->
    <div class="mb-6">
      <a href="consulta.php"
         class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded shadow no-underline min-w-[170px] text-center font-semibold">
        ⬅️ Voltar para Consulta
      </a>
    </div>

    <h1 class="text-2xl font-bold text-cyan-400 text-center mb-8">
      Editar Ficha Técnica: <?= htmlspecialchars($ficha['nome_prato']) ?>
    </h1>

    <form action="salvar_edicao.php" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <input type="hidden" name="id" value="<?= $ficha['id'] ?>">

      <!-- Nome do prato -->
      <div>
        <label class="block mb-2 text-cyan-300 font-medium">Nome do Prato</label>
        <input type="text" name="nome_prato" value="<?= htmlspecialchars($ficha['nome_prato']) ?>"
               class="w-full p-3 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
      </div>

      <!-- Rendimento -->
      <div>
        <label class="block mb-2 text-cyan-300 font-medium">Rendimento</label>
        <input type="text" name="rendimento" value="<?= htmlspecialchars($ficha['rendimento']) ?>"
               class="w-full p-3 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
      </div>

      <!-- Imagem -->
      <div>
        <label class="block mb-2 text-cyan-300 font-medium">Imagem (deixe em branco para não alterar)</label>
        <input type="file" name="imagem"
               class="w-full p-3 bg-gray-800 border border-gray-700 rounded file:text-white file:bg-cyan-600 file:border-none file:rounded file:px-4 file:py-2">
      </div>

      <!-- Responsável -->
      <div>
        <label class="block mb-2 text-cyan-300 font-medium">Responsável</label>
        <input type="text" name="usuario" value="<?= htmlspecialchars($ficha['usuario']) ?>"
               class="w-full p-3 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-cyan-500" required>
      </div>

      <!-- Ingredientes -->
      <div class="col-span-1 md:col-span-2">
        <h2 class="text-xl font-bold text-cyan-300 mb-4">Ingredientes</h2>

        <div id="ingredientesContainer" class="space-y-4">
          <?php foreach ($ingredientes as $ing): ?>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end">
              <input type="hidden" name="ingrediente_id[]" value="<?= $ing['id'] ?>">
              <input type="hidden" name="excluir[]" value="0">

              <div>
                <label class="text-cyan-300 block mb-1">Código (opcional)</label>
                <input type="text" name="codigo[]" value="<?= htmlspecialchars($ing['codigo']) ?>" class="w-full p-2 rounded bg-gray-800 border border-gray-700">
              </div>
              <div>
                <label class="text-cyan-300 block mb-1">Descrição</label>
                <input type="text" name="descricao[]" value="<?= htmlspecialchars($ing['descricao']) ?>" class="w-full p-2 rounded bg-gray-800 border border-gray-700" required>
              </div>
              <div>
                <label class="text-cyan-300 block mb-1">Quantidade</label>
                <input type="number" step="0.01" name="quantidade[]" value="<?= $ing['quantidade'] ?>" class="w-full p-2 rounded bg-gray-800 border border-gray-700" required>
              </div>
              <div>
                <label class="text-cyan-300 block mb-1">Unidade</label>
                <select name="unidade[]" class="w-full p-2 rounded bg-gray-800 border border-gray-700 text-white" required>
                  <?php
                    $unidades = ['g', 'kg', 'ml', 'l', 'unidade'];
                    foreach ($unidades as $un) {
                      $selected = $un === $ing['unidade'] ? 'selected' : '';
                      echo "<option value=\"$un\" $selected>$un</option>";
                    }
                  ?>
                </select>
              </div>
              <div class="flex justify-center items-end pb-2">
                <button type="button" onclick="excluirIngrediente(this)"
                        class="text-red-400 hover:text-red-600 font-bold text-sm">🗑️</button>
              </div>
            </div>
          <?php endforeach; ?>
        </div>

        <!-- Botão adicionar novo ingrediente -->
        <div class="mt-4">
          <button type="button" onclick="addIngrediente()"
                  class="bg-green-500 hover:bg-green-600 text-white px-6 py-2 rounded shadow font-semibold">
            ➕ Adicionar Ingrediente
          </button>
        </div>
      </div>

      <!-- Modo de preparo -->
      <div class="col-span-1 md:col-span-2">
        <label class="block mb-2 text-cyan-300 font-medium">Modo de Preparo</label>
        <textarea name="modo_preparo" rows="6"
                  class="w-full p-3 bg-gray-800 border border-gray-700 rounded focus:outline-none focus:ring-2 focus:ring-cyan-500"
                  required><?= htmlspecialchars($ficha['modo_preparo']) ?></textarea>
      </div>

      <!-- Botão salvar -->
      <div class="col-span-1 md:col-span-2 flex justify-center">
        <button type="submit"
                class="bg-cyan-500 hover:bg-cyan-600 text-white font-semibold px-8 py-3 rounded shadow min-w-[170px]">
          💾 Salvar Alterações
        </button>
      </div>
    </form>
  </div>

  <!-- Template de ingrediente vazio -->
  <template id="linhaIngredienteVazia">
    <div class="grid grid-cols-1 md:grid-cols-5 gap-4 items-end mt-4">
      <input type="hidden" name="ingrediente_id[]" value="">
      <input type="hidden" name="excluir[]" value="0">

      <div>
        <label class="text-cyan-300 block mb-1">Código (opcional)</label>
        <input type="text" name="codigo[]" class="w-full p-2 rounded bg-gray-800 border border-gray-700">
      </div>
      <div>
        <label class="text-cyan-300 block mb-1">Descrição</label>
        <input type="text" name="descricao[]" class="w-full p-2 rounded bg-gray-800 border border-gray-700" required>
      </div>
      <div>
        <label class="text-cyan-300 block mb-1">Quantidade</label>
        <input type="number" step="0.01" name="quantidade[]" class="w-full p-2 rounded bg-gray-800 border border-gray-700" required>
      </div>
      <div>
        <label class="text-cyan-300 block mb-1">Unidade</label>
        <select name="unidade[]" class="w-full p-2 rounded bg-gray-800 border border-gray-700 text-white" required>
          <option value="g">g</option>
          <option value="kg">kg</option>
          <option value="ml">ml</option>
          <option value="l">l</option>
          <option value="unidade">unidade</option>
        </select>
      </div>
      <div class="flex justify-center items-end pb-2">
        <button type="button" onclick="excluirIngrediente(this)"
                class="text-red-400 hover:text-red-600 font-bold text-sm">🗑️</button>
      </div>
    </div>
  </template>

  <script>
    function addIngrediente() {
      const template = document.getElementById('linhaIngredienteVazia');
      const container = document.getElementById('ingredientesContainer');
      const novaLinha = template.content.cloneNode(true);
      container.appendChild(novaLinha);
    }

    function excluirIngrediente(botao) {
      const linha = botao.closest('.grid');
      linha.style.display = 'none';
      linha.querySelector('[name="excluir[]"]').value = "1";
    }
  </script>

</body>
</html>
