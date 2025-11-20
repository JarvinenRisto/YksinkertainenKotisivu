<?php
require_once('./admin/tietokanta.php');
require_once('openai_moderointi.php');

if ($_SERVER["REQUEST_METHOD"] === "POST") {

	//bottitarkistusta
	if (!empty($_POST['homepage'])) {
    	http_response_code(400);
    	die();
	}
	
	$secret = '6LflpxAsAAAAABRrgOE59ph2zqj5SDeEBz5QaWzb';
    $response = $_POST['g-recaptcha-response'];

    $verify = file_get_contents(
        "https://www.google.com/recaptcha/api/siteverify?secret={$secret}&response={$response}"
    );
    $captcha_success = json_decode($verify);

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

		$ipOsoite = $_SERVER['REMOTE_ADDR'];
		
		$lauseke = $sql->prepare("INSERT INTO Kuntokeskus_viestit (name, email, message, maybe_spam) VALUES (?, ?, ?, ?)");

		if ($lauseke->execute([$nimi, $email, $viesti, $ehkaRoskapostia])) {
			echo 'Lomakkeen tiedot lähetetty, sinut ohjataan takaisin etusivulle 5 sekunnin kuluttua.';
			header("Refresh: 5; URL=index.html");
			exit;
		} else {
			echo "Viestin lähetys epäonnistui.";
		}
	} else {
		die();
	}
}
?>
