<?php
require_once 'includes/connection.php';

$base_url = '/optim/info_isr';

// 1. On récupère l'ID de l'URL et on s'assure que c'est un nombre entier
$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id > 0) {
    // 2. On prépare la requête pour récupérer l'article spécifique
    $stmt = $pdo->prepare("SELECT * FROM articles WHERE id = ?");
    $stmt->execute([$id]);
    $article = $stmt->fetch();

    // Si l'article n'existe pas en base
    if (!$article) {
        die("Cet article n'existe pas.");
    }
} else {
    die("ID d'article invalide.");
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title><?php echo htmlspecialchars($article['titre']); ?></title>
    <meta name="description" content="<?php echo htmlspecialchars($article['resume']); ?>">
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/style.css">
</head>
<body>
<header>
    <a href="<?php echo $base_url; ?>/">← Retour a l'accueil</a>
</header>

<main class="article-content">
    <h1><?php echo htmlspecialchars($article['titre']); ?></h1>
    <p><em>Publié le <?php echo $article['date_creation']; ?></em></p>

    <?php if (!empty($article['image_url'])): ?>
        <img src="<?php echo $base_url; ?>/uploads/<?php echo htmlspecialchars($article['image_url']); ?>"
             alt="<?php echo htmlspecialchars($article['titre']); ?>"
             class="cover-image">
    <?php endif; ?>

    <hr>

    <div class="content article-body">
        <?php echo $article['contenu']; ?>
    </div>
</main>
</body>
</html>
