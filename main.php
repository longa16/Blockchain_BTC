<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Loicoin</title>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/js/bootstrap.bundle.min.js" integrity="sha384-MrcW6ZMFYlzcLA8Nl+NtUVF0sA7MsXsP1UyJoMp4YLEuNSfAP+JcXn/tWtIaxVXM" crossorigin="anonymous"></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>
<body>

<nav class="navbar navbar-light bg-light">
  <div class="container-fluid">
    <a class="navbar-brand" href="#">
      <img src="img/blockchain.png" alt="" width="30" height="24" class="d-inline-block align-text-top">
      Loicoin.com
    </a>
    <form class="d-flex">
      <input class="form-control me-2" type="search" placeholder="Search" aria-label="Search">
      <button class="btn btn-outline-success" type="submit">Search</button>
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

<div class="container mt-5">
    <div class="row">
        <div class="col-md-6">
            <img src="img/se-connecter.png" width="20" height="20">
            <span class="ms-2"><?php echo $_SESSION['name'] ?></span>
        </div>
        <div class="col-md-6 text-end">
            <button class="btn btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">Menu</button>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasRightLabel">Portefeuille</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
    <a class="navbar-brand d-flex align-items-center mb-2" href="transaction.php">
        <img src="img/transaction.png" alt="" width="30" height="24" class="d-inline-block align-text-top me-2">
        Transaction
    </a>
    <a class="navbar-brand d-flex align-items-center mb-2" href="minage.php">
        <img src="img/minage-de-bitcoins.png" alt="" width="30" height="24" class="d-inline-block align-text-top me-2">
        Minage
    </a>
    <a class="navbar-brand d-flex align-items-center" href="connect.php">
        <img src="img/se-deconnecter.png" alt="" width="30" height="24" class="d-inline-block align-text-top me-2">
        Déconnexion
    </a>
  </div>
</div>

<?php
if ($result_wallet->num_rows > 0) {
    while ($post = $result_wallet->fetch_assoc()) {
        $id = $post["id_u"];
        $solde = $post["solde"];
        $adresse = $post['addresse'];
    }
    echo '<div class="card" style="width: 18rem; margin: 20px auto;">
            <div class="card-body">
                <h5 class="card-title">Solde</h5>
                <p class="card-text">' . $solde . ' $</p>
            </div>
          </div>';
} else {
    echo "<h3>Vous n'avez pas encore de portefeuille.</h3>";
}
?>

</body>
</html>
