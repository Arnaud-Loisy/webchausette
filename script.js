var ws = null;
var user_nb = 0;
// Création d'un nouveau socket
// (Pour Mozilla < 11 avec version préfixée)
if ('MozWebSocket' in window) {
	ws = new MozWebSocket("ws://127.0.0.1:1337/.../bot.php");
} else if ('WebSocket' in window) {
	ws = new WebSocket("ws://127.0.0.1:1337/.../bot.php");
}
if ( typeof ws !== 'undefined') {
	// Indication de l'état
	var rs = document.getElementById('rs');
	// Lors de l'ouverture de connexion
	ws.onopen = function() {
		log("Socket ouvert");
		//window.login = '<?=$_POST['login']?>';
		//window.pwd = '<?=$_POST['pwd']?>';
		var login = document.getElementById('login');
		var pwd = document.getElementById('pwd');
		log.console("login.value :"+login.value);
		log.console("pwd.value :"+pwd.value);
		var msg_connection = {
			type : "connect",
			login : login.value,
			pwd : pwd.value

		};

		// Envoi du message JSON
		ws.send(JSON.stringify(msg_connection));
		console.log(JSON.stringify(msg_connection));

		rs.innerHTML = this.readyState;
	};
	// Lors de la réception d'un message
	ws.onmessage = function(e) {

		var msg = JSON.parse(e.data);
		switch(msg.type) {
		case "connect":
			//alert("avant ajout user");
			// Affichage de l'user qui se connecte
			document.getElementById('users').innerHTML += '<span class="checkB"><input type="radio" id="'+user_nb+'"  name="users" value="' + msg.login + '" />	<label for="' + user_nb + '">' + msg.login + '</label></span><br>\n';
			user_nb++;
			break;
		case "message":
			// Ajout au journal du contenu du message
			log(msg.from + "< " + msg.message);
			break;
		default:

		}

		rs.innerHTML = this.readyState;
	};
	// Lors d'une erreur de connexion
	ws.onerror = function(e) {
		log("Erreur de connexion");
		rs.innerHTML = this.readyState;
	};
	// Lors de la fermeture de connexion
	ws.onclose = function(e) {
		if (e.wasClean) {
			log("Socket fermé proprement");
		} else {
			log("Socket fermé");
			if (e.reason)
				log(e.reason);
		}
		rs.innerHTML = this.readyState;
	};
	// Evénement submit du formulaire
	document.getElementsByTagName('form')[0].onsubmit = function(e) {
		var dest = "";
		var salon = "";
		var texte = document.getElementById('texte');

		if (document.getElementById('global').checked) {
			salon = document.getElementById('global').value;
		} else {
			if (document.getElementById('0').checked) {
				dest = document.getElementById('0').value;
			} else {
				if (document.getElementById('1').checked) {
					dest = document.getElementById('1').value;
				} else {
					if (document.getElementById('2').checked) {
						dest = document.getElementById('2').value;
					} else {
						if (document.getElementById('3').checked) {
							dest = document.getElementById('3').value;
						} else {
							if (document.getElementById('4').checked) {
								dest = document.getElementById('4').value;
							} else {
								if (document.getElementById('5').checked) {
									dest = document.getElementById('5').value;
								} else {
									if (document.getElementById('6').checked) {
										dest = document.getElementById('6').value;
									} else {
										if (document.getElementById('7').checked) {
											dest = document.getElementById('7').value;
										} else {
											if (document.getElementById('8').checked) {
												dest = document.getElementById('8').value;
											} else {

											}
										}
									}
								}
							}
						}
					}
				}
			}
		}

		console.log("dest=" + dest);
		console.log("salon=" + salon);
		
		var login = document.getElementById('login');
		var msg = {
			type : "message",
			from : "login",
			salon : salon,
			dest : dest,
			message : texte.value
		};

		// Envoi du message JSON
		ws.send(JSON.stringify(msg));
		console.log(JSON.stringify(msg));
		log(msg.from + "> " + texte.value);
		// Mise à zéro du champ et focus
		texte.focus();
		texte.value = '';
		// Empêche de valider le formulaire
		e.preventDefault();
	};
} else {
	alert("Ce navigateur ne supporte pas Web Sockets");
}
// Fonction d'ajout au journal
function log(txt) {
	document.getElementById('log').innerHTML += txt + "<br>\n";
}


function sleep(milliseconds) {
    var start = new Date().getTime();
    for (var i = 0; i < 1e7; i++) {
        if ((new Date().getTime() - start) > milliseconds) {
            break;
        }
    }
}