<?php
ob_start(); // inicia buffer de saída


// modules/usuarios/admin_permissoes.php
//require_once __DIR__ . '/../../auth.php';
require_once __DIR__ . '/../../config/db.php';
include __DIR__ . '/../../sidebar.php';


// Somente admin
if ($_SESSION['usuario_perfil'] !== 'admin') {
    echo "Acesso restrito.";
    exit;
}
// Processa submissão de permissões
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_perms'])) {
    $user_id  = $_POST['user_id'];
    $selected = $_POST['modulos'] ?? [];
    // Remove permissões antigas
    $pdo->prepare("DELETE FROM modulos_usuarios WHERE usuario_id = :uid")
        ->execute(['uid' => $user_id]);
    // Insere novas permissões
    $ins = $pdo->prepare("INSERT INTO modulos_usuarios (usuario_id, modulo_id) VALUES (:uid, :mid)");
    foreach ($selected as $mid) {
        $ins->execute(['uid' => $user_id, 'mid' => $mid]);
    }
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_perms'])) {
    $user_id  = $_POST['user_id'];
    $selected = $_POST['modulos'] ?? [];

    echo "<pre>";
    echo "Salvando permissões para user_id = $user_id\n";
    print_r($selected);
    echo "</pre>";

    $pdo->prepare("DELETE FROM modulos_usuarios WHERE usuario_id = :uid")
        ->execute(['uid' => $user_id]);

    $ins = $pdo->prepare("INSERT INTO modulos_usuarios (usuario_id, modulo_id) VALUES (:uid, :mid)");
    foreach ($selected as $mid) {
        $ins->execute(['uid' => $user_id, 'mid' => $mid]);
    }

    echo "Tudo ok até aqui, tentando redirecionar...";

    header("Location: admin_permissoes.php?user_id=$user_id&ok=1");
    exit;
}

    header("Location: ".$_SERVER['PHP_SELF']."?user_id=$user_id&ok=1");
    exit;
}
// Dados para listagem de usuários e módulos
$usuarios = $pdo->query("SELECT id, nome, email, perfil FROM usuarios ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$modulos  = $pdo->query("SELECT id, nome FROM modulos WHERE ativo = 1 ORDER BY nome")->fetchAll(PDO::FETCH_ASSOC);
$user_id  = $_GET['user_id'] ?? null;
$perms    = [];
if ($user_id) {
    $stmt = $pdo->prepare("SELECT modulo_id FROM modulos_usuarios WHERE usuario_id = :uid");
    $stmt->execute(['uid' => $user_id]);
    $perms = $stmt->fetchAll(PDO::FETCH_COLUMN);
}
$ok = isset($_GET['ok']);
ob_end_flush(); // esvazia e envia o buffer

?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Admin - Gerência de Usuários & Permissões</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="bg-gray-900 text-white min-h-screen flex">

  
  <!-- Conteúdo principal -->
  <main class="flex-1 p-10">
    <!-- Seção de Usuários -->
    <section class="mb-10">
      <div class="flex justify-between items-center mb-4">
        <h1 class="text-2xl font-bold">Gestão de Usuários</h1>
        <a href="novo.php" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-2 px-4 rounded">+ Novo Usuário</a>
      </div>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-left border-collapse">
          <thead>
            <tr class="bg-gray-800">
              <th class="p-2">Nome</th>
              <th class="p-2">E-mail</th>
	      <th class="p-2">Perfil</th>
              <th class="p-2">Ações</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($usuarios as $u): ?>
            <tr class="border-t hover:bg-gray-700">
              <td class="p-2"><?= htmlspecialchars($u['nome']) ?></td>
              <td class="p-2"><?= htmlspecialchars($u['email']) ?></td>
	      <td class="p-2"><?= htmlspecialchars($u['perfil']) ?></td>
              <td class="p-2 space-x-2">
                <a href="editar.php?id=<?= $u['id'] ?>" class="text-blue-400 hover:underline">Editar</a>
                <?php if ($u['id'] != $_SESSION['usuario_id']): ?>
                  <a href="desativar.php?id=<?= $u['id'] ?>" class="text-red-400 hover:underline" onclick="return confirm('Desativar usuário?');">Excluir</a>
                <?php endif; ?>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Seção de Permissões -->
    <section>
      <h2 class="text-2xl font-bold mb-4">Definir Permissões</h2>
      <form method="GET" class="mb-6">
        <label class="block mb-2">Selecione o usuário:</label>
        <select name="user_id" onchange="this.form.submit()" class="border rounded px-3 py-2 bg-gray-700">
          <option value="">-- selecione --</option>
          <?php foreach ($usuarios as $u): ?>
            <option value="<?= $u['id'] ?>" <?= $u['id'] == $user_id ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['nome'].' ('.$u['email'].')') ?>
            </option>
          <?php endforeach; ?>
        </select>
      </form>

      <?php if ($user_id): ?>
        <?php if ($ok): ?>
          <p class="bg-green-500 p-2 rounded mb-4">Permissões atualizadas com sucesso!</p>
        <?php endif; ?>

        <form method="POST">
          <input type="hidden" name="user_id" value="<?= $user_id ?>">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
            <?php foreach ($modulos as $m): ?>
              <label class="flex items-center space-x-2">
                <input type="checkbox" name="modulos[]" value="<?= $m['id'] ?>" <?= in_array($m['id'], $perms) ? 'checked' : '' ?> class="form-checkbox h-5 w-5 text-yellow-500">
                <span><?= htmlspecialchars($m['nome']) ?></span>
              </label>
            <?php endforeach; ?>
          </div>
          <button type="submit" name="save_perms" class="bg-yellow-500 hover:bg-yellow-600 text-gray-900 font-bold py-2 px-4 rounded">
            Salvar Permissões
          </button>
        </form>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
