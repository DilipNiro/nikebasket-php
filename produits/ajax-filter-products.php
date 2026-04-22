<?php
require_once("managementProduits.php");
require_once("../stock/listeStock.php");
require_once("listeProduits.php");

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$price_min = isset($_GET['price_min']) ? $_GET['price_min'] : '';
$price_max = isset($_GET['price_max']) ? $_GET['price_max'] : '';
$categorie_filter = isset($_GET['categorie']) ? $_GET['categorie'] : '';
$tailles_filter = isset($_GET['tailles']) ? array_map('intval', $_GET['tailles']) : [];
$couleurs_filter = isset($_GET['couleurs']) ? array_map('intval', $_GET['couleurs']) : [];

$produits = getProducts2(
	$categorie_filter,
	$price_min,
	$price_max,
	$search,
	$tailles_filter,
	$couleurs_filter
);

foreach ($produits as $produit):
?>
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