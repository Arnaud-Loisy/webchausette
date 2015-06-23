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
                $query = "SELECT * FROM Utilisateur WHERE nom='$login';";
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

                        // Ajout de l'utilisateur dans la base locale
                        $this->assoUsersSockets[$login] = $user->socket;
                        $this->say("> Idsocket ajouté pour $login");

                        // Envoi de la liste des utilisateurs déjà connectés
                        // Pour chaque utilisateur connecté
                        $this->say("RENVOI DE LA LISTE DES PERSONNES CONNECTEES");
                        foreach ($this->users as $utilisateur) {

                            // Si le socket est associé à un login dans la base locale
                            if ($connectedUser = array_search($utilisateur->socket, $this->assoUsersSockets)) {
                                $this->say("$connectedUser fait parti de la liste");
                                // formater le message d'envoi
                                $connectedUserMsgTMP = array('type' => "connect", 'login' => $connectedUser, 'pwd' => "", 'admin' => $arr["admin"]);
                                $connectedUserMsg = json_encode($connectedUserMsgTMP);
                                $this->say($connectedUserMsg);

                                // envoyer la confirmation de connexion
                                $this->send($user->socket, $connectedUserMsg);
                            }
                        }

                        // Notification de nouvelle connexion à tous les clients connectés
                        $this->say("RENVOI DU MESSAGE DE CONNEXION A TOUS LES CLIENTS");
                        foreach ($this->users as $utilisateur) {
                            if ($utilisateur->socket != $user->socket) {

                                $this->send($utilisateur->socket, $msg);

                                $connectMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => 'global', 'dest' => '', 'message' => "$login vient de se connecter");
                                $connectMsg = json_encode($connectMsgTMP);
                                $this->say("$connectMsg");
                                $this->send($utilisateur->socket, $connectMsg);
                                //$this->send($utilisateur->socket, "$login s'est connecté !");
                            }
                        }

                        // Récupération de l'historique
                        $this->say("ENVOI DE L'HISTORIQUE");
                        $query = "SELECT * FROM Message WHERE idSalon=0";
                        $result1 = mysqli_query($link, $query);
                        while ($arr1 = mysqli_fetch_array($result1)) {
                            $idMsg = $arr1['idMessage'];
                            $idFrom = $arr1["idUtilisateur"];
                            $contenu = $arr1['contenu'];
                            $this->say("Message historisé à traiter numéro $idMsg");

                            // Récupération de l'id BDD correspondant au login  
                            $query = "SELECT nom FROM Utilisateur WHERE idUtilisateur='$idFrom';";
                            $result = mysqli_query($link, $query);
                            $arr = mysqli_fetch_array($result);
                            $loginMsg = $arr['nom'];

                            // Construction du message
                            $msgTMP = array('type' => 'message', 'from' => "$loginMsg", 'salon' => "global", 'dest' => '', 'message' => "$contenu");
                            $msg = json_encode($msgTMP);

                            // Envoi du message
                            $this->say($msg);
                            $this->send($user->socket, $msg);
                            $this->say("Fin traitement Message historisé à traiter numéro $idMsg ");
                        }
                    } else { // MDP erroné
                        $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Erreur d'authentification");
                        $errorMsg = json_encode($errorMsgTMP);
                        $this->say("$errorMsg");
                        $this->send($user->socket, $errorMsg);
                        $this->disconnect($user->socket);
                    }
                } else { // Login inconnu
                    $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Erreur d'authentification");
                    $errorMsg = json_encode($errorMsgTMP);
                    $this->say($errorMsg);
                    $this->send($utilisateur->socket, $errorMsg);
                    $this->disconnect($user->socket);
                }
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
                        $this->send($utilisateur->socket, $msg);
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
                $this->say("DEBUT TRAITEMENT MESSAGE");

                $from = $parsedMsg["from"];
                $dest = $parsedMsg["dest"];
                $salon = $parsedMsg["salon"];
                $contenu = $parsedMsg["message"];

                // Récupération de l'id BDD correspondant au login  
                $query = "SELECT idUtilisateur FROM Utilisateur WHERE nom='$from';";
                $this->say("Recherche $from dans BD : $query");
                $result = mysqli_query($link, $query);
                $arr = mysqli_fetch_array($result);
                $this->say("idFROM = " + $arr['idUtilisateur']);
                $idFrom = $arr["idUtilisateur"];

                // si origine du message valide
                if ($this->checkSocket($from, $user->socket)) {
                    // SI MESSAGE PRIVE
                    if ($dest) {
                        $this->say("MESSAGE PRIVE");
                        // lui transmettre le message
                        $destSocket = $this->assoUsersSockets[$dest];
                        if ($this->getuserbysocket($destSocket)) {
                            $this->send($destSocket, $msg);
                        }
                    } else { // SI MESSAGE PUBLIC
                        // si le salon est global
                        if ($salon == "global") {
                            $this->say("MESSAGE GLOBAL");
                            // Ajout du message dans la bdd
                            $query = "INSERT INTO Message (`contenu`, `idSalon`, `idUtilisateur`) VALUES ('$contenu', 0, $idFrom)";
                            $this->say("Ecriture MESSAGE GLOBAL BD : $query");
                            $result = mysqli_query($link, $query);

                            // envoi à tous les autres clients
                            foreach ($this->users as $utilisateur) {
                                if ($utilisateur->socket != $user->socket)
                                    $this->send($utilisateur->socket, $msg);
                            }
                        } else { // SI MESSAGE SALON
                            $this->say("MESSAGE SALON $salon");
                            // Ajout du message dans la bdd
                            $query = "INSERT INTO Message (`contenu`, `idSalon`, `idUtilisateur`) VALUES ('$contenu', 0, $idFrom)";
                            $result = mysqli_query($link, $query);

                            // Récupérer la liste des utilisateurs du salon
                            $this->say("Recup des utilisateurs du salon $salon");
                            $query = "SELECT nom FROM Utilisateur WHERE idSalon='$salon';";
                            $result = mysqli_query($link, $query);
                            while ($loginCourant = mysqli_fetch_array($result)) {
                                $socketCourante = $this->assoUsersSockets[$loginCourant];
                                if ($socketCourante->socket != $user->socket)
                                    $this->send($socketCourante, $msg);
                            }
                        }
                    }
                }
                $this->say("FIN TRAITEMENT MESSAGE");
                break;


            case "open":
                $this->say("DEBUT TRAITEMENT OPEN");

                // Récup des champs
                $login = $parsedMsg["from"];
                if ($parsedMsg["salon"] == 0)
                    $salon = 'global';
                else
                    $salon = $parsedMsg["salon"];


                // Récupération de l'id BDD correspondant au login  
                $query = "SELECT admin FROM Utilisateur WHERE nom='$login';";
                $result = mysqli_query($link, $query);
                $arr = mysqli_fetch_array($result);
                $admin = $arr["admin"];




                if ($this->checkSocket($login, $user->socket)) {

                    if ($admin == 1) {
                        // Récupération de l'état du salon
                        $query = "SELECT ouvert FROM Salon WHERE idSalon='$salon'";
                        $result = mysqli_query($link, $query);
                        $arr = mysqli_fetch_array($result);
                        $etatSalon = $arr["ouvert"];

                        // Si le salon est fermé
                        if ($etatSalon == 0) {
                            // Modification du statut du salon dans la BD
                            $query = "UPDATE Salon SET ouvert = '1' WHERE idSalon = '$salon'";
                            $this->say("Modification de l'état du salon $salon : $query");
                            $result = mysqli_query($link, $query);

                            // Construction du message d'ouverture
                            $msgOpenTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => 'global', 'dest' => "", 'message' => "Le salon $salon est désormais ouvert !");
                            $msgOpen = json_encode($msgOpenTMP);
                            $this->say($msgOpen);

                            // envoi à tous clients
                            foreach ($this->users as $utilisateur) {
                                $this->send($utilisateur->socket, $msgOpen);
                            }
                        } else { // Si le salon est déjà ouvert
                            $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Le salon $salon est déjà ouvert !");
                            $disconnectMsg = json_encode($errorMsgTMP);
                            $this->say($errorMsg);
                            $this->send($user->socket, $errorMsg);
                        }
                    } else {
                        $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Vous devez être administrateur pour effectuer cette opération.");
                        $disconnectMsg = json_encode($errorMsgTMP);
                        $this->say($errorMsg);
                        $this->send($user->socket, $errorMsg);
                    }
                }

                //$this->send($user->socket, "Open bien reçu ");
                $this->say("FIN TRAITEMENT OPEN");
                break;
            case "close":
                $this->say("DEBUT TRAITEMENT CLOSE");

                // Récup des champs
                $login = $parsedMsg["from"];
                if ($parsedMsg["salon"] == 0)
                    $salon = 'global';
                else
                    $salon = $parsedMsg["salon"];


                // Récupération de l'id BDD correspondant au login  
                $query = "SELECT admin FROM Utilisateur WHERE nom='$login';";
                $result = mysqli_query($link, $query);
                $arr = mysqli_fetch_array($result);
                $admin = $arr["admin"];




                if ($this->checkSocket($login, $user->socket)) {

                    if ($admin == 1) {
                        // Récupération de l'état du salon
                        $query = "SELECT ouvert FROM Salon WHERE idSalon='$salon'";
                        $result = mysqli_query($link, $query);
                        $arr = mysqli_fetch_array($result);
                        $etatSalon = $arr["ouvert"];

                        // Si le salon est ouvert
                        if ($etatSalon == 1) {
                            // Modification du statut du salon dans la BD
                            $query = "UPDATE Salon SET ouvert = '0' WHERE idSalon = '$salon'";
                            $this->say("Modification de l'état du salon $salon : $query");
                            $result = mysqli_query($link, $query);

                            // Construction du message de fermeture
                            $msgOpenTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => 'global', 'dest' => "", 'message' => "Le salon $salon est désormais fermé !");
                            $msgOpen = json_encode($msgOpenTMP);
                            $this->say($msgOpen);

                            // envoi à tous clients
                            foreach ($this->users as $utilisateur) {
                                $this->send($utilisateur->socket, $msgOpen);
                            }
                        } else {
                            $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Le salon $salon est déjà fermé !");
                            $disconnectMsg = json_encode($errorMsgTMP);
                            $this->say($errorMsg);
                            $this->send($user->socket, $errorMsg);
                        }
                    } else {
                        $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Vous devez être administrateur pour effectuer cette opération.");
                        $disconnectMsg = json_encode($errorMsgTMP);
                        $this->say($errorMsg);
                        $this->send($user->socket, $errorMsg);
                    }
                }

                $this->say("FIN TRAITEMENT CLOSE");
                break;

            case "join":
                $this->say("DEBUT TRAITEMENT JOIN");

                // Récupération des champs
                $login = $parsedMsg["from"];
                if ($parsedMsg["salon"] == 'global')
                    $salon = 0;
                else
                    $salon = $parsedMsg["salon"];

                // Récupération de l'état du salon
                $query = "SELECT ouvert FROM Salon WHERE idSalon='$salon'";
                $result = mysqli_query($link, $query);
                $arr = mysqli_fetch_array($result);
                $etatSalon = $arr["ouvert"];

                // Si le salon est ouvert
                if ($etatSalon == 1) {
                    // Modification du statut de l'utilisateur dans la DB
                    $query = "UPDATE Utilisateur SET idSalon = '$salon' WHERE nom = '$login'";
                    $this->say("Modification du salon de l'utilisateur $salon vers $salon : $query");
                    $result = mysqli_query($link, $query);


                    // Renvoi de la notif de "join" à l'utilisateur
                    $this->send($user->socket, $msg);

                    // Renvoi de l'historique du salon
                    // Récupération de l'historique
                    $this->say("ENVOI DE L'HISTORIQUE");
                    $query = "SELECT * FROM Message WHERE idSalon=$salon";
                    $result1 = mysqli_query($link, $query);
                    while ($arr1 = mysqli_fetch_array($result1)) {
                        $idMsg = $arr1['idMessage'];
                        $idFrom = $arr1["idUtilisateur"];
                        $contenu = $arr1['contenu'];
                        $this->say("Message historisé à traiter numéro $idMsg");

                        // Récupération de l'id BDD correspondant au login  
                        $query = "SELECT nom FROM Utilisateur WHERE idUtilisateur='$idFrom';";
                        $result = mysqli_query($link, $query);
                        $arr = mysqli_fetch_array($result);
                        $loginMsg = $arr['nom'];

                        // Construction du message
                        $msgTMP = array('type' => 'message', 'from' => "$loginMsg", 'salon' => "global", 'dest' => '', 'message' => "$contenu");
                        $msg = json_encode($msgTMP);

                        // Envoi du message
                        $this->say($msg);
                        $this->send($user->socket, $msg);
                        $this->say("Fin traitement Message historisé à traiter numéro $idMsg ");
                    }

                    // Récupérer la liste des utilisateurs du salon
                    $this->say("Recup des utilisateurs du salon $salon");
                    $query = "SELECT nom FROM Utilisateur WHERE idSalon='$salon';";
                    $result = mysqli_query($link, $query);

                    // Envoyer le message de "join" à tous les membres
                    while ($loginCourant = mysqli_fetch_array($result)) {
                        $socketCourante = $this->assoUsersSockets[$loginCourant];
                        if ($socketCourante->socket != $user->socket) {
                            $this->send($socketCourante, $msg);

                            $joinMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => "$salon", 'dest' => "", 'message' => "$login a rejoint le salon $salon");
                            $joinMsg = json_encode($joinMsgTMP);
                            $this->say($joinMsg);
                            $this->send($user->socket, $joinMsg);
                        }
                    }
                } else { // Si le salon est ouvert
                    $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Le salon $salon est déjà fermé !");
                    $disconnectMsg = json_encode($errorMsgTMP);
                    $this->say($errorMsg);
                    $this->send($user->socket, $errorMsg);
                }
                $this->say("FIN TRAITEMENT JOIN");
                break;
            case "quit":
                $this->say("DEBUT TRAITEMENT QUIT");

                // Récupération des champs
                $login = $parsedMsg["from"];
                if ($parsedMsg["salon"] == 'global')
                    $salon = 0;
                else
                    $salon = $parsedMsg["salon"];

                // Récupération de l'état du salon
                $query = "SELECT ouvert FROM Salon WHERE idSalon='$salon'";
                $result = mysqli_query($link, $query);
                $arr = mysqli_fetch_array($result);
                $etatSalon = $arr["ouvert"];

                // Si le salon est ouvert
                if ($etatSalon == 1) {
                    // Modification du salon de l'utilisateur dans al DB
                    $query = "UPDATE Utilisateur SET idSalon = '$salon' WHERE nom = '$login'";
                    $this->say("Modification du salon de l'utilisateur $salon vers $salon : $query");
                    $result = mysqli_query($link, $query);




                    // Récupérer la liste des utilisateurs du salon
                    $this->say("Recup des utilisateurs du salon $salon");
                    $query = "SELECT nom FROM Utilisateur WHERE idSalon='$salon';";
                    $result = mysqli_query($link, $query);

                    // Envoyer le message de "quit" à tous les membres
                    while ($loginCourant = mysqli_fetch_array($result)) {
                        $socketCourante = $this->assoUsersSockets[$loginCourant];

                        $this->send($socketCourante, $msg);

                        $quitMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => "$salon", 'dest' => "", 'message' => "$login a quitté le salon $salon");
                        $quitMsg = json_encode($quitMsgTMP);
                        $this->say($quitMsg);
                        $this->send($user->socket, $quitMsg);
                    }
                } else { // Si le salon est ouvert
                    $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Le salon $salon est déjà fermé !");
                    $disconnectMsg = json_encode($errorMsgTMP);
                    $this->say($errorMsg);
                    $this->send($user->socket, $errorMsg);
                }



                $this->say("FIN TRAITEMENT QUIT");
                break;
            default:
                $errorMsgTMP = array('type' => 'message', 'from' => 'Serveur', 'salon' => '', 'dest' => "$login", 'message' => "Tu m'as envoyé un message pas formaté : $msg");
                $disconnectMsg = json_encode($errorMsgTMP);
                $this->say($errorMsg);
                $this->send($user->socket, $errorMsg);
                break;
        }
    }

    function checkSocket($login, $socket) {
        if ($this->assoUsersSockets[$login] == $socket) {
            $this->say("CheckSocket OK !");
            return true;
        } else {
            $this->say("CheckSocket problèmatique... !");
            return false;
        }
    }

}

$master = new ChatBot("localhost", 1337);
