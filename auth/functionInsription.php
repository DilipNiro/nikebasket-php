<?php

require_once("../config/dbconnect.php");
require_once("../vendor/autoload.php");

use Sonata\GoogleAuthenticator\GoogleAuthenticator;



/*
function generateSalt($length =32){
    return 
}
*/

function insertUser($data)
{
    // Générer le secret pour Google Authenticator
    $gAuth = new GoogleAuthenticator();
    $secret = $gAuth->generateSecret();

    // Ajouter le secret aux données utilisateur
    $data['secret'] = $secret;

    // Obtenir la connexion à la base de données
    $link = connectsDB();

    if (!$link) {
        echo "Problème de connexion à la base de données.";
        return false;
    }

    // Vérifier si l'utilisateur existe déjà
    if (!checkExistUser($data['email'])) {
        // Requête d'insertion
        $req = "INSERT INTO user (nom, email, password, secret) VALUES (?, ?, ?, ?)";
        $stmt = $link->prepare($req);

        if ($stmt->execute(array_values($data))) {
            // Stocker les informations nécessaires pour le QR code
            $_SESSION['qrcode_secret'] = $secret;
            $_SESSION['qrcode_email'] = $data['email'];
            header('location: show_qrcode.php');
        } else {
            var_dump($stmt->errorInfo());
            return false;
        }
    } else {
        echo "<div class='error-message'>L'utilisateur existe déjà.</div>";
        return false;
    }
}
function checkExistUser($email)
{
    // Obtenir la connexion à la base de données
    $link = connectsDB();

    if (!$link) {
        echo "Problème de connexion à la base de données.";
        return false;
    }

    // Requête pour vérifier si l'utilisateur existe
    $req = "SELECT COUNT(*) AS nombre FROM user WHERE email = ?";
    $stmt = $link->prepare($req);

    if ($stmt->execute([$email])) {
        $value = $stmt->fetch(PDO::FETCH_ASSOC);

        // Vérifier si un utilisateur avec cet email existe
        if ($value['nombre'] > 0) {
            return true;
        } else {
            return false;
        }
    } else {
        // Gestion des erreurs SQL
        var_dump($stmt->errorInfo());
        return false;
    }
}
