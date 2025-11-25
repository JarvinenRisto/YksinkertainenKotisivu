<?php
	header("X-Content-Type-Options: nosniff"); 
	header("Referrer-Policy: no-referrer-when-downgrade"); 
	header("Strict-Transport-Security: max-age=31536000; includeSubDomains; preload"); 
	header("Permissions-Policy: geolocation=(), microphone=()"); 
	header("Content-Security-Policy: 
		default-src 'self'; 
		script-src 'self' https://www.google.com/recaptcha/ https://www.gstatic.com/recaptcha/ https://www.gstatic.com https://www.google.com;
		img-src 'self' https://www.gstatic.com/recaptcha/; 
		style-src 'self' 'unsafe-inline';
		frame-src https://www.google.com/recaptcha/;
	");

	session_set_cookie_params([
	    'lifetime' => 0,
	    'path' => '/',
	    'secure' => true,
	    'httponly' => true,
	    'samesite' => 'Lax'
	]);
	
	session_start();

    //csrf parempi toteutus on ilman sessiota ja HMAC käyttäen
	if (empty($_SESSION['csrf'])) {
	    $_SESSION['csrf'] = bin2hex(random_bytes(32));
	}
	
	$_SESSION['lomake_on_ladattu'] = time();
?>

<!DOCTYPE html>
<html lang="fi"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="teema.css">
    <title>Yhteystiedot – Kuntospurtti</title>
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
</head>
<body>

<header>
    <h1>Yhteystiedot</h1>
</header>


<nav>
    <a href="index.html">Etusivu</a>
    <a href="tunnit.html">Ohjatut tunnit</a>
    <a href="henkilokunta.html">Henkilökunta</a>
    <a class="active" href="yhteystiedot.php">Yhteystiedot</a>
</nav>

<section id="yhteys">
    <h2>Ota yhteyttä</h2>
    <p>Sähköposti: info@kuntospurtti.fi</p>
    <p>Puh: 040 123 4567</p>
    <p>Osoite: Treenikatu 5, 00100 Helsinki</p>

    <h3>Yhteydenottolomake</h3>

<p>Huomio: 20 sekunnin odotusaika onnistuneen viestin lähetyksen jälkeen, joten suositus on että viesti mietitty. Minimi merkkiraja 8 ja max 4000 viestissä, nimessä max merkkiraja 150.</p>
	
<form id="lomake" action="laheta.php" method="POST">
	<input type="hidden" name="csrf" value="<?=htmlspecialchars($_SESSION['csrf'], ENT_QUOTES, 'UTF-8')?>">
	
	<div id="kotisivu">
        <label for="homepage">Kotisivu</label>
        <input type="text" name="homepage" id="homepage">
    </div>

    <div>
        <label for="nimi">Nimi</label>
        <input type="text" id="nimi" name="nimi" required>
    </div>

    <div>
        <label for="email">Sähköposti</label>
        <input type="email" id="email" name="email" required>
    </div>

    <div>
        <label for="viesti">Viesti</label>
        <textarea id="viesti" name="viesti" rows="6" required></textarea>
    </div>

	<div class="g-recaptcha" data-sitekey="6LflpxAsAAAAALiNTUgjOrBgCJgQEMAd272Nampx"></div>

    <button type="submit">Lähetä</button>
</form>

<p>Lomakkeen lähettämisen yhteydessä tallennamme lomaketiedot ja lähetyshetken IP-osoitteen palvelun turvallisuuden ja väärinkäytösten estämisen vuoksi. Lomakkeen sisältö tarkistetaan automaattisesti haitallisen sisällön varalta ulkopuolisella analyysipalvelulla (OpenAI).</p>	

</section>



<footer>
    Kuntospurtti © 2025
</footer>

</body></html>
