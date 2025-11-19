<?php
	$nimi = $_SERVER['DB_USERNAME'];
	$tietokanta = 'DB_P82253';
	$salasana = $_SERVER['DB_PASSWORD'];

	$sql = new mysqli('localhost', $nimi, $salasana, $tietokanta);

	$sql->query("CREATE TABLE IF NOT EXISTS Kuntokeskus_viestit (
    	id INT AUTO_INCREMENT PRIMARY KEY,
		name VARCHAR(255) NOT NULL, 
    	email VARCHAR(255) NOT NULL,
    	message VARCHAR(4000) NOT NULL);"
	);
?>