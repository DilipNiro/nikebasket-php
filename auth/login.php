<?php
session_start();
require_once("functionLogin.php");

$error = null;



if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = htmlspecialchars(trim($_POST['email']));
    $pwd = $_POST['password'];

    if (!empty($email) && !empty($pwd)) {
        $data = [
            'email' => $email,
            'password' => $pwd
        ];

        $loginResult = login($data);

        if ($loginResult === 'setup_2fa') {
            header('Location: setup_2fa.php');
            exit;
        } elseif ($loginResult === true) {
            header('Location: verify_2fa.php');
            exit;
        }
    } else {
        echo "<div class='error-message'>Tous les champs sont obligatoires.</div>";
    }
}

?>
<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Connexion</title>
    <link rel="stylesheet" href="../css/login.css">
</head>

<body>
    <div class="login-container">
        <h2>Se connecter</h2>

        <?php if ($error): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email"
                    id="email"
                    name="email"
                    required
                    placeholder="Entrez votre email"
                    value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
            </div>

            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password"
                    id="password"
                    name="password"
                    required
                    placeholder="Entrez votre mot de passe">
            </div>

            <button type="submit">Connexion</button>
        </form>
        <p>Pas encore de compte ? <a href="register.php">S'inscrire</a></p>
        <p><a href="forgot-password.php">Mot de passe oublié ?</a></p>


    </div>
</body>

</html>