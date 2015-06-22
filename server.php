#!/usr/bin/php -q
<?php


// Execution en ligne de commande : php -q <nomdufichier>.php
// Inclusion de la librairie phpwebsocket
require "websocket.class.php";

// Extension de WebSocket
class ChatBot extends WebSocket {
    

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
                        // Ajout de l'identifiant de socket de l'utilisateur dans la BDD
                        $query = "UPDATE Utilisateur SET idSocket='"+$user->socket+"' WHERE nom='$login';";
                        $result = mysqli_query($link, $query);
                        $this->say("> Idsocket ajouté pour $login");
                        
                        // Notification de nouvelle connexion à tous les clients connectés
                        $this->say("RENVOI DU MESSAGE A TOUS LES CLIENTS");
                        foreach ($this->users as $utilisateur) {
                            $this->send($utilisateur->socket, $msg);
                            $this->send($utilisateur->socket, "$login s'est connecté !");
                        }
                   } else { // MDP erroné
                       
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
