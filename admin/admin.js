	function avaaVastausLomake(nimi, email, viesti) {
	    document.getElementById('vastausLomake_nimi').textContent = nimi;
	    document.getElementById('vastausLomake_email').textContent = email;
	    document.getElementById('vastausLomake_viesti').textContent = viesti;
	
	    document.getElementById('vastausLomake').style.display = 'flex';
	}

	function suljeVastausLomake() {
    	document.getElementById('vastausLomake').style.display = 'none';
	}

	function lahetaVastaus() {
		const nimi = document.getElementById('vastausLomake_nimi').innerText;
		const viesti = document.getElementById('vastausLomake_viesti').innerText;
		const email = document.getElementById('vastausLomake_email').innerText;
		const vastaus = document.getElementById('vastausLomake_vastaa').value;

    	let lomakeData = new FormData();
    	lomakeData.append("email", email);
		lomakeData.append("nimi", nimi);
    	lomakeData.append("vastaus", vastaus);
		lomakeData.append("viesti", viesti);
    	lomakeData.append("lahetaEmail", "1");
		lomakeData.append("csrf", CSRF);
		
		fetch("index.php", {
		    method: "POST",
		    body: lomakeData
		})
		.then(vastaus => vastaus.text())
		.then(tulos => {
			alert("Sähköposti lähetetty");
		    suljeVastausLomake();
		});
	}

	function poista(id) {
	    if (!confirm("Haluatko varmasti poistaa viestin?")) {
	        return;
	    }
	
	    let lomakeData = new FormData();
	    lomakeData.append("poista", id);
	    lomakeData.append("csrf", CSRF);
	
	    fetch("index.php", {
	        method: "POST",
	        body: lomakeData
	    })
	    .then(vastaus => vastaus.text())
	    .then(tulos => {
	        location.reload();
	    });
	}

	suljeVastausLomake();
