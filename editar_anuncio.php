<?php
session_start();
require_once 'config/conexao.php'; 

// Proteção: Só acessa se estiver logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Pega o ID da URL de forma segura e converte para número
$id_anuncio = isset($_GET['id']) ? (int)$_GET['id'] : 0;
$usuario_id = $_SESSION['usuario_id'];

// Se não vier ID nenhum na URL, avisa o erro
if ($id_anuncio === 0) {
    die("<div style='text-align: center; padding: 50px; font-family: sans-serif;'><h2>ID do anúncio inválido ou não informado na URL.</h2><a href='perfil.php' style='color:#00b67a;'>Voltar ao Painel</a></div>");
}

// 1. Carrega os dados exatos do anúncio baseado na sua tabela
$sql = "SELECT id, usuario_id, titulo, descricao, preco_medio FROM anuncios WHERE id = ?";
$stmt = $pdo->prepare($sql);
$stmt->execute([$id_anuncio]);
$anuncio = $stmt->fetch(PDO::FETCH_ASSOC);

// Validações de segurança
if (!$anuncio) {
    die("<div style='text-align: center; padding: 50px; font-family: sans-serif;'><h2>Anúncio não encontrado no banco de dados.</h2><a href='perfil.php' style='color:#00b67a;'>Voltar ao Painel</a></div>");
}

if ($anuncio['usuario_id'] != $usuario_id) {
    die("<div style='text-align: center; padding: 50px; font-family: sans-serif;'><h2>Você não tem permissão para editar este anúncio.</h2><a href='perfil.php' style='color:#00b67a;'>Voltar ao Painel</a></div>");
}

// 2. Processa a atualização dos dados (UPDATE) com as colunas corretas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo = trim($_POST['titulo']);
    $preco_medio = $_POST['preco_medio']; 
    $descricao = trim($_POST['descricao']);

    try {
        // Atualiza usando o nome correto: preco_medio
        $sql_update = "UPDATE anuncios SET titulo = ?, preco_medio = ?, descricao = ? WHERE id = ? AND usuario_id = ?";
        $stmt_update = $pdo->prepare($sql_update);
        $stmt_update->execute([$titulo, $preco_medio, $descricao, $id_anuncio, $usuario_id]);
        
        // Volta pro perfil com sucesso
        header("Location: perfil.php?sucesso=editado");
        exit;
    } catch (PDOException $e) {
        die("<div style='text-align: center; padding: 50px; color: red;'><h2>Erro ao salvar no banco: " . $e->getMessage() . "</h2></div>");
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Anúncio - TrampoCerto</title>
    <!-- Puxa o seu CSS principal -->
    <link rel="stylesheet" href="assets/css/style.css?v=<?php echo time(); ?>">
    <style>
        /* Estilo perfeitamente alinhado com o seu tema claro atual */
        .form-container { 
            max-width: 600px; 
            margin: 40px auto; 
            padding: 30px; 
            border-radius: 8px; 
            border: 1px solid #e2e8f0; 
            background: #ffffff; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05); 
        }
        .form-container h2 { color: #00b67a; margin-top: 0; margin-bottom: 20px; font-size: 1.5rem; }
        .form-group { margin-bottom: 15px; }
        .form-group label { display: block; margin-bottom: 5px; font-weight: 600; color: #334155; }
        .form-group input, .form-group textarea { 
            width: 100%; 
            padding: 10px; 
            border: 1px solid #cbd5e1; 
            border-radius: 6px; 
            box-sizing: border-box; 
            font-family: inherit; 
            font-size: 1rem; 
        }
        .form-group textarea { height: 120px; resize: vertical; }
        .btn-salvar { 
            width: 100%; 
            padding: 12px; 
            background: #00b67a; 
            color: #fff; 
            border: none; 
            border-radius: 6px; 
            font-weight: bold; 
            font-size: 1rem; 
            cursor: pointer; 
            transition: 0.2s; 
            margin-top: 10px; 
        }
        .btn-salvar:hover { background: #009665; }
        .btn-voltar { 
            display: block; 
            text-align: center; 
            margin-top: 15px; 
            text-decoration: none; 
            color: #64748b; 
            font-weight: 600; 
        }
        .btn-voltar:hover { text-decoration: underline; color: #334155; }
    </style>
</head>
<body style="background-color: #f8fafc; font-family: 'Segoe UI', sans-serif; margin: 0; padding: 0;">

    <?php include 'includes/header.php'; ?>

    <main class="form-container">
        <h2>Editar Serviço</h2>
        <form method="POST">
            <div class="form-group">
                <label>Título do Serviço</label>
                <input type="text" name="titulo" value="<?= htmlspecialchars($anuncio['titulo']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Preço Médio (R$)</label>
                <!-- Puxando a variável correta preco_medio -->
                <input type="number" step="0.01" name="preco_medio" value="<?= htmlspecialchars($anuncio['preco_medio']) ?>" required>
            </div>
            
            <div class="form-group">
                <label>Descrição</label>
                <textarea name="descricao" required><?= htmlspecialchars($anuncio['descricao']) ?></textarea>
            </div>
            
            <button type="submit" class="btn-salvar">Salvar Alterações</button>
            <a href="perfil.php" class="btn-voltar">Cancelar e Voltar</a>
        </form>
    </main>

    <?php include 'includes/footer.php'; ?>

</body>
</html>