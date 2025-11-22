<?php
  //Tämä tiedosto ajetaan cronilla kerran minuutissa.

	$nimi = $_SERVER['DB_USERNAME'];
	$tietokanta = 'DB_P82253';
	$salasana = $_SERVER['DB_PASSWORD'];

	$mysqli = new mysqli("localhost", $nimi, $salasana, $tietokanta);

	if ($mysqli->connect_errno) {
		die("Tietokantavirhe: " . $mysqli->connect_error);
	}

	$SEKUNTIA = 60;
	$vanhaAika = time() - $SEKUNTIA;

	$lauseke = $mysqli->prepare("
		DELETE FROM Kuntokeskus_pyyntoraja 
		WHERE aikaleima < ?
	");
	$lauseke->bind_param("i", $vanhaAika);
	$lauseke->execute();
	$lauseke->close();

	$mysqli->close();
?>
