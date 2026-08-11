<?php
session_start();
require_once 'config/conexao.php';

$q            = isset($_GET['q']) ? trim($_GET['q']) : '';
$subcat_id    = isset($_GET['subcategoria']) ? (int)$_GET['subcategoria'] : 0;
$preco_min    = isset($_GET['preco_min']) && $_GET['preco_min'] !== '' ? (float)$_GET['preco_min'] : null;
$preco_max    = isset($_GET['preco_max']) && $_GET['preco_max'] !== '' ? (float)$_GET['preco_max'] : null;
$ordem        = isset($_GET['ordem']) ? $_GET['ordem'] : 'recentes';

// Busca todas as subcategorias para popular o filtro
$subcategorias = $pdo->query("SELECT * FROM subcategorias ORDER BY nome ASC")->fetchAll();

// Monta a Query dinâmica
$where = ["(a.titulo LIKE :q1 OR a.descricao LIKE :q2)"];
$params = [':q1' => "%$q%", ':q2' => "%$q%"];

if ($subcat_id > 0) {
    $where[] = "a.subcategoria_id = :subcat";
    $params[':subcat'] = $subcat_id;
}

if ($preco_min !== null) {
    $where[] = "a.preco_medio >= :p_min";
    $params[':p_min'] = $preco_min;
}

if ($preco_max !== null) {
    $where[] = "a.preco_medio <= :p_max";
    $params[':p_max'] = $preco_max;
}

$where_sql = implode(' AND ', $where);

// Define a ordenação
$order_sql = "a.data_criacao DESC";
if ($ordem === 'preco_asc') {
    $order_sql = "a.preco_medio ASC";
} elseif ($ordem === 'preco_desc') {
    $order_sql = "a.preco_medio DESC";
} elseif ($ordem === 'avaliacao') {
    $order_sql = "media_notas DESC";
}

try {
    $stmt = $pdo->prepare("
        SELECT a.*, u.nome AS prestador, u.id AS prestador_id, s.nome AS subcategoria,
               COALESCE(AVG(av.nota), 0) AS media_notas,
               COUNT(av.id) AS total_avaliacoes
        FROM anuncios a
        JOIN usuarios u ON a.usuario_id = u.id
        JOIN subcategorias s ON a.subcategoria_id = s.id
        LEFT JOIN avaliacoes av ON a.id = av.anuncio_id
        WHERE $where_sql
        GROUP BY a.id
        ORDER BY $order_sql
    ");
    $stmt->execute($params);
    $anuncios = $stmt->fetchAll();
} catch (PDOException $e) {
    $anuncios = [];
}

include 'includes/header.php';
?>

<main class="container" style="padding-top: 30px; padding-bottom: 50px;">
    <h2>Resultados para: "<?= htmlspecialchars($q) ?>"</h2>
    <p style="color: #64748b; margin-bottom: 10px;"><?= count($anuncios) ?> serviço(s) encontrado(s)</p>

    <div class="search-layout">
        <!-- BARRA LATERAL DE FILTROS -->
        <aside class="filter-sidebar">
            <h3 style="font-size: 1.1rem; margin-bottom: 15px; border-bottom: 1px solid var(--border-color); padding-bottom: 8px;">
                <i class="fas fa-filter" style="color: var(--accent-color);"></i> Filtrar Serviços
            </h3>

            <form action="busca.php" method="GET">
                <input type="hidden" name="q" value="<?= htmlspecialchars($q) ?>">

                <!-- Ordenação -->
                <div class="filter-group">
                    <label for="ordem">Ordenar por:</label>
                    <select name="ordem" id="ordem" onchange="this.form.submit()">
                        <option value="recentes" <?= $ordem === 'recentes' ? 'selected' : '' ?>>Mais Recentes</option>
                        <option value="avaliacao" <?= $ordem === 'avaliacao' ? 'selected' : '' ?>>Melhor Avaliados ★</option>
                        <option value="preco_asc" <?= $ordem === 'preco_asc' ? 'selected' : '' ?>>Menor Preço</option>
                        <option value="preco_desc" <?= $ordem === 'preco_desc' ? 'selected' : '' ?>>Maior Preço</option>
                    </select>
                </div>

                <!-- Subcategoria -->
                <div class="filter-group">
                    <label for="subcategoria">Categoria:</label>
                    <select name="subcategoria" id="subcategoria" onchange="this.form.submit()">
                        <option value="0">Todas as Categorias</option>
                        <?php foreach ($subcategorias as $sub): ?>
                            <option value="<?= $sub['id'] ?>" <?= $subcat_id == $sub['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sub['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Faixa de Preço -->
                <div class="filter-group">
                    <label>Preço Médio (R$):</label>
                    <div class="filter-price-range">
                        <input type="number" name="preco_min" placeholder="Mín" value="<?= $preco_min !== null ? $preco_min : '' ?>" step="10">
                        <span>até</span>
                        <input type="number" name="preco_max" placeholder="Máx" value="<?= $preco_max !== null ? $preco_max : '' ?>" step="10">
                    </div>
                </div>

                <button type="submit" class="btn-submit" style="padding: 8px; font-size: 0.9rem; margin-top: 10px;">Aplicar Filtros</button>
                <a href="busca.php?q=<?= urlencode($q) ?>" style="display: block; text-align: center; font-size: 0.85rem; color: #ef4444; margin-top: 10px; text-decoration: none;">Limpar Filtros</a>
            </form>
        </aside>

        <!-- LISTAGEM DOS RESULTADOS -->
        <section>
            <?php if (empty($anuncios)): ?>
                <div style="background: #fff; padding: 40px; border-radius: 10px; border: 1px solid var(--border-color); text-align: center;">
                    <i class="fas fa-search" style="font-size: 2.5rem; color: #cbd5e1; margin-bottom: 10px;"></i>
                    <p style="color: #64748b;">Nenhum serviço atende aos critérios selecionados.</p>
                </div>
            <?php else: ?>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 1fr)); gap: 20px;">
                    <?php foreach ($anuncios as $anuncio): ?>
                        <div style="background: #fff; border: 1px solid var(--border-color); border-radius: 8px; padding: 20px; display: flex; flex-direction: column; justify-content: space-between;">
                            <div>
                                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                                    <span style="background: #ecfdf5; color: var(--accent-color); font-size: 0.75rem; font-weight: 600; padding: 3px 8px; border-radius: 4px;">
                                        <?= htmlspecialchars($anuncio['subcategoria']) ?>
                                    </span>
                                    <span style="color: #f59e0b; font-size: 0.85rem; font-weight: 700;">
                                        ★ <?= $anuncio['media_notas'] > 0 ? round($anuncio['media_notas'], 1) : 'Novo' ?>
                                    </span>
                                </div>

                                <h3 style="margin: 8px 0; font-size: 1.1rem; color: var(--primary-color);">
                                    <?= htmlspecialchars($anuncio['titulo']) ?>
                                </h3>

                                <p style="color: #64748b; font-size: 0.85rem; margin-bottom: 12px;">
                                    Por: <a href="perfil_publico.php?id=<?= $anuncio['prestador_id'] ?>" style="color: var(--primary-color); font-weight: 700; text-decoration: underline;"><?= htmlspecialchars($anuncio['prestador']) ?></a>
                                </p>

                                <?php if ($anuncio['preco_medio']): ?>
                                    <p style="font-weight: 700; color: var(--primary-color); font-size: 1.1rem; margin-bottom: 15px;">
                                        R$ <?= number_format($anuncio['preco_medio'], 2, ',', '.') ?>
                                    </p>
                                <?php endif; ?>
                            </div>

                            <a href="anuncio.php?id=<?= $anuncio['id'] ?>" class="btn-submit" style="display: block; text-align: center; text-decoration: none;">Ver Detalhes</a>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>