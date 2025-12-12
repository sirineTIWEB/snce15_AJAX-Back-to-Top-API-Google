<?php

// Configuration de la connexion à la base de données
// Detecter si on est en local ou en production
$isLocal = ($_SERVER['HTTP_HOST'] === 'localhost' || strpos($_SERVER['HTTP_HOST'], '127.0.0.1') !== false);

if ($isLocal) {
    // Configuration LOCALE (développement)
    define('DB_HOST', 'localhost');
    define('DB_NAME', 'newsletter');
    define('DB_USER', 'root');
    define('DB_PASS', '');
} else {
    // Configuration PRODUCTION (InfinityFree)
    define('DB_HOST', 'sql103.infinityfree.com');
    define('DB_NAME', 'if0_40665547_newsletters');
    define('DB_USER', 'if0_40665547');
    define('DB_PASS', 'Mmw1YZvAte0mtB');
}

define('DB_CHARSET', 'utf8mb4');

// Création de la connexion PDO
try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;

    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);

} catch (PDOException $e) {
    // En production, ne jamais afficher le message d'erreur détaillé
    error_log("Erreur de connexion : " . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Erreur de connexion à la base de données'
    ]);
    exit;
}

?>