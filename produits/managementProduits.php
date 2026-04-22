<?php
require_once("listeProduits.php");
require_once("../auth/functionLogin.php");
require_once("../panier/ajouterProduit.php");

function initializeVariables()
{
	$variables = [
		'quantitePanier' => 0,
		'search' => '',
		'price_min' => '',
		'price_max' => '',
		'categorie_filter' => '',
		'tailles_filter' => [],
		'couleurs_filter' => []
	];

	if ($GLOBALS['user_id']) {
		$variables['quantitePanier'] = quantityPanier($GLOBALS['user_id']);
	}

	// Récupération des filtres de base
	$variables['search'] = isset($_GET['search']) ? trim($_GET['search']) : '';
	$variables['price_min'] = isset($_GET['price_min']) ? $_GET['price_min'] : '';
	$variables['price_max'] = isset($_GET['price_max']) ? $_GET['price_max'] : '';
	$variables['categorie_filter'] = isset($_GET['categorie']) ? $_GET['categorie'] : '';

	// Récupération des filtres de taille
	if (isset($_GET['tailles']) && is_array($_GET['tailles'])) {
		$variables['tailles_filter'] = array_map('intval', $_GET['tailles']);
	}

	// Récupération des filtres de couleur
	if (isset($_GET['couleurs']) && is_array($_GET['couleurs'])) {
		$variables['couleurs_filter'] = array_map('intval', $_GET['couleurs']);
	}

	// Récupération des produits avec tous les filtres
	$variables['produits'] = getProducts2(
		$variables['categorie_filter'],
		$variables['price_min'],
		$variables['price_max'],
		$variables['search'],
		$variables['tailles_filter'],
		$variables['couleurs_filter']
	);

	return $variables;
}
