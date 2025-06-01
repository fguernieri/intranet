<?php
declare(strict_types=1);

// 0) Inclusões obrigatórias
require __DIR__ . '/../../auth.php';
require __DIR__ . '/../../config/db.php';

// 1) Mapeie aqui as suas quatro páginas de disponibilidade,
//    usando a mesma chave que você usará para "form_key" e
//    indicando o nome da tabela de respostas correspondente.
$formKeys = [
    'disp_bdf_almoco'      => 'disp_bdf_almoco',
    'disp_bdf_almoco_fds'  => 'disp_bdf_almoco_fds',
    'disp_bdf_noite'       => 'disp_bdf_noite',
    'disp_wab'             => 'disp_wab'
];

// 2) Para cada formKey, verifique se há uma data nova na tabela de respostas
foreach ($formKeys as $formKey => $respTable) {
    // 2.1) Busque a última data enviada (ou insira com '0000-00-00' se não existir)
    $stmt = $pdo->prepare("
        SELECT last_sent_date
          FROM automation_disp
         WHERE form_key = :form_key
         LIMIT 1
    ");
    $stmt->execute([':form_key' => $formKey]);
    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($row === false) {
        // Cria entrada inicial com data '0000-00-00'
        $ins = $pdo->prepare("
            INSERT INTO automation_disp (form_key, last_sent_date)
            VALUES (:form_key, '0000-00-00')
        ");
        $ins->execute([':form_key' => $formKey]);
        $lastSentDate = '0000-00-00';
    } else {
        $lastSentDate = $row['last_sent_date'];
    }

    // 2.2) Busque a maior data na tabela de respostas que seja maior que last_sent_date
    $dateStmt = $pdo->prepare("
        SELECT COALESCE(MAX(`data`), '0000-00-00') AS max_date
          FROM {$respTable}
         WHERE `data` > :last_sent_date
    ");
    $dateStmt->execute([':last_sent_date' => $lastSentDate]);
    $maxDateRow = $dateStmt->fetch(PDO::FETCH_ASSOC);
    $maxDate    = $maxDateRow['max_date'];

    // 2.3) Se encontrou data nova, processe
    if ($maxDate !== null && $maxDate !== '0000-00-00') {
        // 2.3.1) Busque todas as linhas dessa data
        $allStmt = $pdo->prepare("
            SELECT d.nome_usuario, d.codigo_cloudify, d.disponivel, d.comentarios,
                   f.nome_prato
              FROM {$respTable} AS d
              LEFT JOIN ficha_tecnica AS f
                ON f.codigo_cloudify = d.codigo_cloudify
             WHERE d.`data` = :data_envio
             ORDER BY d.id ASC
        ");
        $allStmt->execute([':data_envio' => $maxDate]);
        $rows = $allStmt->fetchAll(PDO::FETCH_ASSOC);

        if (!empty($rows)) {
            // 2.3.2) Monte o array de placeholders
            $dataEnvio       = $maxDate;
            $usuario         = $rows[0]['nome_usuario'];
            $comentarioGeral = trim((string)$rows[count($rows) - 1]['comentarios']);

            $assoc = [
                'data'         => $dataEnvio,
                'nome_usuario' => $usuario,
            ];

            // 2.3.3) Construa a lista de pratos e disponibilidade
            $lista = [];
            foreach ($rows as $r) {
                $nomePrato = htmlspecialchars($r['nome_prato'], ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
                $dispIcon  = $r['disponivel'] ? '✅' : '❌';
                $lista[] = "- {$nomePrato} : {$dispIcon}";
            }
            $assoc['lista_codigos'] = implode("\n", $lista);

            // 2.3.4) Atribua o comentário geral ao placeholder {comentarios}
            $assoc['comentarios'] = $comentarioGeral;

            // 2.3.5) Carregue o template Markdown para este form_key
            $tplStmt = $pdo->prepare("
                SELECT template_md
                  FROM telegram_disp_templates
                 WHERE form_key = :form_key
            ");
            $tplStmt->execute([':form_key' => $formKey]);
            $tplRow     = $tplStmt->fetch(PDO::FETCH_ASSOC);
            $templateMd = $tplRow['template_md'] ?? '';
            $linhas     = explode("\n", $templateMd);
            $saida      = [];

            foreach ($linhas as $linha) {
                preg_match_all('/\{([^}]+)\}/', $linha, $matches);
                $placeholders = $matches[1]; // array de labels sem chaves

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

            // 2.3.6) Envie ao Telegram (fixando form_id = 3 conforme base)
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
                $params = [
                    'chat_id'    => $chatId,
                    'text'       => $textoEnviar,
                    'parse_mode' => 'Markdown'
                ];
                $ch = curl_init($telegramApiUrl);
                curl_setopt($ch, CURLOPT_POST, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params);
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_exec($ch);
                curl_close($ch);
            }

            // 2.3.7) Atualize last_sent_date em automation_disp
            $upd = $pdo->prepare("
                UPDATE automation_disp
                   SET last_sent_date = :new_date
                 WHERE form_key = :form_key
            ");
            $upd->execute([
                ':new_date' => $maxDate,
                ':form_key' => $formKey
            ]);
        }
    }
}

// Fim do script. Agende no crontab para rodar a cada 5 minutos:
// */5 * * * * /usr/bin/php /caminho/para/telegram_disp_cron.php
