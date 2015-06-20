<?php

function connexionBDD()
{
	global $host;
	global $login;
	global $password;
	global $db;
        
        $host="192.168.0.2";
        $login="root";
        $password="beefIsMagic5";
        $db="webchaussette";
	
	$result = mysqli_connect("host=$host user=$login password=$password dbname=$db");
	return $result;
}

function requeteHistoriqueSalon($numSalon)
{
    $requete = "SELECT * FROM Message WHERE numSalon=$numSalon";
    return $requete;
}

function requeteAjoutSocketUtilisateur($nomUtilisateur,$idSocket){
    $requete = "UPDATE Utilisateur SET idSocket=$idSocket WHERE idUtilisateur = $nomUtilisateur;";
    return $requete;
}

function requeteAjouterUtilisateurAuSalon($nomUser,$numSalon){
    $requete = "UPDATE Utilisateur SET idSalon='$numSalon' WHERE nom = $nomUser;";
    return $requete;
}

function requeteMdpUtilisateur($nomUtilisateur){
    $requete = "SELECT mdp FROM Utilisateur WHERE $nomUtilisateur";
    return $requete;
}
?>