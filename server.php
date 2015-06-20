#!/usr/bin/php -q
<?php
include "fonctions_bdd.php";
global $link;

// Execution en ligne de commande : php -q <nomdufichier>.php
// Inclusion de la librairie phpwebsocket
require "websocket.class.php";

// Extension de WebSocket
class ChatBot extends WebSocket {

    function process($user, $msg) {
        $this->say("< " . $msg);
        
        // Parsage du message pour récup le bon champs
        // 
        // identifier le premier champs, qui détermine la fonction a assurer
        switch ("MESSAGE") {
            case "CONNECT":
                break;
            case "DISCONNECT":
                break;
            case "CLOSE":
                break;
            // envoi d'un message public
            case "MESSAGE":
                /*// SI MESSAGE PRIVE
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
                }*/
                    foreach ( $this->users as $utilisateur ){
                        $this->send($utilisateur->socket,$msg);
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
