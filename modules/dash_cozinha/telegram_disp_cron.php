#!/usr/bin/env php
<?php
declare(strict_types=1);

// 0) Carrega a conexão PDO (sem sessões nem cabeçalhos HTTP)
require __DIR__ . '/../../config/db.php';

// Função para escapar caracteres especiais no Markdown (legacy) do Telegram
function escapeTelegramMarkdown(string $texto): string {
    $caracteres = ['\\', '_', '*', '`', '[', ']'];
    $escapados  = ['\\\\', '\\_', '\\*', '\\`', '\\[', '\\]'];
    return str_replace($caracteres, $escapados, $texto);
}

// 1) Mapeamento formKey -> [label, nome da tabela de respostas]
$formConfig = [
    'disp_bdf_almoco'     => [
        'label' => 'Disponibilidade BDF (Almoço)',
        'table' => 'disp_bdf_almoco',
    ],
    'disp_bdf_almoco_fds' => [
        'label' => 'Disponibilidade BDF (Almoço - FDS)',
        'table' => 'disp_bdf_almoco_fds',
    ],
    'disp_bdf_noite'      => [
        'label' => 'Disponibilidade BDF (Noite)',
        'table' => 'disp_bdf_noite',
    ],
    'disp_wab'            => [
        'label' => 'Disponibilidade WAB',
        'table' => 'disp_wab',
    ],
];

// 2) Carrega do banco o templateMarkdown para cada formKey
$templates = [];
$stmtTpl = $pdo->prepare("
    SELECT form_key, template_md
      FROM telegram_disp_templates
     WHERE form_key = :form_key
");
foreach ($formConfig as $formKey => $cfg) {
    $stmtTpl->execute([':form_key' => $formKey]);
    $row = $stmtTpl->fetch(PDO::FETCH_ASSOC);
    $templates[$formKey] = $row['template_md'] ?? '';
}

// 3) Preparar as consultas fixas que vamos reusar:

// 3.1) Lê o maior response_id já enviado para este formKey
$stmtGetLast = $pdo->prepare("
    SELECT COALESCE(MAX(response_id), 0) AS last_sent_id
      FROM automation_disp
     WHERE form_key = :form_key
");

// 3.2) Busca o próximo registro (o mais recente) cuja id > last_sent_id
$stmtNext = $pdo->prepare("
    SELECT id, data, nome_usuario, comentarios
      FROM {TABLE}
     WHERE id > :last_sent_id
     ORDER BY id DESC
     LIMIT 1
");

// 3.3) Busca todos os pratos para um determinado (data, nome_usuario)
$stmtDetails = $pdo->prepare("
    SELECT f.nome_prato, d.disponivel
      FROM {TABLE} AS d
 LEFT JOIN ficha_tecnica AS f
        ON f.codigo_cloudify = d.codigo_cloudify
     WHERE d.data = :data_envio
       AND d.nome_usuario = :usuario
     ORDER BY d.id ASC
");

// 3.4) Busca destinatários fixos (form_id = 3)
$stmtRecipients = $pdo->prepare("
    SELECT chat_id
      FROM telegram_recipient_forms
     WHERE form_id = 3
");

// 3.5) Insere em automation_disp para registrar que já enviamos esse response_id
$stmtInsertLog = $pdo->prepare("
    INSERT INTO automation_disp (form_key, response_id, sent_at)
    VALUES (:form_key, :response_id, NOW())
");

// 4) Itera sobre cada formKey e tenta enviar uma mensagem nova (se existir id > last_sent_id)
$telegramToken  = '8013231460:AAEhGNGKvHmZz4F_Zc-krqmtogdhX8XR3Bk';
$telegramApiUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";

foreach ($formConfig as $formKey => $cfg) {
    $table      = $cfg['table'];
    $templateMd = $templates[$formKey];

    // Se não houver template cadastrado para esse formKey, ignora
    if (empty($templateMd)) {
        continue;
    }

    // 4.1) Obter o último id já enviado para este formKey
    $stmtGetLast->execute([':form_key' => $formKey]);
    $rowLast = $stmtGetLast->fetch(PDO::FETCH_ASSOC);
    $lastSentId = (int)$rowLast['last_sent_id'];

    // 4.2) Buscar o próximo registro com id > lastSentId
    $sqlNext = str_replace('{TABLE}', $table, $stmtNext->queryString);
    $stmtFetchNext = $pdo->prepare($sqlNext);
    $stmtFetchNext->execute([
        ':last_sent_id' => $lastSentId
    ]);
    $nextRow = $stmtFetchNext->fetch(PDO::FETCH_ASSOC);

    // Se não encontrou nada (não há registro novo), pula para o próximo formKey
    if (!$nextRow) {
        continue;
    }

    // 4.3) Pegamos esse único registro (id, data, nome_usuario, comentarios)
    $respId          = (int)$nextRow['id'];
    $dataEnvio       = $nextRow['data'];
    $usuario         = $nextRow['nome_usuario'];
    $comentarioGeral = trim((string)$nextRow['comentarios']);

    // 4.4) Buscar todos os pratos deste (data, nome_usuario)
    $sqlDetails = str_replace('{TABLE}', $table, $stmtDetails->queryString);
    $stmtFetchDetails = $pdo->prepare($sqlDetails);
    $stmtFetchDetails->execute([
        ':data_envio' => $dataEnvio,
        ':usuario'    => $usuario
    ]);
    $rowsDetalhes = $stmtFetchDetails->fetchAll(PDO::FETCH_ASSOC);

    // 4.5) Montar array de placeholders para o template
    $assoc = [
        'data'          => $dataEnvio,
        'nome_usuario'  => $usuario,
        'comentarios'   => $comentarioGeral,
    ];

    // 4.6) Construir a lista de pratos (cada linha “- prato : ✅/❌”)
    $lista = [];
    foreach ($rowsDetalhes as $r) {
        $nome = htmlspecialchars($r['nome_prato'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
        $disp = $r['disponivel'] ? '✅' : '❌';
        $lista[] = "- {$nome} : {$disp}";
    }
    // Adiciona no array para substituir no template
    $assoc['lista_codigos'] = implode("\n", $lista);

    // 4.7) Substituir cada {campo} no template pelo valor correspondente
    $linhas = explode("\n", $templateMd);
    $saida  = [];
    foreach ($linhas as $linha) {
        preg_match_all('/\{([^}]+)\}/', $linha, $matches);
        $placeholders = $matches[1];
        $novaLinha = $linha;
        $incluir   = true;

        foreach ($placeholders as $label) {
            if (!isset($assoc[$label]) || trim($assoc[$label]) === '') {
                $incluir = false;
                break;
            }
            $valor = htmlspecialchars($assoc[$label], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $novaLinha = str_replace("{" . $label . "}", $valor, $novaLinha);
        }

        if ($incluir) {
            $saida[] = $novaLinha;
        }
    }
    $textoEnviar = implode("\n", $saida);

    // 4.8) Buscar todos os chat_id (form_id = 3)
    $stmtRecipients->execute();
    $destRows = $stmtRecipients->fetchAll(PDO::FETCH_COLUMN, 0);

    // 4.9) Enviar a mensagem ao Telegram **UMA ÚNICA VEZ** para cada chat_id
    foreach ($destRows as $chatId) {
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

    // 4.10) Gravar em automation_disp que enviamos este response_id
    $stmtInsertLog->execute([
        ':form_key'    => $formKey,
        ':response_id' => $respId
    ]);
}

// Fim do script. Não imprime nada em tela.
