<?php
session_start();
require_once 'config/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

$stmt_u = $pdo->prepare("SELECT cidade, estado, telefone FROM usuarios WHERE id = :id");
$stmt_u->execute([':id' => $_SESSION['usuario_id']]);
$user_info = $stmt_u->fetch();

$erro = '';
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nome ASC")->fetchAll();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $titulo          = trim($_POST['titulo']);
    $subcategoria_id = $_POST['subcategoria_id'];
    $preco_medio     = $_POST['preco_medio'];
    $telefone        = trim($_POST['telefone']);
    $bairro          = trim($_POST['bairro']);
    $cidade          = trim($_POST['cidade']);
    $estado          = strtoupper(trim($_POST['estado']));
    $descricao       = trim($_POST['descricao']);
    $itens_inclusos     = trim($_POST['itens_inclusos']);
    $itens_nao_inclusos = trim($_POST['itens_nao_inclusos']);
    $imagem_capa     = NULL;

    // Upload da Imagem de Capa do Anúncio
    if (isset($_FILES['imagem_capa']) && $_FILES['imagem_capa']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['imagem_capa']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg', 'jpeg', 'png', 'webp'])) {
            if (!is_dir('uploads')) { mkdir('uploads', 0777, true); }
            $nome_arq = 'anuncio_' . time() . '_' . rand(100, 999) . '.' . $ext;
            if (move_uploaded_file($_FILES['imagem_capa']['tmp_name'], 'uploads/' . $nome_arq)) {
                $imagem_capa = 'uploads/' . $nome_arq;
            }
        }
    }

    if (!empty($titulo) && !empty($subcategoria_id) && !empty($descricao) && !empty($telefone) && !empty($cidade)) {
        try {
            $stmt = $pdo->prepare("
                INSERT INTO anuncios (usuario_id, subcategoria_id, titulo, descricao, preco_medio, telefone_contato, bairro, cidade, estado, itens_inclusos, itens_nao_inclusos, imagem_capa) 
                VALUES (:usuario_id, :subcategoria_id, :titulo, :descricao, :preco_medio, :telefone, :bairro, :cidade, :estado, :inclusos, :nao_inclusos, :imagem_capa)
            ");
            $stmt->execute([
                ':usuario_id'      => $_SESSION['usuario_id'],
                ':subcategoria_id' => $subcategoria_id,
                ':titulo'          => $titulo,
                ':descricao'       => $descricao,
                ':preco_medio'     => !empty($preco_medio) ? $preco_medio : NULL,
                ':telefone'        => $telefone,
                ':bairro'          => $bairro,
                ':cidade'          => $cidade,
                ':estado'          => $estado,
                ':inclusos'        => $itens_inclusos,
                ':nao_inclusos'    => $itens_nao_inclusos,
                ':imagem_capa'     => $imagem_capa
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

<main class="container" style="padding-top: 30px; padding-bottom: 50px;">
    <div class="form-card" style="max-width: 750px;">
        <h2>Anunciar um Serviço</h2>
        <p style="margin-bottom: 20px; color: #64748b;">Preencha os detalhes para cadastrar seu serviço no formato de Preço Fechado.</p>

        <?php if ($erro): ?>
            <div class="alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form action="anunciar.php" method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label for="titulo">Título do Anúncio *</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Ex: Conserto e Manutenção de Ar Condicionado">
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
                <label for="imagem_capa">Foto Principal do Serviço (Opcional)</label>
                <input type="file" id="imagem_capa" name="imagem_capa" accept="image/*" style="width: 100%;">
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="preco_medio">Preço do Serviço (R$)</label>
                    <input type="number" step="0.01" id="preco_medio" name="preco_medio" placeholder="Ex: 482.90">
                </div>

                <div class="form-group">
                    <label for="telefone">WhatsApp para Contato *</label>
                    <input type="text" id="telefone" name="telefone" required value="<?= htmlspecialchars($user_info['telefone'] ?? '') ?>">
                </div>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr 80px; gap: 10px;">
                <div class="form-group">
                    <label for="bairro">Bairro</label>
                    <input type="text" id="bairro" name="bairro" placeholder="Ex: Centro">
                </div>
                <div class="form-group">
                    <label for="cidade">Cidade *</label>
                    <input type="text" id="cidade" name="cidade" required value="<?= htmlspecialchars($user_info['cidade'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label for="estado">UF *</label>
                    <input type="text" id="estado" name="estado" maxlength="2" required value="<?= htmlspecialchars($user_info['estado'] ?? 'MG') ?>">
                </div>
            </div>

            <div class="form-group">
                <label for="descricao">Sobre este Serviço (Descrição Geral) *</label>
                <textarea id="descricao" name="descricao" rows="3" required placeholder="Serviço profissional para deixar seu equipamento funcionando como novo..." style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div class="form-group">
                <label for="itens_inclusos">O que está incluso (Coloque 1 item por linha)</label>
                <textarea id="itens_inclusos" name="itens_inclusos" rows="4" placeholder="90 dias de garantia&#10;Avaliação técnica do problema&#10;Testes de funcionamento" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <div class="form-group">
                <label for="itens_nao_inclusos">O que NÃO está incluso (Coloque 1 item por linha)</label>
                <textarea id="itens_nao_inclusos" name="itens_nao_inclusos" rows="4" placeholder="Não inclui peças de reposição&#10;Não inclui acabamentos em gesso ou pintura" style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <button type="submit" class="btn-submit">Publicar Anúncio</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>