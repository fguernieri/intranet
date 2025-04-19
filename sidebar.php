<?php
// sidebar.php - Menu lateral compartilhado
require_once 'auth.php';
require_once 'config/db.php';
require_once __DIR__ . '/config/app.php';


// Busca módulos permitidos para o usuário logado
$stmt = $pdo->prepare(
    "SELECT m.nome, m.link
     FROM modulos m
     JOIN modulos_usuarios mu ON m.id = mu.modulo_id
     WHERE mu.usuario_id = :uid AND m.ativo = 1
     ORDER BY m.nome"
);
$stmt->execute(['uid' => $_SESSION['usuario_id']]);
$modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<aside class="w-64 bg-gray-800 p-6 flex flex-col justify-between h-screen">
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
      <a href="<?= BASE_URL ?>/logout.php" class="block text-sm text-red-500 hover:underline mt-2">Sair</a>
    </nav>
  </div>
</aside>