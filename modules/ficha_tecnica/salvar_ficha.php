<?php
require_once '../../config/db.php';

try {
    // Coleta dados principais
    $nome = $_POST['nome_prato'];
    $rendimento = $_POST['rendimento'];
    $modo_preparo = $_POST['modo_preparo'];
    $usuario = $_POST['usuario'];

    $ingredientes = $_POST['descricao'] ?? [];
    $codigos = $_POST['codigo'] ?? [];
    $quantidades = $_POST['quantidade'] ?? [];
    $unidades = $_POST['unidade'] ?? [];

    // ✅ Verifica se ao menos 1 ingrediente foi enviado
    if (count($ingredientes) === 0 || empty($ingredientes[0])) {
        throw new Exception('É necessário adicionar pelo menos um ingrediente.');
    }

    // Upload da imagem
    $imagem_nome = null;

    if (isset($_FILES['imagem']) && $_FILES['imagem']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['imagem']['name'], PATHINFO_EXTENSION);
        $imagem_nome = uniqid('prato_') . '.' . $ext;
        $destino = 'uploads/' . $imagem_nome;

        if (!move_uploaded_file($_FILES['imagem']['tmp_name'], $destino)) {
            throw new Exception('Erro ao mover a imagem para a pasta uploads.');
        }
    }

    // Inserir a ficha
    $stmt = $pdo->prepare("INSERT INTO ficha_tecnica (nome_prato, rendimento, modo_preparo, imagem, usuario)
                           VALUES (:nome, :rendimento, :modo, :imagem, :usuario)");
    $stmt->execute([
        ':nome' => $nome,
        ':rendimento' => $rendimento,
        ':modo' => $modo_preparo,
        ':imagem' => $imagem_nome,
        ':usuario' => $usuario
    ]);

    $ficha_id = $pdo->lastInsertId();

    // Registrar criação da ficha no histórico
    $stmt = $pdo->prepare("INSERT INTO historico (ficha_id, campo_alterado, valor_antigo, valor_novo, usuario)
                           VALUES (:ficha_id, 'criação', '', 'Ficha técnica criada', :usuario)");
    $stmt->execute([
        ':ficha_id' => $ficha_id,
        ':usuario' => $usuario
    ]);

    // Inserir ingredientes
    for ($i = 0; $i < count($ingredientes); $i++) {
        // ✅ Só salva se todos os campos estiverem preenchidos
        if (!empty($ingredientes[$i]) && !empty($quantidades[$i]) && !empty($unidades[$i])) {
            $stmt = $pdo->prepare("INSERT INTO ingredientes (ficha_id, codigo, descricao, quantidade, unidade)
                                   VALUES (:ficha_id, :codigo, :descricao, :quantidade, :unidade)");
            $stmt->execute([
                ':ficha_id' => $ficha_id,
                ':codigo' => $codigos[$i],
                ':descricao' => $ingredientes[$i],
                ':quantidade' => $quantidades[$i],
                ':unidade' => $unidades[$i]
            ]);
        }
    }

    // ✅ Redireciona após sucesso
    header("Location: visualizar_ficha.php?id=" . $ficha_id);
    exit;

} catch (Exception $e) {
    echo "Erro ao cadastrar ficha: " . $e->getMessage();
}
