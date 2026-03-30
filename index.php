<?php
// 1. On appelle la connexion
require_once 'includes/connection.php';

$base_url = '/optim/info_isr';

$articles_par_page = 5; // Tu peux changer ce nombre
$page_actuelle = isset($_GET['page']) ? (int)$_GET['page'] : 1;
if ($page_actuelle < 1) $page_actuelle = 1;

$total_articles = $pdo->query("SELECT COUNT(*) FROM articles")->fetchColumn();
$total_pages = max(1, ceil($total_articles / $articles_par_page));

if ($page_actuelle > $total_pages) $page_actuelle = $total_pages;

$offset = ($page_actuelle - 1) * $articles_par_page;

$stmt = $pdo->prepare("SELECT id, titre, slug, image_url, resume, date_creation 
                       FROM articles 
                       ORDER BY date_creation DESC 
                       LIMIT :limit OFFSET :offset");

$stmt->bindValue(':limit', $articles_par_page, PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Mon Blog - Page <?php echo $page_actuelle; ?></title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/style.css">
</head>
<body>
<main>
    <header class="page-header">
        <h1>Derniers articles</h1>
        <a class="admin-link" href="<?php echo $base_url; ?>/admin/">Admin</a>
    </header>

    <?php if (empty($articles)): ?>
        <p class="empty-state">Aucun article pour le moment.</p>
    <?php endif; ?>

    <?php foreach ($articles as $article): ?>
        <article class="article-card">
            <?php if (!empty($article['image_url'])): ?>
                <img src="<?php echo $base_url; ?>/uploads/<?php echo htmlspecialchars($article['image_url']); ?>"
                     alt="<?php echo htmlspecialchars($article['titre']); ?>"
                     class="article-thumbnail">
            <?php endif; ?>
            <h2><?php echo htmlspecialchars($article['titre']); ?></h2>
            <p><?php echo htmlspecialchars($article['resume']); ?></p>
            <a class="read-more" href="<?php echo $base_url; ?>/article/<?php echo (int)$article['id'] . '-' . urlencode((string)$article['slug']); ?>">Lire la suite</a>
        </article>
    <?php endforeach; ?>

    <nav class="pagination">
        <?php if ($page_actuelle > 1): ?>
            <a href="<?php echo $base_url; ?>/page/<?php echo $page_actuelle - 1; ?>">« Precedent</a>
        <?php endif; ?>

        <span>Page <?php echo $page_actuelle; ?> sur <?php echo $total_pages; ?></span>

        <?php if ($page_actuelle < $total_pages): ?>
            <a href="<?php echo $base_url; ?>/page/<?php echo $page_actuelle + 1; ?>">Suivant »</a>
        <?php endif; ?>
    </nav>
</main>
</body>
</html>