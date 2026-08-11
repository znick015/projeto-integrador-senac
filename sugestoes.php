<?php
require_once 'config/conexao.php';

header('Content-Type: application/json; charset=utf-8');

$q = isset($_GET['q']) ? trim($_GET['q']) : '';

if (strlen($q) < 2) {
    echo json_encode([]);
    exit;
}

try {
    // Busca até 5 anúncios que combinem com o termo digitado
    $stmt = $pdo->prepare("
        SELECT a.id, a.titulo, a.preco_medio, s.nome AS subcategoria 
        FROM anuncios a
        JOIN subcategorias s ON a.subcategoria_id = s.id
        WHERE a.titulo LIKE :q OR a.descricao LIKE :q
        ORDER BY a.data_criacao DESC
        LIMIT 5
    ");
    $stmt->execute([':q' => "%$q%"]);
    $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($resultados);
} catch (PDOException $e) {
    echo json_encode([]);
}