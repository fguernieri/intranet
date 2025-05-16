<?php

require_once '../../config/db.php';
include '../../sidebar.php';


$id = $_GET['id'] ?? null;
if (!$id) {
    echo "ID inválido.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM ficha_tecnica WHERE id = :id");
$stmt->execute([':id' => $id]);
$ficha = $stmt->fetch();

if (!$ficha) {
    echo "Ficha não encontrada.";
    exit;
}

$stmtIng = $pdo->prepare("SELECT * FROM ingredientes WHERE ficha_id = :id");
$stmtIng->execute([':id' => $id]);
$ingredientes = $stmtIng->fetchAll();
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <title><?= htmlspecialchars($ficha['nome_prato']) ?></title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <script src="https://cdn.tailwindcss.com"></script>

  <style>
    @media print {
      body {
        background: white !important;
        color: black !important;
        font-size: 11pt;
      }
      .no-print {
        display: none !important;
      }
      img {
        max-width: 100% !important;
        height: auto !important;
      }
      table {
        page-break-inside: avoid;
      }
      .print-container {
        width: 210mm;
        max-width: 100%;
        margin: 0 auto;
        padding: 20mm;
      }
    }
  </style>
</head>
<body class="bg-gray-900 text-gray-100 min-h-screen flex">

  <div class="print-container max-w-4xl mx-auto space-y-6">
  
    <?php if (isset($_GET['sucesso']) && $_GET['sucesso'] == 1): ?>
    <div id="alerta" class="fixed right-1 top-4 justify-right transform bg-green-500 text-white px-4 py-2 rounded shadow-lg transition-opacity z-50">
        Ficha salva com sucesso!
    </div>
    <script>
        setTimeout(() => {
            const alerta = document.getElementById('alerta');
            if (alerta) alerta.style.opacity = '0';
        }, 4000);
    </script>
    <?php endif; ?>

    <!-- Botão Voltar -->
    <div class="no-print mb-4">
      <a href="consulta.php"
         class="inline-block bg-gray-700 hover:bg-gray-600 text-white px-6 py-3 rounded shadow min-w-[170px] text-center font-semibold">
        ⬅️ Voltar para Consulta
      </a>
    </div>

    <h1 class="text-3xl font-bold text-cyan-400 text-center">
      <?= htmlspecialchars($ficha['nome_prato']) ?>
    </h1>

    <?php if (!empty($ficha['imagem'])): ?>
      <div class="flex justify-center">
        <img src="uploads/<?= htmlspecialchars($ficha['imagem']) ?>" alt="Imagem do prato"
             class="max-w-full md:max-w-[600px] mx-auto rounded shadow-lg border border-gray-700" />
      </div>
    <?php endif; ?>

    <div class="bg-gray-800 rounded shadow p-6">
      <p><span class="font-semibold text-cyan-400">Prato:</span> <?= htmlspecialchars($ficha['nome_prato']) ?></p>
      <p><span class="font-semibold text-cyan-400">Rendimento:</span> <?= htmlspecialchars($ficha['rendimento']) ?></p>
      <p><span class="font-semibold text-cyan-400">Responsável:</span> <?= htmlspecialchars($ficha['usuario']) ?></p>
      <p><span class="font-semibold text-cyan-400">Código Cloudify:</span> <?= htmlspecialchars($ficha['codigo_cloudify']) ?></p>
      <p><span class="font-semibold text-cyan-400">Data:</span> <?= date('d/m/Y H:i', strtotime($ficha['data_criacao'])) ?></p>
    </div>

    <div class="bg-gray-800 rounded shadow p-6">
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
                <td class="p-2"><?= htmlspecialchars($ing['codigo']) ?></td>
                <td class="p-2"><?= htmlspecialchars($ing['descricao']) ?></td>
                <td class="p-2"><?= htmlspecialchars($ing['quantidade']) ?></td>
                <td class="p-2"><?= htmlspecialchars($ing['unidade']) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Campo de Modo de Preparo com TinyMCE -->
    <div class="mt-6 ">
    <label for="modo_preparo" class="block mb-4 text-xl font-bold text-cyan-300">Modo de Preparo</label>
      <?php
        $editor_id = 'modo_preparo';
        $editor_name = 'modo_preparo';
        $editor_label = 'Modo de Preparo';
        $editor_value = $ficha['modo_preparo']; // ou valor vindo do banco
        include $_SERVER['DOCUMENT_ROOT'] . '/components/editor.php';
      ?>
    </div>     
      
  </div>

  <!-- Botão flutuante de imprimir -->
  <button onclick="window.print()" class="no-print fixed bottom-6 right-6 bg-cyan-500 hover:bg-cyan-600 text-white font-semibold py-3 px-5 rounded-full shadow-lg transition">
    🖨️ Imprimir
  </button>

</body>
</html>
