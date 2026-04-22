<!DOCTYPE html>
<html lang="fr">
<!-- Déclaration du type de document HTML5 et définition de la langue du document -->
<?php
require_once("produits/listeProduits.php");
require_once("auth/functionLogin.php");
require_once("panier/ajouterProduit.php");
?>
<?php
// Initialize $user_id
$user_id = null;

if (isUserLoggedIn() && isset($_SESSION['connectedUser']['id'])) {
    $user_id = $_SESSION['connectedUser']['id'];
    $quantitePanier = quantityPanier($user_id);
} else {
    $quantitePanier = 0; // Valeur par défaut
}
?>

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nike Basketball</title>
    <link rel="stylesheet" href="css/styles.css">
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
        unset($_SESSION['message']); // Supprime le message après affichage
    }
    ?>
    <header>
        <nav>
            <div class="nav-container">

                <!-- Logo placé à gauche dans la barre de navigation -->
                <div class="logo">
                    <!-- Affichage de l'image du logo avec un texte alternatif si l'image ne se charge pas -->
                    <img src="images/logo.png" alt="Logo Nike" class="logo-img">
                </div>

                <!-- Menu de navigation avec des liens vers différentes sections de la page -->
                <ul class="nav-links">
                    <li><a href="index.php">Accueil</a></li>
                    <li><a href="#new-arrivals">Nouveautés</a></li>
                    <li><a href="produits/produits.php">Produits</a></li>
                </ul>

                <div class="account-cart">
                    <!-- Panier à droite de la barre de navigation avec une icône et le nombre d'articles -->
                    <div class="cart">
                        <img src="images/cart-icon.png" alt="Panier" class="cart-icon">
                        <?php if (isUserLoggedIn() && isset($_SESSION['connectedUser']['id'])):
                            $user_id = $_SESSION['connectedUser']['id'];
                            $quantitePanier = quantityPanier($user_id);
                        ?>
                            <span id="cart-count"><?php echo $quantitePanier ?></span>
                        <?php endif; ?>
                    </div>

                    <div class="account">
                        <img src="images/account-icon.webp" alt="Compte" class="account-icon" id="account-icon">
                        <?php if (isUserLoggedIn()) { ?>
                            <span><?php echo htmlspecialchars($_SESSION['connectedUser']['nom_utilisateur']); ?></span>
                            <div class="account-dropdown" id="account-dropdown">
                                <ul>
                                    <li><a href="profile/profile.php">Mon profil</a></li>
                                    <li><a href="config/logout.php">Se Déconnecter</a></li>
                                </ul>
                            </div>
                        <?php } else { ?>
                            <a href="auth/login.php">Se connecter</a>
                        <?php } ?>
                    </div>

                    <div id="cart-modal" class="cart-popup">
                        <div class="cart-popup-content">
                            <span class="close-popup"> &times;</span>
                            <h3>Votre Panier</h3>

                            <div id="cart-items">
                                <?php
                                $panierData = contenuPanier($user_id);
                                $contentPanier = $panierData['contenu'];
                                $total = $panierData['total'];

                                if ($contentPanier): ?>
                                    <?php foreach ($contentPanier as $item): ?>
                                        <div class="cart-item">
                                            <img src="<?php echo $item['image_url']; ?>"
                                                alt="<?php echo htmlspecialchars($item['nom']); ?>"
                                                class="cart-item-image">
                                            <div class="cart-item-details">
                                                <p><?php echo htmlspecialchars($item['nom']); ?></p>
                                                <p>Couleur : <?php echo htmlspecialchars($item['couleur']); ?></p>
                                                <p>Taille : <?php echo htmlspecialchars($item['taille']); ?></p>
                                                <p class="cart-item-price"><?php echo number_format($item['prix'], 2, '.', ' '); ?> €</p>
                                                <p>Quantité : <?php echo $item['nombre_de_produits']; ?></p>
                                            </div>
                                            <form action="panier/supprimerProduit.php" method="POST" class="delete-form">
                                                <input type="hidden" name="produit_id" value="<?php echo $item['id']; ?>">
                                                <input type="hidden" name="couleur_id" value="<?php echo $item['couleur_id']; ?>">
                                                <input type="hidden" name="taille_id" value="<?php echo $item['taille_id']; ?>">
                                                <button type="submit" name="delete">Supprimer</button>
                                            </form>
                                        </div>
                                    <?php endforeach; ?>

                                    <p>Total : <?php echo $total; ?> €</p>

                                    <div class="cart-buttons">
                                        <a href="paiement/checkout.php" class="checkout-button">Passer à la caisse</a>
                                        <form action="panier/viderPanier.php" method="POST">
                                            <button type="submit" name="viderPanier">Vider le panier</button>
                                        </form>
                                    </div>
                                <?php else: ?>
                                    <p>Votre panier est vide</p>
                                <?php endif; ?>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </nav>
    </header>


    <section id="home">
        <div class="slider">
            <div class="slide active">
                <img src="images/basket1.jpg" alt="Nike Basket 1">
                <div class="caption">Nike Air Zoom BB NXT</div>
            </div>
            <div class="slide">
                <img src="images/basket2.jpg" alt="Nike Basket 2">
                <div class="caption">Nike LeBron 18</div>
            </div>
            <div class="slide">
                <img src="images/basket3.jpg" alt="Nike Basket 3">
                <div class="caption">Nike KD 14</div>
            </div>
            <button class="prev">❮</button>
            <button class="next">❯</button>
        </div>
    </section>

    <section id="new-arrivals">
        <h2>Nouveautés</h2>
        <div class="product-grid">
            <?php
            $newproduits = getNouveauxProduits();
            foreach ($newproduits as $produit): ?>
                <div class="product <?php echo $produit['statut'] == 'en_rupture' ? 'out-of-stock' : ''; ?>">
                    <?php if ($produit['statut'] == 'en_rupture'): ?>
                        <div class="out-of-stock-label">En rupture de stock</div>
                    <?php endif; ?>
                    <a href="produits/produit.php?id=<?php echo $produit['id']; ?>">
                        <img src="<?php echo $produit['image_url']; ?>"
                            alt="<?php echo htmlspecialchars($produit['nom']); ?>"
                            class="product-image"
                            data-hover="<?php echo $produit['image_hover_url']; ?>">
                    </a>
                    <div class="product-info">
                        <p><?php echo htmlspecialchars($produit['nom']); ?></p>
                        <p><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <section id="products">
        <h2>Tous les Produits</h2>
        <div class="product-grid">
            <?php
            $produits = getProduitsActive();
            foreach ($produits as $produit): ?>
                <div class="product <?php echo $produit['statut'] == 'en_rupture' ? 'out-of-stock' : ''; ?>">
                    <?php if ($produit['statut'] == 'en_rupture'): ?>
                        <div class="out-of-stock-label">En rupture de stock</div>
                    <?php endif; ?>
                    <a href="produits/produit.php?id=<?php echo $produit['id']; ?>">
                        <img src="<?php echo $produit['image_url']; ?>"
                            alt="<?php echo htmlspecialchars($produit['nom']); ?>"
                            class="product-image"
                            data-hover="<?php echo $produit['image_hover_url']; ?>">
                    </a>
                    <div class="product-info">
                        <p><?php echo htmlspecialchars($produit['nom']); ?></p>
                        <p><?php echo number_format($produit['prix'], 2, ',', ' '); ?> €</p>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </section>

    <footer>
        <div class="footer-container">
            <div class="footer-links">
                <a href="../index.php">Accueil</a>
                <a href="#new-arrivals">Nouveautés</a>
                <a href="#contact">Contact</a>
            </div>
            <div class="footer-social">
                <a href="https://facebook.com" target="_blank"><img src="images/facebook-icon.png" alt="Facebook"></a>
                <a href="https://twitter.com" target="_blank"><img src="images/x-icon.png" alt="x"></a>
            </div>
        </div>
        <p>&copy; 2024 Nike Basketball. Tous droits réservés.</p>
    </footer>
    <script src="js/script.js"></script>
    <script src="js/panier.js"></script>
</body>
</html>