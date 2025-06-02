#!/usr/bin/env php
<?php
declare(strict_types=1);

// Este script deve ser rodado via cron (CLI), sem interface web.
// Ele busca respostas novas agrupadas por (data, nome_usuario) em cada tabela de disponibilidade,
// envia UMA mensagem ao Telegram por grupo e registra em "automation_disp" para não reenviar.

// 0) Inclusão de configuração do banco
require __DIR__ . '/../../config/db.php';

// Função para escapar caracteres especiais no Markdown legado do Telegram
function escapeTelegramMarkdown(string $texto): string {
    $caracteres = ['\\', '_', '*', '`', '[', ']'];
    $escapados  = ['\\\\', '\\_', '\\*', '\\`', '\\[', '\\]'];
    return str_replace($caracteres, $escapados, $texto);
}

// 1) Mapping de formKey para nome amigável e tabela de respostas
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

// 2) Obtenha o template Markdown para cada formKey
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

// 3) Para cada formKey, busque grupos (data, nome_usuario) e envie
$telegramToken  = '8013231460:AAEhGNGKvHmZz4F_Zc-krqmtogdhX8XR3Bk';
$telegramApiUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";

// 3.1) Consulta para obter um único ID por (data, nome_usuario) ainda não enviado
$stmtNew = $pdo->prepare("
    SELECT 
      MIN(id) AS id, 
      data, 
      nome_usuario, 
      comentarios
    FROM {TABLE}
    WHERE id NOT IN (
      SELECT response_id 
        FROM automation_disp 
       WHERE form_key = :form_key
    )
    GROUP BY data, nome_usuario, comentarios
    ORDER BY data ASC, nome_usuario ASC
");

// 3.2) Consulta para detalhes dos pratos de um grupo
$stmtDetails = $pdo->prepare("
    SELECT f.nome_prato, d.disponivel
      FROM {TABLE} AS d
 LEFT JOIN ficha_tecnica AS f
        ON f.codigo_cloudify = d.codigo_cloudify
     WHERE d.data = :data_envio
       AND d.nome_usuario = :usuario
     ORDER BY d.id ASC
");

// 3.3) Consulta para obter destinatários fixos (form_id = 3)
$stmtRecipients = $pdo->prepare("
    SELECT chat_id
      FROM telegram_recipient_forms
     WHERE form_id = 3
");

// 3.4) Consulta para logar cada envio em automation_disp
$stmtInsertLog = $pdo->prepare("
    INSERT INTO automation_disp (form_key, response_id, sent_at)
    VALUES (:form_key, :response_id, NOW())
");

foreach ($formConfig as $formKey => $cfg) {
    $table      = $cfg['table'];
    $templateMd = $templates[$formKey];
    if (empty($templateMd)) {
        // Se não houver template cadastrado, pula
        continue;
    }

    // 3.5) Busque grupos novos (um registro por data + usuário)
    $queryNew = str_replace('{TABLE}', $table, $stmtNew->queryString);
    $stmtFetchNew = $pdo->prepare($queryNew);
    $stmtFetchNew->execute([':form_key' => $formKey]);
    $newGroups = $stmtFetchNew->fetchAll(PDO::FETCH_ASSOC);

    foreach ($newGroups as $grp) {
        $respId         = (int)$grp['id'];
        $dataEnvio      = $grp['data'];
        $usuario        = $grp['nome_usuario'];
        $comentarioGeral = trim((string)$grp['comentarios']);

        // 3.6) Busque detalhes dos pratos para este grupo (data + usuário)
        $queryDetails = str_replace('{TABLE}', $table, $stmtDetails->queryString);
        $stmtFetchDetails = $pdo->prepare($queryDetails);
        $stmtFetchDetails->execute([
            ':data_envio' => $dataEnvio,
            ':usuario'    => $usuario
        ]);
        $rowsDetalhes = $stmtFetchDetails->fetchAll(PDO::FETCH_ASSOC);

        // 3.7) Monte o array de placeholders
        $assoc = [
            'data'          => $dataEnvio,
            'nome_usuario'  => $usuario,
            'comentarios'   => $comentarioGeral,
        ];

        // 3.8) Construa a lista de pratos
        $lista = [];
        foreach ($rowsDetalhes as $r) {
            $nome = htmlspecialchars($r['nome_prato'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
            $disp = $r['disponivel'] ? '✅' : '❌';
            $lista[] = "- {$nome} : {$disp}";
        }
        $assoc['lista_codigos'] = implode("\n", $lista);

        // 3.9) Substitua placeholders no template
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

        // 3.10) Busque destinatários (form_id = 3)
        $stmtRecipients->execute();
        $destRows = $stmtRecipients->fetchAll(PDO::FETCH_COLUMN, 0);

        // 3.11) Envie ao Telegram (apenas UMA vez por data + usuário)
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

        // 3.12) Registre este envio em automation_disp
        $stmtInsertLog->execute([
            ':form_key'    => $formKey,
            ':response_id' => $respId
        ]);
    }
}

// Fim do script. Não imprimir nada em tela.
