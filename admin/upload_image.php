<?php
require_once __DIR__ . '/auth.php';

if (!isAdminAuthenticated()) {
    http_response_code(401);
    header('Content-Type: application/json');
    echo json_encode(['error' => 'Non autorise.']);
    exit;
}

header('Content-Type: application/json');

$dir = __DIR__ . '/../uploads/';
if (!is_dir($dir)) {
    mkdir($dir, 0755, true);
}

if (!isset($_FILES['file']) || $_FILES['file']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(['error' => 'Fichier manquant ou upload invalide.']);
    exit;
}

$file = $_FILES['file'];
$extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
$allowed = ['jpg', 'jpeg', 'png', 'webp'];

if (!in_array($extension, $allowed, true)) {
    http_response_code(415);
    echo json_encode(['error' => 'Format non supporte.']);
    exit;
}

$dimensions = @getimagesize($file['tmp_name']);
if ($dimensions === false) {
    http_response_code(415);
    echo json_encode(['error' => 'Le fichier n\'est pas une image valide.']);
    exit;
}

list($width, $height) = $dimensions;
if ($width <= 0 || $height <= 0) {
    http_response_code(415);
    echo json_encode(['error' => 'Dimensions invalides.']);
    exit;
}

// Nom unique pour l'optimisation du cache et éviter les conflits
$target_ext = function_exists('imagewebp') ? 'webp' : 'jpg';
$name = bin2hex(random_bytes(8)) . '.' . $target_ext;
$destination = $dir . $name;

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

if ($src_image === false) {
    http_response_code(415);
    echo json_encode(['error' => 'Impossible de lire cette image.']);
    exit;
}

$dst_image = imagecreatetruecolor((int)$new_width, (int)$new_height);
imagecopyresampled($dst_image, $src_image, 0, 0, 0, 0, (int)$new_width, (int)$new_height, (int)$width, (int)$height);

$saved = false;
if ($target_ext === 'webp') {
    $saved = imagewebp($dst_image, $destination, 80);
} else {
    $saved = imagejpeg($dst_image, $destination, 82);
}

if (!$saved) {
    imagedestroy($src_image);
    imagedestroy($dst_image);
    http_response_code(500);
    echo json_encode(['error' => 'Erreur lors de la sauvegarde de l\'image.']);
    exit;
}

imagedestroy($src_image);
imagedestroy($dst_image);

echo json_encode([
    'location' => '/optim/info_isr/uploads/' . $name,
]);
