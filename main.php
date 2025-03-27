<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loicoin</title>
    <link rel="stylesheet" href="main.css">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <img src="img/blockchain.png" alt="" width="30" height="24">
      Loicoin.com
    </a>
    <form class="d-flex">
      <input class="form-control me-2" type="search" placeholder="Rechercher..." aria-label="Search">
      <button class="btn btn-outline-success" type="submit">Go</button>
    </form>
  </div>
</nav>

<?php
session_start();
if (!isset($_SESSION['name'])) {
    header("Location: connect.php");
    exit();
}

$name = $_SESSION['name'];

require('database.php');

// Sélectionner les informations de l'utilisateur
$userselection = "SELECT * FROM user WHERE identifiant = ?";
$stmt = $connect->prepare($userselection);
$stmt->bind_param("s", $name);
$stmt->execute();
$result = $stmt->get_result();

// Sélectionner les informations du portefeuille de l'utilisateur
$walletuser = "SELECT * FROM wallet WHERE client = ?";
$stmt_wallet = $connect->prepare($walletuser);
$stmt_wallet->bind_param("s", $name);
$stmt_wallet->execute();
$result_wallet = $stmt_wallet->get_result();

$stmt->close();
$stmt_wallet->close();
?>

<div class="container mt-4">
    <div class="row">
        <div class="col-md-6">
            <div class="user-info">
                <img src="img/se-connecter.png" width="20" height="20">
                <span class="ms-2 fw-medium"><?php echo htmlspecialchars($_SESSION['name']) ?></span>
            </div>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">
                <i class="bi bi-list"></i> Menu
            </button>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasRightLabel">Portefeuille</h5>
    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <a class="navbar-brand d-flex align-items-center mb-2" href="transaction.php">
        <img src="img/transaction.png" alt="" width="30" height="24" class="me-2">
        Transaction
    </a>
    <a class="navbar-brand d-flex align-items-center mb-2" href="minage.php">
        <img src="img/minage-de-bitcoins.png" alt="" width="30" height="24" class="me-2">
        Minage
    </a>
    <a class="navbar-brand d-flex align-items-center" href="connect.php">
        <img src="img/se-deconnecter.png" alt="" width="30" height="24" class="me-2">
        Déconnexion
    </a>
  </div>
</div>

<div class="container">
    <?php
    if ($result_wallet->num_rows > 0) {
        while ($post = $result_wallet->fetch_assoc()) {
            $id = $post["id_u"];
            $solde = $post["solde"];
            $adresse = $post['addresse'];
            $euroValue = $solde * 80732.72;
        }
        echo '<div class="wallet-card text-center p-4">
                <h5 class="card-title">Votre solde</h5>
                <div class="btc-amount">' . number_format($solde, 8) . ' BTC</div>
                <div class="eur-amount">≈ ' . number_format($euroValue, 2) . ' €</div>
                <div class="mt-3 text-muted small">Adresse: ' . substr($adresse, 0, 12) . '...' . substr($adresse, -4) . '</div>
              </div>';
    } else {
        echo '<div class="no-wallet-message">
                <h3>Vous n\'avez pas encore de portefeuille.</h3>
                <p>Créez un portefeuille pour commencer à utiliser Loicoin</p>
              </div>';
    }
    ?>
</div>

</body>
</html>