<?php
// modules/usuarios/novo.php
require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config/db.php';

// Verifica se admin
if ($_SESSION['usuario_perfil'] !== 'admin') {
    echo "Acesso restrito.";
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Novo Usuário - Intranet Bastards</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white min-h-screen flex">

  <!-- Barra lateral compartilhada -->
  <?php include __DIR__ . '/../../sidebar.php'; ?>

  <!-- Conteúdo principal -->
  <main class="flex-1 p-10">
    <div class="bg-gray-800 p-8 rounded-lg shadow-md max-w-lg mx-auto">
      <h2 class="text-2xl font-bold mb-6 text-yellow-400">Cadastrar Novo Usuário</h2>
      <?php if (isset($_GET['ok'])): ?>
        <p class="bg-green-500 text-gray-900 p-2 rounded mb-6 text-center">Usuário cadastrado com sucesso!</p>
      <?php endif; ?>
      <form action="salvar.php" method="POST" class="space-y-6">
        <div>
          <label class="block text-sm font-medium mb-1">Nome completo</label>
          <input type="text" name="nome" placeholder="Nome" required
                 class="w-full p-3 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">E-mail</label>
          <input type="email" name="email" placeholder="E-mail" required
                 class="w-full p-3 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Senha</label>
          <input type="password" name="senha" placeholder="Senha" required
                 class="w-full p-3 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Cargo</label>
          <input type="text" name="cargo" placeholder="Cargo"
                 class="w-full p-3 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Setor</label>
          <input type="text" name="setor" placeholder="Setor"
                 class="w-full p-3 bg-gray-700 border border-gray-600 rounded text-white placeholder-gray-400">
        </div>
        <div>
          <label class="block text-sm font-medium mb-1">Perfil</label>
          <select name="perfil"
                  class="w-full p-3 bg-gray-700 border border-gray-600 rounded text-white">
            <option value="user">Usuário</option>
            <option value="supervisor">Supervisor</option>
            <option value="admin">Administrador</option>
          </select>
        </div>
        <button type="submit" class="w-full bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-3 rounded">
          Cadastrar
        </button>
      </form>
    </div>
  </main>
</body>
</html>
