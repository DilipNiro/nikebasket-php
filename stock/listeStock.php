<?php
require_once("../config/dbconnect.php");
require_once("../produits/listeProduits.php");
// Connexion à la base de données



// Fonction pour récupérer les tailles
function getTailles()
{
	$conn = connectsDB();
	$stmt = $conn->prepare("SELECT id, valeur FROM taille");
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour récupérer les couleurs
function getCouleurs()
{
	$conn = connectsDB();
	$stmt = $conn->prepare("SELECT id, nom FROM couleur");
	$stmt->execute();
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Fonction pour ajouter du stock
function ajouterStock($produit_id, $taille_id, $couleur_id, $quantite)
{
	$conn = connectsDB();

	// Vérification du stock existant pour le produit, taille et couleur
	$stmt_check = $conn->prepare("SELECT quantite FROM stock WHERE produit_id = :produit_id AND taille_id = :taille_id AND couleur_id = :couleur_id");
	$stmt_check->execute(['produit_id' => $produit_id, 'taille_id' => $taille_id, 'couleur_id' => $couleur_id]);
	$result_check = $stmt_check->fetch(PDO::FETCH_ASSOC);

	if ($result_check) {
		// Mise à jour du stock existant
		$nouvelle_quantite = $result_check['quantite'] + $quantite;
		$stmt_update = $conn->prepare("UPDATE stock SET quantite = :nouvelle_quantite WHERE produit_id = :produit_id AND taille_id = :taille_id AND couleur_id = :couleur_id");
		$stmt_update->execute(['nouvelle_quantite' => $nouvelle_quantite, 'produit_id' => $produit_id, 'taille_id' => $taille_id, 'couleur_id' => $couleur_id]);
	} else {
		// Insertion d'un nouveau stock
		$stmt_insert = $conn->prepare("INSERT INTO stock (produit_id, taille_id, couleur_id, quantite) VALUES (:produit_id, :taille_id, :couleur_id, :quantite)");
		$stmt_insert->execute(['produit_id' => $produit_id, 'taille_id' => $taille_id, 'couleur_id' => $couleur_id, 'quantite' => $quantite]);
	}

	// Mise à jour de la quantité totale dans la table produits
	$stmt_update_produit = $conn->prepare("UPDATE produits SET quantite = quantite + :quantite WHERE id = :produit_id");
	$stmt_update_produit->execute(['quantite' => $quantite, 'produit_id' => $produit_id]);

	// Vérification du statut actuel du produit
	$stmt_status = $conn->prepare("SELECT statut FROM produits WHERE id = :produit_id");
	$stmt_status->execute(['produit_id' => $produit_id]);
	$produit = $stmt_status->fetch(PDO::FETCH_ASSOC);

	// Si le statut est "en_rupture" et que du stock est ajouté, changer le statut à "actif"
	if ($produit['statut'] == 'en_rupture') {
		$stmt_update_status = $conn->prepare("UPDATE produits SET statut = 'actif' WHERE id = :produit_id");
		$stmt_update_status->execute(['produit_id' => $produit_id]);
	}
}

function getStockByProduit($produit_id)
{
	$conn = connectsDB(); // Établir la connexion à la base de données

	// Récupérer le stock du produit avec ses tailles et couleurs
	$sql_stock = "
        SELECT 
            s.id,              
            s.produit_id,      
            t.valeur AS taille, 
            c.nom AS couleur,   
            s.quantite        
        FROM stock s
        JOIN taille t ON s.taille_id = t.id
        JOIN couleur c ON s.couleur_id = c.id
        WHERE s.produit_id = ?
    ";

	$stmt_stock = $conn->prepare($sql_stock); // Préparer la requête
	$stmt_stock->execute([$produit_id]); // Exécuter la requête avec l'ID du produit
	$stocks = $stmt_stock->fetchAll(PDO::FETCH_ASSOC); // Récupérer tous les résultats

	closesDB($conn); // Fermer la connexion à la base de données
	return $stocks; // Retourner les stocks récupérés
}


function getTotalQuantiteProduit($produit_id)
{
	$conn = connectsDB(); // Établir la connexion à la base de données

	$stmt = $conn->prepare("
        SELECT SUM(quantite) as total_quantite
        FROM stock
        WHERE produit_id = ?
    ");
	$stmt->execute([$produit_id]);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);

	return $result ? (int)$result['total_quantite'] : 0; // Si pas de résultat, retourne 0
}


function getStockById($stock_id)
{
	$conn = connectsDB(); // Établir la connexion à la base de données

	$stmt = $conn->prepare("SELECT s.id, s.produit_id, s.taille_id, s.couleur_id, s.quantite
                             FROM stock s
                             WHERE s.id = ?");
	$stmt->execute([$stock_id]);
	$stock = $stmt->fetch(PDO::FETCH_ASSOC); // Récupérer le stock par ID

	closesDB($conn); // Fermer la connexion à la base de données
	return $stock; // Retourner le stock récupéré
}


function updateStock($stock_id, $produit_id, $taille_id, $couleur_id, $quantite)
{
	$conn = connectsDB();

	try {
		$conn->beginTransaction();

		// 1. Récupérer l'ancienne quantité du stock
		$stmt_old_quantity = $conn->prepare("SELECT quantite FROM stock WHERE id = ?");
		$stmt_old_quantity->execute([$stock_id]);
		$old_stock = $stmt_old_quantity->fetch(PDO::FETCH_ASSOC);
		$old_quantity = $old_stock['quantite'];

		// 2. Mettre à jour le stock
		$stmt = $conn->prepare("UPDATE stock SET quantite = ?, taille_id = ?, couleur_id = ? WHERE id = ?");
		$stmt->execute([$quantite, $taille_id, $couleur_id, $stock_id]);

		// 3. Recalculer le total du stock pour ce produit
		$stmt_total = $conn->prepare("SELECT SUM(quantite) as total FROM stock WHERE produit_id = ?");
		$stmt_total->execute([$produit_id]);
		$total_result = $stmt_total->fetch(PDO::FETCH_ASSOC);
		$new_total = $total_result['total'] ?? 0;

		// 4. Mettre à jour directement la quantité totale dans la table produits
		$stmt_update_produit = $conn->prepare("UPDATE produits SET quantite = ? WHERE id = ?");
		$stmt_update_produit->execute([$new_total, $produit_id]);

		// 5. Mettre à jour le statut si nécessaire
		if ($new_total <= 0) {
			$stmt_status = $conn->prepare("UPDATE produits SET statut = 'en_rupture' WHERE id = ?");
			$stmt_status->execute([$produit_id]);
		} else {
			$stmt_status = $conn->prepare("UPDATE produits SET statut = 'actif' WHERE id = ?");
			$stmt_status->execute([$produit_id]);
		}

		$conn->commit();
		return true;
	} catch (Exception $e) {
		$conn->rollBack();
		return false;
	}
}

function deleteStock($id)
{
	// Connexion à la base de données via PDO
	$conn = connectsDB(); // Assurez-vous que cette fonction retourne une instance PDO

	// Récupérer d'abord les informations du stock que nous allons supprimer
	$stockQuery = "SELECT produit_id, quantite FROM stock WHERE id = :id";
	$stmt = $conn->prepare($stockQuery);
	$stmt->bindParam(':id', $id, PDO::PARAM_INT);
	$stmt->execute();
	$stock = $stmt->fetch(PDO::FETCH_ASSOC);

	if (!$stock) {
		return false; // Stock introuvable
	}

	// Récupérer l'ID du produit et la quantité à soustraire
	$produit_id = $stock['produit_id'];
	$quantiteASoustraire = $stock['quantite'];

	// Supprimer le stock
	$deleteQuery = "DELETE FROM stock WHERE id = :id";
	$deleteStmt = $conn->prepare($deleteQuery);
	$deleteStmt->bindParam(':id', $id, PDO::PARAM_INT);
	$deleteResult = $deleteStmt->execute();

	if ($deleteResult) {
		// Mettre à jour la quantité dans la table produits
		$updateQuery = "UPDATE produits SET quantite = quantite - :quantite WHERE id = :produit_id";
		$updateStmt = $conn->prepare($updateQuery);
		$updateStmt->bindParam(':quantite', $quantiteASoustraire, PDO::PARAM_INT);
		$updateStmt->bindParam(':produit_id', $produit_id, PDO::PARAM_INT);
		$updateStmt->execute();

		return true; // Suppression réussie et quantité mise à jour
	}

	return false; // La suppression a échoué
}


function getStockParCouleurEtTaille($produit_id)
{
	$conn = connectsDB(); // Assurez-vous que cette fonction retourne une instance PDO

	// Requête SQL mise à jour avec les bons noms de colonnes
	$stmt = $conn->prepare("
        SELECT couleur_id, taille_id, quantite 
        FROM stock 
        WHERE produit_id = ?
    ");
	$stmt->execute([$produit_id]);

	$stocks = [];
	while ($row = $stmt->fetch()) {
		$couleur_id = $row['couleur_id'];
		$taille_id = $row['taille_id'];
		$quantite = $row['quantite'];

		if (!isset($stocks[$couleur_id])) {
			$stocks[$couleur_id] = [];
		}

		$stocks[$couleur_id][$taille_id] = $quantite;
	}

	return $stocks;
}


function getTailleByColor($colorId)
{
	// Assuming you have a database connection
	$conn = connectsDB(); // Assurez-vous que cette fonction retourne une instance PDO

	// Prepare the SQL query to get sizes based on the color
	$query = "
        SELECT t.id, t.valeur 
        FROM tailles t
        JOIN stocks s ON s.taille_id = t.id 
        WHERE s.couleur_id = :colorId AND s.quantite > 0
    ";

	// Prepare the statement
	$stmt = $conn->prepare($query);
	$stmt->bindParam(':colorId', $colorId, PDO::PARAM_INT);

	// Execute the query
	$stmt->execute();

	// Fetch the results
	$sizes = $stmt->fetchAll(PDO::FETCH_ASSOC);

	return $sizes;
}
