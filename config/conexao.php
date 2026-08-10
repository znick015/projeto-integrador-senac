<?php
// Configurações do Banco de Dados no Codespaces
$host = '127.0.0.1';
$db   = 'plataforma_autonomos';
$user = 'admin';
$pass = '123456';
$port = '3306';

try {
    // Cria a conexão via PDO com suporte a caracteres UTF-8
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    // Em produção tratamos de forma genérica, mas para desenvolvimento mostramos o erro
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}
?>