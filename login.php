<?php
session_start();
$erro = isset($_GET['erro']) ? true : false;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Login - Bastards Brewery</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">

  <div class="bg-gray-800 p-8 rounded-lg shadow-lg w-full max-w-sm text-white">

    <div class="flex justify-center mb-2">
      <img src="assets/img/logo.png" alt="Bastards Brewery" class="w-80">
    </div>

    <h1 class="text-2xl font-bold text-center text-yellow-400 mb-6">Área Restrita</h1>

    <?php if ($erro): ?>
      <p class="bg-red-500 text-white text-sm p-2 rounded mb-4 text-center">E-mail ou senha inválidos.</p>
    <?php endif; ?>

    <form action="verifica_login.php" method="POST">
      <input
        type="email"
        name="email"
        placeholder="E-mail"
        required
        class="w-full p-3 rounded bg-gray-700 border border-gray-600 text-white placeholder-gray-400 mb-4"
      >
      <input
        type="password"
        name="senha"
        placeholder="Senha"
        required
        class="w-full p-3 rounded bg-gray-700 border border-gray-600 text-white placeholder-gray-400 mb-6"
      >
      <button
        type="submit"
        class="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-3 px-4 rounded transition"
      >
        Entrar
      </button>
    </form>

    <div class="mt-4 text-center text-sm">
      <a href="#" class="text-yellow-400 hover:underline">Esqueceu a senha?</a>
    </div>
  </div>

</body>
</html>
