<!DOCTYPE html>
<html lang="fi"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
    <link rel="stylesheet" href="../teema.css">
    <title>Kuntospurtti – Ylläpito</title>
</head>
<body>

<header>
    <h1>Kuntokeskus Kuntospurtti</h1>
    <p>Hyvinvointia arkeen</p>
</header>

<nav>
    <a href="../index.html">Etusivu</a>
    <a href="../tunnit.html">Ohjatut tunnit</a>
    <a href="../henkilokunta.html">Henkilökunta</a>
    <a href="../yhteystiedot.html">Yhteystiedot</a>
</nav>

<section>

<div id="vastausLomake">
	<h3>Vastaa viestiin</h3><br>

	<p>Nimi:<span id="vastausLomake_nimi"></span></p>
    <p>Email osoite: <span id="vastausLomake_email"></span></p>
    <p>Viesti:<br><span id="vastausLomake_viesti"></span></p>

    <textarea id="vastausLomake_vastaa" placeholder="Kirjoita vastaus..."></textarea><br>

	<div class="vastausLomake_buttons">
		<button onclick="suljeVastausLomake()">Sulje</button>
    	<button onclick="lahetaVastaus()">Lähetä</button><br>
	</div>
</div>

<?php
	function hsc($merkkijono) {
    	return htmlspecialchars($merkkijono, ENT_QUOTES, 'UTF-8');
	}

	require_once('tietokanta.php');


	if (isset($_POST['lahetaEmail'])) {

		$email = trim($_POST['email']);

		if (preg_match('/[\r\n]/', $email)) {
		    die("Virheellinen sähköposti!");
		}

		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		    die("Virheellinen sähköposti!");
		}

		$vastaus = str_replace(["\r"], '', $_POST['vastaus']);
		$vastaus = substr($vastaus, 0, 5000);

		$otsikko = "Vastaus lomakkeelta";

		$otsikot  = "Content-Type: text/plain; charset=UTF-8\r\n";
		$domain = $_SERVER['SERVER_NAME'];
		$from = "admin@" . $domain;

		$otsikot .= "From: $from\r\n";
		$otsikot .= "Reply-To: $from\r\n";

		if (mail($email, $otsikko, $vastaus, $otsikot)) {
		    echo "Sähköposti lähetetty!";
		} else {
		    echo "Virhe sähköpostin lähetyksessä.";
		}
	}


	if (isset($_GET['poista'])) {
    	$tunniste = intval($_GET['poista']);
    	$lauseke = $sql->prepare("DELETE FROM Kuntokeskus_viestit WHERE id = ?");
    	$lauseke->execute([$tunniste]);
	}


	if (isset($_POST['poista_valitut']) && !empty($_POST['valitut'])) {
    	$tunnisteet = $_POST['valitut']; 

		$tunnisteidenMaara  = count($tunnisteet);

   	 	$paikkaMerkit = implode(',', array_fill(0, $tunnisteidenMaara, '?'));

    	$lauseke = $sql->prepare("DELETE FROM Kuntokeskus_viestit 
								WHERE id IN ($paikkaMerkit)");

    	$tyypit = str_repeat('i', $tunnisteidenMaara);
    	$lauseke->bind_param($tyypit, ...$tunnisteet);

    	$lauseke->execute();
	}


	$tuloksetPerSivu = 10;

	$sivu = isset($_GET['sivu']) ? intval($_GET['sivu']) : 1;

	if ($sivu < 1) {
		$sivu = 1;
	}

	$riviMaara = $sql->query("SELECT COUNT(*) AS kokonaisMaara 
							FROM Kuntokeskus_viestit")->fetch_assoc()['kokonaisMaara'];


	$sivuMaara = ceil($riviMaara / $tuloksetPerSivu);

	if ($sivu > $sivuMaara && $sivuMaara > 0) {
		$sivu = $sivuMaara;
	}

	$kohta = ($sivu - 1) * $tuloksetPerSivu;

	$lauseke = $sql->prepare("
   		SELECT *
    	FROM Kuntokeskus_viestit
    	ORDER BY id DESC
    	LIMIT ?, ?
	");

	$lauseke->bind_param("ii", $kohta, $tuloksetPerSivu);
	$lauseke->execute();
	$tulos = $lauseke->get_result();


	if ($tulos->num_rows === 0) {
   		echo 'Ei viestejä lomakkeelta';
	} else {
		echo '<h2>Viestit</h2>';

		echo "<div>";

		if ($sivu > 1) {
			echo "<a href='?sivu=" . ($sivu - 1) . "'>Edellinen</a> ";
		}

		for ($i = 1; $i <= $sivuMaara; $i++) {
			if ($i == $sivu) {
				echo "<strong>$i</strong> ";
			} else {
				echo "<a href='?sivu=$i'>$i</a> ";
			}
		}

		if ($sivu < $sivuMaara) {
			echo "<a href='?sivu=" . ($sivu + 1) . "'>Seuraava</a>";
		}

		echo "</div><br>";
		echo '<form method="POST">';

		foreach ($tulos as $rivi) {
			echo 'Nimi: ' . hsc($rivi['name']);
			echo ' <a href="#" onclick="avaaVastausLomake('
				. hsc(json_encode($rivi['name'])) . ', '
     			. hsc(json_encode($rivi['email'])) . ', '
     			. hsc(json_encode($rivi['message']))
     			. ');">Vastaa</a>, ';
			echo '<a href="#" onclick="poista(' . intval($rivi['id']) . ');">Poista</a>, ';
			echo '<input type="checkbox" name="valitut[]" value="' . intval($rivi['id']) . '">';
    		echo '<div>Email osoite: ' . hsc($rivi['email']) . '</div><br>';
			echo '<div>Viesti: ' . hsc($rivi['message']) . '</div><br>';
		}

		echo '<button type="submit" name="poista_valitut" onclick="return confirm(\'Haluatko varmasti poistaa valitut viestit?\')">Poista valitut</button>';
		echo '</form>';
	}
?>

</section>
<footer>
    Kuntokeskus Kuntospurtti © 2025
</footer>

<script>
	function avaaVastausLomake(nimi, email, viesti) {
		document.getElementById('vastausLomake_nimi').innerText = nimi;
		document.getElementById('vastausLomake_email').innerText = email;
   	 	document.getElementById('vastausLomake_viesti').innerText = viesti;

    	document.getElementById('vastausLomake').style.display = 'flex';
	}

	function suljeVastausLomake() {
    	document.getElementById('vastausLomake').style.display = 'none';
	}

	function lahetaVastaus() {
		const email = document.getElementById('vastausLomake_email').innerText;
		const vastaus = document.getElementById('vastausLomake_vastaa').value;

    	let lomakeData = new FormData();
    	lomakeData.append("email", email);
    	lomakeData.append("vastaus", vastaus);
    	lomakeData.append("lahetaEmail", "1");

		fetch("index.php", {
		    method: "POST",
		    body: lomakeData
		})
		.then(vastaus => vastaus.text())
		.then(tulos => {
			alert("Email lähetetty");
		    suljeVastausLomake();
		});
	}

	function poista(id) {
    	if (confirm("Haluatko varmasti poistaa viestin?")) {
        	window.location.href = "?poista=" + id;
    	}
	}

	suljeVastausLomake();
</script>

</body></html>
