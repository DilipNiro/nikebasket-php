<?php
// create-payment-intent.php
require(__DIR__ . '/../vendor/autoload.php');
require(__DIR__ . '/../config/dbconnect.php');

if (!isset($_SESSION['connectedUser']) || !isset($_SESSION['pending_order'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Non autorisé']);
    exit;
}

// Récupérer les données JSON envoyées
$jsonStr = file_get_contents('php://input');
$jsonData = json_decode($jsonStr);

if (!isset($jsonData->amount)) {
    http_response_code(400);
    echo json_encode(['error' => 'Montant non spécifié']);
    exit;
}

\Stripe\Stripe::setApiKey(getenv('STRIPE_SECRET_KEY') ?: 'sk_test_votre_cle_stripe');

try {
    $payment_intent = \Stripe\PaymentIntent::create([
        'amount' => $jsonData->amount,
        'currency' => 'eur',
        'payment_method_types' => ['card'],
        'metadata' => [
            'user_id' => $_SESSION['connectedUser']['id']
        ]
    ]);

    // Stocker les informations du PaymentIntent en session
    $_SESSION['payment_intent'] = [
        'id' => $payment_intent->id,
        'client_secret' => $payment_intent->client_secret,
        'amount' => $jsonData->amount
    ];

    echo json_encode([
        'clientSecret' => $payment_intent->client_secret
    ]);
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}