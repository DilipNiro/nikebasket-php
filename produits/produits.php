<!DOCTYPE html>
<html lang="fr">
<?php
require_once("managementProduits.php");
require_once("../stock/listeStock.php");
require_once("listeProduits.php");
require_once("../auth/functionLogin.php");

$user_id = null;
if (isUserLoggedIn() && isset($_SESSION['connectedUser']['id'])) {
	$user_id = $_SESSION['connectedUser']['id'];
}

$variables = initializeVariables();
extract($variables);
?>

<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Tous les Produits - Nike Basketball</title>
	<link rel="stylesheet" href="../css/product2.css">
	<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.css">
	<script src="https://cdnjs.cloudflare.com/ajax/libs/sweetalert/1.1.3/sweetalert.min.js"></script>
</head>

<body>
	<?php
	if (isset($_SESSION['message'])) {
		echo "<script>
            swal({
                title: 'Information',
                text: '{$_SESSION['message']}',
                icon: 'info',
                button: 'OK',
            });
        </script>";
		unset($_SESSION['message']);
	}
	?>
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

				<div class="search-bar">
					<input type="text" name="search" id="search-input" placeholder="Rechercher un produit..."
						class="search-input" value="<?php echo htmlspecialchars($search ?? ''); ?>">
					<button type="button" class="search-button">
						<img src="../images/search-icon.jpg" alt="Rechercher" class="search-icon">
					</button>
				</div>

				<div class="account-cart">
					<div class="cart">
						<img src="../images/cart-icon.png" alt="Panier" class="cart-icon">
						<?php if (isUserLoggedIn() && isset($_SESSION['connectedUser']['id'])): ?>
							<span id="cart-count"><?php echo $quantitePanier ?></span>
						<?php endif; ?>
					</div>

					<div class="account">
						<img src="../images/account-icon.webp" alt="Compte" class="account-icon" id="account-icon">
						<?php if (isUserLoggedIn()) { ?>
							<span><?php echo htmlspecialchars($_SESSION['connectedUser']['nom_utilisateur']); ?></span>
							<div class="account-dropdown" id="account-dropdown">
								<ul>
									<li><a href="../profile/profile.php">Mon profil</a></li>
									<li><a href="../config/logout.php">Se Déconnecter</a></li>
								</ul>
							</div>
						<?php } else { ?>
							<a href="../auth/login.php">Se connecter</a>
						<?php } ?>
					</div>

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
		<div class="container">
			<aside class="filter-wrapper">
				<!-- Remplacé le form par une div pour le filtrage dynamique -->
				<div id="filter-form">
					<input type="hidden" name="search" id="hidden-search" value="<?php echo htmlspecialchars($search ?? ''); ?>">

					<!-- Filtre Catégorie -->
					<div class="filter-group">
						<div class="filter-header" onclick="toggleFilter('categorie-content')">
							<span>Catégorie</span>
							<span class="toggle-icon">+</span>
						</div>
						<div id="categorie-content" class="filter-content">
							<select name="categorie" id="categorie">
								<?php $categories = getCategories(); ?>
								<option value="">Toutes</option>
								<?php foreach ($categories as $categorie): ?>
									<option value="<?php echo htmlspecialchars($categorie['id']); ?>" <?php echo $categorie_filter == $categorie['id'] ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($categorie['nom']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
					</div>

					<!-- Filtre Taille -->
					<div class="filter-group">
						<div class="filter-header" onclick="toggleFilter('taille-content')">
							<span>Taille</span>
							<span class="toggle-icon">+</span>
						</div>
						<div id="taille-content" class="filter-content">
							<?php $tailles = getTailles(); ?>
							<?php foreach ($tailles as $taille): ?>
								<label class="checkbox-container">
									<input type="checkbox" name="tailles[]" value="<?php echo $taille['id']; ?>" <?php echo in_array($taille['id'], $tailles_filter ?? []) ? 'checked' : ''; ?>>
									<span class="checkmark"></span>
									<span><?php echo htmlspecialchars($taille['valeur']); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Filtre Couleur -->
					<div class="filter-group">
						<div class="filter-header" onclick="toggleFilter('couleur-content')">
							<span>Couleur</span>
							<span class="toggle-icon">+</span>
						</div>
						<div id="couleur-content" class="filter-content">
							<?php $couleurs = getCouleurs(); ?>
							<?php foreach ($couleurs as $couleur): ?>
								<label class="checkbox-container color-checkbox">
									<input type="checkbox" name="couleurs[]" value="<?php echo $couleur['id']; ?>" <?php echo in_array($couleur['id'], $couleurs_filter ?? []) ? 'checked' : ''; ?>>
									<span class="checkmark" style="background-color: <?php echo htmlspecialchars($couleur['nom']); ?>"></span>
									<span><?php echo htmlspecialchars($couleur['nom']); ?></span>
								</label>
							<?php endforeach; ?>
						</div>
					</div>

					<!-- Filtres Prix -->
					<div class="filter-group">
						<div class="filter-header" onclick="toggleFilter('prix-content')">
							<span>Prix</span>
							<span class="toggle-icon">+</span>
						</div>
						<div id="prix-content" class="filter-content">
							<div class="price-inputs">
								<input type="number" name="price_min" id="price_min" value="<?php echo htmlspecialchars($price_min); ?>" placeholder="Min €">
								<span>-</span>
								<input type="number" name="price_max" id="price_max" value="<?php echo htmlspecialchars($price_max); ?>" placeholder="Max €">
							</div>
						</div>
					</div>
				</div>
			</aside>

			<div class="product-section">
				<h1>Tous les Produits</h1>
				<section id="products">
					<div class="product-grid">
						<?php foreach ($produits as $produit): ?>
							<div class="product <?php echo $produit['statut'] == 'en_rupture' ? 'out-of-stock' : ''; ?>">
								<?php if ($produit['statut'] == 'en_rupture'): ?>
									<div class="out-of-stock-label">En rupture de stock</div>
								<?php endif; ?>
								<a href="produit.php?id=<?php echo $produit['id']; ?>">
									<img src="<?php echo "../" . $produit['image_url']; ?>"
										alt="<?php echo htmlspecialchars($produit['nom']); ?>"
										class="product-image"
										data-hover="<?php echo "../" . $produit['image_hover_url']; ?>">
								</a>
								<div class="product-info">
									<p><?php echo htmlspecialchars($produit['nom']); ?></p>
									<p><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>
								</div>
							</div>
						<?php endforeach; ?>
					</div>
				</section>
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
				<a href="https://facebook.com" target="_blank"><img src="../images/facebook-icon.png" alt="Facebook"></a>
				<a href="https://twitter.com" target="_blank"><img src="../images/x-icon.png" alt="x"></a>
			</div>
		</div>
		<p>&copy; 2024 Nike Basketball. Tous droits réservés.</p>
	</footer>

	<script src="../js/script.js"></script>
	<script src="../js/panier.js"></script>
	<script src="../js/produits.js"></script>
	<script src="../js/dynamic-filters.js"></script>

	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const filterWrapper = document.querySelector('.filter-wrapper');
			const productSection = document.querySelector('.product-section');
			const searchInput = document.getElementById('search-input');
			const hiddenSearch = document.getElementById('hidden-search');

			function toggleFilter(contentId) {
				const content = document.getElementById(contentId);
				const header = content.previousElementSibling;
				const icon = header.querySelector('.toggle-icon');

				if (!content.classList.contains('active')) {
					content.style.display = content.id === 'taille-content' ? 'grid' : 'block';
					requestAnimationFrame(() => {
						content.classList.add('active');
						if (icon) icon.textContent = '-';
					});
				} else {
					content.classList.remove('active');
					if (icon) icon.textContent = '+';
					content.addEventListener('transitionend', function handler() {
						if (!content.classList.contains('active')) {
							content.style.display = 'none';
						}
						content.removeEventListener('transitionend', handler);
					});
				}
			}

			function initializeFilters() {
				const isFilterActive = sessionStorage.getItem('filterActive') === 'true';

				requestAnimationFrame(() => {
					if (isFilterActive || window.location.search) {
						filterWrapper.classList.add('active');
						productSection.classList.add('shifted');
					} else {
						setTimeout(() => {
							filterWrapper.classList.add('active');
							productSection.classList.add('shifted');
							sessionStorage.setItem('filterActive', 'true');
						}, 50);
					}
				});
			}

			function initializeActiveFilters() {
				const filterTypes = ['categorie', 'taille', 'couleur', 'prix'];

				filterTypes.forEach(filterType => {
					const content = document.getElementById(`${filterType}-content`);
					if (!content) return;

					const hasActiveInputs = [...content.querySelectorAll('input:checked, select option:checked')]
						.some(input => input.value !== '');

					if (hasActiveInputs) {
						content.classList.add('active');
						content.style.display = filterType === 'taille' ? 'grid' : 'block';
						const icon = content.previousElementSibling.querySelector('.toggle-icon');
						if (icon) icon.textContent = '-';
					}
				});
			}

			if (searchInput && hiddenSearch) {
				searchInput.addEventListener('input', (e) => {
					hiddenSearch.value = e.target.value;
				});
			}

			initializeFilters();
			initializeActiveFilters();

			window.toggleFilter = toggleFilter;
		});
	</script>
</body>

</html>