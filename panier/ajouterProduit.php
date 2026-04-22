<?php
// Inclure la connexion à la base de données
require_once(__DIR__ . '/../config/dbconnect.php');
require_once(__DIR__ . '/../produits/listeProduits.php'); // Ajout de cette ligne car getPrixPromotion() est dedans



function ajoutProduitPanier($data)
{
	if (!isset($_SESSION['connectedUser'])) {
		// Redirection vers la page de connexion si l'utilisateur n'est pas connecté
		header('Location: ../auth/login.php');
		exit;
	}

	$conn = connectsDB();

	// Récupérer l'ID de l'utilisateur connecté
	if (isset($data)) {
		// Récupérer l'ID du produit, la quantité, la couleur et la taille
		$produit_id = intval($data['produit_id']);
		$quantite = isset($data['quantite']) ? intval($data['quantite']) : 1;
		$user_id = $_SESSION['connectedUser']['id'];
		$prix = floatval($data['prix']); // Récupérer le prix unitaire du produit
		$couleur = htmlspecialchars($data['couleur']); // Couleur choisie
		$taille = htmlspecialchars($data['taille']); // Taille choisie

		// Vérifier si la couleur existe dans la table couleur
		$sqlCheckCouleur = "SELECT id FROM couleur WHERE id = :couleur"; // Utiliser `nom` pour la couleur
		$stmtCheckCouleur = $conn->prepare($sqlCheckCouleur);
		$stmtCheckCouleur->bindParam(':couleur', $couleur);
		$stmtCheckCouleur->execute();
		$resultCouleur = $stmtCheckCouleur->fetch(PDO::FETCH_ASSOC);

		// Vérifier si la taille existe dans la table taille
		$sqlCheckTaille = "SELECT id FROM taille WHERE id = :taille"; // Utiliser `valeur` pour la taille
		$stmtCheckTaille = $conn->prepare($sqlCheckTaille);
		$stmtCheckTaille->bindParam(':taille', $taille);
		$stmtCheckTaille->execute();
		$resultTaille = $stmtCheckTaille->fetch(PDO::FETCH_ASSOC);

		// Vérifier que les couleurs et tailles existent
		if (!$resultCouleur || !$resultTaille) {
			echo "<div class='error-message'>La couleur ou la taille sélectionnée n'est pas valide.</div>";
			return; // Sortir si l'une des deux n'est pas valide
		}

		// Récupérer les IDs de la couleur et de la taille
		$couleur_id = $resultCouleur['id'];
		$taille_id = $resultTaille['id'];

		// Vérifier si le produit est déjà dans le panier pour cet utilisateur
		$sqlCheck = "SELECT * FROM panier WHERE user_id = :user_id AND produit_id = :produit_id AND couleur_id = :couleur_id AND taille_id = :taille_id";
		$stmtCheck = $conn->prepare($sqlCheck);
		$stmtCheck->bindParam(':user_id', $user_id);
		$stmtCheck->bindParam(':produit_id', $produit_id);
		$stmtCheck->bindParam(':couleur_id', $couleur_id);
		$stmtCheck->bindParam(':taille_id', $taille_id);
		$stmtCheck->execute();
		$resultCheck = $stmtCheck->fetch(PDO::FETCH_ASSOC);

		if ($resultCheck) {
			// Si le produit existe déjà dans le panier avec la même couleur et taille, mettre à jour la quantité
			$sqlUpdate = "UPDATE panier SET quantite = quantite + :quantite WHERE user_id = :user_id AND produit_id = :produit_id AND couleur_id = :couleur_id AND taille_id = :taille_id";
			$stmtUpdate = $conn->prepare($sqlUpdate);
			$stmtUpdate->bindParam(':quantite', $quantite);
			$stmtUpdate->bindParam(':user_id', $user_id);
			$stmtUpdate->bindParam(':produit_id', $produit_id);
			$stmtUpdate->bindParam(':couleur_id', $couleur_id);
			$stmtUpdate->bindParam(':taille_id', $taille_id);
			$stmtUpdate->execute();
		} else {
			// Si le produit n'est pas dans le panier, on l'ajoute avec le prix
			$sqlInsert = "INSERT INTO panier (user_id, produit_id, quantite, prix, couleur_id, taille_id) VALUES (:user_id, :produit_id, :quantite, :prix, :couleur_id, :taille_id)";
			$stmtInsert = $conn->prepare($sqlInsert);
			$stmtInsert->bindParam(':user_id', $user_id);
			$stmtInsert->bindParam(':produit_id', $produit_id);
			$stmtInsert->bindParam(':quantite', $quantite);
			$stmtInsert->bindParam(':prix', $prix);
			$stmtInsert->bindParam(':couleur_id', $couleur_id);
			$stmtInsert->bindParam(':taille_id', $taille_id);
			$stmtInsert->execute();
		}

		// Redirection vers la page de produits ou autre
		header("Location: " . $_SERVER['HTTP_REFERER']); // This will redirect to the referring page
		exit;
	} else {
		// Si le produit_id est manquant
		echo "Les informations du produit sont manquantes.";
		exit;
	}
}



// tous les produits dans le panier 
// Dans ajouterProduit.php
function contenuPanier($user_id)
{
    $conn = connectsDB();

    $sqlCart = "
        SELECT 
            pt.id,
            pt.nom, 
            pt.image_url, 
            pt.prix,
            pn.prix as prix_panier,
            pn.quantite AS nombre_de_produits,
            pn.couleur_id,          
            pn.taille_id,           
            c.nom AS couleur,       
            t.valeur AS taille      
        FROM 
            panier pn 
        JOIN 
            produits pt ON pn.produit_id = pt.id 
        LEFT JOIN 
            couleur c ON pn.couleur_id = c.id    
        LEFT JOIN 
            taille t ON pn.taille_id = t.id      
        WHERE 
            pn.user_id = :user_id
    ";

    $stmtCart = $conn->prepare($sqlCart);
    $stmtCart->bindParam(':user_id', $user_id);
    $stmtCart->execute();
    $contentPanier = $stmtCart->fetchAll(PDO::FETCH_ASSOC);

    // Calculer le total
    $total = 0;
    foreach ($contentPanier as &$item) {
        $item['prix'] = $item['prix'];
        $total += $item['prix'] * $item['nombre_de_produits'];
    }

    return [
        'contenu' => $contentPanier,
        'total' => number_format($total, 2)
    ];
}

// nombre de produits dans le panier
function quantityPanier($user_id)
{

	$conn = connectsDB();

	// Récupérer les produits dans le panier pour cet utilisateur
	$sqlCart = "SELECT SUM(quantite) AS total_quantite FROM  panier  WHERE user_id = :user_id";
	$stmtCart = $conn->prepare($sqlCart);
	$stmtCart->execute(['user_id' => $user_id]);
	$contentPanier = $stmtCart->fetch(PDO::FETCH_ASSOC);
	return $contentPanier['total_quantite'] ? $contentPanier['total_quantite'] : 0;
}


//SELECT SUM(pn.quantite) AS total_quantite, pt.nom, pt.image_url, pt.prix FROM produits pt JOIN panier pn WHERE pn.user_id = 14 GROUP BY pt.id;

/*SELECT 
    SUM(pn.quantite) AS total_quantite, 
    pt.nom, 
    pt.image_url, 
    pt.prix 
FROM 
    panier pn 
JOIN 
    produits pt ON pn.produit_id = pt.id 
WHERE 
    pn.user_id = 14 
GROUP BY 
    pn.produit_id, pt.nom, pt.image_url, pt.prix;*/
