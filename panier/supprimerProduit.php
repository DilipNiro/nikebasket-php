<?php
// Inclure les fichiers nécessaires pour la connexion à la base de données et les fonctions du panier
require_once(__DIR__ . '/../config/dbconnect.php');
require_once("ajouterProduit.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connectedUser'])) {
	header("Location: ../index.php");
	exit;
} else {
	// Récupérer l'ID de l'utilisateur
	$user_id = $_SESSION['connectedUser']['id'];

	// Vérifier si l'ID du produit, la couleur et la taille sont fournis
	if (isset($_POST['produit_id']) && isset($_POST['couleur_id']) && isset($_POST['taille_id'])) {
		$produit_id = $_POST['produit_id'];
		$couleur_id = $_POST['couleur_id'];
		$taille_id = $_POST['taille_id'];
		var_dump($couleur_id);
		var_dump($taille_id);




		// Connexion à la base de données
		$conn = connectsDB();

		// Vérifier la quantité actuelle du produit dans le panier
		$sqlCheckQuantity = "SELECT quantite FROM panier WHERE user_id = :user_id AND produit_id = :produit_id AND couleur_id = :couleur_id AND taille_id = :taille_id";
		$stmtCheck = $conn->prepare($sqlCheckQuantity);
		$stmtCheck->bindParam(':user_id', $user_id);
		$stmtCheck->bindParam(':produit_id', $produit_id);
		$stmtCheck->bindParam(':couleur_id', $couleur_id);
		$stmtCheck->bindParam(':taille_id', $taille_id);
		$stmtCheck->execute();

		// Obtenir la quantité actuelle
		$result = $stmtCheck->fetch(PDO::FETCH_ASSOC);

		if ($result) {
			$currentQuantity = $result['quantite'];

			if ($currentQuantity > 1) {
				// Si la quantité est supérieure à 1, diminuer la quantité de 1
				$newQuantity = $currentQuantity - 1;
				$sqlUpdate = "UPDATE panier SET quantite = :newQuantity WHERE user_id = :user_id AND produit_id = :produit_id AND couleur_id = :couleur_id AND taille_id = :taille_id";
				$stmtUpdate = $conn->prepare($sqlUpdate);
				$stmtUpdate->bindParam(':newQuantity', $newQuantity);
				$stmtUpdate->bindParam(':user_id', $user_id);
				$stmtUpdate->bindParam(':produit_id', $produit_id);
				$stmtUpdate->bindParam(':couleur_id', $couleur_id);
				$stmtUpdate->bindParam(':taille_id', $taille_id);
				$stmtUpdate->execute();
			} else {
				// Si la quantité est 1, supprimer le produit du panier
				$sqlDelete = "DELETE FROM panier WHERE user_id = :user_id AND produit_id = :produit_id AND couleur_id = :couleur_id AND taille_id = :taille_id";
				$stmtDelete = $conn->prepare($sqlDelete);
				$stmtDelete->bindParam(':user_id', $user_id);
				$stmtDelete->bindParam(':produit_id', $produit_id);
				$stmtDelete->bindParam(':couleur_id', $couleur_id);
				$stmtDelete->bindParam(':taille_id', $taille_id);
				$stmtDelete->execute();
			}

			// Rediriger vers la page du panier ou une autre page de votre choix
			header("Location: " . $_SERVER['HTTP_REFERER']); // This will redirect to the referring page
			exit;
		} else {
			// Rediriger en cas d'erreur si le produit n'est pas trouvé dans le panier
			header("Location: ../produits/produits.php?error=produit_non_trouve");

			exit;
		}
	} else {
		// Rediriger en cas d'erreur
		header("Location: ../panier.php?error=produit_non_specifie");

		exit;
	}
}
