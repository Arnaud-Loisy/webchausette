<!doctype html>
<html lang="fr">
	<head>
		
		<title>WebCat</title>
		<meta charset="utf-8">
		<link rel="stylesheet" href="styles.css" type="text/css">

	</head>
	<body>
		<?php
		session_start();
		echo '<div id="login">' . $_POST["login"] . '</div>';
		echo '<div id="login">' . $_POST["mdp"] . '</div>';
		?>
		<div class="wrap">
			<div name="users" id="users">
				<span class="checkB">
					<input type="radio" id="global" name="users" value="global" checked="checked" />
					<label for="global">global</label></span>
				<br>
			</div>
			<form>
				<p id="readyState">
					readyState : <span
					id="rs">&nbsp;</span>
				</p>
				<p>
					<strong>Journal</strong>
				</p>
				<div name="log" id="log">

				</div>
				<p>
					<label for="texte">Envoyer</label>
					<input type="text" name="texte" id="texte">
					<input type="submit" value="OK" id="valid">
				</p>
				<p>
					<strong>Commandes</strong>
				</p>
				<ul>
					<li>
						<kbd>/list</kbd> : demander la date
					</li>
					<li>
						<kbd>/quit</kbd> : quitter
					</li>
				</ul>
			</form>
		</div>
		<script type="text/javascript" src="script.js"></script>

	</body>
</html>