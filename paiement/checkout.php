<?php
// checkout.php
require_once("../panier/ajouterProduit.php");
require_once("../config/dbconnect.php");

// Vérifier si l'utilisateur est connecté
if (!isset($_SESSION['connectedUser'])) {
    header("Location: ../auth/login.php");
    exit;
}

$user_id = $_SESSION['connectedUser']['id'];
// Récupérer le contenu du panier
$panierData = contenuPanier($user_id);
$contentPanier = $panierData['contenu'];
$sous_total = $panierData['total'];

// Vérifier si le panier est vide
if (empty($contentPanier)) {
    $_SESSION['message'] = "Votre panier est vide.";
    header("Location: ../index.php");
    exit;
}

// Vérifier la disponibilité des stocks
$conn = connectsDB();
foreach ($contentPanier as $item) {
    $sqlStockCheck = "SELECT s.quantite 
        FROM stock s 
        WHERE s.produit_id = :produit_id 
        AND s.taille_id = :taille_id 
        AND s.couleur_id = :couleur_id";

    $stmtStockCheck = $conn->prepare($sqlStockCheck);
    $stmtStockCheck->execute([
        ':produit_id' => $item['id'],
        ':taille_id' => $item['taille_id'],
        ':couleur_id' => $item['couleur_id']
    ]);

    $stock = $stmtStockCheck->fetch(PDO::FETCH_ASSOC);

    if ($stock['quantite'] < $item['nombre_de_produits']) {
        $_SESSION['message'] = 'Stock insuffisant pour le produit: ' . $item['nom'];
        header("Location: ../index.php");
        exit;
    }
}

// Stocker les informations de commande en session
$_SESSION['pending_order'] = [
    'items' => $contentPanier,
    'sous_total' => $sous_total
];
?>

<!DOCTYPE html>
<html lang="fr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Finalisation de votre commande</title>
    <link rel="stylesheet" href="../css/checkout.css">
    <script src="https://js.stripe.com/v3/"></script>
</head>

<body>
    <div class="checkout-container">
        <a href="../index.php" class="back-button">← Retour au panier</a>

        <h1>Finalisation de votre commande</h1>

        <div class="checkout-grid">
            <!-- Récapitulatif de commande -->
            <div class="order-summary">
                <h2>Récapitulatif de la commande</h2>

                <div class="order-products">
                    <h2>Produits commandés</h2>
                    <?php foreach ($contentPanier as $produit): ?>
                        <div class="product-item">
                            <img src="<?php echo "../" . htmlspecialchars($produit['image_url']); ?>"
                                alt="<?php echo htmlspecialchars($produit['nom']); ?>"
                                class="product-image">
                            <div class="product-details">
                                <h4><?php echo htmlspecialchars($produit['nom']); ?></h4>
                                <p>Taille: <?php echo htmlspecialchars($produit['taille']); ?></p>
                                <p>Couleur: <?php echo htmlspecialchars($produit['couleur']); ?></p>
                                <p>Quantité: <?php echo $produit['nombre_de_produits']; ?></p>
                                <p class="price"><?php echo number_format($produit['prix'], 2); ?> €</p>
                            </div>
                        </div>
                    <?php endforeach; ?>

                    <div class="cost-summary">
                        <div class="cost-line total">
                            <span>Total</span>
                            <span id="total-amount"><?php echo number_format($sous_total, 2); ?> €</span>
                        </div>
                    </div>
                </div>

                <!-- Formulaire de paiement -->
                <form id="payment-form">
                    <div id="card-element" class="card-element"></div>
                    <div id="card-errors" class="card-errors"></div>
                    <button id="submit-button" type="submit">
                        Payer <span id="payment-amount"><?php echo number_format($sous_total, 2); ?> €</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Initialisation de Stripe et gestion du paiement
        const stripe = Stripe('pk_test_51QHmTUP2n2YjjvGkOkb9yN0nSDUpzV51UsHKeesrabKmkBJJvhv8GSiALgYnmD2HWvAA7rcqIvgv31wpC75Adxoa00JI9kIxc7');
        const elements = stripe.elements();
        const card = elements.create('card', {
            style: {
                base: {
                    fontSize: '16px',
                    color: '#32325d',
                }
            }
        });
        card.mount('#card-element');

        card.addEventListener('change', function(event) {
            const displayError = document.getElementById('card-errors');
            if (event.error) {
                displayError.textContent = event.error.message;
            } else {
                displayError.textContent = '';
            }
        });

        // Gestion du formulaire de paiement
        document.getElementById('payment-form').addEventListener('submit', async function(event) {
            event.preventDefault();

            // Désactiver le bouton et afficher le chargement
            const submitButton = document.getElementById('submit-button');
            submitButton.disabled = true;
            submitButton.textContent = 'Traitement en cours...';

            try {
                // Récupérer le montant total
                const amount = parseFloat(document.getElementById('total-amount').textContent) * 100;

                // Créer l'intention de paiement
                const response = await fetch('create-payment-intent.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        amount: amount
                    })
                });

                if (!response.ok) {
                    throw new Error('Erreur réseau');
                }

                const data = await response.json();

                if (data.error) {
                    throw new Error(data.error);
                }

                // Confirmer le paiement avec Stripe
                const {
                    paymentIntent,
                    error
                } = await stripe.confirmCardPayment(
                    data.clientSecret, {
                        payment_method: {
                            card: card
                        }
                    }
                );

                if (error) {
                    throw error;
                }

                if (paymentIntent.status === 'succeeded') {
                    // Redirection vers la page de confirmation
                    window.location.href = 'process-order.php';
                }

            } catch (error) {
                console.error('Erreur:', error);
                const errorElement = document.getElementById('card-errors');
                errorElement.textContent = error.message;

                // Réactiver le bouton en cas d'erreur
                submitButton.disabled = false;
                submitButton.textContent = 'Payer';
            }
        });
    </script>
</body>
</html>