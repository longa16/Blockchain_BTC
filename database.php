<?php
$host = "localhost";
$bdd = "blockchain";
$user = "root";
$pwd = "ROOT";

$connect = mysqli_connect($host, $user, $pwd, $bdd);

if ($connect->connect_error) {
    die("unsucsseful connexion : " . $connect->connect_error);
}
?>

