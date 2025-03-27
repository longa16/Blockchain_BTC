<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'Inscription</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .form-container {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
            width: 100%;
            max-width: 450px;
            transition: all 0.3s ease;
        }

        .form-container:hover {
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        h2 {
            color: #333;
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            font-weight: 600;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }

        input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s;
        }

        input:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.2);
            outline: none;
        }

        button {
            width: 100%;
            padding: 14px;
            background: linear-gradient(to right, #667eea, #764ba2);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s;
            margin-top: 10px;
        }

        button:hover {
            background: linear-gradient(to right, #5a6fd1, #6a4295);
            transform: translateY(-2px);
        }

        .error-message {
            color: #e74c3c;
            text-align: center;
            margin-top: 20px;
            font-size: 14px;
            padding: 10px;
            background-color: #fadbd8;
            border-radius: 5px;
            border-left: 4px solid #e74c3c;
        }

        @media (max-width: 480px) {
            .form-container {
                padding: 30px 20px;
            }
            
            h2 {
                font-size: 24px;
            }
        }
    </style>
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
</body>
</html>