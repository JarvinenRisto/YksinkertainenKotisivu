https://rosebasic.fi/KuntokeskusKuntospurtti/

https://rosebasic.fi/KuntokeskusKuntospurtti/admin

Käyttäjätunnus: admin

Salasana: 52,5koP3kj,3mc0X906

## Toteutuksessa hyödynnetty
- OpenAI Moderation
- ReCaptcha

## Muut ominaisuudet muun muassa
1 sekunnin viive käyttäjälle selaimessa onnistuneen lomakkeen lähetyksen jälkeen. Lomakkeessa piilofieldi, jos botti täyttää = lomakkeen lähetys estyy.

IP kohtainen POST rajoitus (10 pyyntöä/min/IP) aktivoituu vain silloin, kun UserAgent ei läpäise roskatarkistusta, jolloinka ylimenevä osa merkataan spämmiksi. Huonoa on että UserAgent on spoofattavissa. Oikeilla yrityissivuilla voi todennäköisesti olla käytössä jonkin sortin palomuuri, ja/tai esimerkiksi Cloudflare.

Onko sama viesti md5 tarkistus (md5 vanhentunut, SHA256 suositeltu), jos on merkitään meneväksi spam laatikkoon admin sivulle, josta voi vastata/lukea/poistaa.

Jos käyttäjä lähetti lomakkeen nopeammin kuin 3 sekunnissa, lähetys estyy, $SESSION avulla toteutettu. Onko POST pyyntö laitettu sivujen lomakkeen kautta tarkistus, $SESSION tässäkin. $SESSION kohtainen 20 sekunnin viive edellisestä onnistuneesta käyttäjän lomakkeen lähetyksestä. CSRF tarkistus.

$SESSION voi olla huonoa, pienelle määrälle käyttäjistä, että tarvitsee selaimen keksit (cookies) asetuksen päällä (FireFoxissa keksit estetty: "kaikki evästeet (aiheuttaa sivustovirheitä)" asetus). Käsittääkseni Redis pystyisi toteuttaan näitä anti-flood ominaisuuksia ilman $SESSION ja sql käyttöä, mutta sitä ei ole käytössä hostilla, eikä myöskään Apcu, MemCached.

Käyttäjän lomakeviestin minimipituus 8 merkkiä, max pituus 4000. 

Admin sivu:
<img width="888" height="629" alt="kuva" src="https://github.com/user-attachments/assets/a641d58e-89a2-4ea7-bddc-b84f4eedf9dd" />


