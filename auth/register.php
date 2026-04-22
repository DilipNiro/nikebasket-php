<?php
session_start();
require_once("functionInsription.php");
require_once("password_validation.php");
require_once("email_validation.php");

$errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nom = htmlspecialchars(trim($_POST['nom']));
    $email = trim($_POST['email']);
    $pwd = $_POST['pwd'];

    // Valider l'email
    $emailValidation = validateEmail($email);
    if (!$emailValidation['isValid']) {
        $errors = array_merge($errors, $emailValidation['errors']);
    } else {
        $email = $emailValidation['sanitized_email'];
    }

    // Valider le mot de passe
    $passwordValidation = validatePassword($pwd);
    if (!$passwordValidation['isValid']) {
        $errors = array_merge($errors, $passwordValidation['errors']);
    }

    // Valider le nom
    if (empty($nom)) {
        $errors[] = "Le nom est obligatoire";
    } elseif (strlen($nom) < 2 || strlen($nom) > 50) {
        $errors[] = "Le nom doit contenir entre 2 et 50 caractères";
    }

    // Si aucune erreur, procéder à l'inscription
    if (empty($errors)) {
        $hashed_password = hashPassword($pwd);

        $data = [
            'nom' => $nom,
            'email' => $email,
            'password' => $hashed_password
        ];

        if (insertUser($data)) {
            header('Location: show_qrcode.php');
            exit;
        } else {
            $errors[] = "Un problème est survenu lors de l'inscription. Veuillez réessayer plus tard.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscription</title>
    <link rel="stylesheet" href="../css/register.css">
</head>

<body>
    <div class="signup-container">
        <h2>Inscription</h2>

        <?php if (!empty($errors)): ?>
            <div class="error-message">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo htmlspecialchars($error); ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="#" method="post" novalidate>
            <div class="form-group">
                <label for="nom">Nom:</label>
                <input type="text"
                    id="nom"
                    name="nom"
                    required
                    value="<?php echo isset($_POST['nom']) ? htmlspecialchars($_POST['nom']) : ''; ?>"
                    minlength="2"
                    maxlength="50">
                <small class="form-text">Entre 2 et 50 caractères</small>
            </div>

            <div class="form-group">
                <label for="email">Email:</label>
                <input type="email"
                    id="email"
                    name="email"
                    required
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                <small class="form-text">Entrez une adresse email valide</small>
            </div>

            <div class="form-group">
                <label for="pwd">Mot de passe:</label>
                <input type="password"
                    id="pwd"
                    name="pwd"
                    required>
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
            </div>

            <button type="submit">S'inscrire</button>
        </form>

        <p>Déjà un compte ? <a href="login.php">Se connecter</a></p>
    </div>
</body>

</html>