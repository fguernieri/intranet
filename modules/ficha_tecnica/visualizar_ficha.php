<?php
declare(strict_types=1);

// 1) Includes e inicialização do PDO
require_once __DIR__ . '/../../config/db.php';
// Sidebar e controles não-print incluídos no body

// 2) Captura e validação do ID
$id = filter_input(INPUT_GET, 'id', FILTER_VALIDATE_INT);
if (!$id) {
    exit('ID inválido.');
}

// 3) Fetch da ficha técnica
$stmtFicha = $pdo->prepare('SELECT * FROM ficha_tecnica WHERE id = :id');
$stmtFicha->execute([':id' => $id]);
$ficha = $stmtFicha->fetch(PDO::FETCH_ASSOC);
if (!$ficha) {
    exit('Ficha não encontrada.');
}

// 4) Fetch dos ingredientes
$stmtIngs = $pdo->prepare('SELECT codigo, descricao, quantidade, unidade FROM ingredientes WHERE ficha_id = :id');
$stmtIngs->execute([':id' => $id]);
$ingredientes = $stmtIngs->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title>Ficha Técnica – <?= htmlspecialchars($ficha['nome_prato'], ENT_QUOTES) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>

  <!-- CSS de impressão para controle de quebras -->
  <style media="print">
    @page { size: A4 portrait; margin: 10mm; }
    body { background: #fff !important; color: #000 !important; }
    .no-print { display: none !important; }
    .print-container { display: block !important; width: auto !important; margin: 0 !important; padding: 0 !important; }

    /* Seções não devem quebrar internamente */
    .section-ingredientes,
    .section-preparo,
    .section-montagem,
    .section-observacoes {
      page-break-inside: avoid;
      break-inside: avoid-page;
    }

    /* Força modo de preparo em nova página se não couber inteiro */
    .section-preparo {
      page-break-before: always;
      break-before: page;
    }

    /* Tabelas: evita quebra de linhas, repete cabeçalho */
    thead { display: table-header-group; }
    tbody { display: table-row-group; }
    table, tr { page-break-inside: avoid; break-inside: avoid-page; }

    /* Outros blocos que não devem quebrar */
    img, ul, ol, .prose { page-break-inside: avoid; break-inside: avoid-page; }
  </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex">

  <!-- Sidebar e controles de navegação (não-print) -->
  <div class="no-print">
    <?php include __DIR__ . '/../../sidebar.php'; ?>
  </div>

  <main class="print-container bg-gray-900 text-gray-100 p-6 mx-auto space-y-6">

    <!-- Alerta de sucesso (não-print) -->
    <?php if (!empty($_GET['sucesso']) && $_GET['sucesso'] === '1'): ?>
      <div id="alerta" class="no-print fixed right-4 top-4 bg-green-500 text-white px-4 py-2 rounded shadow-lg z-50">
        Ficha salva com sucesso!
      </div>
      <script>
        setTimeout(() => document.getElementById('alerta')?.style.opacity = '0', 4000);
      </script>
    <?php endif; ?>

    <!-- Botão Voltar (não-print) -->
    <div class="no-print">
      <a href="consulta.php" class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded shadow font-semibold">
        ⬅️ Voltar para Consulta
      </a>
    </div>

    <!-- Título -->
    <h1 class="text-3xl font-bold text-cyan-400 text-center">
      <?= htmlspecialchars($ficha['nome_prato'], ENT_QUOTES) ?>
    </h1>

    <!-- Imagem do prato (se houver) -->
    <?php if (!empty($ficha['imagem'])): ?>
      <div class="flex justify-center">
        <img src="uploads/<?= htmlspecialchars($ficha['imagem'], ENT_QUOTES) ?>"
             alt="Imagem do prato"
             class="max-w-full md:max-w-lg mx-auto rounded shadow-lg border border-gray-700">
      </div>
    <?php endif; ?>

    <!-- Detalhes gerais -->
    <div class="bg-gray-800 rounded shadow p-6 space-y-2">
      <p><strong>Prato:</strong> <?= htmlspecialchars($ficha['nome_prato'], ENT_QUOTES) ?></p>
      <p><strong>Rendimento:</strong> <?= htmlspecialchars($ficha['rendimento'], ENT_QUOTES) ?></p>
      <p><strong>Responsável:</strong> <?= htmlspecialchars($ficha['usuario'], ENT_QUOTES) ?></p>
      <p><strong>Código Cloudify:</strong> <?= htmlspecialchars($ficha['codigo_cloudify'], ENT_QUOTES) ?></p>
      <p><strong>Data:</strong> <?= date('d/m/Y H:i', strtotime($ficha['data_criacao'])) ?></p>
    </div>

    <!-- Ingredientes em tabela -->
    <section class="section-ingredientes bg-gray-800 rounded shadow p-6">
      <h2 class="text-xl font-bold text-cyan-300 mb-4">Ingredientes</h2>
      <div class="overflow-x-auto">
        <table class="w-full text-sm text-center border border-gray-700">
          <thead class="bg-gray-700 text-cyan-200">
            <tr>
              <th class="p-2">Código</th>
              <th class="p-2">Descrição</th>
              <th class="p-2">Quantidade</th>
              <th class="p-2">Unidade</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-700">
            <?php foreach ($ingredientes as $ing): ?>
              <tr>
                <td class="p-2"><?= htmlspecialchars($ing['codigo'], ENT_QUOTES) ?></td>
                <td class="p-2"><?= htmlspecialchars($ing['descricao'], ENT_QUOTES) ?></td>
                <td class="p-2"><?= htmlspecialchars($ing['quantidade'], ENT_QUOTES) ?></td>
                <td class="p-2"><?= htmlspecialchars($ing['unidade'], ENT_QUOTES) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </section>

    <!-- Modo de Preparo -->
    <section class="section-preparo bg-gray-800 rounded shadow p-6 prose print:prose-sm">
      <h2 class="text-xl font-bold text-cyan-300 mb-4">Modo de Preparo</h2>
      <?= $ficha['modo_preparo'] ?>
    </section>

    <!-- Botão Imprimir Flutuante (não-print) -->
    <button onclick="window.print()"
            class="no-print fixed bottom-6 right-6 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-3 px-5 rounded-full shadow-lg">
      🖨️ Imprimir
    </button>

  </main>
</body>
</html>
