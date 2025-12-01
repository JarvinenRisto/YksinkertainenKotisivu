<?php
session_start();

require_once('./admin/tietokanta.php');
require_once('openai_moderointi.php');

function tulostaVirhe($viesti = "Virhe palvelimella.", $koodi = 400) {
    http_response_code($koodi);
    error_log("[viestilomake] $viesti");
    exit($viesti);
}

function tulostaVirheNollaaAika($viesti) {
	$_SESSION['viimeksiLahetetty'] = 20;
	exit($viesti);
}

function tarkistaCsrf() {
    $csrf = $_POST['csrf'] ?? '';
    $tallennettu = $_SESSION['csrf'] ?? '';

    unset($_SESSION['csrf']);
    if (empty($csrf) || empty($tallennettu) || !hash_equals($tallennettu, $csrf)) {
        tulostaVirhe("Virheellinen lomaketunniste (CSRF).", 400);
    }
}

function pyyntoRaja(mysqli $sql, string $ipOsoite) {
	$onkoRoskaAgentti = false;
	
	$agentti = $_SERVER['HTTP_USER_AGENT'] ?? '';

	$onkoSelain = false;
	$selaimet = ['Chrome','Safari','Firefox','Edge','OPR','Opera','Gecko','AppleWebKit'];
	foreach ($selaimet as $selain) {
        if (stripos($agentti, $selain) !== false) {
	      	$onkoSelain = true;
            break;
        }
    }

	if (!$onkoSelain) {
    	$onkoRoskaAgentti = true;
   	}

	if (!$onkoRoskaAgentti) {
		return 0;
	}
	
	$AIKA_SEKUNTEINA = 60;
	$RAJA = 10;
	
	$nyt = time();
	$vanhaAika = $nyt - $AIKA_SEKUNTEINA;
	
	$sql->begin_transaction();
	
	$lauseke = $sql->prepare("
	     SELECT COUNT(*) 
	    FROM Kuntokeskus_pyyntoraja
	    WHERE ip_osoite = ? AND aikaleima >= ?
	    FOR UPDATE
	");
	$lauseke->bind_param("si", $ipOsoite, $vanhaAika);
	$lauseke->execute();
	$lauseke->store_result();
	$lauseke->bind_result($maara);
	$lauseke->fetch();
	$lauseke->close();
	
	if ($maara >= $RAJA) {
	     $sql->rollback();
	    return 1;
	}
	
	$lauseke = $sql->prepare("
	    INSERT INTO Kuntokeskus_pyyntoraja (ip_osoite, aikaleima)
	    VALUES (?, ?)
	");
 	$lauseke->bind_param("si", $ipOsoite, $nyt);
	$lauseke->execute();
	$lauseke->close();
	
	$sql->commit();
	return 0;
}

function onkoLomakeLadattu() {

	if (!isset($_SESSION['lomake_on_ladattu'])) {
		tulostaVirhe("Lomaketta ei ole ladattu! Keksit (cookies) puuttuu??", 429);
	}

	$aika = time() - $_SESSION['lomake_on_ladattu'];

	if ($aika < 3) {
		tulostaVirhe("“Lomake lähetettiin poikkeuksellisen nopeasti (3 sekunnin sisällä). Jos et ole botti, yritä uudelleen.", 429);
	}

	unset($_SESSION['lomake_on_ladattu']);
}

function istuntoPyyntoRaja() {
	$VIIVE = 20;
	$nyt = time();

	if (isset($_SESSION['viimeksiLahetetty']) && 
			($nyt - $_SESSION['viimeksiLahetetty']) < $VIIVE) {
		tulostaVirhe("Liian lyhyt aika kulunut edellisen viestin lähetyksestä samalla selaimella! (odotusaika 20 sekuntia)", 429);
    }
	
	$_SESSION['viimeksiLahetetty'] = $nyt;
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	onkoLomakeLadattu();
	istuntoPyyntoRaja();
	tarkistaCsrf();
	
	//bottitarkistusta
	if (!empty($_POST['homepage'])) {
    	tulostaVirhe("Botti täytti homepage fieldin", 400);
	}

	$ipOsoite = $_SERVER['REMOTE_ADDR'];

	if (!filter_var($ipOsoite, FILTER_VALIDATE_IP)) {
    	$ipOsoite = '0.0.0.0';
	}

	$secret = getenv('RECAPTCHA_SECRET_KEY');
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


    if ($captcha_success !== null && $captcha_success->success) {
		
		$ehkaRoskapostia = pyyntoRaja($sql, $ipOsoite);
		
		$nimi = trim(str_replace(["\n", "\r"], "", $_POST['nimi']));
		$email = trim(str_replace(["\n", "\r"], "", $_POST['email']));
		$viesti = trim($_POST['viesti'] ?? '');

		$pituus = mb_strlen($nimi, 'UTF-8');
		if ($pituus > 100) {
			tulostaVirheNollaaAika("Yli 100 merkin pituinen nimi.");
		}
		
		$pituus = mb_strlen($viesti, 'UTF-8');

		if ($pituus < 8) {
			tulostaVirheNollaaAika("Alle 8 merkin pituinen viesti.");
		}

		if ($pituus > 3000) {
			tulostaVirheNollaaAika("Yli 3000 merkin pituinen viesti.");
		}
		
		if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
			tulostaVirheNollaaAika("Virheellinen sähköposti.");
		}

		$moderointi = new OpenAI_Moderointi(getenv('OPENAI_API_KEY')); 
		$tulos = $moderointi->tarkista($viesti);

		$hash = md5($viesti);

		$tarkistus = $sql->prepare("SELECT COUNT(*) FROM Kuntokeskus_viestit WHERE md5_hash = ?");
		$tarkistus->bind_param("s", $hash);
		$tarkistus->execute();
		$tarkistus->bind_result($count);
		$tarkistus->fetch();
		$tarkistus->close();
		
		if ($tulos['havaittu'] || $count > 0) {
			$ehkaRoskapostia = 1;
		}

		$lauseke = $sql->prepare("INSERT INTO Kuntokeskus_viestit (nimi, sposti, viesti, ehkaRoskapostia, ip_osoite, md5_hash) VALUES (?, ?, ?, ?, ?, ?)");
		$lauseke->bind_param("sssiss", $nimi, $email, $viesti, $ehkaRoskapostia, $ipOsoite, $hash);
		
		if ($lauseke->execute()) {
		    $lauseke->close();
		    header("Refresh: 3; URL=index.html");
		    echo 'Lomakkeen tiedot lähetetty, sinut ohjataan takaisin etusivulle 3 sekunnin kuluttua.';
		    exit;
		} else {
		    $lauseke->close();
		    tulostaVirhe("Viestin lähetys epäonnistui.", 500);
		}

	} else {
		tulostaVirhe("Captcha epäonnistui.", 400);
	}
}
?>
