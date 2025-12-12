<?php // Activer les erreurs en développement (à retirer en production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Headers pour JSON
header('Content-Type: application/json; charset=utf-8');

// Vérifier que c'est bien une requête POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Méthode non autorisée']);
    exit;
}

// Inclure la configuration de la base de données
require_once 'config/database.php';

// Récupérer les données envoyées
$nom = trim($_POST['nom'] ?? '');
$email = trim($_POST['email'] ?? '');

// Validation côté serveur (TOUJOURS nécessaire !)
$errors = [];

if (empty($nom)) {
    $errors[] = 'Le nom est obligatoire';
} elseif (strlen($nom) < 2) {
    $errors[] = 'Le nom doit contenir au moins 2 caractères';
}

if (empty($email)) {
    $errors[] = 'L\'email est obligatoire';
} elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $errors[] = 'L\'adresse email n\'est pas valide';
}

// Si erreurs de validation
if (!empty($errors)) {
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'message' => 'Erreurs de validation',
        'errors' => $errors
    ]);
    exit;
}

// Insertion dans la base de données
try {
    $sql = "INSERT INTO newsletter (nom, email) VALUES (:nom, :email)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':nom' => $nom,
        ':email' => $email
    ]);

    // Succès !
    http_response_code(201);
    echo json_encode([
        'success' => true,
        'message' => 'Merci ' . htmlspecialchars($nom) . ' ! Votre inscription a bien été enregistrée.',
        'data' => [
            'id' => $pdo->lastInsertId(),
            'email' => $email
        ]
    ]);

} catch (PDOException $e) {
    // Vérifier si c'est une erreur de doublon (email déjà inscrit)
    if ($e->getCode() == 23000) {
        http_response_code(409);
        echo json_encode([
            'success' => false,
            'message' => 'Cet email est déjà inscrit à notre newsletter.'
        ]);
    } else {
        // Autre erreur SQL
        error_log("Erreur SQL : " . $e->getMessage());
        http_response_code(500);
        echo json_encode([
            'success' => false,
            'message' => 'Une erreur est survenue lors de l\'inscription.'
        ]);
    }
}

?>