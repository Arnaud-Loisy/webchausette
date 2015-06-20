var ws = null;
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
		var login = document.getElementById('login');
		var msg_connection = {
			type : "connect",
			login : "toto",
			pwd : "toto",

		};

		// Envoi du message JSON
		ws.send(JSON.stringify(msg_connection));

		rs.innerHTML = this.readyState;
	};
	// Lors de la réception d'un message
	ws.onmessage = function(e) {

		var msg = JSON.parse(e.data);

		switch(msg.type) {
		case "connect":
			// Affichage de l'user qui se connecte
			document.getElementById('users').innerHTML += '<span class="checkB"><input type="checkbox" id="' + msg.login + '"  name="' + msg.login + '" value=' + msg.login + ' />	<label for="' + msg.login + '">' + msg.login + '</label></span><br>\n';
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
		var texte = document.getElementById('texte');
		var login = document.getElementById('login');
		var msg = {
			type : "message",
			from : "login",
			salon : "",
			dest : "",
			message : texte.value
		};

		// Envoi du message JSON
		ws.send(JSON.stringify(msg));
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