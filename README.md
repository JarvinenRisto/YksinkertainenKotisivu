https://rosebasic.fi/KuntokeskusKuntospurtti/

https://rosebasic.fi/KuntokeskusKuntospurtti/admin

Käyttäjätunnus: admin

Salasana: 52,5koP3kj,3mc0X906

## Toteutuksessa hyödynnetty
- OpenAI Moderation
- ReCaptcha

## Muut ominaisuudet muun muassa
1 sekunnin viive käyttäjälle selaimessa onnistuneen lomakkeen lähetyksen jälkeen. Lomakkeessa piilofieldi, jos botti täyttää = lomakkeen lähetys estyy.

POST pyyntöraja onnistuneen captchan jälkeen, 60 pyyntöä per ip / per minuutti, jonka jälkeen viesti merkataan spämmiksi, jossa huonoa voi olla jos jaetussa verkossa samaan aikaan lähetetään lomake että raja tulee vastaan. Oikeilla yrityissivuilla voi todennäköisesti olla käytössä jonkin sortin palomuuri.

Onko sama viesti md5 tarkistus (md5 vanhentunut, SHA256 suositeltu), jos on merkitään meneväksi spam laatikkoon admin sivulle, josta voi vastata/lukea/poistaa, voisi toki mennä toiseen spam laatikkoon ja openai tunnistamat omaansa.

Jos käyttäjä lähetti lomakkeen nopeammin kuin 3 sekunnissa, lähetys estyy, $SESSION avulla toteutettu. Onko POST pyyntö laitettu sivujen lomakkeen kautta tarkistus, $SESSION tässäkin. $SESSION kohtainen 20 sekunnin viive edellisestä onnistuneesta käyttäjän lomakkeen lähetyksestä. CSRF tarkistus.

$SESSION voi olla huonoa, pienelle määrälle käyttäjistä, että tarvitsee selaimen keksit (cookies) asetuksen päällä (FireFoxissa keksit estetty: "kaikki evästeet (aiheuttaa sivustovirheitä)" asetus). Käsittääkseni Redis pystyisi toteuttaan näitä anti-flood ominaisuuksia ilman $SESSION käyttöä, mutta sitä ei ole käytössä hostilla, eikä myöskään Apcu.

Käyttäjän lomakeviestin minimipituus 8 merkkiä, max pituus 4000. 

Admin sivu:
<img width="888" height="629" alt="kuva" src="https://github.com/user-attachments/assets/a641d58e-89a2-4ea7-bddc-b84f4eedf9dd" />


