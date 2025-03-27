<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Transaction Loicoin</title>
    <link rel="stylesheet" href="transaction.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
   
</head>
<body>
<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: connect.php");
    exit();
}

$name = $_SESSION['name'];
$date = date('Y-m-d H:i:s'); 
$statut = "attente";

require('database.php');

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Récupérer les données du formulaire
    $receiver = htmlspecialchars($_POST['receiver']);
    $montant = floatval($_POST['montant']);
    $fee = 0.5;

    // Récupérer l'ID de l'expéditeur
    $sql_expediteur = "SELECT id_u FROM wallet WHERE client = ?";
    $stmt_expediteur = $connect->prepare($sql_expediteur);
    $stmt_expediteur->bind_param("s", $name);
    $stmt_expediteur->execute();
    $result_expediteur = $stmt_expediteur->get_result();
    $row_expediteur = $result_expediteur->fetch_assoc();

    if ($row_expediteur) {
        $id_expediteur = $row_expediteur['id_u'];

        // Récupérer le solde de l'expéditeur
        $sql_solde = "SELECT solde FROM wallet WHERE id_u = ?";
        $stmt_solde = $connect->prepare($sql_solde);
        $stmt_solde->bind_param("i", $id_expediteur);
        $stmt_solde->execute();
        $result_solde = $stmt_solde->get_result();
        $row_solde = $result_solde->fetch_assoc();
        $solde_expediteur = $row_solde['solde'];

        // Vérifier le solde
        $total_cost = $montant + $fee;
        
        if ($total_cost > $solde_expediteur) {
            echo '<div class="container"><div class="error-message transaction-message">Erreur : Solde insuffisant pour effectuer cette transaction.</div></div>';
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
                $in_transaction = "INSERT INTO transaction (sender, receiver, montant, fee, date, statut) VALUES (?, ?, ?, ?, ?, ?)";
                $stmt_transaction = $connect->prepare($in_transaction);
                $stmt_transaction->bind_param("ssddss", $name, $receiver, $montant, $fee, $date, $statut);
                $stmt_transaction->execute();

                echo '<div class="container"><div class="success-message transaction-message">Transaction réussie ! ' . number_format($montant, 8) . ' BTC a été envoyé à ' . htmlspecialchars($receiver) . '.</div></div>';
            } else {
                echo '<div class="container"><div class="error-message transaction-message">Erreur : Destinataire non trouvé.</div></div>';
            }

            // Fermer les déclarations
            $stmt_update_expediteur->close();
            $stmt_select_receiver->close();
            $stmt_update_receiver->close();
            $stmt_transaction->close();
        }

        // Fermer les déclarations de solde
        $stmt_solde->close();
    } else {
        echo '<div class="container"><div class="error-message transaction-message">Erreur : Expéditeur non trouvé.</div></div>';
    }

    // Fermer les déclarations d'expéditeur
    $stmt_expediteur->close();
}
?>

<div class="container">
    <div class="transaction-card">
        <h2><i class="fas fa-paper-plane"></i> Envoi de BTC</h2>
        
        <div class="fee-info">
            <i class="fas fa-info-circle"></i>
            <div class="fee-text">
                <strong>Frais de transaction : 0.5 BTC</strong><br>
                Ce montant sera déduit en plus du montant envoyé.
            </div>
        </div>
        
        <form action="#" method="POST">
            <div class="mb-3">
                <label for="receiver" class="form-label">Destinataire</label>
                <input type="text" class="form-control" id="receiver" name="receiver" placeholder="Adresse ou identifiant du destinataire" required>
            </div>
            <div class="mb-3">
                <label for="montant" class="form-label">Montant à envoyer</label>
                <div class="btc-input">
                    <input type="number" step="0.00000001" class="form-control" id="montant" name="montant" placeholder="0.00" required>
                    <span>BTC</span>
                </div>
            </div>
            <div class="mb-3 form-check">
                <input type="checkbox" class="form-check-input" id="confirmation" required>
                <label class="form-check-label" for="confirmation">Je confirme cette transaction</label>
            </div>
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-paper-plane"></i> Envoyer
            </button>
        </form>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
</body>
</html>