<?php
require_once 'auth.php';
require_once 'config/db.php';
require_once 'config/app.php';

if (!isset($_SESSION['modulos'])) {
    $stmt = $pdo->prepare(
        "SELECT m.nome, m.link
         FROM modulos m
         JOIN modulos_usuarios mu ON m.id = mu.modulo_id
         WHERE mu.usuario_id = :uid AND m.ativo = 1
         ORDER BY m.nome"
    );
    $stmt->execute(['uid' => $_SESSION['usuario_id']]);
    $_SESSION['modulos'] = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
$modulos = $_SESSION['modulos'];
?>

<!-- Overlay -->
<div id="overlay" onclick="toggleSidebar()" class="hidden fixed inset-0 bg-black bg-opacity-50 z-40 sm:hidden"></div>

<!-- Sidebar mobile -->
<div id="mobileSidebar"
     class="hidden fixed top-0 left-0 h-full w-64 bg-gray-800 p-6 space-y-3 text-white transform -translate-x-full transition-transform duration-300 z-50 sm:hidden">

  <button onclick="toggleSidebar()" class="text-right w-full text-gray-400 hover:text-white mb-4">✕</button>

  <a href="<?= BASE_URL ?>/painel.php" class="block text-yellow-400 font-bold">Painel</a>
  <?php foreach ($modulos as $m): ?>
    <a href="<?= htmlspecialchars($m['link']) ?>" class="block hover:text-yellow-400"><?= htmlspecialchars($m['nome']) ?></a>
  <?php endforeach; ?>
  <?php if ($_SESSION['usuario_perfil'] === 'admin'): ?>
    <a href="<?= BASE_URL ?>/modules/usuarios/admin_permissoes.php" class="block text-sm text-gray-400 hover:text-yellow-400">Admin</a>
  <?php endif; ?>
  <a href="<?= BASE_URL ?>/modules/usuarios/alterar_senha.php" class="block text-sm text-gray-400 hover:text-yellow-400">Alterar Senha</a>
  <a href="<?= BASE_URL ?>/logout.php" class="block text-sm text-red-500 hover:underline">Sair</a>
</div>

<!-- Botão fixo lateral para abrir sidebar -->
<button onclick="toggleSidebar()"
        class="sm:hidden fixed top-7 left transform -translate-y-1/2 bg-yellow-500 text-gray-900 p-3 rounded-r z-40 shadow hover:bg-yellow-600 transition">
  ☰
</button>

<!-- Sidebar desktop -->
<aside class="hidden sm:flex w-64 bg-gray-800 p-6 flex-col justify-between h-screen text-white">
  <div>
    <img src="<?= BASE_URL ?>/assets/img/logo.png" alt="Bastards Brewery" class="w-28 mx-auto mb-6">
    <nav class="space-y-4">
      <a href="<?= BASE_URL ?>/painel.php" class="block text-yellow-400 font-bold">Painel</a>
      <?php foreach ($modulos as $m): ?>
        <a href="<?= htmlspecialchars($m['link']) ?>" class="block hover:text-yellow-400"><?= htmlspecialchars($m['nome']) ?></a>
      <?php endforeach; ?>
      <?php if ($_SESSION['usuario_perfil'] === 'admin'): ?>
        <a href="<?= BASE_URL ?>/modules/usuarios/admin_permissoes.php" class="block text-sm text-gray-400 mt-6 hover:text-yellow-400">Admin</a>
      <?php endif; ?>
      <a href="<?= BASE_URL ?>/modules/usuarios/alterar_senha.php" class="block text-sm text-gray-400 hover:text-yellow-400">Alterar Senha</a>
      <a href="<?= BASE_URL ?>/logout.php" class="block text-sm text-red-500 hover:underline">Sair</a>
    </nav>
  </div>
</aside>

<script>
  function toggleSidebar() {
    const sidebar = document.getElementById('mobileSidebar');
    const overlay = document.getElementById('overlay');
    const isHidden = sidebar.classList.contains('hidden');

    if (isHidden) {
      sidebar.classList.remove('hidden');
      overlay.classList.remove('hidden');
      requestAnimationFrame(() => {
        sidebar.classList.remove('-translate-x-full');
        overlay.classList.add('block');
      });
    } else {
      sidebar.classList.add('-translate-x-full');
      overlay.classList.remove('block');
      setTimeout(() => {
        sidebar.classList.add('hidden');
        overlay.classList.add('hidden');
      }, 300);
    }
  }
</script>
