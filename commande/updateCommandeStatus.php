<?php
require_once("../auth/FunctionLogin.php");
require_once("gestionCommandes.php");

// Vérifier si la requête est en POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
	echo json_encode(['success' => false, 'error' => 'Méthode non autorisée']);
	exit;
}

// Modifier la condition pour autoriser les admins ET les employés
if (!isUserLoggedIn() || ($_SESSION['connectedUser']['role'] !== 'admin' && $_SESSION['connectedUser']['role'] !== 'employe')) {
	echo json_encode(['success' => false, 'error' => 'Accès non autorisé']);
	exit;
}

// Vérifier la présence des paramètres requis
if (!isset($_POST['commande_id']) || !isset($_POST['status'])) {
	echo json_encode(['success' => false, 'error' => 'Paramètres manquants']);
	exit;
}

$commande_id = (int)$_POST['commande_id'];
$status = $_POST['status'];

// Vérifier que le statut est valide
$statuts_valides = ['en_attente', 'payee', 'en_preparation', 'expediee', 'livree', 'annulee'];
if (!in_array($status, $statuts_valides)) {
	echo json_encode(['success' => false, 'error' => 'Statut invalide']);
	exit;
}

try {
	$conn = connectsDB();
	$conn->beginTransaction();

	// Mise à jour directe du statut dans la table commande
	$stmt = $conn->prepare("UPDATE commande SET statut = :status WHERE id = :commande_id");
	$success = $stmt->execute([
		':status' => $status,
		':commande_id' => $commande_id
	]);

	if ($success) {
		// Journaliser le changement de statut
		$stmt = $conn->prepare("
            INSERT INTO commande_historique 
            (commande_id, status, modifie_par, modifie_le) 
            VALUES (:commande_id, :status, :user_id, NOW())
        ");

		$stmt->execute([
			':commande_id' => $commande_id,
			':status' => $status,
			':user_id' => $_SESSION['connectedUser']['id']
		]);

		// Si le statut est passé à "payee", mettre à jour le statut de paiement
		if ($status === 'payee') {
			$stmt = $conn->prepare("
                UPDATE paiement 
                SET statut_paiement = 'completed'
                WHERE commande_id = :commande_id
            ");
			$stmt->execute([':commande_id' => $commande_id]);
		}

		$conn->commit();
		echo json_encode([
			'success' => true,
			'message' => 'Statut mis à jour avec succès',
			'new_status' => $status
		]);
	} else {
		$conn->rollBack();
		echo json_encode(['success' => false, 'error' => 'Erreur lors de la mise à jour']);
	}
} catch (Exception $e) {
	$conn->rollBack();
	error_log('Erreur lors de la mise à jour du statut: ' . $e->getMessage());
	echo json_encode(['success' => false, 'error' => 'Erreur serveur: ' . $e->getMessage()]);
}
