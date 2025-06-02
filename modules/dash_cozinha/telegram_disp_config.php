<?php
declare(strict_types=1);

// 0) Inclusões obrigatórias
require __DIR__ . '/../../auth.php';
require __DIR__ . '/../../config/db.php';

// 0.1) Flash de sucesso do teste (utilizando sessão)
$flashTeste = '';
if (isset($_SESSION['sucesso_teste'])) {
    // Recupera a mensagem enviada e limpa o flash
    $flashTeste = $_SESSION['sucesso_teste'];
    unset($_SESSION['sucesso_teste']);
}

// Função para escapar caracteres especiais no Markdown legado do Telegram
function escapeTelegramMarkdown(string $texto): string {
    // Somente escapamos os símbolos que podem quebrar itálico/negrito/código/links
    $caracteres = ['\\', '_', '*', '`', '[', ']'];
    $escapados  = ['\\\\', '\\_', '\\*', '\\`', '\\[', '\\]'];
    return str_replace($caracteres, $escapados, $texto);
}

// 1) Mapeie aqui as suas quatro páginas de disponibilidade,
//    usando a mesma chave que você usará para "form_key" e
//    indicando o nome amigável para exibir no config.
$formKeys = [
    'disp_bdf_almoco'      => 'Disponibilidade BDF (Almoço)',
    'disp_bdf_almoco_fds'  => 'Disponibilidade BDF (Almoço - FDS)',
    'disp_bdf_noite'       => 'Disponibilidade BDF (Noite)',
    'disp_wab'             => 'Disponibilidade WAB'
];

// 2) Captura de POST: pode ser "save_template" ou "send_test"
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action  = $_POST['action'] ?? '';
    $formKey = $_POST['form_key'] ?? '';

    // 2.1) Salvar / Atualizar o template Markdown no banco
    if ($action === 'save_template' && isset($formKeys[$formKey])) {
        $raw  = $_POST['template_md'][$formKey] ?? '';
        $md   = trim($raw);

        // Verifica se já existe registro para esse form_key
        $stmt = $pdo->prepare("
            SELECT COUNT(*) 
              FROM telegram_disp_templates 
             WHERE form_key = :form_key
        ");
        $stmt->execute([':form_key' => $formKey]);
        $exists = (bool)$stmt->fetchColumn();

        if ($exists) {
            // Atualiza
            $upd = $pdo->prepare("
                UPDATE telegram_disp_templates
                   SET template_md = :template_md
                 WHERE form_key = :form_key
            ");
            $upd->execute([
                ':template_md' => $md,
                ':form_key'    => $formKey
            ]);
        } else {
            // Insere
            $ins = $pdo->prepare("
                INSERT INTO telegram_disp_templates (form_key, template_md)
                VALUES (:form_key, :template_md)
            ");
            $ins->execute([
                ':form_key'    => $formKey,
                ':template_md' => $md
            ]);
        }

        // Redireciona para evitar re-submission
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }

    // 2.2) Enviar Teste: pega a última resposta da tabela X e manda ao Telegram
    if ($action === 'send_test' && isset($formKeys[$formKey])) {
        // 2.2.1) Carrega o template.md do banco
        $tplStmt = $pdo->prepare("
            SELECT template_md
              FROM telegram_disp_templates
             WHERE form_key = :form_key
        ");
        $tplStmt->execute([':form_key' => $formKey]);
        $tplRow = $tplStmt->fetch(PDO::FETCH_ASSOC);
        $templateMd = $tplRow['template_md'] ?? '';

        // 2.2.2) Descobre em qual tabela buscar a última resposta
        switch ($formKey) {
            case 'disp_bdf_almoco':
                $respTable = 'disp_bdf_almoco';
                break;
            case 'disp_bdf_almoco_fds':
                $respTable = 'disp_bdf_almoco_fds';
                break;
            case 'disp_bdf_noite':
                $respTable = 'disp_bdf_noite';
                break;
            case 'disp_wab':
                $respTable = 'disp_wab';
                break;
            default:
                $respTable = '';
        }

        if ($respTable !== '') {
            // 2.2.3) Primeiro, busque o último registro (ORDER BY id DESC LIMIT 1)
            $respStmt = $pdo->prepare("
                SELECT *
                  FROM {$respTable}
                 ORDER BY id DESC
                 LIMIT 1
            ");
            $respStmt->execute();
            $lastResp = $respStmt->fetch(PDO::FETCH_ASSOC);

            if ($lastResp) {
                // 2.2.4) Captura 'data' e 'nome_usuario' para buscar todas as linhas
                $dataEnvio = $lastResp['data'];
                $usuario   = $lastResp['nome_usuario'];
                $comentarioGeral = trim((string)$lastResp['comentarios']);

                $allStmt = $pdo->prepare("
                    SELECT f.nome_prato, d.disponivel
                      FROM {$respTable} AS d
                      LEFT JOIN ficha_tecnica AS f
                        ON f.codigo_cloudify = d.codigo_cloudify
                     WHERE d.data = :data_envio
                       AND d.nome_usuario = :usuario
                     ORDER BY d.id ASC
                ");
                $allStmt->execute([
                    ':data_envio' => $dataEnvio,
                    ':usuario'    => $usuario
                ]);
                $rows = $allStmt->fetchAll(PDO::FETCH_ASSOC);

                // 2.2.5) Monte o array de placeholders:
                $assoc = [
                    'data'         => (string)$dataEnvio,
                    'nome_usuario' => (string)$usuario,
                ];

                // 2.2.6) Constrói a lista apenas com nome do prato + ícone de disponibilidade
                $lista = [];
                foreach ($rows as $r) {
                    $nome = htmlspecialchars($r['nome_prato'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $disp = $r['disponivel'] ? '✅' : '❌';
                    $lista[] = "- {$nome} : {$disp}";
                }
                $assoc['lista_codigos'] = implode("\n", $lista);

                // Se seu template usar {comentarios}, preencha
                $assoc['comentarios'] = $comentarioGeral;

                // 2.2.7) Substitui cada {label} no template pelo valor correspondente
                $linhas = explode("\n", $templateMd);
                $saida  = [];

                foreach ($linhas as $linha) {
                    preg_match_all('/\{([^}]+)\}/', $linha, $matches);
                    $placeholders = $matches[1]; // labels sem chaves

                    $novaLinha = $linha;
                    $incluir   = true;

                    foreach ($placeholders as $label) {
                        if (!isset($assoc[$label]) || trim($assoc[$label]) === '') {
                            $incluir = false;
                            break;
                        }
                        $valor     = htmlspecialchars($assoc[$label], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                        $novaLinha = str_replace("{" . $label . "}", $valor, $novaLinha);
                    }

                    if ($incluir) {
                        $saida[] = $novaLinha;
                    }
                }

                $textoEnviar = implode("\n", $saida);

                // 2.2.8) Salva no flash de sessão para exibir ao retornar à página
                $_SESSION['sucesso_teste'] = $textoEnviar;

                // 2.2.9) Envie ao Telegram (form_id = 3 para todos)
                $telegramToken  = '8013231460:AAEhGNGKvHmZz4F_Zc-krqmtogdhX8XR3Bk';
                $telegramApiUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";

                $destStmt = $pdo->prepare("
                    SELECT chat_id
                      FROM telegram_recipient_forms
                     WHERE form_id = :form_id
                ");
                $destStmt->execute([':form_id' => 3]);
                $destRows = $destStmt->fetchAll(PDO::FETCH_COLUMN, 0);

                foreach ($destRows as $chatId) {
                    // Aplica escape no texto antes de enviar
                    $params = [
                        'chat_id'    => $chatId,
                        'text'       => escapeTelegramMarkdown($textoEnviar),
                        'parse_mode' => 'Markdown'
                    ];
                    $ch = curl_init($telegramApiUrl);
                    curl_setopt($ch, CURLOPT_POST, true);
                    curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                    curl_exec($ch);
                    curl_close($ch);
                }
            } // fim if ($lastResp)
        } // fim if ($respTable !== '')
        
        // Redireciona para a mesma página (para não reenviar no F5)
        header('Location: ' . $_SERVER['REQUEST_URI']);
        exit;
    }
}

// 3) Carregue os templates atuais para exibir no formulário
$currentTemplates = [];
$inClause         = implode(',', array_fill(0, count($formKeys), '?'));
$stmt             = $pdo->prepare("
    SELECT form_key, template_md
      FROM telegram_disp_templates
     WHERE form_key IN ({$inClause})
");
$stmt->execute(array_keys($formKeys));
$rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
foreach ($rows as $r) {
    $currentTemplates[$r['form_key']] = $r['template_md'];
}
unset($rows);
?>
<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Configuração – Templates Telegram (Disponibilidade)</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="../../assets/css/style.css">
</head>
<body class="bg-gray-100 text-gray-900">
  <main class="p-6 md:ml-64">
    <h1 class="text-3xl font-bold mb-6">Configuração de Templates – Telegram (Disponibilidade)</h1>
    <p class="mb-4 text-gray-700">
      Edite o Markdown de cada modelo e clique em “Salvar Modelo”.  
      Para testar imediatamente a última resposta, clique em “Enviar Teste”.
    </p>

    <!-- Mensagem de sucesso ao enviar teste -->
    <?php if (!empty($flashTeste)): ?>
      <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-6">
        <strong class="font-semibold">Teste enviado com sucesso!</strong>
        <p class="mt-2 text-sm">
          Abaixo você vê o Markdown completo que foi enviado ao Telegram. Compare com o que chegou no app:
        </p>
        <pre class="mt-2 bg-gray-50 border border-gray-200 rounded p-3 overflow-auto text-sm"><?= htmlspecialchars($flashTeste, ENT_QUOTES, 'UTF-8') ?></pre>
      </div>
    <?php endif; ?>

    <?php foreach ($formKeys as $key => $titulo): ?>
      <form method="POST" class="bg-white shadow rounded-lg p-6 mb-6">
        <h2 class="text-2xl font-semibold mb-4"><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></h2>

        <label class="block text-gray-700 mb-2 font-medium">
          Template Markdown para <em><?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?></em>:
        </label>
        <textarea
          name="template_md[<?= $key; ?>]"
          class="w-full p-2 border border-gray-300 rounded h-48 font-mono text-sm"
          placeholder="Digite aqui o template para <?= htmlspecialchars($titulo, ENT_QUOTES, 'UTF-8'); ?>…"
        ><?= htmlspecialchars($currentTemplates[$key] ?? '', ENT_QUOTES, 'UTF-8'); ?></textarea>

        <p class="mt-2 text-sm text-gray-500">
          Exemplos de placeholders (copie exatamente do nome das colunas no BD):<br>
          <code class="bg-gray-100 px-1 rounded">{data}</code>,
          <code class="bg-gray-100 px-1 rounded">{nome_usuario}</code>,
          <code class="bg-gray-100 px-1 rounded">{lista_codigos}</code>,
          <code class="bg-gray-100 px-1 rounded">{comentarios}</code><br>
          *Cada `{campo}` deve bater exatamente com a coluna na tabela.*
        </p>

        <div class="mt-4 flex space-x-2">
          <!-- Botão Salvar Modelo -->
          <button
            type="submit"
            name="action"
            value="save_template"
            class="btn-acao-azul"
          >
            Salvar Modelo
          </button>

          <!-- Botão Enviar Teste -->
          <button
            type="submit"
            name="action"
            value="send_test"
            class="btn-acao-verde"
          >
            Enviar Teste
          </button>

          <!-- Passa junto o form_key para diferenciarmos qual template testar -->
          <input type="hidden" name="form_key" value="<?= htmlspecialchars($key, ENT_QUOTES, 'UTF-8'); ?>" />
        </div>
      </form>
    <?php endforeach; ?>
  </main>
</body>
</html>
