<?php
$user = 'manjaka';
$password = 'admin'; // <--- Mets ton mot de passe ici

// password_hash utilise l'algorithme BCrypt par défaut,
// très sécurisé et compatible avec les versions récentes d'Apache.
$hashed = password_hash($password, PASSWORD_BCRYPT);

echo "<h3>Générateur .htpasswd (Format BCrypt)</h3>";
echo "Copie cette ligne dans ton fichier <b>.htpasswd</b> :<br><br>";
echo "<code style='background:#f4f4f4; padding:15px; border:1px solid #ccc; display:block;'>";
echo $user . ":" . $hashed;
echo "</code>";
?>