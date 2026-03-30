<?php
require_once __DIR__ . '/auth.php';

$base_url = '/optim/info_isr';
$error = '';

if (isAdminAuthenticated()) {
    header('Location: ' . $base_url . '/admin/ajouter_article.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = (string)($_POST['password'] ?? '');

    if (attemptAdminLogin($username, $password)) {
        header('Location: ' . $base_url . '/admin/ajouter_article.php');
        exit;
    }

    $error = 'Identifiants invalides.';
}
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Connexion admin</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/admin.css">
</head>
<body>
<main class="admin-container admin-login-container">
    <h1>Connexion admin</h1>

    <?php if ($error !== ''): ?>
        <p class="admin-alert"><?php echo htmlspecialchars($error); ?></p>
    <?php endif; ?>

    <form method="POST" action="<?php echo $base_url; ?>/admin/login.php">
        <label for="username">Utilisateur</label>
        <input class="admin-input" type="text" id="username" name="username" required>

        <label for="password">Mot de passe</label>
        <input class="admin-input" type="password" id="password" name="password" required>

        <button class="admin-btn" type="submit">Se connecter</button>
    </form>
</main>
</body>
</html>

