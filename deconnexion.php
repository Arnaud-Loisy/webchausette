<?php


session_start();
session_unset(); // on unset toutes les variables de session pour la deconnexion
header('Location:../index.php');

?>