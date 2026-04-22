<?php
// process-order.php - Version optimisée pour la production
require_once(__DIR__ . '/../config/dbconnect.php');
require_once(__DIR__ . '/../panier/ajouterProduit.php');

// Vérifier la session et les données nécessaires
if (
    !isset($_SESSION['connectedUser']) || !isset($_SESSION['pending_order']) ||
    !isset($_SESSION['payment_intent'])
) {
    $_SESSION['message'] = "Une erreur est survenue lors du traitement de votre commande.";
    header("Location: ../index.php");
    exit;
}

$conn = connectsDB();
$user_id = $_SESSION['connectedUser']['id'];
$orderData = $_SESSION['pending_order'];
$paymentData = $_SESSION['payment_intent'];

// Vérifier si les transactions sont supportées
$transaction_supported = false;
try {
    if ($conn->getAttribute(PDO::ATTR_DRIVER_NAME) === 'mysql') {
        $result = $conn->query("SHOW VARIABLES LIKE 'have_innodb'");
        $row = $result->fetch(PDO::FETCH_ASSOC);
        
        if ($row && ($row['Value'] === 'YES' || $row['Value'] === 'ON')) {
            $transaction_supported = true;
        }
    }
} catch (Exception $e) {
    // Silence cette erreur, continuer sans transaction
}

// Commencer une transaction si supportée
if ($transaction_supported) {
    try {
        $conn->beginTransaction();
    } catch (Exception $e) {
        $transaction_supported = false;
    }
}

try {
    // 1. Créer la commande
    $sqlOrder = "INSERT INTO commande (user_id, montant_total, statut) 
                 VALUES (:user_id, :montant_total, :statut)";

    $stmtOrder = $conn->prepare($sqlOrder);
    $stmtOrder->execute([
        ':user_id' => $user_id,
        ':montant_total' => $orderData['sous_total'],
        ':statut' => 'payee'
    ]);

    $orderId = $conn->lastInsertId();

    // 2. Ajouter les produits à la commande et mettre à jour les stocks
    foreach ($orderData['items'] as $item) {
        // Ajouter à commande_produits
        $sqlOrderProducts = "INSERT INTO commande_produits (commande_id, produit_id, taille_id, couleur_id, quantite, prix_unitaire) 
                           VALUES (:commande_id, :produit_id, :taille_id, :couleur_id, :quantite, :prix_unitaire)";

        $stmtOrderProducts = $conn->prepare($sqlOrderProducts);
        $stmtOrderProducts->execute([
            ':commande_id' => $orderId,
            ':produit_id' => $item['id'],
            ':taille_id' => $item['taille_id'],
            ':couleur_id' => $item['couleur_id'],
            ':quantite' => $item['nombre_de_produits'],
            ':prix_unitaire' => $item['prix']
        ]);

        // Mise à jour du stock spécifique
        $sqlUpdateStock = "UPDATE stock 
                         SET quantite = GREATEST(0, quantite - :quantite) 
                         WHERE produit_id = :produit_id 
                         AND taille_id = :taille_id 
                         AND couleur_id = :couleur_id";

        $stmtUpdateStock = $conn->prepare($sqlUpdateStock);
        $stmtUpdateStock->execute([
            ':quantite' => $item['nombre_de_produits'],
            ':produit_id' => $item['id'],
            ':taille_id' => $item['taille_id'],
            ':couleur_id' => $item['couleur_id']
        ]);
        
        // Calculer la somme des stocks pour ce produit
        $sqlSumStock = "SELECT SUM(quantite) FROM stock WHERE produit_id = :produit_id";
        $stmtSumStock = $conn->prepare($sqlSumStock);
        $stmtSumStock->execute([':produit_id' => $item['id']]);
        $totalStock = $stmtSumStock->fetchColumn();
        
        // Mettre à jour le produit
        $sqlUpdateProduct = "UPDATE produits 
                           SET quantite = :total_stock,
                               statut = CASE 
                                   WHEN :total_stock <= 0 THEN 'en_rupture'
                                   ELSE 'actif' 
                               END
                           WHERE id = :produit_id";

        $stmtUpdateProduct = $conn->prepare($sqlUpdateProduct);
        $stmtUpdateProduct->execute([
            ':produit_id' => $item['id'],
            ':total_stock' => $totalStock
        ]);
    }

    // 3. Enregistrer le paiement
    $sqlPayment = "INSERT INTO paiement (user_id, commande_id, mode_paiement, montant, statut_paiement, transaction_id) 
                   VALUES (:user_id, :commande_id, 'carte', :montant, 'completed', :transaction_id)";

    $stmtPayment = $conn->prepare($sqlPayment);
    $stmtPayment->execute([
        ':user_id' => $user_id,
        ':commande_id' => $orderId,
        ':montant' => $paymentData['amount'] / 100,
        ':transaction_id' => $paymentData['id']
    ]);

    // 4. Vider le panier
    $sqlClearCart = "DELETE FROM panier WHERE user_id = :user_id";
    $stmtClearCart = $conn->prepare($sqlClearCart);
    $stmtClearCart->execute([':user_id' => $user_id]);

    // Si transactions supportées, commit
    if ($transaction_supported) {
        $conn->commit();
    }

    // 5. Nettoyer les données de session
    unset($_SESSION['pending_order']);
    unset($_SESSION['payment_intent']);

    // Rediriger vers la page de confirmation
    $_SESSION['message'] = "Votre commande a été traitée avec succès !";
    header("Location: ../confirmation.php?order=" . $orderId);
    exit;
} catch (Exception $e) {
    // Rollback si transactions supportées
    if ($transaction_supported) {
        try {
            $conn->rollBack();
        } catch (Exception $rollbackError) {
            // Silence cette erreur
        }
    }
    
    error_log('Erreur lors du traitement de la commande: ' . $e->getMessage());
    
    $_SESSION['message'] = "Une erreur est survenue lors du traitement de votre commande.";
    header("Location: ../index.php");
    exit;
}