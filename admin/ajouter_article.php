<?php
require_once __DIR__ . '/auth.php';
requireAdminAuth();

$base_url = '/optim/info_isr';
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>Ajouter un article - Admin</title>
    <link rel="stylesheet" href="<?php echo $base_url; ?>/public/admin.css">
    <!-- 
        TinyMCE Editor
        ⚠️ IMPORTANT: Remplace "YOUR_API_KEY" par ta clé API TinyMCE gratuite
        Obtiens une clé gratuite sur: https://www.tiny.cloud/auth/signup
    -->
    <script src="https://cdn.tiny.cloud/1/t0ryip7gltoft29j0o4y484ibhed77y4vre0ahshz2dk7v48/tinymce/6/tinymce.min.js" referrerpolicy="origin"></script>

    <script>
        tinymce.init({
            selector: '#mon_contenu', // On cible le textarea par son ID
            plugins: 'lists link image code table help wordcount',
            toolbar: 'undo redo | blocks | bold italic | alignleft aligncenter alignright | bullist numlist outdent indent | link image | code',
            language: 'fr_FR',
            images_upload_url: 'upload_image.php',
            automatic_uploads: true,
            images_reuse_filename: true
        });
    </script>
</head>
<body>
<main class="admin-container">
<div class="admin-topbar">
    <a class="admin-logout" href="<?= $base_url ?>/admin/logout.php">Deconnexion</a>
</div>
<h1>Rédiger un nouvel article</h1>

<form action="<?= $base_url ?>/admin/traitement.php" method="POST" enctype="multipart/form-data">
    <label for="titre">Titre de l'article :</label><br>
    <input type="text" name="titre" id="titre" class="admin-input" required><br>

    <label for="image_couverture">Image de couverture (Optionnel) :</label><br>
    <input type="file" name="image_couverture" id="image_couverture" class="admin-file" accept="image/*"><br><br>

    <label for="mon_contenu">Contenu complet :</label><br>
    <textarea name="mon_contenu" id="mon_contenu"></textarea>

    <br>
    <button type="submit" class="admin-btn">Publier l'article</button>
</form>
</main>
</body>
</html>