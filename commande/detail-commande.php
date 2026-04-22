<?php
require_once("countCommande.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connectedUser'])) {
	header("Location: ../auth/login.php");
	exit;
}

// Vérifier si l'ID de commande est fourni
if (!isset($_GET['id'])) {
	header("Location: ../profile/profile.php");
	exit;
}

$commande_id = (int)$_GET['id'];
$user_id = $_SESSION['connectedUser']['id'];

// Connexion à la base de données
$conn = connectsDB();

// Récupérer les détails de la commande sans les informations de livraison
$stmt = $conn->prepare("
    SELECT c.*, p.statut_paiement, p.transaction_id
    FROM commande c 
    LEFT JOIN paiement p ON c.id = p.commande_id 
    WHERE c.id = ? AND c.user_id = ?
");
$stmt->execute([$commande_id, $user_id]);
$commande = $stmt->fetch(PDO::FETCH_ASSOC);

// Vérifier si la commande existe et appartient à l'utilisateur
if (!$commande) {
	header("Location: ../profile/profile.php");
	exit;
}

// Récupérer les produits de la commande
$stmt = $conn->prepare("
    SELECT cp.*, p.nom, p.image_url, t.valeur as taille, c.nom as couleur 
    FROM commande_produits cp
    JOIN produits p ON cp.produit_id = p.id
    JOIN taille t ON cp.taille_id = t.id
    JOIN couleur c ON cp.couleur_id = c.id
    WHERE cp.commande_id = ?
");
$stmt->execute([$commande_id]);
$produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Détail de la commande #<?php echo $commande_id; ?></title>
	<link rel="stylesheet" href="../css/profile.css">
</head>

<body>
	<header>
		<!-- [En-tête similaire à profile.php] -->
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

	<main class="order-detail-container">
		<a href="../profile/profile.php" class="back-button">← Retour au profil</a>

		<div class="order-header">
			<h1>Commande #<?php echo $commande_id; ?></h1>
			<div class="order-meta">
				<p>Passée le <?php echo date('d/m/Y à H:i', strtotime($commande['commandee_le'])); ?></p>
				<div class="status-badge status-<?php echo strtolower($commande['statut']); ?>">
					<?php echo htmlspecialchars($commande['statut']); ?>
				</div>
			</div>
		</div>

		<div class="order-products">
			<h2>Produits commandés</h2>
			<?php foreach ($produits as $produit): ?>
				<div class="product-item">
					<img src="../<?php echo htmlspecialchars($produit['image_url']); ?>"
						alt="<?php echo htmlspecialchars($produit['nom']); ?>"
						class="product-image">
					<div class="product-details">
						<h3><?php echo htmlspecialchars($produit['nom']); ?></h3>
						<p>Taille: <?php echo htmlspecialchars($produit['taille']); ?></p>
						<p>Couleur: <?php echo htmlspecialchars($produit['couleur']); ?></p>
						<p>Quantité: <?php echo $produit['quantite']; ?></p>
						<?php
						// Récupérer le prix original depuis la table produits
						$conn = connectsDB();
						$stmt = $conn->prepare("SELECT prix FROM produits WHERE id = ?");
						$stmt->execute([$produit['produit_id']]);
						$prix_original = $stmt->fetchColumn();

						// Si le prix unitaire est différent du prix original, c'est qu'il y avait une promotion
						if ($prix_original != $produit['prix_unitaire']):
							$reduction = round((($prix_original - $produit['prix_unitaire']) / $prix_original) * 100);
						?>
							<div class="cart-item-price">
								<span class="price-original"><?php echo number_format($prix_original, 2); ?> €</span>
								<span class="price-promo"><?php echo number_format($produit['prix_unitaire'], 2); ?> €</span>
								<span class="reduction-badge">-<?php echo $reduction; ?>%</span>
							</div>
						<?php else: ?>
							<p class="price"><?php echo number_format($produit['prix_unitaire'], 2); ?> €</p>
						<?php endif; ?>
					</div>
				</div>
			<?php endforeach; ?>

			<div class="order-total">
				<p>Total: <?php echo number_format($commande['montant_total'], 2); ?> €</p>
			</div>
		</div>
	</main>

	<footer>
		<!-- [Pied de page similaire à profile.php] -->
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