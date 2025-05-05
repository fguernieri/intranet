<?php


require_once '../../config/db.php';
include '../../sidebar.php';

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


$filtro = $_GET['filtro'] ?? '';
$stmt = $pdo->prepare("SELECT * FROM ficha_tecnica WHERE nome_prato LIKE :filtro ORDER BY id DESC");
$stmt->execute([':filtro' => "%$filtro%"]);
$fichas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Consulta de Fichas Técnicas</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0"> <!-- Responsivo -->
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../../assets/css/style.css">

</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex">

  <!-- Alerta de exclusão -->
	<?php if (isset($_GET['excluido'])): ?>
	  <div id="alert-box" class="fixed top-6 left-1/2 transform -translate-x-1/2 z-50 animate-slideDown transition-all duration-300">
		<div class="bg-green-600 text-white px-6 py-4 rounded shadow text-center font-semibold">
		  ✅ Ficha excluída com sucesso!
		</div>
	  </div>

	  <script>
		// Auto-hide depois de 3 segundos
		setTimeout(() => {
		  const alert = document.getElementById('alert-box');
		  if (alert) {
			alert.classList.add('opacity-0');
			alert.classList.remove('animate-slideDown');
			setTimeout(() => alert.remove(), 500); // remove do DOM depois da transição
		  }
		}, 3000);
	  </script>

	  <style>
		@keyframes slideDown {
		  0% { opacity: 0; transform: translateY(-20px) translateX(-50%); }
		  100% { opacity: 1; transform: translateY(0) translateX(-50%); }
		}

		.animate-slideDown {
		  animation: slideDown 0.4s ease-out forwards;
		}
	  </style>
	<?php endif; ?>


  <div class="max-w-6xl mx-auto py-6">
    <h1 class="text-3xl font-bold text-cyan-400 text-center mb-8">Consulta de Fichas Técnicas</h1>

    <!-- Campo de busca + botões -->
    <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4">
      <form method="GET" class="w-full flex flex-col sm:flex-row items-stretch sm:items-center sm:justify-start gap-2">
        <input 
          type="text" 
          name="filtro" 
          value="<?= htmlspecialchars($filtro) ?>" 
          placeholder="Buscar por nome do prato..."
          class="w-full sm:w-96 p-3 rounded bg-gray-800 border border-gray-600 focus:outline-none focus:ring-2 focus:ring-cyan-500"
        >
        <button 
          type="submit"
          class="bg-cyan-500 hover:bg-cyan-600 text-white font-semibold px-5 py-3 rounded"
        >Buscar</button>
      </form>

      <!-- Botões lado a lado -->
      <div class="flex flex-col sm:flex-row gap-2 w-full sm:w-auto">
        <a href="consultar_alteracoes.php"
           class="w-full sm:w-auto text-center bg-purple-500 hover:bg-purple-600 text-white px-6 py-3 rounded shadow font-semibold">
          📜 Histórico
        </a>
        <a href="cadastrar_ficha.php"
           class="w-full sm:w-auto text-center bg-green-500 hover:bg-green-600 text-white px-6 py-3 rounded shadow font-semibold">
          ➕ Nova Ficha
        </a>
      </div>
    </div>

    <?php if (count($fichas) > 0): ?>

      <!-- 📱 Mobile: Cards -->
      <div class="space-y-4 md:hidden">
        <?php foreach ($fichas as $ficha): ?>
          <div class="bg-gray-800 p-4 rounded shadow-md">
            <div class="flex justify-between items-center mb-2">
              <h2 class="text-lg font-semibold text-cyan-400"><?= htmlspecialchars($ficha['nome_prato']) ?></h2>
              <span class="text-sm text-gray-400">#<?= $ficha['id'] ?></span>
            </div>
            <div class="text-sm text-gray-300">
              <p><strong>Rendimento:</strong> <?= $ficha['rendimento'] ?></p>
              <p><strong>Data:</strong> <?= date('d/m/Y', strtotime($ficha['data_criacao'])) ?></p>
            </div>
            <div class="mt-3 flex flex-wrap gap-2">
              <a href="visualizar_ficha.php?id=<?= $ficha['id'] ?>" class="text-cyan-400 hover:underline text-sm">Ver</a>
              <a href="editar_ficha_form.php?id=<?= $ficha['id'] ?>" class="text-yellow-400 hover:underline text-sm">Editar</a>
              <a href="historico.php?id=<?= $ficha['id'] ?>" class="text-purple-400 hover:underline text-sm">Histórico</a>
              <a href="excluir_ficha.php?id=<?= $ficha['id'] ?>" class="text-red-500 hover:underline text-sm">Excluir</a>
            </div>
          </div>
        <?php endforeach; ?>
      </div>

      <!-- 💻 Desktop: Tabela -->
      <div class="overflow-x-auto bg-gray-800 rounded shadow hidden md:block">
        <table class="min-w-full text-sm text-center">
          <thead class="bg-gray-700 text-cyan-300">
            <tr>
              <th class="p-3">ID</th>
              <th class="p-3">Nome do Prato</th>
              <th class="p-3">Rendimento</th>
              <th class="p-3">Data</th>
              <th class="p-3">Ações</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <?php foreach ($fichas as $ficha): ?>
              <tr class="hover:bg-gray-700">
                <td class="p-2"><?= $ficha['id'] ?></td>
                <td class="p-2"><?= htmlspecialchars($ficha['nome_prato']) ?></td>
                <td class="p-2"><?= $ficha['rendimento'] ?></td>
                <td class="p-2"><?= date('d/m/Y', strtotime($ficha['data_criacao'])) ?></td>
                <td class="p-2 space-x-2">
                  <a href="visualizar_ficha.php?id=<?= $ficha['id'] ?>" class="text-cyan-400 hover:underline">Ver</a>
                  <a href="editar_ficha_form.php?id=<?= $ficha['id'] ?>" class="text-yellow-400 hover:underline">Editar</a>
                  <a href="historico.php?id=<?= $ficha['id'] ?>" class="text-purple-400 hover:underline">Histórico</a>
                  <a href="excluir_ficha.php?id=<?= $ficha['id'] ?>" class="text-red-500 hover:underline">Excluir</a>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

    <?php else: ?>
      <p class="text-center text-gray-400 mt-10">Nenhuma ficha encontrada com o filtro: <strong><?= htmlspecialchars($filtro) ?></strong></p>
    <?php endif; ?>
  </div>

</body>
</html>
