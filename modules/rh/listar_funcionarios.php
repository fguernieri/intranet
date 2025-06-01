<?php
// listar_funcionarios.php
require_once '../../config/db.php';
include '../../sidebar.php';


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
  <link rel="stylesheet" href="../../assets/css/style.css">

</head>
<body class="bg-gray-100 mt-12 mb-8 flex">

  <div class="mx-auto bg-white p-6 rounded-2xl shadow-md">
    <div class="flex justify-between items-center mb-4">
      <h1 class="text-2xl font-bold">Lista de Funcionários</h1>
      <a href="form_funcionario.php" class="btn-acao py-2 px-4">+ Novo Funcionário</a>
    </div>
    <div class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="p-3 text-left text-sm font-medium uppercase tracking-wider">Nome</th>
            <th class="p-3 text-left text-sm font-medium uppercase tracking-wider">CPF</th>
            <th class="p-3 text-left text-sm font-medium uppercase tracking-wider">Cargo</th>
            <th class="p-3 text-left text-sm font-medium uppercase tracking-wider">Empresa</th>
            <th class="p-3 text-left text-sm font-medium uppercase tracking-wider">Admissão</th>
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
                <td class="p-3 whitespace-nowrap">
                  <a href="form_funcionario.php?nome=<?php echo urlencode($func['nome_completo']); ?>" class="text-blue-600 text-sm hover:underline">
                    <?php echo htmlspecialchars($func['nome_completo']); ?>
                  </a>
                </td>
                <td class="p-3 text-sm whitespace-nowrap"><?php echo htmlspecialchars($func['cpf']); ?></td>
                <td class="p-3 text-sm whitespace-nowrap"><?php echo htmlspecialchars($func['cargo']); ?></td>
                <td class="p-3 text-sm whitespace-nowrap"><?php echo htmlspecialchars($func['empresa_contratante']); ?></td>
                <td class="p-3 text-sm whitespace-nowrap"><?php echo date('d/m/Y', strtotime($func['data_admissao'])); ?></td>
              </tr>
            <?php endforeach; ?>
          <?php endif; ?>
        </tbody>
      </table>
    </div>
  </div>
</body>
</html>
