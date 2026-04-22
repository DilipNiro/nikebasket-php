<?php
require_once("../auth/functionLogin.php");
require_once("../auth/password_validation.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connectedUser'])) {
	header('Location: ../auth/login.php');
	exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$current_password = $_POST['current_password'];
	$new_password = $_POST['new_password'];
	$confirm_password = $_POST['confirm_password'];

	// Vérifier que les nouveaux mots de passe correspondent
	if ($new_password !== $confirm_password) {
		$error = "Les nouveaux mots de passe ne correspondent pas.";
	} else {
		// Valider le nouveau mot de passe
		$passwordValidation = validatePassword($new_password);
		if (!$passwordValidation['isValid']) {
			$error = implode('<br>', $passwordValidation['errors']);
		} else {
			try {
				$conn = connectsDB();

				// Vérifier l'ancien mot de passe
				$stmt = $conn->prepare("SELECT password FROM user WHERE id = ?");
				$stmt->execute([$_SESSION['connectedUser']['id']]);
				$user = $stmt->fetch();

				if ($user && password_verify($current_password, $user['password'])) {
					// Hacher le nouveau mot de passe
					$new_password_hash = hashPassword($new_password);

					// Mettre à jour le mot de passe et marquer comme changé
					$stmt = $conn->prepare("UPDATE user SET password = ?, password_changed = TRUE WHERE id = ?");
					if ($stmt->execute([$new_password_hash, $_SESSION['connectedUser']['id']])) {
						$success = "Mot de passe modifié avec succès.";
						// Rediriger vers le dashboard après 2 secondes
						header("refresh:2;url=../admin/dashboard.php");
					} else {
						$error = "Erreur lors de la mise à jour du mot de passe.";
					}
				} else {
					$error = "Le mot de passe actuel est incorrect.";
				}
			} catch (PDOException $e) {
				$error = "Une erreur est survenue.";
			}
		}
	}
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Changer le mot de passe</title>
	<link rel="stylesheet" href="../css/login.css">
</head>

<body>
	<div class="login-container">
		<h2>Changer votre mot de passe</h2>

		<?php if ($error): ?>
			<div class="error-message"><?php echo $error; ?></div>
		<?php endif; ?>

		<?php if ($success): ?>
			<div class="success-message"><?php echo $success; ?></div>
		<?php endif; ?>

		<form method="POST">
			<div class="form-group">
				<label for="current_password">Mot de passe actuel</label>
				<input type="password" id="current_password" name="current_password" required>
			</div>

			<div class="form-group">
				<label for="new_password">Nouveau mot de passe</label>
				<input type="password" id="new_password" name="new_password" required>
			</div>

			<div class="form-group">
				<label for="confirm_password">Confirmez le nouveau mot de passe</label>
				<input type="password" id="confirm_password" name="confirm_password" required>
			</div>

			<div class="password-requirements">
				<p>Le mot de passe doit contenir :</p>
				<ul>
					<li>Au moins 12 caractères</li>
					<li>Au moins une lettre majuscule</li>
					<li>Au moins une lettre minuscule</li>
					<li>Au moins un chiffre</li>
					<li>Au moins un caractère spécial (!@#$%^&*(),.?":{}|<>)</li>
					<li>Pas d'espaces</li>
				</ul>
			</div>

			<button type="submit">Changer le mot de passe</button>
		</form>
	</div>
</body>

</html>