<?php
session_start();
require_once("../vendor/autoload.php");
require_once("../config/dbconnect.php");

use Sonata\GoogleAuthenticator\GoogleAuthenticator;
use Sonata\GoogleAuthenticator\GoogleQrUrl;

// Vérifier si l'utilisateur est en attente de configuration 2FA
if (!isset($_SESSION['pending_setup_user']) || !isset($_SESSION['qrcode_secret'])) {
	header('Location: login.php');
	exit();
}

$secret = $_SESSION['qrcode_secret'];
$email = $_SESSION['qrcode_email'];
$qrCodeUrl = GoogleQrUrl::generate($email, $secret, 'Nike Basketball');

// Traitement de la validation du code
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['code'])) {
	$g = new GoogleAuthenticator();
	$code = $_POST['code'];

	if ($g->checkCode($secret, $code)) {
		// Mettre à jour le secret dans la base de données
		$conn = connectsDB();
		$stmt = $conn->prepare('UPDATE user SET secret = ? WHERE id = ?');
		$stmt->execute([$secret, $_SESSION['pending_setup_user']['id']]);

		// Configurer la session
		$_SESSION['connectedUser'] = $_SESSION['pending_setup_user'];

		// Nettoyer les variables de session temporaires
		unset($_SESSION['pending_setup_user']);
		unset($_SESSION['qrcode_secret']);
		unset($_SESSION['qrcode_email']);

		// Rediriger selon le rôle
		switch ($_SESSION['connectedUser']['role']) {
			case 'admin':
				header('Location: ../admin/dashboard.php');
				break;
			case 'employe':
				header('Location: ../admin/dashboard.php');
				break;
			default:
				header('Location: ../index.php');
		}
		exit();
	} else {
		$error = "Code incorrect. Veuillez réessayer.";
	}
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Configuration de la double authentification</title>
	<link rel="stylesheet" href="../css/login.css">
</head>

<body>
	<div class="qrcode-container">
		<h2>Configuration de la double authentification</h2>
		<p>Veuillez scanner ce QR code avec Google Authenticator pour configurer votre compte</p>

		<img src="<?php echo $qrCodeUrl; ?>" alt="QR Code pour Google Authenticator">

		<p>Si vous ne pouvez pas scanner le QR code, utilisez cette clé : <strong><?php echo $secret; ?></strong></p>

		<?php if (isset($error)): ?>
			<div class="error-message"><?php echo $error; ?></div>
		<?php endif; ?>

		<form method="POST" class="verification-form">
			<div class="form-group">
				<label for="code">Entrez le code de vérification :</label>
				<input type="text" id="code" name="code" required pattern="[0-9]{6}" maxlength="6">
			</div>
			<button type="submit">Valider</button>
		</form>
	</div>
</body>

</html>