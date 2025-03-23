<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'Inscription</title>
    <link rel="stylesheet" href="connect.css">
</head>
<body>
    <div class="form-container">
        <h2>Se connecter</h2>
        <form action="#" method="POST">
            <div class="form-group">
                <label for="name">Identifiant</label>
                <input type="text" id="name" name="name" placeholder="Entrez votre nom" required>
            </div>
            <div class="form-group">
                <label for="password">Mot de passe</label>
                <input type="password" id="password" name="password" placeholder="Entrez votre mot de passe" required>
            </div>
            <button type="submit">Se connecter</button>
        </form>
    </div>
</body>

<?php
session_start();

$host = "localhost";
$bdd = "blockchain";
$user = "root";
$pwd = "ROOT";

try {
    $connexion = new PDO("mysql:host=$host;dbname=$bdd", $user, $pwd);
    $connexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Erreur de connexion : " . $e->getMessage());
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Récupération des données du formulaire
    $username = isset($_POST['name']) ? trim($_POST['name']) : '';
    $password = isset($_POST['password']) ? $_POST['password'] : '';

    if (!empty($username) && !empty($password)) {
        try {
            // Préparer la requête SQL pour éviter les injections
            $stmt = $connexion->prepare("SELECT * FROM user WHERE identifiant = :username");
            $stmt->bindParam(':username', $username, PDO::PARAM_STR);
            $stmt->execute();

            if ($stmt->rowCount() === 1) {
                // Récupérer les informations de l'utilisateur
                $row = $stmt->fetch(PDO::FETCH_ASSOC);
                if (($password) == $row['password']) {
                    // Informations de connexion valides
                    $_SESSION['name'] = $username;
                    header("Location: main.php");
                    exit;
                } else {
                    // Mot de passe incorrect
                    echo '<p class="error-message">Votre mot de passe est incorrect.</p>';
                }
            } else {
                // Aucun utilisateur trouvé avec ce pseudo
                echo '<p class="error-message">Votre nom d\'utilisateur/email est incorrect.</p>';
            }
        } catch (PDOException $e) {
            echo '<p class="error-message">Une erreur est survenue : ' . $e->getMessage() . '</p>';
        }
    } else {
        echo '<p class="error-message">Veuillez remplir tous les champs.</p>';
    }
}
?>
</html>