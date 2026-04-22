<?php
require_once(__DIR__ . '/../config/dbconnect.php');
require_once(__DIR__ . '/../panier/ajouterProduit.php');

if (!isset($_SESSION['connectedUser'])) {
	header("Location: ../index.php");
	exit;
} else {
	$user_id = $_SESSION['connectedUser']['id'];

	// Appel à la fonction pour vider le panier de l'utilisateur
	viderPanier($user_id);
}





function viderPanier($user_id)
{
	$conn = connectsDB();

	// Suppression de tous les articles du panier pour l'utilisateur connecté
	$query = $conn->prepare("DELETE FROM panier WHERE user_id = ?");
	$query->execute([$user_id]);
	header("Location: ../index.php");
}
