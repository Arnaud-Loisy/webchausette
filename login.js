window.onload = function  () {

 ws = null;
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
				  window.location.href="chat.html" ;
			}
			
		default:
		console.log(msg);

		}
	};
	
} else {
	alert("Ce navigateur ne supporte pas Web Sockets");
};
};
function formlog (e) {
  // Evénement submit du formulaire
	//document.getElementsByTagName('form')[0].onsubmit = function(e) {
		
		console.log("e="+e);
		login=document.getElementById('login');
		console.log("login="+login.value);
		var mdp = document.getElementById('mdp');
		console.log("mdp="+mdp.value);
		var msg_connection = {
			type : "connect",
			login : login.value,
			pwd : mdp.value
		};


		// Envoi du message JSON
		ws.send(JSON.stringify(msg_connection));
		console.log(JSON.stringify(msg_connection));

		
		// Empêche de valider le formulaire
		//e.preventDefault();
	//};
}
