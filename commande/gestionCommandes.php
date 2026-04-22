<?php
require_once("../config/dbconnect.php");

function getAllCommandes($filters = [])
{
	$conn = connectsDB();

	$sql = "SELECT c.*, u.nom as nom_client, p.statut_paiement 
            FROM commande c 
            LEFT JOIN user u ON c.user_id = u.id 
            LEFT JOIN paiement p ON c.id = p.commande_id 
            WHERE 1=1";

	$params = [];

	if (!empty($filters['status'])) {
		$status = str_replace(' ', '_', $filters['status']);
		$sql .= " AND c.statut = :status";
		$params[':status'] = $status;
	}

	if (!empty($filters['date_debut'])) {
		$sql .= " AND c.commandee_le >= :date_debut";
		$params[':date_debut'] = $filters['date_debut'] . ' 00:00:00';
	}

	if (!empty($filters['date_fin'])) {
		$sql .= " AND c.commandee_le <= :date_fin";
		$params[':date_fin'] = $filters['date_fin'] . ' 23:59:59';
	}

	$sql .= " ORDER BY c.commandee_le DESC";

	$stmt = $conn->prepare($sql);
	$stmt->execute($params);

	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function getCommandeDetails($commande_id)
{
	$conn = connectsDB();

	// Récupérer les informations générales de la commande sans les détails de livraison
	$stmt_commande = $conn->prepare("
        SELECT 
            c.*,
            u.nom as nom_client,
            u.email as email_client,
            p.statut_paiement,
            p.transaction_id
        FROM commande c
        LEFT JOIN user u ON c.user_id = u.id
        LEFT JOIN paiement p ON c.id = p.commande_id
        WHERE c.id = :commande_id
    ");

	$stmt_commande->execute([':commande_id' => $commande_id]);
	$commande = $stmt_commande->fetch(PDO::FETCH_ASSOC);

	// Récupérer les produits de la commande
	$stmt_produits = $conn->prepare("
        SELECT 
            cp.*,
            p.nom as produit_nom,
            p.image_url,
            t.valeur as taille,
            c.nom as couleur
        FROM commande_produits cp
        JOIN produits p ON cp.produit_id = p.id
        JOIN taille t ON cp.taille_id = t.id
        JOIN couleur c ON cp.couleur_id = c.id
        WHERE cp.commande_id = :commande_id
    ");

	$stmt_produits->execute([':commande_id' => $commande_id]);
	$produits = $stmt_produits->fetchAll(PDO::FETCH_ASSOC);

	return [
		'commande' => $commande,
		'produits' => $produits
	];
}

function updateCommandeStatus($commande_id, $nouveau_statut)
{
	$conn = connectsDB();

	$stmt = $conn->prepare("UPDATE commande SET statut = :statut WHERE id = :id");
	return $stmt->execute([
		':statut' => $nouveau_statut,
		':id' => $commande_id
	]);
}

function getCommandeHistorique($commande_id)
{
	$conn = connectsDB();
	$sql = "
        SELECT 
            ch.status,
            ch.modifie_le,
            u.nom as modifie_par_nom
        FROM commande_historique ch
        JOIN user u ON ch.modifie_par = u.id
        WHERE ch.commande_id = :commande_id
        ORDER BY ch.modifie_le DESC
    ";

	$stmt = $conn->prepare($sql);
	$stmt->execute([':commande_id' => $commande_id]);
	return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
