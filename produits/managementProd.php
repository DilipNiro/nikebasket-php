<?php
require_once(__DIR__ . '/../config/dbconnect.php');
require_once(__DIR__ . '/listeProduits.php'); // Inclure le fichier listeProduits.php pour accéder aux fonctions

$produit = null; // Initialisation de la variable produit

if (isset($_GET['id'])) {
	$produit_id = intval($_GET['id']);

	// Utiliser la fonction getProduitById pour récupérer le produit par ID
	$produit = getProduitById($produit_id);

	// Vérifiez si le produit existe
	if (!$produit) {
		echo "Produit non trouvé.";
		exit;
	}
} else {
	echo "ID de produit manquant.";
	exit;
}
