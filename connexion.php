<?php
//include '../admin/secret.php';
//include '../bdd/requetes.php';
//include '../bdd/connexionBDD.php';
$trouver=false;
$dbcon=connexionBDD ();
if((isset($_POST["login"])) && (isset ($_POST["mdp"]))){
	/*Si les var "login" et "mdp" sont SET on va chercher tout les id et mdp de la table étudiant
         * on les rentrent dans un tableau et on compare avec ceux passer en post si c'est correcte on 
         * redirige l'utilisateur vers la pag d'accueil connecté et sinon on vérifie la valeur de la variable
         * $trouver si elle n'est pas à "true" on déclenche une erreur.
         */
	$result_user= mysqli_query($dbcon,requete_recherche_login_mdp_etudiant());
        
       while($arr = mysqli_fetch_array($result_user)){
           $mdp=($_POST["mdp"]);
           if(pg_escape_string($_POST["login"])==$arr["idetudiant"] && $mdp==$arr["mdpetudiant"]){
            $_SESSION["id"] = $_POST["login"];
            $_SESSION["statut"]="user";
            $trouver=true;
		header("Location:./accueil.php");
       }
       }
        $result_adm = mysqli_query($dbcon,requete_recherche_login_mdp_admin());
       
        while($tab = mysqli_fetch_array($result_adm)){
           
       $mdp=($_POST["mdp"]);
      if(pg_escape_string($_POST["login"])==$tab["idadmin"] &&  $mdp==$tab["mdpadmin"]){
		$_SESSION["id"] = $_POST["login"];
                $_SESSION["statut"] = "admin";}
                $trouver=true;
              
		header('Location:./accueil.php');
	}
        
        
        }
	
	if(!$trouver){
        
	
		$_SESSION["erreur_log"]=1;
                 
		header('Location:../index.php');
	}
 ?>

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

