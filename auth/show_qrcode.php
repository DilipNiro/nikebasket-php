<?php
session_start();
require_once("../vendor/autoload.php");

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

// Vérifier si les informations nécessaires sont en session
if (!isset($_SESSION['qrcode_secret']) || !isset($_SESSION['qrcode_email'])) {
	header('Location: login.php');
	exit();
}

$secret = $_SESSION['qrcode_secret'];
$email = $_SESSION['qrcode_email'];

// Générer l'URL du QR code
$qrCodeUrl = GoogleQrUrl::generate($email, $secret, 'Nike Basketball');

?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Configuration 2FA</title>
	<link rel="stylesheet" href="../css/login.css">

</head>

<body>
	<div class="qrcode-container">
		<h2>Configuration de la double authentification</h2>
		<p>Scannez le QR code avec Google Authenticatore pour activer la 2FA</p>
		<img src="<?php echo $qrCodeUrl; ?>" alt="QR Code pour Google Authenticator">
		<p>Si vous ne pouvez pas scanner le QR code,utilisez cette clé manuelle : <strong><?php echo $secret; ?> </strong></p>
		<a href="login.php">Continuez vers la connexion</a>
	</div>
</body>

</html>