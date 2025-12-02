<?php
	header("X-Content-Type-Options: nosniff"); 
	header("Referrer-Policy: no-referrer-when-downgrade"); 
	header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload"); 
	header("Permissions-Policy: geolocation=(), microphone=()"); 

	$nonce = base64_encode(random_bytes(16));

	header("Content-Security-Policy: default-src 'self'; script-src 'self' \"nonce-$nonce\"; style-src 'self'; img-src 'self'; connect-src 'self'; frame-ancestors 'none'; object-src 'none'; base-uri 'none'; form-action 'self';");

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

	require_once('tietokanta.php');
?>

<script nonce="<?= $nonce ?>">
  const CSRF = "<?= hsc($_SESSION['csrf']) ?>";
</script>

<!DOCTYPE html>
<html lang="fi"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
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
		<button type="button" id="btnSulje">Sulje</button>
		<button type="button" id="btnLaheta">Lähetä</button><br>
	</div>
</div>

<script nonce="<?= $nonce ?>" src="admin.js"></script>

<?php
	function tarkistaCsrf() {
	    $csrf = $_POST['csrf'] ?? '';
	    $tallennettu = $_SESSION['csrf'] ?? '';
	
	    if (empty($csrf) || empty($tallennettu) || !hash_equals($tallennettu, $csrf)) {
	        tulostaVirhe("Virheellinen lomaketunniste (CSRF).", 400);
	    }
	}

	if (isset($_POST['lahetaEmail'])) {
	    tarkistaCsrf();
	
	    $nimi    = trim($_POST['nimi'] ?? '');
	    $email   = trim($_POST['email'] ?? '');
	    $viesti  = trim($_POST['viesti'] ?? '');
	    $vastaus = trim($_POST['vastaus'] ?? '');

		if (!mb_check_encoding($vastaus, 'UTF-8')) {
			die("Virheellinen merkistö.");
		}
		
	    if (preg_match('/[\r\n]/', $nimi) || preg_match('/[\r\n]/', $email)) {
	        die("Virheellinen syöte.");
	    }
	
	    if (mb_strlen($nimi, 'UTF-8') < 1 || mb_strlen($nimi, 'UTF-8') > 255) {
	        die("Nimen pituus virheellinen.");
	    }

	    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
	        die("Virheellinen sähköposti!");
	    }
	
	    $viestiPituus = mb_strlen($viesti, 'UTF-8');
		
	    if ($viestiPituus > 10000) {
	        die("Viestin pituus liian pitkä.");
	    }
	
	    $teksti  = "Hei " . hsc($nimi) . ",\r\n\r\n";
	    $teksti .= "Vastaus viestiisi:\r\n";
	    $teksti .= hsc($vastaus) . "\r\n\r\n";
	    $teksti .= "Alkuperäinen viestisi oli:\r\n";
	    $teksti .= hsc($viesti) . "\r\n";
	
	    $kenelta = "admin@rosebasic.fi";
	
	    $otsikot  = "MIME-Version: 1.0\r\n";
	    $otsikot .= "Content-Type: text/plain; charset=UTF-8\r\n";
	    $otsikot .= "From: $kenelta\r\n";
	    $otsikot .= "Reply-To: $kenelta\r\n";
	
	    if (mail($email, "Vastaus lomakkeelta", $teksti, $otsikot)) {
	        echo "Sähköposti lähetetty!";
	    } else {
	        echo "Virhe sähköpostin lähetyksessä.";
	    }
	}

	if (isset($_POST['poista'])) {
	    tarkistaCsrf();
	
	    $tunniste = intval($_POST['poista']);
	    $lauseke = $sql->prepare("DELETE FROM Kuntokeskus_viestit WHERE id_kuntokeskus = ?");
	    $lauseke->bind_param("i", $tunniste);
	    $lauseke->execute();
	}

	if (isset($_POST['poista_valitut']) && !empty($_POST['valitut'])) {
		tarkistaCsrf();
		
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
	
	$tuloksetPerSivu = 30;
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

	echo '<div id="yla"></div>';
	echo '<h2>Viestit</h2>';
	echo "<div>";

	if ($tila === 'inbox') {
		$spamRiviMaara = $sql->query($roskaPostiKysely)->fetch_assoc()['kokonaisMaara'];
		
		echo "<strong>Saapuneet (" . $riviMaara . ")</strong> | <a href='?tila=spam'>Spam (" . $spamRiviMaara . ")</a>" . ' | <a href="#ala">Alas</a>';
	} else {
		$asiaRiviMaara = $sql->query($asiaPostiKysely)->fetch_assoc()['kokonaisMaara'];
		
		echo "<a href='?tila=inbox'>Saapuneet (" . $asiaRiviMaara . ")</a> | <strong>Spam (" . $riviMaara . ")</strong>" . ' | <a href="#ala">Alas</a>';
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
		echo '<input type="hidden" name="csrf" value="' . hsc($_SESSION['csrf']) . '">';
		
		foreach ($tulos as $rivi) {
		    echo 'Nimi: ' . hsc($rivi['nimi']);
?>
		<a href="#"
		   class="link-vastaa"
		   data-nimi="<?= hsc($rivi['nimi']) ?>"
		   data-email="<?= hsc($rivi['sposti']) ?>"
		   data-viesti="<?= hsc($rivi['viesti']) ?>"
		>Vastaa,</a>

<?php
		echo '<a href="#" 
				class="link-poista" 
				data-id="' . intval($rivi['id_kuntokeskus']) . '" 
				data-viesti="' . hsc($rivi['viesti']) . '"
			>Poista</a>, ';

		echo '<input type="checkbox" name="valitut[]" value="' . intval($rivi['id_kuntokeskus']) . '">';

		echo '<div>Email osoite: ' . hsc($rivi['sposti']) . '</div>';
		echo '<div><p>Viesti: ' . hsc($rivi['viesti']) . '</p></div>';

		}

		echo '<button type="submit" class="btn-poista-valitut" name="poista_valitut">Poista valitut</button>';
		echo '</form>';
	}
?>

<a href="#yla">Ylös</a>
<div id="ala"></div>

</section>
<footer>
    Kuntokeskus Kuntospurtti © 2025
</footer>

</body></html>
