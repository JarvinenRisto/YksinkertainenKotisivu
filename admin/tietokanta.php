<?php
	function hsc($merkkijono) {
		return htmlspecialchars($merkkijono, ENT_QUOTES, 'UTF-8');
	}

	$nimi = $_SERVER['DB_USERNAME'];
	$tietokanta = 'DB_P82253';
	$salasana = $_SERVER['DB_PASSWORD'];

	$sql = new mysqli('localhost', $nimi, $salasana, $tietokanta);

	$sql->query("CREATE TABLE IF NOT EXISTS Kuntokeskus_viestit (
    	id_kuntokeskus INT AUTO_INCREMENT PRIMARY KEY,
		nimi VARCHAR(255) NOT NULL, 
    	sposti VARCHAR(255) NOT NULL,
    	viesti VARCHAR(4000) NOT NULL,
		ehkaRoskapostia TINYINT(1) DEFAULT 0,
		ip_osoite VARCHAR(45) NOT NULL);"
	);

	$sql->query("CREATE TABLE IF NOT EXISTS Kuntokeskus_pyyntoraja (
    	ip_osoite VARCHAR(45) NOT NULL,
		aikaleima INT NOT NULL,
		INDEX (ip_osoite));"
	);
?>
