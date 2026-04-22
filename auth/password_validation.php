<?php
function validatePassword($password)
{
	$errors = [];

	// Vérifier la longueur minimale (12 caractères recommandés)
	if (strlen($password) < 12) {
		$errors[] = "Le mot de passe doit contenir au moins 12 caractères";
	}

	// Vérifier la présence d'au moins une lettre majuscule
	if (!preg_match('/[A-Z]/', $password)) {
		$errors[] = "Le mot de passe doit contenir au moins une lettre majuscule";
	}

	// Vérifier la présence d'au moins une lettre minuscule
	if (!preg_match('/[a-z]/', $password)) {
		$errors[] = "Le mot de passe doit contenir au moins une lettre minuscule";
	}

	// Vérifier la présence d'au moins un chiffre
	if (!preg_match('/[0-9]/', $password)) {
		$errors[] = "Le mot de passe doit contenir au moins un chiffre";
	}

	// Vérifier la présence d'au moins un caractère spécial
	if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
		$errors[] = "Le mot de passe doit contenir au moins un caractère spécial (!@#$%^&*(),.?\":{}|<>)";
	}

	// Vérifier l'absence d'espaces
	if (preg_match('/\s/', $password)) {
		$errors[] = "Le mot de passe ne doit pas contenir d'espaces";
	}

	return [
		'isValid' => empty($errors),
		'errors' => $errors
	];
}

function hashPassword($password)
{
	// Utilisation de l'algorithme Argon2id (recommandé pour le RGPD)
	$options = [
		'memory_cost' => 65536, // 64MB
		'time_cost' => 4,      // 4 iterations
		'threads' => 3         // 3 threads
	];

	return password_hash($password, PASSWORD_ARGON2ID, $options);
}
