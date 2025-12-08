https://rosebasic.fi/KuntokeskusKuntospurtti/

https://rosebasic.fi/KuntokeskusKuntospurtti/admin

Käyttäjätunnus: admin

Salasana: 52,5koP3kj,3mc0X906

## Toteutuksessa hyödynnetty
- OpenAI Moderation
- ReCaptcha

## Muut ominaisuudet muun muassa
3 sekunnin viive käyttäjälle selaimessa onnistuneen lomakkeen lähetyksen jälkeen, vain jotta käyttäjä ehtii lukea onnistumisviestin ja lisäksi linkki, jolla käyttäjä voi palata etusivulle ilman odotusta. Lomakkeessa piilofieldi, jos botti täyttää = lomakkeen lähetys estyy.

IP kohtainen POST rajoitus (10 pyyntöä/min/IP) aktivoituu vain silloin, kun UserAgent ei läpäise roskatarkistusta, jolloinka ylimenevä osa merkataan spämmiksi. Huonoa on että UserAgent on spoofattavissa. Huonoa myös että jaetuissa verkoissa, voi raja tulla vastaan. Oikeilla yrityissivuilla voi todennäköisesti olla käytössä jonkin sortin palomuuri, ja/tai esimerkiksi Cloudflare. Mutta nuo tarkistukset ovat tässä mukana, vain maininnan vuoksi.

Onko sama viesti md5 tarkistus (md5 vanhentunut, SHA256 suositeltu), jos on merkitään meneväksi spam laatikkoon admin sivulle, josta voi vastata/lukea/poistaa.

Jos käyttäjä lähetti lomakkeen 1 sekunnissa, lähetys estyy, $SESSION avulla toteutettu. $SESSION kohtainen 20 sekunnin viive edellisestä onnistuneesta käyttäjän lomakkeen lähetyksestä. CSRF tarkistus.

$SESSION voi olla huonoa, pienelle määrälle käyttäjistä, että tarvitsee selaimen keksit (cookies) asetuksen päällä (FireFoxissa keksit estetty: "kaikki evästeet (aiheuttaa sivustovirheitä)" asetus). Käsittääkseni Redis pystyisi toteuttaan näitä antiflood/ratelimit ominaisuuksia ilman $SESSION ja sql käyttöä, mutta sitä ei ole käytössä hostilla, eikä myöskään Apcu, MemCached.

Käyttäjän lomakeviestin minimipituus 8 merkkiä, max pituus 3000. 

Etusivu:
<img width="954" height="405" alt="kuva" src="https://github.com/user-attachments/assets/3b08d6cd-6132-4b5a-bd21-2e2c405727d6" />

Ohjatut liikuntatunnit:
<img width="992" height="455" alt="kuva2" src="https://github.com/user-attachments/assets/2fdaa5dd-dd62-415b-899a-6d09a94e0a40" />

Personal trainer esimerkki:
<img width="956" height="475" alt="trainer" src="https://github.com/user-attachments/assets/d79888c9-ee20-4cec-9c67-2c500625b36c" />

Admin sivu:

<img width="888" height="629" alt="kuva" src="https://github.com/user-attachments/assets/a641d58e-89a2-4ea7-bddc-b84f4eedf9dd" />


