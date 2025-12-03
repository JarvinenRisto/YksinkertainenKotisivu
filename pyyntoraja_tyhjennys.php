<?php
  //Tämä tiedosto ajetaan cronilla kerran minuutissa.
  //Vastaavaa voi soveltaa tyhjentämään spam laatikon, esimerkiksi kerran viikossa.
	$nimi = $_SERVER['DB_USERNAME'];
	$tietokanta = 'DB_P82253';
	$salasana = $_SERVER['DB_PASSWORD'];

	$mysqli = new mysqli("localhost", $nimi, $salasana, $tietokanta);

	if ($mysqli->connect_errno) {
		die("Tietokantavirhe: " . $mysqli->connect_error);
	}

	$lauseke = $mysqli->prepare("
		DELETE FROM Kuntokeskus_pyyntoraja
    	WHERE ampari < FLOOR(UNIX_TIMESTAMP() / 60) - 2;
	");
	$lauseke->execute();
	$lauseke->close();

	$mysqli->close();
?>
