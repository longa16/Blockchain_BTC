<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Formulaire d'Inscription</title>
 
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

$userselection = "SELECT * FROM user WHERE identifiant = '$name' ";
$result2 = $connect->query($userselection);

$walletuser = "SELECT * FROM wallet INNER JOIN user ON wallet.id_u = user.id_u" ;
$result2 = $connect->query($walletuser);
?>

 <img src="img/se-connecter.png" weight="20" height="20"><?php echo $_SESSION['name'] ?>

<button class="btn-primary" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasRight" aria-controls="offcanvasRight">Menu</button>
<style> 
    .btn-primary {
        margin-left: 10px;
        margin-top: 50px;
        border-radius: 5px;
    }
</style>
<div class="offcanvas offcanvas-end" tabindex="-1" id="offcanvasRight" aria-labelledby="offcanvasRightLabel">
  <div class="offcanvas-header">
    <h5 id="offcanvasRightLabel">Portefeuille</h5>
    <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
  </div>
  <div class="offcanvas-body">
  <a class="navbar-brand" href="transaction.php">
      <img src="img/transaction.png" alt="" width="30" height="24" class="d-inline-block align-text-top">
      Transaction <br>
    </a>
    <a class="navbar-brand" href="minage.php">
      <img src="img/minage-de-bitcoins.png" alt="" width="30" height="24" class="d-inline-block align-text-top">
      Minage <br>
    </a>
    <a class="navbar-brand" href="connect.php">
      <img src="img/se-deconnecter.png" alt="" width="30" height="24" class="d-inline-block align-text-top">
      Deconnexion
    </a>
  </div>
</div>
<?php
if ($result2->num_rows > 0) {
         while ($post = $result2->fetch_assoc()){
            $id = $post["id_u"];
            $solde = $post["solde"];
            $adresse = $post['addresse'];
            } 
        } 
     else {
     
        echo"<h3>You have not yet published a topic.</h3>";
    }   ?>

<div class="card" style="width: 18rem;">
  <div class="card-body">
    <h5 class="card-title">Solde</h5>
    <p class="card-text"><?php echo "$solde $" ?></p>
  </div>
  <style> 
    .card {
        margin-left: 500px;
        border-radius: 5px;
    }
</style>
</div>
</body>




