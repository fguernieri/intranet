<?php
$host = 'localhost';
$dbname = 'basta920_bastards_cozinha';   // nome completo do banco
$username = 'root';             // usuário criado no cPanel
$password = '';            // senha que você definiu para esse usuário

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erro na conexão com o banco de dados: " . $e->getMessage());
}
