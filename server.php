#!/usr/bin/php -q
<?php


// Execution en ligne de commande : php -q <nomdufichier>.php
// Inclusion de la librairie phpwebsocket
require "websocket.class.php";

// Extension de WebSocket
class ChatBot extends WebSocket {
    
    var $assoUsersSockets = array();

    function process($user, $msg) {
        
        // Debug
        $this->say("> Message reçu : " . $msg);
        
        // Récupération des différents champs du message
        $parsedMsg=json_decode($msg,true);
        $type=$parsedMsg["type"];
        $login=$parsedMsg["login"];
        $pwd=$parsedMsg["pwd"];
        
        // Connexion à la bdd
        //$link = mysqli_connect('192.168.0.2', 'projet', 'projet', 'webchaussette');
        $link = mysqli_connect('www.remi-boyer.fr', 'projet', 'projet', 'webchaussette');
        
        // Traitement fonction du type du message
        $this->say("> Type du message reçu : " .$type );
        $this->say("> Id socket : $user->socket");
        switch ($type) {
            case "connect":
                // Construction de la requête
                $query = "SELECT mdp FROM Utilisateur WHERE nom='$login';";
                $result = mysqli_query($link, $query);
                
                // Si le nom d'utilisateur existe
                if ($arr = mysqli_fetch_array($result)) {
                    // Si le mot de passe est OK
                    if ($arr["mdp"] == $pwd) {
                        
                        // Si un socket est déjà ouvert pour cet utilisateur, fermeture de ce dernier
                        $this->say("AAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAAA Verification si $login est deja connecté");
                        
                        $socketToDisconnect=$this->assoUsersSockets[$login];
                        if($socketToDisconnect!=null){
                            $this->say("$login deja connecte sur le socket $socketToDisconnect");
                            $this->disconnect($socketToDisconnect);
                            $this->say("$login est désormais deconnecte !");
                        }
                        /**$query = "SELECT idSocket FROM Utilisateur WHERE nom='$login';";
                        $this->say("Requete envoyée = $query");
                        $result = mysqli_query($link, $query);
                        $arr = mysqli_fetch_array($result);
                        $ancienSocket=$arr['idSocket'];
                        
                        $ancienUser=$this->getuserbysocket($ancienSocket);
                        
                        */
                        /*if($u){
                            $this->say("$login était deje connecte ailleurs sur le socket $ancienSocket");
                           // $this->disconnect(8);
                           //$this->disconnect($assoUsersSockets[);
                           // $this->disconnect($ancienSocket);
                        }*/
                        //if($assoUsersSockets[$user->socket]!=null){
                          //  $this->disconnect($ancienUser->socket);
                         // Ajout de l'identifiant de socket de l'utilisateur dans la BDD
                        $this->assoUsersSockets[$login]=$user->socket;
                        $this->say("> Idsocket ajouté pour $login");
                        //$idSocketUser = $user->socket;
                        //$query = "UPDATE Utilisateur SET idSocket='$idSocketUser' WHERE nom='$login';";
                        //$this->say("AAAAAAAAAAAAAAAAAAAAA : $query");
                        //$result = mysqli_query($link, $query);
                        
                       
                                
                        // Envoi de la liste des utilisateurs déjà connectés
                        
                        
                        // Pour chaque utilisateur connecté
                        $this->say("Liste des connectés actuel");
                        foreach ($this->users as $utilisateur) {
                            
                            // récupérer son login
                            //$socketCourant = $utilisateur->socket;
                            //$query = "SELECT nom FROM Utilisateur WHERE idSocket='$socketCourant'";
                            //$this->say("Requete envoyée : $query");
                            //$result = mysqli_query($link, $query);
                            //$arr = mysqli_fetch_array($result);
                            
                            //$this->say($arr['nom']);
                            
                            if($connectedUser=array_search($utilisateur->socket, $this->assoUsersSockets)){
                            $this->say($connectedUser);
                            // formater le message d'envoi
                            $connectedUserMsg = json_encode("{'type':'connect','login':'"+$connectedUser+"','pwd':''}");
                   
                            // envoyer au mec nouvellement connecté
                            $this->send($user->socket, $connectedUserMsg);
                            }
                        }

                       
                        
                        // Notification de nouvelle connexion à tous les clients connectés
                        $this->say("RENVOI DU MESSAGE A TOUS LES CLIENTS");
                        foreach ($this->users as $utilisateur) {
                            $this->send($utilisateur->socket, $msg);
                            $this->send($utilisateur->socket, "$login s'est connecté !");
                        }
                   } else { // MDP erroné
                       $this->say("> MDP erroné pour $login");
                   }
                } else { // Login inconnu
                    
                }
                //$this->send($user->socket, $msg);
                //$this->send($user->socket, "Bravo $login, tu t'es bien connecté !");
                break;
            case "disconnect":
                $this->send($user->socket, "Disconnect bien reçu ");
                break;
            // envoi d'un message public
            case "message": // broadcast pour l'instant
                /* // SI MESSAGE PRIVE
                  if ($true) {

                  } else { // SI MESSAGE PUBLIC
                  // Récupérer le num du salon concerné
                  $numSalon;

                  // Récupérer la liste des utilisateurs du salon
                  $link = connectBDD();
                  $query = requeteUtilisateursDuSalon($numSalon);
                  $result = mysqli_query($link, $query);
                  while ($utilisateurCourant = mysqli_fetch_array($result)) {
                  $socket = $this->getuserbysocket($utilisateurCourant["idSocket"]);
                  $this->send($socket, "TEST");
                  }
                  } */
                foreach ($this->users as $utilisateur) {
                    $this->send($utilisateur->socket, $msg);
                }
                break;
            case "open":
                $this->send($user->socket, "Open bien reçu ");
                break;
            case "close":
                $this->send($user->socket, "Close bien reçu !");
                break;
            default:
                $this->send($user->socket, "Pas compris mec !");
                break;
        }
    }

}

$master = new ChatBot("localhost", 1337);
