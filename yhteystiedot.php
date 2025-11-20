<!DOCTYPE html>
<html lang="fi"><head>
<meta http-equiv="content-type" content="text/html; charset=UTF-8">
    <meta charset="UTF-8">
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

<?php 
	session_start();
	$_SESSION['lomake_on_ladattu'] = time(); 
?>

<form id="lomake" action="laheta.php" method="POST">
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

</section>



<footer>
    Kuntospurtti © 2025
</footer>

</body></html>
