<?php
$host = '127.0.0.1';
$db   = 'plataforma_autonomos';$user = 'admin';
$pass = '123456';$port = '3306';

try {
    $pdo = new PDO("mysql:host=$host;port=$port;dbname=$db;charset=utf8mb4", $user,$pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ]);
} catch (PDOException $e) {
    die("Erro ao conectar com o banco de dados: " . $e->getMessage());
}

/**
 * Retorna o caminho da imagem do anúncio (própria ou padrão da subcategoria)
 */
function obterImagemAnuncio($imagem_capa,$subcategoria_id = 0) {
    // 1. Se o anunciante fez upload de imagem própria e ela existe no servidor
    if (!empty($imagem_capa) && file_exists($imagem_capa)) {
        return $imagem_capa;
    }

    // 2. Mapeamento por ID da Subcategoria baseado na tabela do banco
    $mapa_subcategorias = [
        1 => 'assets/img/servicos/1-celular.jpg',
        2 => 'assets/img/servicos/2-computador.jpg',
        3 => 'assets/img/servicos/3-pintor.jpg',
        4 => 'assets/img/servicos/4-eletricista.jpg',
        5 => 'assets/img/servicos/5-encanador.jpg',
        6 => 'assets/img/servicos/6-web.jpg',
        7 => 'assets/img/servicos/7-design.jpg',
        8 => 'assets/img/servicos/8-diarista.jpg'
    ];

    if (isset($mapa_subcategorias[$subcategoria_id]) && file_exists($mapa_subcategorias[$subcategoria_id])) {
        return $mapa_subcategorias[$subcategoria_id];
    }

    // 3. Fallback geral caso a imagem específica não seja encontrada
    return 'assets/img/hero-bg.jpg';
}
?>