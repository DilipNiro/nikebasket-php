<?php
require_once(__DIR__ . "/../config/dbconnect.php");

function getProduits()
{
    $conn = connectsDB();

    // Sélectionne uniquement les produits avec le statut 'actif'
    $sql = "SELECT 
    p.id,
    p.nom ,  
    c.nom AS categorie,  
    p.description,
    p.prix,
    p.image_url,
    p.image_hover_url,
    p.date_sortie,
    p.quantite,
    p.statut
    
FROM 
    produits p
LEFT JOIN 
    categorie c ON p.categorie_id = c.id"; // Utilisation d'une jointure pour récupérer le nom de la catégorie

    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $produits;
}

function getProduitsActive()
{
    $conn = connectsDB();
    // Sélectionne uniquement les produits avec le statut 'actif' ou 'en_rupture'
    $sql = "SELECT * FROM produits WHERE statut = 'actif' OR statut = 'en_rupture'";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
    return $produits;
}


function getNouveauxProduits()
{
    $conn = connectsDB();
    // Requête pour récupérer les produits actifs sortis dans le dernier mois
    $sql = "SELECT * FROM produits WHERE (statut = 'actif' OR statut = 'en_rupture') AND date_sortie >= CURDATE() - INTERVAL 1 MONTH";
    $stmt = $conn->prepare($sql);
    $stmt->execute();
    $nouveaux_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);

    if ($nouveaux_produits) {
        return $nouveaux_produits;
    } else {
        // Si aucun produit récent, retourne les produits actifs les plus récents
        $sql = "SELECT * FROM produits WHERE statut = 'actif' ORDER BY date_sortie DESC";
        $stmt = $conn->prepare($sql);
        $stmt->execute();
        $nouveaux_produits = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return $nouveaux_produits;
    }
}



function addProduit($nom, $prix, $description, $quantite, $statut = 'actif')
{
    $conn = connectsDB();

    // Requête SQL pour insérer un nouveau produit
    $sql = "INSERT INTO produits (nom, prix, description, quantite, statut) VALUES (:nom, :prix, :description, :quantite, :statut)";

    // Préparer et exécuter la requête
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':nom', $nom);
    $stmt->bindParam(':prix', $prix);
    $stmt->bindParam(':description', $description);
    $stmt->bindParam(':quantite', $quantite);
    $stmt->bindParam(':statut', $statut);

    $stmt->execute();
}

function supprimerProduit($id)
{
    // Connexion à la base de données
    try {
        $conn = connectsDB(); // Assurez-vous que cette fonction retourne une connexion PDO valide

        // Démarrer une transaction
        $conn->beginTransaction();

        // Suppression des entrées dans la table stock
        $stmtStock = $conn->prepare("DELETE FROM stock WHERE produit_id = :id");
        $stmtStock->bindParam(':id', $id);
        $stmtStock->execute();

        // Suppression des entrées dans la table panier
        $stmtPanier = $conn->prepare("DELETE FROM panier WHERE produit_id = :id");
        $stmtPanier->bindParam(':id', $id);
        $stmtPanier->execute();

        // Suppression du produit
        $stmtProduit = $conn->prepare("DELETE FROM produits WHERE id = :id");
        $stmtProduit->bindParam(':id', $id);

        // Exécution de la requête de suppression
        if ($stmtProduit->execute()) {
            // Si la suppression du produit réussit, valider la transaction
            $conn->commit();
            return true; // Suppression réussie
        } else {
            // Si la suppression du produit échoue, annuler la transaction
            $conn->rollBack();
            echo "Erreur lors de la suppression du produit.";
            return false; // Échec de la suppression
        }
    } catch (PDOException $e) {
        // En cas d'erreur, annuler la transaction
        $conn->rollBack();
        echo "Erreur: " . $e->getMessage();
        return false; // Échec de la suppression
    }
}


function getProduitById($id)
{
    try {
        $conn = connectsDB(); // Assurez-vous que cette fonction retourne une connexion PDO valide
        $stmt = $conn->prepare("
        SELECT produits.*, categorie.nom AS categorie_nom 
        FROM produits 
        JOIN categorie ON produits.categorie_id = categorie.id 
        WHERE produits.id = :id
    ");
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
        return false;
    }
}


function updateProduit($id, $nom, $categorie_id, $description, $prix, $image_url, $image_hover_url, $quantite, $statut)
{
    try {
        $conn = connectsDB();
        // Requête pour mettre à jour toutes les informations du produit, y compris la catégorie
        $stmt = $conn->prepare("UPDATE produits SET nom = :nom, categorie_id = :categorie_id, description = :description,prix = :prix,  image_url = :image_url, image_hover_url = :image_hover_url, quantite = :quantite, statut = :statut WHERE id = :id");

        // Liaison des paramètres
        $stmt->bindParam(':id', $id, PDO::PARAM_INT);
        $stmt->bindParam(':nom', $nom);
        $stmt->bindParam(':categorie_id', $categorie_id, PDO::PARAM_INT); // Lier la catégorie
        $stmt->bindParam(':description', $description);
        $stmt->bindParam(':prix', $prix);
        $stmt->bindParam(':image_url', $image_url);
        $stmt->bindParam(':image_hover_url', $image_hover_url);
        $stmt->bindParam(':quantite', $quantite);
        $stmt->bindParam(':statut', $statut);

        return $stmt->execute();
    } catch (PDOException $e) {
        echo "Erreur: " . $e->getMessage();
        return false;
    }
}




function getProducts($status_filter = '', $categorie_filter = '', $quantity_filter = '', $price_min_filter = '', $price_max_filter = '')
{
    // Connexion à la base de données (assurez-vous d'avoir cette partie)
    $conn = connectsDB();

    $query = "SELECT 
        p.id,
        p.nom,  
        c.nom AS categorie,  
        p.description,
        p.prix,
        p.image_url,
        p.image_hover_url,
        p.date_sortie,
        p.quantite,
        p.statut
    FROM 
        produits p
    LEFT JOIN 
        categorie c ON p.categorie_id = c.id
    WHERE 
        1=1"; // Commencez avec une requête de base

    // Ajout des filtres
    if (!empty($status_filter)) {
        $query .= " AND p.statut = :statut"; // Filtre par statut
    }
    if (!empty($categorie_filter)) {
        $query .= " AND p.categorie_id = :categorie"; // Filtre par catégorie
    }
    if (!empty($quantity_filter)) {
        $query .= " AND p.quantite >= :quantite"; // Filtre par quantité
    }
    if (!empty($price_min_filter)) {
        $query .= " AND p.prix >= :prix_min"; // Filtre par prix minimum
    }
    if (!empty($price_max_filter)) {
        $query .= " AND p.prix <= :prix_max"; // Filtre par prix maximum
    }

    $stmt = $conn->prepare($query); // Préparez la requête

    // Liez les paramètres si applicable
    if (!empty($status_filter)) {
        $stmt->bindParam(':statut', $status_filter);
    }
    if (!empty($categorie_filter)) {
        $stmt->bindParam(':categorie', $categorie_filter, PDO::PARAM_INT); // Liaison pour le filtre de catégorie
    }
    if (!empty($quantity_filter)) {
        $stmt->bindParam(':quantite', $quantity_filter, PDO::PARAM_INT);
    }
    if (!empty($price_min_filter)) {
        $stmt->bindParam(':prix_min', $price_min_filter, PDO::PARAM_INT);
    }
    if (!empty($price_max_filter)) {
        $stmt->bindParam(':prix_max', $price_max_filter, PDO::PARAM_INT);
    }

    $stmt->execute(); // Exécutez la requête
    return $stmt->fetchAll(PDO::FETCH_ASSOC); // Retournez les résultats
}

function getProducts2($categorie_filter = '', $price_min_filter = '', $price_max_filter = '', $search_term = '', $tailles_filter = [], $couleurs_filter = [])
{
    $conn = connectsDB();
    $params = [];

    $query = "SELECT DISTINCT
        p.id, p.nom, c.nom AS categorie,
        p.description, p.prix, p.image_url,
        p.image_hover_url, p.date_sortie,
        p.quantite, p.statut
    FROM produits p
    LEFT JOIN categorie c ON p.categorie_id = c.id
    LEFT JOIN stock s ON p.id = s.produit_id
    WHERE 1=1";

    if (!empty($search_term)) {
        $query .= " AND (p.nom LIKE :search_term OR p.description LIKE :search_term)";
        $params[':search_term'] = "%{$search_term}%";
    }

    if (!empty($categorie_filter)) {
        $query .= " AND p.categorie_id = :categorie";
        $params[':categorie'] = $categorie_filter;
    }

    if (!empty($price_min_filter)) {
        $query .= " AND p.prix >= :prix_min";
        $params[':prix_min'] = $price_min_filter;
    }

    if (!empty($price_max_filter)) {
        $query .= " AND p.prix <= :prix_max";
        $params[':prix_max'] = $price_max_filter;
    }

    if (!empty($tailles_filter)) {
        $placeholders = array_map(function ($i) {
            return ':taille_' . $i;
        }, array_keys($tailles_filter));
        $query .= " AND s.taille_id IN (" . implode(',', $placeholders) . ")";
        foreach ($tailles_filter as $key => $taille) {
            $params[':taille_' . $key] = $taille;
        }
    }

    if (!empty($couleurs_filter)) {
        $placeholders = array_map(function ($i) {
            return ':couleur_' . $i;
        }, array_keys($couleurs_filter));
        $query .= " AND s.couleur_id IN (" . implode(',', $placeholders) . ")";
        foreach ($couleurs_filter as $key => $couleur) {
            $params[':couleur_' . $key] = $couleur;
        }
    }

    $query .= " GROUP BY p.id";
    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
function getProductImages($product_id)
{
    $conn = connectsDB();
    $stmt = $conn->prepare("SELECT image_url, ordre FROM produit_images WHERE produit_id = ? ORDER BY ordre ASC");
    $stmt->execute([$product_id]);
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCategories()
{
    // Connexion à la base de données avec PDO
    try {
        $conn = connectsDB(); // Assurez-vous que cette fonction renvoie une instance PDO valide
        // Définir le mode d'erreur de PDO pour qu'il lance des exceptions
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Préparer la requête pour récupérer les catégories
        $sql = "SELECT id, nom FROM categorie";
        $stmt = $conn->prepare($sql);
        $stmt->execute(); // Exécuter la requête

        // Récupérer toutes les catégories
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        // Gérer les erreurs de connexion
        die("Connection failed: " . $e->getMessage());
    }
}



