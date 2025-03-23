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
</html>