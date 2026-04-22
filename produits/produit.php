<?php
require_once("managementProd.php");
require_once("../auth/functionLogin.php");
require_once("listeProduits.php");
require_once("../panier/ajouterProduit.php");
require_once("../stock/listeStock.php");

// Check if a product ID is provided in the URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
	header("Location: produits.php");
	exit;
}

$product_id = $_GET['id'];
$produit = getProduitById($product_id);

// Check if product exists
if (!$produit) {
	header("Location: produits.php?error=notfound");
	exit;
}

$user_id = null;
if (isUserLoggedIn() && isset($_SESSION['connectedUser']['id'])) {
	$user_id = $_SESSION['connectedUser']['id'];
	$quantitePanier = quantityPanier($user_id);
} else {
	$quantitePanier = 0;
}

// Récupérer les images du produit
$productImages = getProductImages($product_id);
?>

<!DOCTYPE html>
<html lang="fr">

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title><?php echo htmlspecialchars($produit['nom']); ?></title>
	<link rel="stylesheet" href="../css/prod.css">
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
					<li><a href="#new-arrivals">Nouveautés</a></li>
					<li><a href="../produits/produits.php">Produits</a></li>
				</ul>
				<div class="account-cart">
					<div class="cart">
						<img src="../images/cart-icon.png" alt="Panier" class="cart-icon">
						<?php if (isUserLoggedIn() && isset($_SESSION['connectedUser']['id'])): ?>
							<span id="cart-count"><?php echo $quantitePanier; ?></span>
						<?php endif; ?>
					</div>
					<div class="account">
						<img src="../images/account-icon.webp" alt="Compte" class="account-icon" id="account-icon">
						<?php if (isUserLoggedIn()): ?>
							<span><?php echo htmlspecialchars($_SESSION['connectedUser']['nom_utilisateur']); ?></span>
							<div class="account-dropdown" id="account-dropdown">
								<ul>
									<li><a href="../profile/profile.php">Mon profil</a></li>
									<li><a href="../config/logout.php">Se Déconnecter</a></li>
								</ul>
							</div>
						<?php else: ?>
							<a href="../auth/login.php">Se connecter</a>
						<?php endif; ?>
					</div>

					<!-- Modal du panier -->
					<div id="cart-modal" class="cart-popup">
						<div class="cart-popup-content">
							<span class="close-popup">&times;</span>
							<h3>Votre Panier</h3>
							<div id="cart-items">
								<?php
								if (isset($user_id) && $user_id !== null) {
									$panierData = contenuPanier($user_id);
									$contentPanier = $panierData['contenu'];
									$total = $panierData['total'];
									if ($contentPanier): ?>
										<?php foreach ($contentPanier as $item): ?>
											<div class="cart-item">
												<img src="<?php echo "../" . $item['image_url']; ?>"
													alt="<?php echo htmlspecialchars($item['nom']); ?>"
													class="cart-item-image">
												<div class="cart-item-details">
													<p><?php echo htmlspecialchars($item['nom']); ?></p>
													<p>Couleur : <?php echo htmlspecialchars($item['couleur']); ?></p>
													<p>Taille : <?php echo htmlspecialchars($item['taille']); ?></p>
													<p class="cart-item-price"><?php echo number_format($item['prix'], 2, '.', ' '); ?> €</p>
													<p>Quantité : <?php echo $item['nombre_de_produits']; ?></p>
												</div>
												<form action="../panier/supprimerProduit.php" method="POST" class="delete-form">
													<input type="hidden" name="produit_id" value="<?php echo $item['id']; ?>">
													<input type="hidden" name="couleur_id" value="<?php echo $item['couleur_id']; ?>">
													<input type="hidden" name="taille_id" value="<?php echo $item['taille_id']; ?>">
													<button type="submit" name="delete">Supprimer</button>
												</form>
											</div>
										<?php endforeach; ?>

										<p>Total : <?php echo $total; ?> €</p>

										<div class="cart-buttons">
											<a href="../paiement/checkout.php" class="checkout-button">Passer à la caisse</a>
											<form action="../panier/viderPanier.php" method="POST">
												<button type="submit" name="viderPanier">Vider le panier</button>
											</form>
										</div>
									<?php else: ?>
										<p>Votre panier est vide</p>
									<?php endif;
								} else { ?>
									<p>Veuillez vous connecter pour voir votre panier</p>
								<?php } ?>
							</div>
						</div>
					</div>
				</div>
			</div>
		</nav>
	</header>

	<main>
		<div class="product-container">
			<div class="product-image">
				<?php if (empty($productImages)): ?>
					<div class="main-image">
						<img id="main-product-image"
							src="<?php echo "../" . htmlspecialchars($produit['image_url']); ?>"
							alt="<?php echo htmlspecialchars($produit['nom']); ?>">
					</div>
				<?php else: ?>
					<div class="main-image">
						<img id="main-product-image"
							src="../carousel/<?php echo htmlspecialchars($productImages[0]['image_url']); ?>"
							alt="<?php echo htmlspecialchars($produit['nom']); ?>">
					</div>
					<div class="thumbnail-container">
						<button class="thumbnail-nav prev">&lt;</button>
						<div class="thumbnail-wrapper">
							<div class="thumbnails">
								<?php foreach ($productImages as $index => $image): ?>
									<img src="../carousel/<?php echo htmlspecialchars($image['image_url']); ?>"
										alt="<?php echo htmlspecialchars($produit['nom']); ?>"
										class="thumbnail <?php echo $index === 0 ? 'active' : ''; ?>"
										onclick="changeMainImage(this.src, this)">
								<?php endforeach; ?>
							</div>
						</div>
						<button class="thumbnail-nav next">&gt;</button>
					</div>
				<?php endif; ?>
			</div>

			<div class="product-info">
				<h1><?php echo htmlspecialchars($produit['nom']); ?></h1>
				<p class="category"><?php echo htmlspecialchars($produit['categorie_nom']); ?></p>

				<p><strong>Prix :</strong> <?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>


				<p><strong>Description :</strong> <?php echo htmlspecialchars($produit['description']); ?></p>

				<form action="../panier/addProduitPanier.php" method="POST">
					<input type="hidden" name="produit_id" value="<?php echo $produit['id']; ?>">
					<input type="hidden" name="quantite" value="1">
					<input type="hidden" name="prix" value="<?php echo $produit['prix']; ?>">
					<?php if (isUserLoggedIn()): ?>
						<input type="hidden" name="user_id" value="<?php echo htmlspecialchars($_SESSION['connectedUser']['id']); ?>">
					<?php endif; ?>

					<?php $stocks = getStockParCouleurEtTaille($produit['id']); ?>
					<div class="color-selector">
						<div class="colors">
							<?php
							$couleurs = getCouleurs();
							foreach ($couleurs as $couleur):
								$disponible = isset($stocks[$couleur['id']]) && array_sum($stocks[$couleur['id']]) > 0;
								$couleurId = 'choixradio_' . htmlspecialchars($couleur['id']);
							?>
								<label class="color-option <?php echo !$disponible ? 'indisponible' : ''; ?>"
									style="background-color: <?php echo htmlspecialchars($couleur['nom']); ?>">
									<input type="radio"
										name="couleur"
										id="<?php echo $couleurId; ?>"
										value="<?php echo htmlspecialchars($couleur['id']); ?>"
										<?php echo !$disponible ? 'disabled' : ''; ?>
										required>
									<span><?php echo htmlspecialchars($couleur['nom']); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<div class="size-selector">
						<div class="sizes">
							<?php
							$tailles = getTailles();
							foreach ($tailles as $taille):
								$tailleId = $taille['id'];
							?>
								<label class="size-option">
									<input type="radio"
										name="taille"
										value="<?php echo $tailleId; ?>"
										required>
									<span>EU <?php echo htmlspecialchars($taille['valeur']); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<button type="submit" class="add-to-cart">Ajouter au panier</button>
				</form>
			</div>
		</div>
	</main>

	<footer class="footer">
		<div class="footer-container">
			<div class="footer-links">
				<a href="../index.php">Accueil</a>
				<a href="#new-arrivals">Nouveautés</a>
				<a href="#contact">Contact</a>
			</div>
			<div class="footer-social">
				<a href="https://facebook.com" target="_blank">
					<img src="../images/facebook-icon.png" alt="Facebook">
				</a>
				<a href="https://twitter.com" target="_blank">
					<img src="../images/x-icon.png" alt="X">
				</a>
			</div>
		</div>
		<p>&copy; 2024 Nike Basketball. Tous droits réservés.</p>
	</footer>

	<script src="../js/product-scripts.js"></script>
	<script src="../js/panier.js"></script>

	<script>
		const stocks = <?php echo json_encode($stocks); ?>;

		function updateSizes() {
			const selectedColorInput = document.querySelector('input[name="couleur"]:checked');
			if (!selectedColorInput) {
				return;
			}

			const selectedColorId = selectedColorInput.value;
			const sizeInputs = document.querySelectorAll('input[name="taille"]');

			// Réinitialiser toutes les tailles
			sizeInputs.forEach(sizeInput => {
				const label = sizeInput.closest('label');
				sizeInput.disabled = false;
				label.classList.remove('indisponible');
			});

			// Désactiver les tailles non disponibles
			if (stocks[selectedColorId]) {
				sizeInputs.forEach(sizeInput => {
					const tailleId = sizeInput.value;
					const label = sizeInput.closest('label');

					if (!stocks[selectedColorId][tailleId] || stocks[selectedColorId][tailleId] <= 0) {
						sizeInput.disabled = true;
						label.classList.add('indisponible');
					}
				});
			}
		}

		document.querySelectorAll('input[name="couleur"]').forEach(input => {
			input.addEventListener('change', updateSizes);
		});
	</script>
</body>

</html>