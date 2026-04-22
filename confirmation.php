<?php
require_once("config/dbconnect.php");
require_once("auth/functionLogin.php");

if (!isset($_GET['order'])) {
	header("Location: index.php");
	exit;
}

$order_id = intval($_GET['order']);
$user_id = $_SESSION['connectedUser']['id'] ?? null;

if (!$user_id) {
	header("Location: auth/login.php");
	exit;
}

try {
	$conn = connectsDB();

	// Récupérer les détails de la commande
	$sql = "SELECT c.*, l.nom as mode_livraison, l.delai, 
            al.nom as nom_livraison, al.adresse, al.code_postal, al.ville, al.pays, al.telephone
            FROM commande c
            JOIN livraison l ON c.livraison_id = l.id
            JOIN adresse_livraison al ON c.adresse_livraison_id = al.id
            WHERE c.id = :order_id AND c.user_id = :user_id";

	$stmt = $conn->prepare($sql);
	$stmt->execute([
		':order_id' => $order_id,
		':user_id' => $user_id
	]);

	$commande = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$commande) {
		header("Location: index.php");
		exit;
	}
} catch (Exception $e) {
	error_log($e->getMessage());
	header("Location: index.php");
	exit;
}
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Confirmation de commande</title>
	<link rel="stylesheet" href="css/styles.css">
	<style>
		.confirmation-container {
			max-width: 800px;
			margin: 50px auto;
			padding: 30px;
			background: white;
			border-radius: 12px;
			box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
		}

		.success-message {
			text-align: center;
			margin-bottom: 30px;
		}

		.success-message h1 {
			color: #28a745;
			margin-bottom: 15px;
		}

		.order-details {
			background: #f8f9fa;
			padding: 20px;
			border-radius: 8px;
			margin-bottom: 30px;
		}

		.order-details h2 {
			margin-bottom: 15px;
			border-bottom: 2px solid #dee2e6;
			padding-bottom: 10px;
		}

		.shipping-info {
			margin-top: 20px;
		}

		.buttons {
			text-align: center;
			margin-top: 30px;
		}

		.btn {
			display: inline-block;
			padding: 12px 24px;
			background: #000;
			color: white;
			text-decoration: none;
			border-radius: 25px;
			margin: 0 10px;
			transition: all 0.3s ease;
		}

		.btn:hover {
			background: #333;
			transform: translateY(-2px);
		}
	</style>
</head>

<body>
	<div class="confirmation-container">
		<div class="success-message">
			<h1>Commande confirmée !</h1>
			<p>Merci pour votre achat. Votre commande a été traitée avec succès.</p>
		</div>

		<div class="order-details">
			<h2>Détails de la commande #<?php echo $order_id; ?></h2>
			<p><strong>Date :</strong> <?php echo date('d/m/Y H:i', strtotime($commande['commandee_le'])); ?></p>
			<p><strong>Total :</strong> <?php echo number_format($commande['montant_total'] + $commande['frais_livraison'], 2); ?> €</p>

			<div class="shipping-info">
				<h3>Livraison</h3>
				<p><strong>Mode :</strong> <?php echo htmlspecialchars($commande['mode_livraison']); ?></p>
				<p><strong>Délai estimé :</strong> <?php echo htmlspecialchars($commande['delai']); ?></p>

				<h3>Adresse de livraison</h3>
				<p><?php echo htmlspecialchars($commande['nom_livraison']); ?></p>
				<p><?php echo htmlspecialchars($commande['adresse']); ?></p>
				<p><?php echo htmlspecialchars($commande['code_postal'] . ' ' . $commande['ville']); ?></p>
				<p><?php echo htmlspecialchars($commande['pays']); ?></p>
				<p><?php echo htmlspecialchars($commande['telephone']); ?></p>
			</div>
		</div>

		<div class="buttons">
			<a href="index.php" class="btn">Retour à l'accueil</a>
			<a href="profile/profile.php" class="btn">Voir mes commandes</a>
		</div>
	</div>
</body>

</html>