<?php
require_once('./admin/tietokanta.php');
require_once('openai_moderointi.php');

function tulostaVirhe($viesti = "Virhe palvelimella.", $koodi = 400) {
    http_response_code($koodi);
    error_log("[viestilomake] $viesti");
    exit($viesti);
}

function tarkistaCsrf() {
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_start();
    }
    $csrf = $_POST['csrf'] ?? '';
    $tallennettu = $_SESSION['csrf'] ?? '';

    unset($_SESSION['csrf']);
    if (empty($csrf) || empty($tallennettu) || !hash_equals($tallennettu, $csrf)) {
        tulostaVirhe("Virheellinen lomaketunniste (CSRF).", 400);
    }
}

function pyyntoRaja(mysqli $sql, string $ipOsoite) {
    $AIKA_SEKUNTEINA = 60;
    $RAJA = 10;
	
    $vanhaAika = time() - $AIKA_SEKUNTEINA;

    $lauseke = $sql->prepare("DELETE FROM Kuntokeskus_pyyntoraja WHERE aikaleima < ?");
    $lauseke->bind_param("i", $vanhaAika);
    $lauseke->execute();
    $lauseke->close();

    $lauseke = $sql->prepare("SELECT COUNT(*) FROM Kuntokeskus_pyyntoraja WHERE ip_osoite = ?");
    $lauseke->bind_param("s", $ipOsoite);
    $lauseke->execute();

    $lauseke->bind_result($maara);
    $lauseke->fetch();
    $lauseke->close();

    if ($maara >= $RAJA) {
        http_response_code(429);
        die("Liikaa pyyntöjä samasta IP-osoitteesta. Yritä myöhemmin uudelleen!");
    }

    $aikaleima = time();
    $lauseke = $sql->prepare("
        INSERT INTO Kuntokeskus_pyyntoraja (ip_osoite, aikaleima)
        VALUES (?, ?)
    ");
    $lauseke->bind_param("si", $ipOsoite, $aikaleima);
    $lauseke->execute();
    $lauseke->close();
}

function onkoLomakeLadattu() {
	session_start();

	if (!isset($_SESSION['lomake_on_ladattu'])) {
    	die("Lomaketta ei ole ladattu! Keksit (cookies) puuttuu??");
	}

	$aika = time() - $_SESSION['lomake_on_ladattu'];

	if ($aika < 3) {
    	die("Lähetetty liian nopeasti, taijat olla botti.");
	}

	unset($_SESSION['lomake_on_ladattu']);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	onkoLomakeLadattu();

	tarkistaCsrf();
	
	//bottitarkistusta
	if (!empty($_POST['homepage'])) {
    	http_response_code(400);
    	die();
	}
	
	if (isset($_SERVER['HTTP_CF_CONNECTING_IP'])) {
    	$ipOsoite = $_SERVER['HTTP_CF_CONNECTING_IP'];
	} else {
    	$ipOsoite = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
	}

	if (!filter_var($ipOsoite, FILTER_VALIDATE_IP)) {
    	$ipOsoite = '0.0.0.0';
	}

	pyyntoRaja($sql, $ipOsoite);

	$secret = getenv('RECAPTCHA_SECRET');
    $response = $_POST['g-recaptcha-response'];

	$captchaSivu = curl_init("https://www.google.com/recaptcha/api/siteverify");

	curl_setopt($captchaSivu, CURLOPT_POST, 1);
	curl_setopt($captchaSivu, CURLOPT_POSTFIELDS, http_build_query([
    	'secret'   => $secret,
    	'response' => $response
	]));
	curl_setopt($captchaSivu, CURLOPT_RETURNTRANSFER, true);
	curl_setopt($captchaSivu, CURLOPT_TIMEOUT, 5);
	curl_setopt($captchaSivu, CURLOPT_SSL_VERIFYPEER, true);

	$verifyResponse = curl_exec($captchaSivu);
	curl_close($captchaSivu);

	$captcha_success = json_decode($verifyResponse);


    if ($captcha_success->success) {

		$nimi   = trim($_POST['nimi'] ?? '');
		$email  = trim($_POST['email'] ?? '');
		$viesti = trim($_POST['viesti'] ?? '');

		$pituus = strlen($viesti);

		if ($pituus < 10) {
			die("Alle 10 merkin pituinen viesti.");
		}

		if ($pituus > 4000) {
			die("Yli 4000 merkin pituinen viesti.");
		}
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
		    die("Virheellinen sähköposti.");
		}

		$ehkaRoskapostia = 0;
		
		$moderointi = new OpenAI_Moderointi(getenv("OPENAI_API_KEY")); 
		$tarkistettava = $nimi . ' ' . $email . ' ' . $viesti; 
		$tulos = $moderointi->tarkista($tarkistettava);

		if ($tulos['havaittu']) {
			$ehkaRoskapostia = 1;
		}
		
		$lauseke = $sql->prepare("INSERT INTO Kuntokeskus_viestit (nimi, sposti, viesti, ehkaRoskapostia, ip_osoite) VALUES (?, ?, ?, ?, ?)");
		$lauseke->bind_param("sssis", $nimi, $email, $viesti, $ehkaRoskapostia, $ipOsoite);
		
		if ($lauseke->execute()) {
		    $lauseke->close();
		    header("Refresh: 5; URL=index.html");
		    echo 'Lomakkeen tiedot lähetetty, sinut ohjataan takaisin etusivulle 5 sekunnin kuluttua.';
		    exit;
		} else {
		    $lauseke->close();
		    tulostaVirhe("Viestin lähetys epäonnistui.", 500);
		}

	} else {
		die();
	}
}
?>
