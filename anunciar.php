<?php
session_start();
require_once 'config/conexao.php';

if (!isset($_SESSION['usuario_id'])) {
    header("Location: login.php");
    exit;
}

// Puxa dados do usuário para preencher cidade/estado padrão
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

    if (!empty($titulo) && !empty($subcategoria_id) && !empty($descricao) && !empty($telefone) && !empty($cidade)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO anuncios (usuario_id, subcategoria_id, titulo, descricao, preco_medio, telefone_contato, bairro, cidade, estado) 
                                   VALUES (:usuario_id, :subcategoria_id, :titulo, :descricao, :preco_medio, :telefone, :bairro, :cidade, :estado)");
            $stmt->execute([
                ':usuario_id'      => $_SESSION['usuario_id'],
                ':subcategoria_id' => $subcategoria_id,
                ':titulo'          => $titulo,
                ':descricao'       => $descricao,
                ':preco_medio'     => !empty($preco_medio) ? $preco_medio : NULL,
                ':telefone'        => $telefone,
                ':bairro'          => $bairro,
                ':cidade'          => $cidade,
                ':estado'          => $estado
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
    <div class="form-card" style="max-width: 650px;">
        <h2>Anunciar um Serviço</h2>
        <p style="margin-bottom: 20px; color: #64748b;">Divulgue seu trabalho com localização detalhada.</p>

        <?php if ($erro): ?>
            <div class="alert-danger"><?= $erro ?></div>
        <?php endif; ?>

        <form action="anunciar.php" method="POST">
            <div class="form-group">
                <label for="titulo">Título do Anúncio *</label>
                <input type="text" id="titulo" name="titulo" required placeholder="Ex: Formatação de PC e Redes de Computadores">
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

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 15px;">
                <div class="form-group">
                    <label for="preco_medio">A partir de (R$)</label>
                    <input type="number" step="0.01" id="preco_medio" name="preco_medio" placeholder="Ex: 120.00">
                </div>

                <div class="form-group">
                    <label for="telefone">WhatsApp para Contato *</label>
                    <input type="text" id="telefone" name="telefone" required value="<?= htmlspecialchars($user_info['telefone'] ?? '') ?>">
                </div>
            </div>

            <!-- Campos de Localização do Serviço -->
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
                <label for="descricao">Descrição Detalhada do Serviço *</label>
                <textarea id="descricao" name="descricao" rows="4" required placeholder="Descreva sua experiência, garantia, orçamento..." style="width: 100%; padding: 10px; border: 1px solid var(--border-color); border-radius: 6px;"></textarea>
            </div>

            <button type="submit" class="btn-submit">Publicar Anúncio</button>
        </form>
    </div>
</main>

<?php include 'includes/footer.php'; ?>