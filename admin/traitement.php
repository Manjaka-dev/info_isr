<?php
// On inclut la connexion PDO
require_once __DIR__ . '/../includes/connection.php';

function slugify($text) {
    // 1. Remplace les caractères accentués avec fallback si intl n'est pas chargé
    if (function_exists('transliterator_transliterate')) {
        $text = transliterator_transliterate('Any-Latin; Latin-ASCII; Lower()', $text);
    } else {
        $converted = @iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $text);
        $text = $converted !== false ? strtolower($converted) : strtolower($text);
    }

    // 2. Supprime tout ce qui n'est pas une lettre, un chiffre ou un espace
    $text = preg_replace('/[^a-z0-9\s-]+/', '', $text);

    // 3. Remplace les espaces et plusieurs tirets par un seul tiret
    $text = preg_replace('/[\s-]+/', '-', $text);

    // 4. Nettoie les tirets au début et à la fin
    return trim($text, '-');
}

function genererResume($html, $maxChars = 100) {
    $texte = strip_tags($html);
    $texte = html_entity_decode($texte, ENT_QUOTES | ENT_HTML5, 'UTF-8');
    $texte = trim(preg_replace('/\s+/', ' ', $texte));

    if ($texte === '') {
        return '';
    }

    if (function_exists('mb_strlen') && function_exists('mb_substr')) {
        if (mb_strlen($texte, 'UTF-8') <= $maxChars) {
            return $texte;
        }

        return mb_substr($texte, 0, $maxChars, 'UTF-8') . '...';
    }

    if (strlen($texte) <= $maxChars) {
        return $texte;
    }

    return substr($texte, 0, $maxChars) . '...';
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // 1. Récupération des données
    $titre = trim($_POST['titre'] ?? '');
    $contenu = $_POST['mon_contenu'] ?? ''; // Contient le HTML de TinyMCE
    $resume = genererResume($contenu, 100);
    $slug = slugify($titre);
    $image_name = null; // Par défaut, pas d'image
    if (isset($_FILES['image_couverture']) && $_FILES['image_couverture']['error'] === 0) {

        $file = $_FILES['image_couverture'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];

        if (in_array($extension, $allowed, true)) {
            // Nom unique pour l'optimisation du cache et éviter les conflits
            $target_ext = function_exists('imagewebp') ? 'webp' : 'jpg';
            $name = bin2hex(random_bytes(8)) . "." . $target_ext;
            $uploads_dir = __DIR__ . '/../uploads/';
            if (!is_dir($uploads_dir)) {
                mkdir($uploads_dir, 0755, true);
            }
            $destination = $uploads_dir . $name;

            // On récupère les dimensions d'origine
            $dimensions = @getimagesize($file['tmp_name']);
            if ($dimensions !== false) {
                list($width, $height) = $dimensions;
            } else {
                $width = 0;
                $height = 0;
            }

            if ($width > 0 && $height > 0) {
                $max_width = 1000;
                $ratio = $width / $height;

                if ($width > $max_width) {
                    $new_width = $max_width;
                    $new_height = $max_width / $ratio;
                } else {
                    $new_width = $width;
                    $new_height = $height;
                }

                switch ($extension) {
                    case 'png':
                        $src_image = @imagecreatefrompng($file['tmp_name']);
                        break;
                    case 'webp':
                        $src_image = @imagecreatefromwebp($file['tmp_name']);
                        break;
                    default:
                        $src_image = @imagecreatefromjpeg($file['tmp_name']);
                }

                if ($src_image !== false) {
                    $dst_image = imagecreatetruecolor((int)$new_width, (int)$new_height);

                    imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, (int)$new_width, (int)$new_height, (int)$width, (int)$height);

                    $saved = false;
                    if ($target_ext === 'webp') {
                        $saved = imagewebp($dst_image, $destination, 80);
                    } else {
                        $saved = imagejpeg($dst_image, $destination, 82);
                    }

                    if ($saved) {
                        $image_name = $name;
                    }

                    imagedestroy($src_image);
                    imagedestroy($dst_image);
                }
            }
        }
    }
    // 2. Préparation de la requête SQL
    $sql = "INSERT INTO articles (titre, slug, image_url, resume, contenu) VALUES (:titre, :slug, :image_url, :resume, :contenu)";
    if ($pdo) {
        $stmt = $pdo->prepare($sql);
    } else {
        die("Erreur de connexion à la base de données.");
    }

    // 3. Exécution
    try {
        $stmt->execute([
            ':titre' => $titre,
            ':slug' => $slug,
            ':image_url' => $image_name,
            ':resume' => $resume,
            ':contenu' => $contenu
        ]);
        header('Location: /optim/info_isr/?published=1');
        exit;
    } catch (PDOException $e) {
        echo "Erreur lors de l'enregistrement : " . $e->getMessage() . " <a href='/optim/info_isr/admin/'>Retour</a>";
    }
} else {
    header('Location: /optim/info_isr/admin/');
    exit;
}