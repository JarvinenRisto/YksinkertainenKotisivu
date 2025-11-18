<?php

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nimi   = trim($_POST['nimi'] ?? '');
    $email  = trim($_POST['email'] ?? '');
    $viesti = trim($_POST['viesti'] ?? '');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Virheellinen sähköposti.");
    }

    $kenelle = "p82253@edu.sasky.fi";
    $otsikko = "Uusi viesti lomakkeelta";

    $sisalto = "Nimi: $nimi\n";
    $sisalto .= "Sähköposti: $email\n\n";
    $sisalto .= "Viesti:\n$viesti\n";

    $otsikot  = "From: $nimi <$email>\r\n";
    $otsikot .= "Reply-To: $email\r\n";
    $otsikot .= "Content-Type: text/plain; charset=utf-8\r\n";
    $otsikot .= "X-Mailer: PHP/" . phpversion();

    if (mail($kenelle, $otsikko, $sisalto, $otsikot)) {
	echo 'Lomakkeen tiedot lähetetty, sinut ohjataan takaisin etusivulle 3 sekunnin kuluttua.';
	header("Refresh: 3; URL=index.html");
	exit;
    } else {
    	echo "Viestin lähetys epäonnistui.";
    }
}
?>
