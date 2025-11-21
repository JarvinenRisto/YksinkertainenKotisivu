<?php
	session_set_cookie_params([
	  'lifetime' => 0,
	  'path' => '/',
	  'secure' => true,
	  'httponly' => true,
	  'samesite' => 'Lax'
	]);
	session_start();

	if (!isset($_SESSION['csrf'])) {
	    $_SESSION['csrf'] = bin2hex(random_bytes(32));
	}	
?>

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
    <a href="../yhteystiedot.php">Yhteystiedot</a>
</nav>

<section>

<div id="vastausLomake">
	<h3>Vastaa viestiin</h3><br>

	<p>Nimi:<span id="vastausLomake_nimi"></span></p>
    <p>Email osoite: <span id="vastausLomake_email"></span></p>
    <p>Viesti:<br><span id="vastausLomake_viesti"></span></p>

    <textarea id="vastausLomake_vastaa" placeholder="Kirjoita vastaus..."></textarea><br>

	<div class="vastausLomake_buttons">
		<button type="button" onclick="suljeVastausLomake()">Sulje</button>
    	<button type="button" onclick="lahetaVastaus()">Lähetä</button><br>
	</div>
</div>

<?php
	require_once('tietokanta.php');

	function tarkista_CSRF() {
		if (!isset($_POST['csrf']) || $_POST['csrf'] !== $_SESSION['csrf']) {
			http_response_code(403);
    		die("Virheellinen CSRF!");
		}
	}

	if (isset($_POST['lahetaEmail'])) {
		tarkista_CSRF();
		
	    $nimi = trim($_POST['nimi'] ?? '');
	    $email = trim($_POST['email'] ?? '');
	    $viesti = trim($_POST['viesti'] ?? '');
	    $vastaus = trim($_POST['vastaus'] ?? '');
	
	    if (!preg_match('/^[a-zA-ZåäöÅÄÖ\s\-]{1,60}$/u', $nimi)) {
	        die("Virheellinen nimi.");
	    }
	
	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	        die("Virheellinen sähköposti!");
	    }
	
	    if (preg_match('/[\r\n]/', $email)) {
	        die("Virheellinen sähköposti!");
	    }
	
	    if (preg_match('/[\r\n]/', $nimi) || preg_match('/[\r\n]/', $viesti)) {
	        die("Virheellinen syöte.");
	    }
	
	    $teksti  = "Hei " . hsc($nimi) . ",\r\n\r\n";
	    $teksti .= "Vastaus viestiisi:\r\n";
	    $teksti .= hsc($vastaus) . "\r\n\r\n";
	    $teksti .= "Alkuperäinen viestisi oli:\r\n";
	    $teksti .= hsc($viesti) . "\r\n";
	
	    $kenelta = "admin@rosebasic.fi";
	
	    $otsikot  = "Content-Type: text/plain; charset=UTF-8\r\n";
	    $otsikot .= "From: $kenelta\r\n";
	    $otsikot .= "Reply-To: $kenelta\r\n";
	
	    if (mail($email, "Vastaus lomakkeelta", $teksti, $otsikot)) {
	        echo "Sähköposti lähetetty!";
	    } else {
	        echo "Virhe sähköpostin lähetyksessä.";
	    }
	}

	if (isset($_POST['poista'])) {
	    tarkista_CSRF();
	
	    $tunniste = intval($_POST['poista']);
	    $lauseke = $sql->prepare("DELETE FROM Kuntokeskus_viestit WHERE id_kuntokeskus = ?");
	    $lauseke->bind_param("i", $tunniste);
	    $lauseke->execute();
	}

	if (isset($_POST['poista_valitut']) && !empty($_POST['valitut'])) {
		tarkista_CSRF();
		
	    $tunnisteet = array_map('intval', $_POST['valitut']);
	
		$paikat = implode(',', array_fill(0, count($tunnisteet), '?'));
		$lauseke = $sql->prepare("DELETE FROM Kuntokeskus_viestit WHERE id_kuntokeskus IN ($paikat)");

		$tyypit = str_repeat("i", count($tunnisteet)); 
		$lauseke->bind_param($tyypit, ...$tunnisteet);
		$lauseke->execute();
	}

	$tila = $_GET['tila'] ?? 'inbox';

	$sallitutTilat = ['inbox', 'spam'];
	
	if (!in_array($tila, $sallitutTilat, true)) {
	    $tila = 'inbox';
	}
	
	$tuloksetPerSivu = 15;
	$sivu = isset($_GET['sivu']) ? intval($_GET['sivu']) : 1;

	if ($sivu < 1) {
		$sivu = 1;
	}

	$roskaPostiKysely = "SELECT COUNT(*) AS kokonaisMaara FROM Kuntokeskus_viestit WHERE ehkaRoskapostia = 1";
	$asiaPostiKysely  = "SELECT COUNT(*) AS kokonaisMaara FROM Kuntokeskus_viestit WHERE ehkaRoskapostia = 0";
	
	$laskeKysely = $tila === 'spam' ? $roskaPostiKysely : $asiaPostiKysely;
	$riviMaara = $sql->query($laskeKysely)->fetch_assoc()['kokonaisMaara'];
	
	$sivuMaara = ceil($riviMaara / $tuloksetPerSivu);

	if ($sivu > $sivuMaara && $sivuMaara > 0) {
		$sivu = $sivuMaara;
	}

	$kohta = ($sivu - 1) * $tuloksetPerSivu;

	$kyselyLauseke = "
		SELECT *
		FROM Kuntokeskus_viestit
		WHERE ehkaRoskapostia = ?
		ORDER BY id_kuntokeskus DESC
		LIMIT ?, ?
	";

	$lauseke = $sql->prepare($kyselyLauseke);

	$roska = ($tila === 'spam') ? 1 : 0;
	$kohta = (int)$kohta;
	$tuloksetPerSivu = (int)$tuloksetPerSivu;

	$lauseke->bind_param("iii", $roska, $kohta, $tuloksetPerSivu);
	$lauseke->execute();
	$tulos = $lauseke->get_result()->fetch_all(MYSQLI_ASSOC);


	echo '<h2>Viestit</h2>';

	echo "<div>";
	if ($tila === 'inbox') {
		$spamRiviMaara = $sql->query($roskaPostiKysely)->fetch_assoc()['kokonaisMaara'];
		
		echo "<strong>Saapuneet (" . $riviMaara . ")</strong> | <a href='?tila=spam'>Spam (" . $spamRiviMaara . ")</a>";
	} else {
		$asiaRiviMaara = $sql->query($asiaPostiKysely)->fetch_assoc()['kokonaisMaara'];
		
		echo "<a href='?tila=inbox'>Saapuneet (" . $asiaRiviMaara . ")</a> | <strong>Spam (" . $riviMaara . ")</strong>";
	}
	echo "</div><br>";

	if (count($tulos) === 0) {
		echo 'Ei viestejä lomakkeelta';
	} else {

		echo "<div>";
		if ($sivu > 1) {
		    echo "<a href='?tila=$tila&sivu=" . ($sivu - 1) . "'>Edellinen</a> ";
		}
		for ($indeksi = 1; $indeksi <= $sivuMaara; $indeksi++) {
		    if ($indeksi == $sivu) {
		        echo "<strong>$indeksi</strong> ";
		    } else {
		        echo "<a href='?tila=$tila&sivu=$indeksi'>$indeksi</a> ";
		    }
		}
		if ($sivu < $sivuMaara) {
		    echo "<a href='?tila=$tila&sivu=" . ($sivu + 1) . "'>Seuraava</a>";
		}
		echo "</div><br>";

		echo '<form method="POST">';
		echo '<input type="hidden" name="csrf" value="' . $_SESSION['csrf'] . '">';
		
		foreach ($tulos as $rivi) {
		    echo 'Nimi: ' . hsc($rivi['nimi']);
?>
			<button 
			  data-nimi="<?= hsc($rivi['nimi']) ?>"
			  data-email="<?= hsc($rivi['sposti']) ?>"
			  data-viesti="<?= hsc($rivi['viesti']) ?>"
			  onclick="avaaVastausLomake(this.dataset.nimi, this.dataset.email, this.dataset.viesti)">Vastaa</button>

<?php
		    echo '<a href="#" onclick="poista(' . intval($rivi['id_kuntokeskus']) . ');">Poista</a>, ';
		    
		    echo '<input type="checkbox" name="valitut[]" value="' . intval($rivi['id_kuntokeskus']) . '">';

		    echo '<div>Email osoite: ' . hsc($rivi['sposti']) . '</div><br>';
		    echo '<div>Viesti: ' . hsc($rivi['viesti']) . '</div><br>';
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
	const CSRF = "<?php echo $_SESSION['csrf']; ?>";
	
	function avaaVastausLomake(nimi, email, viesti) {
	    document.getElementById('vastausLomake_nimi').textContent = nimi;
	    document.getElementById('vastausLomake_email').textContent = email;
	    document.getElementById('vastausLomake_viesti').textContent = viesti;
	
	    document.getElementById('vastausLomake').style.display = 'flex';
	}

	function suljeVastausLomake() {
    	document.getElementById('vastausLomake').style.display = 'none';
	}

	function lahetaVastaus() {
		const nimi = document.getElementById('vastausLomake_nimi').innerText;
		const viesti = document.getElementById('vastausLomake_viesti').innerText;
		const email = document.getElementById('vastausLomake_email').innerText;
		const vastaus = document.getElementById('vastausLomake_vastaa').value;

    	let lomakeData = new FormData();
    	lomakeData.append("email", email);
		lomakeData.append("nimi", nimi);
    	lomakeData.append("vastaus", vastaus);
		lomakeData.append("viesti", viesti);
    	lomakeData.append("lahetaEmail", "1");
		lomakeData.append("csrf", CSRF);
		
		fetch("index.php", {
		    method: "POST",
		    body: lomakeData
		})
		.then(vastaus => vastaus.text())
		.then(tulos => {
			alert("Sähköposti lähetetty");
		    suljeVastausLomake();
		});
	}

	function poista(id) {
	    if (!confirm("Haluatko varmasti poistaa viestin?")) {
	        return;
	    }
	
	    let lomakeData = new FormData();
	    lomakeData.append("poista", id);
	    lomakeData.append("csrf", CSRF);
	
	    fetch("index.php", {
	        method: "POST",
	        body: lomakeData
	    })
	    .then(vastaus => vastaus.text())
	    .then(tulos => {
	        location.reload();
	    });
	}

</script>

</body></html>
