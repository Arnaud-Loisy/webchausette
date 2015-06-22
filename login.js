var ws = null;
// Création d'un nouveau socket
// (Pour Mozilla < 11 avec version préfixée)
if ('MozWebSocket' in window) {
	ws = new MozWebSocket("ws://127.0.0.1:1337/.../bot.php");
} else if ('WebSocket' in window) {
	ws = new WebSocket("ws://127.0.0.1:1337/.../bot.php");
}
if ( typeof ws !== 'undefined') {
	
	// Lors de l'ouverture de connexion
	ws.onopen = function() {
		
		
		
		
		
	};
	// Lors de la réception d'un message
	ws.onmessage = function(e) {

		var msg = JSON.parse(e.data);

		switch(msg.type) {
		case "connect":
			// Affichage de l'user qui se connecte
			if (msg.login==document.getElementById('login')){
				  document.location.href="chat.html" ;
			}
			
		default:

		}

		rs.innerHTML = this.readyState;
	};
	// Evénement submit du formulaire
	document.getElementsByTagName('form').onsubmit = function(e) {
		var texte = document.getElementById('texte');
		var login = document.getElementById('login');
		var mdp = document.getElementById('mdp');
		var msg_connection = {
			type : "connect",
			login : login,
			pwd : mdp,

		};


		// Envoi du message JSON
		ws.send(JSON.stringify(msg_connection));

		
		// Empêche de valider le formulaire
		e.preventDefault();
	};
} else {
	alert("Ce navigateur ne supporte pas Web Sockets");
}