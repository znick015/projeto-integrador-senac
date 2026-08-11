<?php
session_start();
require_once 'config/conexao.php';

// Bloqueia acesso de quem não está logado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$erro = '';

// Busca as subcategorias cadastradas no banco
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nome ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo          = trim($_POST['titulo']);
    $subcategoria_id = $_POST['subcategoria_id'];
    $preco_medio     = $_POST['preco_medio'];
    $telefone        = trim($_POST['telefone']);
    $descricao       = trim($_POST['descricao']);

    if (!empty($titulo) && !empty($subcategoria_id) && !empty($descricao) && !empty($telefone)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO anuncios (usuario_id, subcategoria_id, titulo, descricao, preco_medio, telefone_contato) 
                                   VALUES (:usuario_id, :subcategoria_id, :titulo, :descricao, :preco_medio, :telefone)");
            $stmt->execute([
                ':usuario_id'      => $_SESSION['usuario_id'],
                ':subcategoria_id' => $subcategoria_id,
                ':titulo'          => $titulo,
                ':descricao'       => $descricao,
                ':preco_medio'     => !empty($preco_medio) ? $preco_medio : NULL,
                ':telefone'        => $telefone
            ]);

            $id_anuncio = $pdo->lastInsertId();
            header("Location: anuncio.php?id=" . $id_anuncio);
            exit;
        } catch (PDOException $e) {
            $erro = "Erro ao cadastrar anúncio: " . $e->getMessage();
        }
    } else {
        $erro = "Preencha todos os campos obrigatórios!";
    }
}

include 'includes/header.php';
?>

<main class="container">
    <div class="form-card" style="max-width: 600px;">
        <h2>Anunciar um Serviço</h2>
        <p style="margin-bottom: 20px; color: #64748b;">Divulgue seu trabalho e receba contatos de clientes.</p>

        <?php if ($erro): ?>
            <div class="alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form action="anunciar.php" method="POST">
            <div class="form-group">
                <label for="titulo">Título do Anúncio *</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Ex: Manutenção de Computadores e Formatação">
            </div>

            <div class="form-group">
                <label for="subcategoria_id">Categoria do Serviço *</label>
                <select name="subcategoria_id" id="subcategoria_id" required style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;">
                    <option value="">Selecione uma opção...</option>
                    <?php foreach ($subcategorias as $sub): ?>
                        <option value="<?= $sub['id'] ?>"><?= htmlspecialchars($sub['nome']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="preco_medio">Preço Médio / A partir de (R$)</label>
                <input type="number" step="0.01" id="preco_medio" name="preco_medio" placeholder="Ex: 150.00">
            </div>

            <div class="form-group">
                <label for="telefone">WhatsApp para Contato *</label>
                <input type="text" id="telefone" name="telefone" required placeholder="(31) 99999-9999">
            </div>

            <div class="form-group">
                <label for="descricao">Descrição Detalhada do Serviço *</label>
                <textarea id="descricao" name="descricao" rows="5" required placeholder="Descreva sua experiência, horários de atendimento, garantia, etc." style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <button type="submit" class="btn-submit">Publicar Anúncio</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>