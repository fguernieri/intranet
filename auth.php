<?php
declare(strict_types=1);

require_once __DIR__ . '/config/session_config.php';

session_start();

// 1) Inclui a conexão principal e configurações
require_once __DIR__ . '/config/db.php';      // $pdo aponta para o banco principal
require_once __DIR__ . '/config/app.php';     // constantes de URL, paths, etc.

// 2) Função para obter IDs de vendedores autorizados para um usuário e ativos

function getAuthorizedVendors(PDO $pdo, int $userId): array {
    // Monta a SQL como string normal para evitar problemas de heredoc
    $sql = "
        SELECT uvp.vendedor_id
          FROM user_vendedor_permissoes AS uvp
     INNER JOIN vendedores            AS v
             ON v.id             = uvp.vendedor_id
         WHERE uvp.usuario_id    = ?
           AND v.ativo           = 1
        ORDER BY uvp.vendedor_id
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([$userId]);
    return $stmt->fetchAll(PDO::FETCH_COLUMN);
}

// 3) Verifica se o usuário está autenticado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// 4) Carrega e armazena em sessão os vendedores permitidos
$_SESSION['vendedores_permitidos'] = getAuthorizedVendors(
    $pdo,
    (int) $_SESSION['usuario_id']
);
