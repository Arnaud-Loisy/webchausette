#!/usr/bin/php -q
<?php


// Execution en ligne de commande : php -q <nomdufichier>.php
// Inclusion de la librairie phpwebsocket
require "websocket.class.php";

// Extension de WebSocket
class ChatBot extends WebSocket {
    

    function process($user, $msg) {
        $link = mysqli_connect('192.168.0.2', 'projet', 'projet', 'webchaussette');
        $this->say("< " . $msg);
        
        // Parsage du message pour récup le bon champs
        $parsedMsg=json_decode($msg,true);
        $type=$parsedMsg["type"];
        $login=$parsedMsg["login"];
        $pwd=$parsedMsg["pwd"];
        $this->say("< Type de message " .$type );
        
        // identifier le premier champs, qui détermine la fonction a assurer
        switch ($type) {
            case "disconnect":
                break;
            case "connect":
                //$query = "SELECT mdp FROM Utilisateur WHERE $login";
                //$result = mysqli_query($link, $query);
                //$this->say(mysqli_fetch_array($result));
                // Si le nom d'utilisateur existe
                /*if ($arr = mysqli_fetch_array($result)) {
                    // Si le mot de passe est OK
                    if ($arr["mdp"] == $pwd) {
                        // Ajouter l'identifiant de socket de l'utilisateur dans la BDD
                        $query = "UPDATE Utilisateur SET idSocket="+$user->socket+"WHERE idUtilisateur = $login;";
                        $result = mysqli_query($link, $query);
                        if (mysqli_fetch_array($result) != NULL) {
                            // Renvoyer le bon message au client
                            $this->send($utilisateur->socket, "Connexion OK !");
                            // Notifier tout le monde qu'il s'est connecté
                            foreach ($this->users as $utilisateur) {
                                $this->send($utilisateur->socket, "$nomUtilisateur s'est connecté");
                            }
                        }
                   }
                }  */
                $this->send($user->socket, $msg);
                $this->send($user->socket, "Bravo $login tu t'es bien connecté !");
                





                break;
            case "CLOSE":
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
            case "OPEN":
                break;
            case "CLOSE":
                break;
            default:
                $this->send($user->socket, "Pas compris !");
                break;
        }
    }

}

$master = new ChatBot("localhost", 1337);
