#!/usr/bin/env php
<?php
declare(strict_types=1);

// 0) Conexão PDO
require __DIR__ . '/../../config/db.php';

// Função de escape de Markdown (legacy) para Telegram
function escapeTelegramMarkdown(string $texto): string {
    $caracteres = ['\\', '_', '*', '`', '[', ']'];
    $escapados  = ['\\\\', '\\_', '\\*', '\\`', '\\[', '\\]'];
    return str_replace($caracteres, $escapados, $texto);
}

// 1) Mapeamento formKey → [label, tabela de disponibilidade]
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

// 2) Carrega o template Markdown para cada formKey
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

// 3) Preparar consultas fixas:

// 3.1) Obter a data máxima já processada para este formKey
$stmtGetMaxDate = $pdo->prepare("
    SELECT COALESCE(MAX(last_sent_date), '0000-00-00') AS last_date
      FROM automation_disp
     WHERE form_key = :form_key
");

// 3.2) Buscar datas novas (data > last_date)
$stmtNewDates = $pdo->prepare("
    SELECT DISTINCT data
      FROM {TABLE}
     WHERE data > :last_date
     ORDER BY data ASC
");

// 3.3) Buscar usuários distintos que preencheram uma determinada data
$stmtUsers = $pdo->prepare("
    SELECT DISTINCT nome_usuario
      FROM {TABLE}
     WHERE data = :data_envio
");

// 3.4) Buscar detalhes dos pratos para (data, nome_usuario)
// *** AQUI INCLUÍMOS `d.id` para ter o response_id válido ***
$stmtDetails = $pdo->prepare("
    SELECT 
      d.id           AS disp_id,            -- ← quer dizer “ID desta resposta de disponibilidade”
      f.nome_prato, 
      d.disponivel, 
      d.comentarios
      FROM {TABLE} AS d
 LEFT JOIN ficha_tecnica AS f
        ON f.codigo_cloudify = d.codigo_cloudify
     WHERE d.data = :data_envio
       AND d.nome_usuario = :usuario
     ORDER BY d.id ASC
");

// 3.5) Buscar destinatários (form_id = 3)
$stmtRecipients = $pdo->prepare("
    SELECT chat_id
      FROM telegram_recipient_forms
     WHERE form_id = 3
");

// 3.6) Registrar em `automation_disp` cada bloco enviado
//     Campos: form_key, response_id (que será o `disp_id` que obtivemos acima), last_sent_date, sent_at
$stmtLogSend = $pdo->prepare("
    INSERT INTO automation_disp (
        form_key,
        response_id,
        last_sent_date,
        sent_at
    ) VALUES (
        :form_key,
        :response_id,
        :last_date,
        NOW()
    )
");

// 4) Configuração do Bot Telegram
$telegramToken  = '8013231460:AAEhGNGKvHmZz4F_Zc-krqmtogdhX8XR3Bk';
$telegramApiUrl = "https://api.telegram.org/bot{$telegramToken}/sendMessage";

// 5) Loop principal: para cada formKey, processa datas novas
foreach ($formConfig as $formKey => $cfg) {
    $table      = $cfg['table'];
    $templateMd = $templates[$formKey];

    // Se não existir template, pula este formKey
    if (empty($templateMd)) {
        continue;
    }

    // 5.1) Descobrir a maior data (last_sent_date) já enviada
    $stmtGetMaxDate->execute([':form_key' => $formKey]);
    $rowDate = $stmtGetMaxDate->fetch(PDO::FETCH_ASSOC);
    $lastDateSent = $rowDate['last_date']; // ex: '2025-05-27' ou '0000-00-00'

    // 5.2) Buscar datas novas (todas as data > lastDateSent)
    $queryNewDates   = str_replace('{TABLE}', $table, $stmtNewDates->queryString);
    $stmtFetchDates  = $pdo->prepare($queryNewDates);
    $stmtFetchDates->execute([':last_date' => $lastDateSent]);
    $datesToProcess  = $stmtFetchDates->fetchAll(PDO::FETCH_COLUMN, 0);

    if (empty($datesToProcess)) {
        // Não há datas novas, pula para o próximo formKey
        continue;
    }

    // 5.3) Para cada data nova, enviamos UMA mensagem a cada usuário desta data
    foreach ($datesToProcess as $dataEnvio) {
        // 5.3.1) Buscar lista de usuários que enviaram disponibilidade nesta data
        $queryUsers    = str_replace('{TABLE}', $table, $stmtUsers->queryString);
        $stmtFetchUsers = $pdo->prepare($queryUsers);
        $stmtFetchUsers->execute([':data_envio' => $dataEnvio]);
        $userList      = $stmtFetchUsers->fetchAll(PDO::FETCH_COLUMN, 0);

        if (empty($userList)) {
            // Se não houver ninguém para esta data, gravamos um “log vazio” só para marcar a data como processada:
            $stmtLogSend->execute([
                ':form_key'    => $formKey,
                ':response_id' => 0,
                ':last_date'   => $dataEnvio
            ]);
            continue;
        }

        // 5.3.2) Para cada usuário nesta data, montar e enviar UMA mensagem
        foreach ($userList as $usuario) {
            // (a) Buscar todos os pratos / disponibilidade para (dataEnvio, usuario)
            $queryDetails     = str_replace('{TABLE}', $table, $stmtDetails->queryString);
            $stmtFetchDetails = $pdo->prepare($queryDetails);
            $stmtFetchDetails->execute([
                ':data_envio' => $dataEnvio,
                ':usuario'    => $usuario
            ]);
            $rowsDetails     = $stmtFetchDetails->fetchAll(PDO::FETCH_ASSOC);

            // (b) Montar array de placeholders
            $assoc = [
                'data'          => $dataEnvio,
                'nome_usuario'  => $usuario,
            ];

            // Constrói a lista de pratos e capta o “comentário geral” do primeiro registro
            $listaPratos    = [];
            $comentarioGeral = '';
            foreach ($rowsDetails as $i => $r) {
                $nome  = htmlspecialchars($r['nome_prato'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $disp  = $r['disponivel'] ? '✅' : '❌';
                $listaPratos[] = "- {$nome} : {$disp}";

                if ($i === 0 && isset($r['comentarios'])) {
                    $comentarioGeral = trim((string)$r['comentarios']);
                }
            }
            $assoc['lista_codigos'] = implode("\n", $listaPratos);
            $assoc['comentarios']   = $comentarioGeral;

            // (c) Substituir placeholders no template linha a linha
            $linhas  = explode("\n", $templateMd);
            $saida   = [];
            foreach ($linhas as $linha) {
                preg_match_all('/\{([^}]+)\}/', $linha, $matches);
                $placeholders = $matches[1];
                $novaLinha    = $linha;
                $incluiLinha  = true;

                foreach ($placeholders as $lbl) {
                    if (!isset($assoc[$lbl]) || trim($assoc[$lbl]) === '') {
                        $incluiLinha = false;
                        break;
                    }
                    $valor = htmlspecialchars($assoc[$lbl], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                    $novaLinha = str_replace("{" . $lbl . "}", $valor, $novaLinha);
                }

                if ($incluiLinha) {
                    $saida[] = $novaLinha;
                }
            }
            $textoEnviar = implode("\n", $saida);

            // (d) Buscar destinatários fixos (form_id = 3)
            $stmtRecipients->execute();
            $destRows = $stmtRecipients->fetchAll(PDO::FETCH_COLUMN, 0);

            // (e) Enviar a mensagem para cada chat_id
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

            // (f) Registrar este envio em `automation_disp`:
            //     usamos o próprio “d.id” (disp_id) como response_id.
            $firstDispId = (int)($rowsDetails[0]['disp_id'] ?? 0);
            $stmtLogSend->execute([
                ':form_key'    => $formKey,
                ':response_id' => $firstDispId,
                ':last_date'   => $dataEnvio
            ]);
        }

        // 5.3.3) Após processar TODOS os usuários desta data, não esquecemos de
        //         “marcar” last_sent_date para não reprocessá-la:
        $stmtUpdate = $pdo->prepare("
            UPDATE automation_disp
               SET last_sent_date = :nova_data
             WHERE form_key = :form_key
            ORDER BY sent_at DESC
            LIMIT 1
        ");
        // Para simplificar, atualizamos a linha MAIS RECENTE daquele form_key
        // — mas como todo envio naquela data grava last_sent_date igual,
        // basta esse UPDATE para avançar last_sent_date no grupo.
        $stmtUpdate->execute([
            ':nova_data' => $dataEnvio,
            ':form_key'  => $formKey
        ]);
    }
}

// Fim do script CLI. Não imprime nada em tela.
