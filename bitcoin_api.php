<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

// URL de l'API de CoinGecko pour obtenir les données historiques du Bitcoin
$apiUrl = 'https://api.coingecko.com/api/v3/coins/bitcoin/market_chart?vs_currency=usd&days=30&interval=daily';

// Fonction pour récupérer les données de l'API
function getBitcoinData($url) {
    if (function_exists('curl_init')) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $response = curl_exec($ch);
        curl_close($ch);
    } else {
        $response = file_get_contents($url);
    }

    if ($response === FALSE) {
        die('Erreur lors de la récupération des données de l\'API.');
    }

    return json_decode($response, true);
}

// Récupérer les données de l'API
$bitcoinData = getBitcoinData($apiUrl);

// Afficher les données sous forme de JSON
echo json_encode($bitcoinData);
?>
