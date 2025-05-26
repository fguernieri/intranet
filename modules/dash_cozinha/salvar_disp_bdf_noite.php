<?php
require_once '../../config/db.php';

$data = $_POST['data'] ?? null;
$nome = $_POST['nome'] ?? null;
$comentarios = $_POST['comentarios'] ?? null;

?><!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Resposta - Disp BDF Noite</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            primary: '#309898',
            warning: '#FF9F00',
            alert: '#F4631E',
            danger: '#CB0404'
          }
        }
      }
    }
  </script>
</head>
<body class="bg-primary bg-opacity-10 min-h-screen flex items-center justify-center">
  <main class="p-4">
<?php
if (!$data || !$nome) {
    echo "<div class='max-w-xl mx-auto mt-12 bg-white border-l-4 border-danger p-6 rounded-xl shadow-md'>
            <h2 class='text-xl font-semibold text-danger mb-2'>Erro</h2>
            <p class='text-sm text-gray-700'>Data e nome são obrigatórios.</p>
          </div></main></body></html>";
    exit;
}

try {
    $pdo->beginTransaction();

    foreach ($_POST as $key => $value) {
        if (in_array($key, ['data', 'nome', 'comentarios'])) continue;
        if (!in_array($value, ['0', '1'])) continue;

        $stmt = $pdo->prepare("INSERT INTO disp_bdf_noite (data, nome_usuario, codigo_cloudify, disponivel, comentarios)
                               VALUES (:data, :nome, :codigo, :disponivel, :comentarios)
                               ON DUPLICATE KEY UPDATE disponivel = VALUES(disponivel), comentarios = VALUES(comentarios)");

        $stmt->execute([
            ':data' => $data,
            ':nome' => $nome,
            ':codigo' => $key,
            ':disponivel' => (int)$value,
            ':comentarios' => $comentarios
        ]);
    }

    $pdo->commit();
    echo "<div class='max-w-xl mx-auto mt-12 bg-white border-l-4 border-primary p-6 rounded-xl shadow-md'>
            <h2 class='text-xl font-semibold text-primary mb-2'>Formulário salvo com sucesso!</h2>
            <p class='text-sm text-gray-700'>Obrigado!</p>
          </div>";
} catch (PDOException $e) {
    $pdo->rollBack();
    echo "<div class='max-w-xl mx-auto mt-12 bg-white border-l-4 border-danger p-6 rounded-xl shadow-md'>
            <h2 class='text-xl font-semibold text-danger mb-2'>Erro ao salvar</h2>
            <p class='text-sm text-gray-700'>" . $e->getMessage() . "</p>
          </div>";
}
?>
  </main>
</body>
</html>