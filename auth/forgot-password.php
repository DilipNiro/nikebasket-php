<?php
require_once('../vendor/autoload.php');
require_once("../config/dbconnect.php");

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function sendResetEmail($email, $token)
{
	$mail = new PHPMailer(true);

	try {
		// Configuration du serveur
		$mail->isSMTP();
		$mail->Host = 'smtp.gmail.com';
		$mail->SMTPAuth = true;
		$mail->Username = getenv('SMTP_USER') ?: 'votre_email@gmail.com'; // Votre Gmail
		$mail->Password = getenv('SMTP_PASS') ?: 'votre_mot_de_passe_application'; // Mot de passe application Gmail
		$mail->setFrom(getenv('SMTP_USER') ?: 'votre_email@gmail.com', 'Nike Basketball');
		$mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
		$mail->Port = 587;

		// Configuration de l'email
		$mail->setFrom('votre_email@gmail.com', 'Nike Basketball');
		$mail->addAddress($email);
		$mail->CharSet = 'UTF-8';

		// Contenu
		$mail->isHTML(true);
		$mail->Subject = 'Réinitialisation de votre mot de passe';

// Nouveau code (solution)
// Détecter automatiquement le protocole (http/https) et le domaine
$protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
$domain = $_SERVER['HTTP_HOST'];
$base_path = dirname(dirname($_SERVER['PHP_SELF']));

// Construire le lien avec le chemin correct
$reset_link = "$protocol://$domain$base_path/auth/reset-password.php?token=" . urlencode($token);
		$mail->Body = "
            <p>Vous avez demandé la réinitialisation de votre mot de passe.</p>
            <p>Cliquez sur le lien suivant pour réinitialiser votre mot de passe :</p>
            <p><a href='$reset_link'>Réinitialiser mon mot de passe</a></p>
            <p>Ce lien expire dans 1 heure.</p>
            <p>Si vous n'avez pas demandé cette réinitialisation, ignorez cet email.</p>
        ";

		$mail->send();
		return true;
	} catch (Exception $e) {
		error_log("Erreur d'envoi d'email: " . $mail->ErrorInfo);
		return false;
	}
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);

	try {
		$conn = connectsDB();

		// Vérifier si l'email existe
		$stmt = $conn->prepare("SELECT * FROM user WHERE email = :email");
		$stmt->execute(['email' => $email]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if ($user) {
			// Générer un token unique
			$token = bin2hex(random_bytes(32));
			$token_hash = hash('sha256', $token);
			$expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

			// Mettre à jour la base de données
			$stmt = $conn->prepare("
                UPDATE user 
                SET reset_token_hash = :token_hash,
                    reset_token_expires_at = :expiry
                WHERE email = :email
            ");

			$stmt->execute([
				'token_hash' => $token_hash,
				'expiry' => $expiry,
				'email' => $email
			]);

			// Envoyer l'email
			if (sendResetEmail($email, $token)) {
				$_SESSION['message'] = "Si l'adresse email existe, vous recevrez un lien de réinitialisation.";
			} else {
				$_SESSION['message'] = "Une erreur est survenue lors de l'envoi de l'email.";
			}
		} else {
			// Même message que si l'email existe pour la sécurité
			$_SESSION['message'] = "Si l'adresse email existe, vous recevrez un lien de réinitialisation.";
		}

		header("Location: login.php");
		exit;
	} catch (PDOException $e) {
		$_SESSION['message'] = "Une erreur est survenue. Veuillez réessayer plus tard.";
		header("Location: login.php");
		exit;
	}
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Mot de passe oublié</title>
	<link rel="stylesheet" href="../css/login.css">
</head>

<body>
	<div class="login-container">
		<h2>Mot de passe oublié</h2>

		<form method="post" action="">
			<div class="form-group">
				<label for="email">Email</label>
				<input type="email" id="email" name="email" required>
			</div>

			<button type="submit">Envoyer le lien de réinitialisation</button>
		</form>

		<p><a href="login.php">Retour à la connexion</a></p>
	</div>
</body>

</html>