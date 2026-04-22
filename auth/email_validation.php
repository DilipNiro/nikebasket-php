<?php
function validateEmail($email)
{
	$errors = [];

	// Retirer les espaces
	$email = trim($email);

	// Vérification de la longueur
	if (strlen($email) > 254) {
		$errors[] = "L'adresse email ne peut pas dépasser 254 caractères";
		return ['isValid' => false, 'errors' => $errors];
	}

	// Vérification du format basique avec filter_var
	if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		$errors[] = "Format d'adresse email invalide";
		return ['isValid' => false, 'errors' => $errors];
	}

	// Vérification plus approfondie avec une expression régulière
	$pattern = '/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/';
	if (!preg_match($pattern, $email)) {
		$errors[] = "L'adresse email contient des caractères non autorisés";
		return ['isValid' => false, 'errors' => $errors];
	}

	// Vérifier la partie locale de l'email
	$local_part = substr($email, 0, strpos($email, '@'));
	if (strlen($local_part) > 64) {
		$errors[] = "La partie locale de l'email ne peut pas dépasser 64 caractères";
		return ['isValid' => false, 'errors' => $errors];
	}

	// Vérifier le domaine
	$domain = substr($email, strpos($email, '@') + 1);

	// Vérifier si le domaine existe avec checkdnsrr (optionnel)
	if (!checkdnsrr($domain, 'MX') && !checkdnsrr($domain, 'A')) {
		$errors[] = "Le domaine de l'email semble invalide ou n'existe pas";
		return ['isValid' => false, 'errors' => $errors];
	}

	// Vérifications supplémentaires de sécurité
	$blocked_domains = ['example.com', 'test.com']; // Ajouter d'autres domaines si nécessaire
	if (in_array($domain, $blocked_domains)) {
		$errors[] = "Ce domaine d'email n'est pas autorisé";
		return ['isValid' => false, 'errors' => $errors];
	}

	return [
		'isValid' => empty($errors),
		'errors' => $errors,
		'sanitized_email' => $email
	];
}

function sanitizeEmail($email)
{
	// Nettoyer l'email
	$email = filter_var($email, FILTER_SANITIZE_EMAIL);
	$email = strtolower($email); // Convertir en minuscules
	return $email;
}
