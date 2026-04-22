<?php
require_once(__DIR__ . '/../config/dbconnect.php');
require_once(__DIR__ . '/../vendor/autoload.php');

use Sonata\GoogleAuthenticator\GoogleAuthenticator;



function login($data)
{
    $conn = connectsDB();

    if (!$conn) {
        echo "Problème de connexion à la base de données.";
        return false;
    }

    try {
        $stmt = $conn->prepare('SELECT id, nom, email, password, role, secret, password_changed FROM user WHERE email = ?');

        $stmt->execute([$data['email']]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($data['password'], $user['password'])) {
            // Si c'est un utilisateur avec 2FA déjà configuré
            if (!empty($user['secret'])) {
                $_SESSION['pending_user'] = [
                    'id' => $user['id'],
                    'nom_utilisateur' => $user['nom'],
                    'email' => $user['email'],
                    'role' => 'client', // Forcer le rôle client même si admin/employé
                    'secret' => $user['secret'],
                    'password_changed' => $user['password_changed']
                ];
                return true;
            } 
            // Si c'est un nouvel utilisateur sans 2FA configuré
            else {
                // Générer un nouveau secret pour 2FA
                $gAuth = new GoogleAuthenticator();
                $secret = $gAuth->generateSecret();

                // Stocker les informations en session pour la configuration 2FA
                $_SESSION['qrcode_secret'] = $secret;
                $_SESSION['qrcode_email'] = $user['email'];
                $_SESSION['pending_setup_user'] = [
                    'id' => $user['id'],
                    'nom_utilisateur' => $user['nom'],
                    'email' => $user['email'],
                    'role' => 'client' // Forcer le rôle client
                ];
                return 'setup_2fa';
            }
        } else {
            echo "<div class='error-message'>Identifiants incorrects.</div>";
            return false;
        }
    } catch (PDOException $e) {
        echo "Erreur lors de la connexion : " . $e->getMessage();
        return false;
    } finally {
        closesDB($conn);
    }
}
function isUserLoggedIn()
{
    return isset($_SESSION['connectedUser']);
}
