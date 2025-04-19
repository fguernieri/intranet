<?php
// Inicia sessão, protege página e configura DB
require_once 'auth.php';
require_once 'config/db.php';
include __DIR__ . '/sidebar.php';

// Busca módulos permitidos
$stmt = $pdo->prepare(
  "SELECT m.nome, m.descricao, m.link
   FROM modulos m
   INNER JOIN modulos_usuarios mu ON m.id = mu.modulo_id
   WHERE mu.usuario_id = :uid AND m.ativo = 1
   ORDER BY m.nome"
);
$stmt->execute(['uid' => $_SESSION['usuario_id']]);
$modulos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <title>Painel - Intranet Bastards</title>
  <style>
  body { visibility: hidden; }
</style>

<script src="https://cdn.tailwindcss.com"></script>
<link rel="stylesheet" href="../../assets/css/style.css">

<script>
  window.addEventListener('load', () => {
    document.body.style.visibility = 'visible';
  });
</script>
</head>
<body class="bg-gray-900 text-white min-h-screen flex">

  <!-- Conteúdo principal -->
  <main class="flex-1 p-10">
    <header class="mb-8">
      <h1 class="text-3xl font-bold">Bem-vindo, <?php echo htmlspecialchars($_SESSION['usuario_nome']); ?></h1>
      <p class="text-gray-400 text-sm">
        <?php
          // Formatação de data com IntlDateFormatter
          $hoje = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
          $fmt = new IntlDateFormatter(
            'pt_BR',
            IntlDateFormatter::FULL,
            IntlDateFormatter::NONE,
            'America/Sao_Paulo',
            IntlDateFormatter::GREGORIAN,
            "EEEE, d 'de' MMMM 'de' yyyy"
          );
          echo ucfirst($fmt->format($hoje));
        ?>
      </p>
    </header>

    <!-- Grid de módulos -->
    <section class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
      <?php if (empty($modulos)): ?>
        <p class="text-gray-400">Nenhum módulo disponível.</p>
      <?php else: ?>
        <?php foreach ($modulos as $mod): ?>
          <a href="<?php echo htmlspecialchars($mod['link']); ?>" class="block bg-gray-800 p-6 rounded-lg shadow hover:shadow-lg transition">
            <h2 class="text-xl font-semibold mb-2"><?php echo htmlspecialchars($mod['nome']); ?></h2>
            <p class="text-sm text-gray-400"><?php echo htmlspecialchars($mod['descricao']); ?></p>
          </a>
        <?php endforeach; ?>
      <?php endif; ?>
    </section>
  </main>
</body>
</html>
