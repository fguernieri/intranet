<?php
// listar_funcionarios.php
require_once '../../config/db.php';

// Busca todos os funcionários
$stmt = $pdo->query(
    'SELECT nome_completo, cpf, rg, data_nascimento, empresa_contratante, cargo, data_admissao FROM funcionarios ORDER BY nome_completo'
);
$funcionarios = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="utf-8" />
  <title>Lista de Funcionários</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-400 mt-12 mb-8 min-h-screen flex">
  <?php include '../../sidebar.php'; ?>

  <div class="max-w-4xl mx-auto bg-white p-6 rounded-2xl shadow-md">
    <h2 class="text-xl font-bold mb-4">Lista de Funcionários</h2>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Nome</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">CPF</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Cargo</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Empresa</th>
            <th class="px-6 py-3 text-left text-xs font-medium uppercase tracking-wider">Admissão</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <?php if (count($funcionarios) === 0): ?>
            <tr>
              <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">Nenhum funcionário encontrado.</td>
            </tr>
          <?php else: ?>
            <?php foreach ($funcionarios as $func): ?>
              <tr>
                <td class="px-6 py-4 whitespace-nowrap">
                  <a href="form_funcionario.php?nome=<?php echo urlencode($func['nome_completo']); ?>" class="text-blue-600 hover:underline">
                    <?php echo htmlspecialchars($func['nome_completo']); ?>
                  </a>
                </td>
                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($func['cpf']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($func['cargo']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?php echo htmlspecialchars($func['empresa_contratante']); ?></td>
                <td class="px-6 py-4 whitespace-nowrap"><?php echo date('d/m/Y', strtotime($func['data_admissao'])); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
