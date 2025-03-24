<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loicoin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="style.css">
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: connect.php");
    exit();
}
$name = $_SESSION['name'];

require('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $id_expediteur = 1; // Remplacez par l'ID de l'expéditeur réel
    $receiver = $_POST['receiver'];
    $montant = $_POST['montant'];

    // Convertir le montant en nombre
    $montant = floatval($montant);

    // Récupérer le solde de l'expéditeur
    $sql = "SELECT solde FROM wallet WHERE id_u = ?";
    $stmt = $connect->prepare($sql);
    $stmt->bind_param("i", $id_expediteur);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $solde_expediteur = $row['solde'];

    // Vérifier si le montant plus les frais est supérieur au solde
    $fee = 0.5;
    $total_cost = $montant + $fee;
    $trahash = 123456789987654;

    if ($total_cost > $solde_expediteur) {
        echo "Erreur : Solde insuffisant pour effectuer cette transaction.";
    } else {
        // Mettre à jour le solde de l'expéditeur
        $nouveau_solde_expediteur = $solde_expediteur - $total_cost;
        $sql_update_expediteur = "UPDATE wallet SET solde = ? WHERE id_u = ?";
        $stmt_update_expediteur = $connect->prepare($sql_update_expediteur);
        $stmt_update_expediteur->bind_param("di", $nouveau_solde_expediteur, $id_expediteur);
        $stmt_update_expediteur->execute();

        // Mettre à jour le solde du destinataire
        $sql_select_receiver = "SELECT id_u FROM wallet WHERE client = ?";
        $stmt_select_receiver = $connect->prepare($sql_select_receiver);
        $stmt_select_receiver->bind_param("s", $receiver);
        $stmt_select_receiver->execute();
        $result_receiver = $stmt_select_receiver->get_result();
        $row_receiver = $result_receiver->fetch_assoc();

        if ($row_receiver) {
            $id_receiver = $row_receiver['id_u'];
            $sql_update_receiver = "UPDATE wallet SET solde = solde + ? WHERE id_u = ?";
            $stmt_update_receiver = $connect->prepare($sql_update_receiver);
            $stmt_update_receiver->bind_param("di", $montant, $id_receiver);
            $stmt_update_receiver->execute();

            // Enregistrer la transaction
            $in_transaction = "INSERT INTO transaction (sender, receiver, montant, fee, tra_hash) VALUES (?, ?, ?, ?, ?)";
            $stmt_transaction = $connect->prepare($in_transaction);
            $stmt_transaction->bind_param("ssddd", $name, $receiver, $montant, $fee, $trahash);
            $stmt_transaction->execute();

            echo "Transaction réussie ! $montant BTC a été envoyé à $receiver.";
        } else {
            echo "Erreur : Destinataire non trouvé.";
        }

        // Fermer les déclarations
        $stmt_update_expediteur->close();
        $stmt_select_receiver->close();
        $stmt_update_receiver->close();
        $stmt_transaction->close();
    }

}
?>

<div class="container mt-5">
    <h2>Envoi d'Argent</h2>
    <form action="#" method="POST">
        <div class="mb-3">
            <label for="receiver" class="form-label">Destinataire</label>
            <input type="text" class="form-control" id="receiver" name="receiver" aria-describedby="emailHelp" required>
        </div>
        <div class="mb-3">
            <label for="montant" class="form-label">Montant</label>
            <input type="text" class="form-control" id="montant" name="montant" placeholder="BTC" required>
        </div>
        <div class="mb-3 form-check">
            <input type="checkbox" class="form-check-input" id="exampleCheck1" required>
            <label class="form-check-label" for="exampleCheck1">Valider l'opération</label>
        </div>
        <button type="submit" class="btn btn-primary">Envoyer</button>
    </form>
    <?php echo "$name, envoie d'argent page"; ?>
</div>
</body>
</html>
