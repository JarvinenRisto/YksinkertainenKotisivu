	function avaaVastausLomake(nimi, email, viesti) {
	    document.getElementById('vastausLomake_nimi').textContent = nimi;
	    document.getElementById('vastausLomake_email').textContent = email;
	    document.getElementById('vastausLomake_viesti').textContent = viesti;
	
	    document.getElementById('vastausLomake').style.display = 'flex';

		window.scrollTo({top: 0, behavior: 'smooth'});
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

	function poista(id, viesti) {
	    if (!confirm("Haluatko varmasti poistaa viestin? " + viesti)) {
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

document.addEventListener("click", function(e) {

    const vastaa = e.target.closest(".link-vastaa");
    if (vastaa) {
        e.preventDefault();

        avaaVastausLomake(
            vastaa.dataset.nimi,
            vastaa.dataset.email,
            vastaa.dataset.viesti
        );
        return;
    }

    const poistalinkki = e.target.closest(".link-poista");
    if (poistalinkki) {
        e.preventDefault();

        poista(
            poistalinkki.dataset.id,
            poistalinkki.dataset.viesti
        );
        return;
    }

    const massaPoisto = e.target.closest(".btn-poista-valitut");
    if (massaPoisto) {
        if (!confirm("Haluatko varmasti poistaa valitut viestit?")) {
            e.preventDefault();
        }
    }

 	if (e.target.id === "btnSulje") {
        e.preventDefault();
        suljeVastausLomake();
        return;
    }

    if (e.target.id === "btnLaheta") {
        e.preventDefault();
        lahetaVastaus();
        return;
    }
});
