<?php
require_once("ajouterProduit.php"); // Adjust path if needed

if (!isset($_SESSION['connectedUser'])) {
	// Redirection vers la page de connexion si l'utilisateur n'est pas connecté
	header('Location: ../auth/login.php');
	exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	// Retrieve and sanitize form data
	$id_produit = $_POST['produit_id'];
	$quantite = $_POST['quantite'];
	$user_id = $_POST['user_id'];
	$prix = $_POST['prix'];
	$couleur = $_POST['couleur'];
	$taille = $_POST['taille'];

	// Check if required data is available
	if (!empty($id_produit) && !empty($user_id) && !empty($prix) && !empty($couleur) && !empty($taille)) {
		// Prepare data for adding to cart
		$data = [
			'produit_id' => $id_produit,
			'quantite' => $quantite,
			'user_id' => $user_id,
			'prix' => $prix,
			'couleur' => $couleur,
			'taille' => $taille
		];

		// Add product to cart
		if (ajoutProduitPanier($data)) {
			header("Location: " . $_SERVER['HTTP_REFERER']); // This will redirect to the referring page
			exit;
		} else {
			// Display an error message if the addition fails
			echo "<div class='error-message'>Un problème est survenu. Veuillez réessayer plus tard.</div>";
		}
	} else {
		// Display a message if any required field is missing
		echo "<div class='error-message'>Veuillez sélectionner la couleur et la taille.</div>";
	}
}
