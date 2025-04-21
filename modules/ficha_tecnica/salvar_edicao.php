<?php
require_once '../../config/db.php';
include '../../sidebar.php';

try {
    $id = $_POST['id'];
    $nome = $_POST['nome_prato'];
    $rendimento = $_POST['rendimento'];
    $modo_preparo = $_POST['modo_preparo'];
    $usuario = $_POST['usuario'];
    $ingredientes = $_POST['descricao'];
    $codigos = $_POST['codigo'];
    $quantidades = $_POST['quantidade'];
    $unidades = $_POST['unidade'];

    // Buscar dados anteriores
    $stmt = $pdo->prepare("SELECT * FROM ficha_tecnica WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $anterior = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$anterior) {
        throw new Exception('Ficha técnica não encontrada.');
    }

    // Tratamento de imagem
    $imagem_nome = $anterior['imagem']; // padrão: manter imagem anterior

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem_nome = uniqid('prato_') . '.' . $ext;
        $destino = 'uploads/' . $imagem_nome;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
            throw new Exception('Erro ao mover a imagem para a pasta uploads.');
        }

        // Remover imagem antiga
        if ($anterior['imagem'] && $anterior['imagem'] !== $imagem_nome) {
            $anterior_path = '../uploads/' . $anterior['imagem'];
            if (file_exists($anterior_path)) {
                unlink($anterior_path);
            }
        }
    }

    // Atualizar ficha
    $stmt = $pdo->prepare("UPDATE ficha_tecnica 
        SET nome_prato = :nome, rendimento = :rendimento, modo_preparo = :modo_preparo, imagem = :imagem, usuario = :usuario 
        WHERE id = :id");
    $stmt->execute([
        ':nome' => $nome,
        ':rendimento' => $rendimento,
        ':modo_preparo' => $modo_preparo,
        ':imagem' => $imagem_nome,
        ':usuario' => $usuario,
        ':id' => $id
    ]);

    // Verificar alterações e registrar no histórico
    foreach (['nome_prato', 'rendimento', 'modo_preparo', 'imagem', 'usuario'] as $campo) {
        if ($anterior[$campo] != $$campo) {
            $stmt = $pdo->prepare("INSERT INTO historico (ficha_id, campo_alterado, valor_antigo, valor_novo, usuario)
                VALUES (:ficha_id, :campo, :antigo, :novo, :usuario)");
            $stmt->execute([
                ':ficha_id' => $id,
                ':campo' => $campo,
                ':antigo' => $anterior[$campo],
                ':novo' => $$campo,
                ':usuario' => $usuario
            ]);
        }
    }

    // Remover ingredientes antigos
    $pdo->prepare("DELETE FROM ingredientes WHERE ficha_id = :id")->execute([':id' => $id]);

    // Inserir ingredientes novos
    for ($i = 0; $i < count($ingredientes); $i++) {
        if (!empty($ingredientes[$i])) {
            $stmt = $pdo->prepare("INSERT INTO ingredientes (ficha_id, codigo, descricao, quantidade, unidade)
                VALUES (:ficha_id, :codigo, :descricao, :quantidade, :unidade)");
            $stmt->execute([
                ':ficha_id' => $id,
                ':codigo' => $codigos[$i],
                ':descricao' => $ingredientes[$i],
                ':quantidade' => $quantidades[$i],
                ':unidade' => $unidades[$i]
            ]);

            // Log no histórico
            $stmt = $pdo->prepare("INSERT INTO historico (ficha_id, campo_alterado, valor_antigo, valor_novo, usuario)
                VALUES (:ficha_id, 'ingrediente_adicionado', '', :desc, :usuario)");
            $stmt->execute([
                ':ficha_id' => $id,
                ':desc' => $ingredientes[$i] . ' (' . $quantidades[$i] . ' ' . $unidades[$i] . ')',
                ':usuario' => $usuario
            ]);
        }
    }

    header("Location: visualizar_ficha.php?id=" . $id);
    exit;

} catch (Exception $e) {
    echo "Erro ao salvar edição: " . $e->getMessage();
}
