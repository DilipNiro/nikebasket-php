<?php
require_once("../config/dbconnect.php");
require_once("password_validation.php");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$token = $_POST["token"] ?? '';
	$token_hash = hash("sha256", $token);

	try {
		$conn = connectsDB();
		$stmt = $conn->prepare("SELECT * FROM user WHERE reset_token_hash = :token_hash");
		$stmt->execute(['token_hash' => $token_hash]);
		$user = $stmt->fetch(PDO::FETCH_ASSOC);

		if (!$user) {
			$errors[] = "Token non trouvé";
		} elseif (strtotime($user["reset_token_expires_at"]) <= time()) {
			$errors[] = "Le token a expiré";
		} else {
			$password = $_POST["password"] ?? '';
			$password_confirmation = $_POST["password_confirmation"] ?? '';

			$passwordValidation = validatePassword($password);
			if (!$passwordValidation['isValid']) {
				$errors = array_merge($errors, $passwordValidation['errors']);
			}

			if ($password !== $password_confirmation) {
				$errors[] = "Les mots de passe doivent correspondre";
			}

			if (empty($errors)) {
				$password_hash = hashPassword($password);
				$stmt = $conn->prepare("
                    UPDATE user 
                    SET password = :password_hash,
                        reset_token_hash = NULL,
                        reset_token_expires_at = NULL 
                    WHERE id = :user_id
                ");

				$stmt->execute([
					'password_hash' => $password_hash,
					'user_id' => $user["id"]
				]);

				$_SESSION['message'] = "Mot de passe mis à jour avec succès. Vous pouvez maintenant vous connecter.";
				header("Location: login.php");
				exit;
			}
		}
	} catch (PDOException $e) {
		$errors[] = "Erreur lors de la réinitialisation du mot de passe : " . $e->getMessage();
	}
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Réinitialiser le mot de passe</title>
	<link rel="stylesheet" href="../css/login.css">
</head>

<body>
	<div class="login-container">
		<h2>Réinitialiser le mot de passe</h2>

		<?php if (!empty($errors)): ?>
			<div class="error-message">
				<?php foreach ($errors as $error): ?>
					<p><?php echo htmlspecialchars($error); ?></p>
				<?php endforeach; ?>
			</div>
		<?php endif; ?>

		<form method="post">
			<input type="hidden" name="token" value="<?php echo htmlspecialchars($_GET['token'] ?? ''); ?>">

			<div class="form-group">
				<label for="password">Nouveau mot de passe</label>
				<input type="password" id="password" name="password" required>
			</div>

			<div class="form-group">
				<label for="password_confirmation">Confirmer le mot de passe</label>
				<input type="password" id="password_confirmation" name="password_confirmation" required>
			</div>

			<button type="submit">Réinitialiser le mot de passe</button>
		</form>
	</div>
</body>

</html>