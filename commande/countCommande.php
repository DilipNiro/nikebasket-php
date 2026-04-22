<?php
require_once("../auth/functionLogin.php");


// Vérifie si l'utilisateur est connecté
if (isset($_SESSION['connectedUser'])) {
	// Récupérer l'ID de l'utilisateur connecté
	$user_id = $_SESSION['connectedUser']['id'];

	// Connexion à la base de données
	$conn = connectsDB();



	// Requête pour obtenir les commandes de l'utilisateur
	$stmt = $conn->prepare("SELECT * FROM commande WHERE user_id = :user_id ");
	$stmt->execute(['user_id' => $user_id]);
	$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
} else {
	// Gérer le cas où l'utilisateur n'est pas connecté
	echo "<div class='error-message'>Vous devez être connecté pour voir votre profil.</div>";
}
