<?php
if (!isset($_SESSION)) {
    session_start();
}
function connectsDB()
{

    $host = 'localhost';
    $dbname = 'basketnike';
    $username = 'root';
    $password = '';


    try {
        // Créer une nouvelle connexion PDO
        $conn = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8", $username, $password);
        // Définir le mode d'erreur PDO pour les exceptions

        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        // Afficher un message d'erreur si la connexion échoue
        die("Échec de la connexion : " . $e->getMessage());
    }
}


function closesDB($conn)
{
    $conn = null;
}
