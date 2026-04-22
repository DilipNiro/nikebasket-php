<?php
session_start();
require_once("../vendor/autoload.php");

use Sonata\GoogleAuthenticator\GoogleAuthenticator;

// Vérifier si l'utilisateur a des informations en attente de vérification
if (!isset($_SESSION['pending_user'])) {
	header('Location: login.php');
	exit();
}

$error = null;
$g = new GoogleAuthenticator();

// Traiter la soumission du code
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	if (isset($_POST['code'])) {
		$code = $_POST['code'];
		$secret = $_SESSION['pending_user']['secret'];

		if ($g->checkCode($secret, $code)) {
			// Le code est valide, on finalise la connexion
			$_SESSION['connectedUser'] = $_SESSION['pending_user'];
			unset($_SESSION['pending_user']);

			// Vérifier si l'employé doit changer son mot de passe
			if (
				$_SESSION['connectedUser']['role'] === 'employe' &&
				!$_SESSION['connectedUser']['password_changed']
			) {
				header('Location: ../auth/change-password.php');
				exit();
			}

			// Redirection selon le rôle
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
}
?>
<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Vérification 2FA</title>
	<link rel="stylesheet" href="../css/login.css">
	<style>
		.help-text {
			margin-top: 15px;
			color: #666;
			font-size: 0.9em;
		}

		.code-input {
			letter-spacing: 4px;
			font-family: monospace;
			font-size: 1.2em;
		}
	</style>
</head>

<body>
	<div class="login-container">
		<h2>Vérification 2FA</h2>

		<p>Veuillez entrer le code généré par Google Authenticator.</p>

		<?php if ($error): ?>
			<div class="error-message">
				<?php echo htmlspecialchars($error); ?>
			</div>
		<?php endif; ?>

		<form method="POST">
			<div class="form-group">
				<label for="code">Code de vérification</label>
				<input type="text"
					id="code"
					name="code"
					class="code-input"
					required
					placeholder="000000"
					pattern="[0-9]{6}"
					maxlength="6"
					inputmode="numeric"
					autocomplete="off">
			</div>
			<button type="submit">Vérifier</button>
		</form>

		<div class="help-text">
			<p>1. Ouvrez l'application Google Authenticator</p>
			<p>2. Trouvez l'entrée "Nike Basketball"</p>
			<p>3. Entrez le code à 6 chiffres affiché</p>
		</div>
	</div>

	<script>
		// Focus automatique sur le champ de code
		document.getElementById('code').focus();

		// Permettre uniquement les chiffres
		document.getElementById('code').addEventListener('input', function(e) {
			this.value = this.value.replace(/[^0-9]/g, '');
		});
	</script>
</body>

</html>