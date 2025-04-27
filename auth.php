<?php
session_start();

// -> 1) Inclua aqui a sua conexão PDO
require_once __DIR__ . '/config/db.php';      // ajusta o caminho se preciso
require_once __DIR__ . '/config/app.php';     // caso use constantes de URL, etc.

// -> 2) Verifique se o usuário está logado
if (!isset($_SESSION['usuario_id'])) {
    header('Location: login.php');
    exit;
}

// -> 3) Carregue as permissões de vendedores
$stmt = $pdo->prepare(
    "SELECT vendedor_id
       FROM user_vendedor_permissoes 
      WHERE usuario_id = ?"
);
$stmt->execute([ $_SESSION['usuario_id'] ]);
$_SESSION['vendedores_permitidos'] = $stmt->fetchAll(PDO::FETCH_COLUMN);

