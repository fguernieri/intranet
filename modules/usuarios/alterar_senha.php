<?php
require_once '../../auth.php';
$erro    = $_GET['erro'] ?? '';
$sucesso = isset($_GET['ok']);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Alterar Senha</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen text-white">
  <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-md">
    <h2 class="text-2xl font-bold mb-6 text-yellow-400 text-center">Alterar Senha</h2>

    <?php if ($erro === 'atual'): ?>
      <p class="bg-red-500 p-2 rounded mb-4 text-center">Senha atual incorreta.</p>
    <?php elseif ($erro === 'confirma'): ?>
      <p class="bg-red-500 p-2 rounded mb-4 text-center">Nova senha e confirmação não conferem.</p>
    <?php elseif ($erro === 'igual'): ?>
      <p class="bg-red-500 p-2 rounded mb-4 text-center">A nova senha não pode ser igual à anterior.</p>
    <?php elseif ($sucesso): ?>
      <p class="bg-green-500 p-2 rounded mb-4 text-center">Senha atualizada com sucesso!</p>
    <?php endif; ?>

    <form action="atualizar_senha.php" method="POST">
      <label class="block mb-2">Senha atual</label>
      <input type="password" name="senha_atual" required class="border rounded w-full px-3 py-2 mb-4 bg-gray-700 placeholder-gray-400">

      <label class="block mb-2">Nova senha</label>
      <input type="password" name="senha_nova" required class="border rounded w-full px-3 py-2 mb-4 bg-gray-700 placeholder-gray-400">

      <label class="block mb-2">Confirmar nova senha</label>
      <input type="password" name="senha_confirma" required class="border rounded w-full px-3 py-2 mb-6 bg-gray-700 placeholder-gray-400">

      <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900 py-3 rounded">
        Atualizar
      </button>
    </form>

    <p class="mt-4 text-center">
      <a href="../../painel.php" class="text-gray-400 hover:text-yellow-400">← Voltar ao Painel</a>
    </p>
  </div>
</body>
</html>
