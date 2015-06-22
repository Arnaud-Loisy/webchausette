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
        $parsedMsg = json_decode($msg, true);
        $type = $parsedMsg["type"];


        // Connexion à la bdd
        //$link = mysqli_connect('192.168.0.2', 'projet', 'projet', 'webchaussette');
        $link = mysqli_connect('www.remi-boyer.fr', 'projet', 'projet', 'webchaussette');

        // Traitement fonction du type du message
        $this->say("> Type du message reçu : " . $type);
        $this->say("> Id socket : $user->socket");
        switch ($type) {
            case "connect":
                // Récupération des champs
                $login = $parsedMsg["login"];
                $pwd = $parsedMsg["pwd"];
                // Construction de la requête
                $query = "SELECT mdp FROM Utilisateur WHERE nom='$login';";
                $result = mysqli_query($link, $query);

                // Si le nom d'utilisateur existe
                if ($arr = mysqli_fetch_array($result)) {
                    // Si le mot de passe est OK
                    if ($arr["mdp"] == $pwd) {

                        // Si un socket est déjà ouvert pour cet utilisateur, fermeture de ce dernier
                        $this->say("Verification si $login est deja connecté");
                        $socketToDisconnect = $this->assoUsersSockets[$login];
                        if ($socketToDisconnect != null) {
                            $this->say("$login deja connecte sur le socket $socketToDisconnect");
                            $this->disconnect($socketToDisconnect);
                            $this->say("$login est désormais deconnecte !");
                        }
                        /* $query = "SELECT idSocket FROM Utilisateur WHERE nom='$login';";
                          $this->say("Requete envoyée = $query");
                          $result = mysqli_query($link, $query);
                          $arr = mysqli_fetch_array($result);
                          $ancienSocket=$arr['idSocket'];

                          $ancienUser=$this->getuserbysocket($ancienSocket);

                         */
                        /* if($u){
                          $this->say("$login était deje connecte ailleurs sur le socket $ancienSocket");
                          // $this->disconnect(8);
                          //$this->disconnect($assoUsersSockets[);
                          // $this->disconnect($ancienSocket);
                          } */
                        //if($assoUsersSockets[$user->socket]!=null){
                        //  $this->disconnect($ancienUser->socket);
                        // Ajout de l'identifiant de socket de l'utilisateur dans la BDD
                        $this->assoUsersSockets[$login] = $user->socket;
                        $this->say("> Idsocket ajouté pour $login");
                        //$idSocketUser = $user->socket;
                        //$query = "UPDATE Utilisateur SET idSocket='$idSocketUser' WHERE nom='$login';";
                        //$this->say("AAAAAAAAAAAAAAAAAAAAA : $query");
                        //$result = mysqli_query($link, $query);
                        // Envoi de la liste des utilisateurs déjà connectés
                        // Pour chaque utilisateur connecté
                        $this->say("RENVOI DE LA LISTE DES PERSONNES CONNECTEES");
                        foreach ($this->users as $utilisateur) {

                            // récupérer son login
                            //$socketCourant = $utilisateur->socket;
                            //$query = "SELECT nom FROM Utilisateur WHERE idSocket='$socketCourant'";
                            //$this->say("Requete envoyée : $query");
                            //$result = mysqli_query($link, $query);
                            //$arr = mysqli_fetch_array($result);
                            //$this->say($arr['nom']);

                            if ($connectedUser = array_search($utilisateur->socket, $this->assoUsersSockets)) {
                                $this->say("$connectedUser fait parti de la liste");
                                // formater le message d'envoi
                                //$connectedUserMsg = json_encode("{'type':'connect','login':'" + $connectedUser + "','pwd':''}");
                                $connectedUserMsgTMP = array('type' => "connect", 'login' => $connectedUser, 'pwd' => "");
                                $connectedUserMsg = json_encode($connectedUserMsgTMP);
                                $this->say($connectedUserMsg);
                                // envoyer au mec nouvellement connecté
                                $this->send($user->socket, $connectedUserMsg);
                            }
                        }

                        // Notification de nouvelle connexion à tous les clients connectés
                        $this->say("RENVOI DU MESSAGE DE CONNEXION A TOUS LES CLIENTS");
                        foreach ($this->users as $utilisateur) {
                            if ($utilisateur->socket != $user->socket) {

                                $this->send($utilisateur->socket, $msg);
                                //$this->send($utilisateur->socket, "$login s'est connecté !");
                            }
                        }
                    } else { // MDP erroné
                        $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => 'global', 'dest' => '', 'message' => "Erreur d'authentification");
                        $errorMsg = json_encode($errorMsgTMP);
                        $this->say("$errorMsg");
                        $this->send($utilisateur->socket, $errorMsg);
                        $this->disconnect($user->socket);
                    }
                } else { // Login inconnu
                   $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => 'global', 'dest' => '', 'message' => "Erreur d'authentification");
                        $disconnectMsg = json_encode($errorMsgTMP);
                        $this->say($errorMsg);
                        $this->send($utilisateur->socket, $errorMsg);
                        $this->disconnect($user->socket);
                }
                //$this->send($user->socket, $msg);
                //$this->send($user->socket, "Bravo $login, tu t'es bien connecté !");
                $this->say("FIN TRAITEMENT CONNECT");
                break;



            case "disconnect":
                $this->say("DEBUT TRAITEMENT DISCONNECT");
                // Récupération des champs
                $login = $parsedMsg["login"];

                if ($this->checkSocket($login, $user->socket)) {
                    // Notification de deconnexion à tous les clients connectés
                    $this->say("RENVOI DU MESSAGE DE DECONNEXION");
                    foreach ($this->users as $utilisateur) {
                        $disconnectMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => 'global', 'dest' => '', 'message' => "$login s'est deconnecte");
                        $disconnectMsg = json_encode($disconnectMsgTMP);
                        $this->say("$disconnectMsg");

                        $this->send($utilisateur->socket, $disconnectMsg);
                    }
                    // Suppression du socket dans la table
                    $this->assoUsersSockets[$login];

                    // Déconnexion de l'utilisateur
                    $this->disconnect($user->socket);
                }
                $this->say("FIN TRAITEMENT DISCONNECT");
                break;




            // envoi d'un message
            case "message":
                $from = $parsedMsg["from"];
                $dest = $parsedMsg["dest"];
                $contenu = $parsedMsg["message"];
                $salon = $parsedMsg["salon"];

                // si origine du message valide
                /* if ($this->checkSocket($from, $user->socket)) {
                  // SI MESSAGE PRIVE
                  if () {

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

    function checkSocket($login, $socket) {
        if ($this->assoUsersSockets[$login] == $socket)
            return true;
        else
            return false;
    }

}

$master = new ChatBot("localhost", 1337);
