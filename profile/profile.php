<?php
require_once("../auth/FunctionLogin.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connectedUser'])) {
	header("Location: ../auth/login.php");
	exit;
}

// Récupérer les informations de l'utilisateur
$user = $_SESSION['connectedUser'];

// Récupérer toutes les commandes de l'utilisateur
$conn = connectsDB();
$stmt = $conn->prepare("
    SELECT c.*, p.statut_paiement 
    FROM commande c 
    LEFT JOIN paiement p ON c.id = p.commande_id 
    WHERE c.user_id = ? 
    ORDER BY c.commandee_le DESC
");
$stmt->execute([$user['id']]);
$commandes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Mon Profil - Nike Basketball</title>
	<link rel="stylesheet" href="../css/profile.css">
</head>

<body>
	<header>
		<nav>
			<div class="nav-container">
				<div class="logo">
					<img src="../images/logo.png" alt="Logo Nike" class="logo-img">
				</div>
				<ul class="nav-links">
					<li><a href="../index.php">Accueil</a></li>
					<li><a href="../produits/produits.php">Produits</a></li>
				</ul>
				<div class="account-cart">
					<a href="../config/logout.php" class="logout-btn">Se Déconnecter</a>
				</div>
			</div>
		</nav>
	</header>

	<main class="container">
		<div class="profile-header">
			<h1>Bienvenue, <?php echo htmlspecialchars($user['nom_utilisateur'] ?? ''); ?></h1>
			<p>Email: <?php echo htmlspecialchars($user['email'] ?? ''); ?></p>
		</div>

		<div class="profile-stats">
			<div class="stat-card">
				<h3><?php echo count($commandes); ?></h3>
				<p>Commandes totales</p>
			</div>
			<div class="stat-card">
				<h3><?php
					$totalDepense = array_reduce($commandes, function ($carry, $item) {
						return $carry + $item['montant_total'];
					}, 0);
					echo number_format($totalDepense, 2, ',', ' ') . ' €';
					?></h3>
				<p>Total dépensé</p>
			</div>
			<div class="stat-card">
				<h3><?php
					$commandesRecentes = array_filter($commandes, function ($commande) {
						return strtotime($commande['commandee_le']) > strtotime('-30 days');
					});
					echo count($commandesRecentes);
					?></h3>
				<p>Commandes ce mois</p>
			</div>
		</div>

		<h2>Mes commandes</h2>
		<div class="orders-list">
			<?php if (empty($commandes)): ?>
				<div class="order-item">
					<p>Vous n'avez pas encore passé de commande.</p>
				</div>
			<?php else: ?>
				<?php foreach ($commandes as $commande): ?>
					<div class="order-item">
						<div class="order-details">
							<strong>Commande #<?php echo $commande['id']; ?></strong>
							<p>Date: <?php echo date('d/m/Y H:i', strtotime($commande['commandee_le'])); ?></p>
							<p>Montant: <?php echo number_format($commande['montant_total'], 2, ',', ' '); ?> €</p>
						</div>
						<span class="order-status status-<?php echo strtolower($commande['statut']); ?>">
							<?php echo htmlspecialchars($commande['statut']); ?>
						</span>
						<a href="../commande/detail-commande.php?id=<?php echo $commande['id']; ?>" class="view-order-btn">
							Voir détails
						</a>
					</div>
				<?php endforeach; ?>
			<?php endif; ?>
		</div>
	</main>

	<footer>
		<div class="footer-container">
			<div class="footer-links">
				<a href="../index.php">Accueil</a>
				<a href="../produits/produits.php">Produits</a>
			</div>
			<div class="footer-social">
				<a href="https://facebook.com" target="_blank"><img src="../images/facebook-icon.png" alt="Facebook"></a>
				<a href="https://twitter.com" target="_blank"><img src="../images/x-icon.png" alt="x"></a>
			</div>
		</div>
		<p>&copy; 2024 Nike Basketball. Tous droits réservés.</p>
	</footer>
</body>

</html>