<?php
	function hsc($merkkijono) {
		return htmlspecialchars($merkkijono, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
	}

	$nimi = $_SERVER['DB_USERNAME'];
	$tietokanta = 'DB_P82253';
	$salasana = $_SERVER['DB_PASSWORD'];

	$sql = new mysqli('localhost', $nimi, $salasana, $tietokanta);
	$sql->set_charset("utf8mb4");

	$sql->query("CREATE TABLE IF NOT EXISTS Kuntokeskus_viestit (
    	id_kuntokeskus INT AUTO_INCREMENT PRIMARY KEY,
		nimi VARCHAR(255) NOT NULL, 
    	sposti VARCHAR(255) NOT NULL,
    	viesti VARCHAR(4000) NOT NULL,
		ehkaRoskapostia TINYINT(1) DEFAULT 0,
		ip_osoite VARCHAR(45) NOT NULL,
		md5_hash CHAR(32) NOT NULL);"
	);

	$sql->query("
	CREATE TABLE IF NOT EXISTS Kuntokeskus_pyyntoraja (
	    id INT UNSIGNED NOT NULL AUTO_INCREMENT,
	    ip_osoite VARCHAR(45) NOT NULL,
	    aikaleima INT UNSIGNED NOT NULL,
	    PRIMARY KEY (id),
	    INDEX ip_time (ip_osoite, aikaleima)
	) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
	");
?>
