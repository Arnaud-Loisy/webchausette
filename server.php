#!/usr/bin/php -q
<?php
include "fonctions_bdd.php";

// Execution en ligne de commande : php -q <nomdufichier>.php
// Inclusion de la librairie phpwebsocket
require "websocket.class.php";

// Extension de WebSocket
class ChatBot extends WebSocket {

    function process($user, $msg) {
        $link = connectBDD();
        $this->say("< " . $msg);

        // Parsage du message pour récup le bon champs
        // 
        // identifier le premier champs, qui détermine la fonction a assurer
        switch ($msg) {
            case "DISCONNECT":
                break;
            case "CONNECT":

                $query = requeteMdpUtilisateur($nomUtilisateur);
                $result = mysqli_query($link, $query);

                // Si le nom d'utilisateur existe
                if ($arr = mysqli_fetch_array($result) != NULL) {

                    // Si le mot de passe est OK
                    if ($arr["mdp"] == $mdpTransmis) {
                        // Ajouter l'identifiant de socket de l'utilisateur dans la BDD
                        $query = requeteAjoutSocketUtilisateur($nomUtilisateur, $idSocket);
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
                }





                break;
            case "CLOSE":
                break;
            // envoi d'un message public
            case "MESSAGE": // broadcast pour l'instant
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
