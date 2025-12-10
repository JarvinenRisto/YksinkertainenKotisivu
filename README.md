https://rosebasic.fi/KuntokeskusKuntospurtti/

https://rosebasic.fi/KuntokeskusKuntospurtti/admin

Käyttäjätunnus: admin

Salasana: 52,5koP3kj,3mc0X906

## Toteutuksessa hyödynnetty
- OpenAI Moderation
- reCAPTCHA V2 (myöhemmin on mahdollista päivittää V3-versioon paremman käytettävyyden vuoksi)

## Muut ominaisuudet muun muassa
Kun käyttäjä lähettää lomakkeen onnistuneesti, sivulla näkyy kolme sekuntia ilmoitus, jotta käyttäjä ehtii lukea sen ennen siirtymistä eteenpäin. Lisäksi näkyy linkki, jolla voi palata etusivulle ilman viivettä. Lomakkeessa on piilotettu kenttä, joka estää joidenkin bottien tekemät automaattiset lähetykset.

Silloin kun käyttäjän UserAgent ei läpäise roskatarkistusta (ei näytä selaimelta): järjestelmä tarkistaa IP-osoitteen perusteella, jos toiminta vaikuttaa automaattiselta (10 pyyntöä minuutissa). Tällöin ylimääräiset viestit merkitään roskapostiksi, joka menee admin sivun Spam laatikkoon. Tämä ei ole täydellinen ratkaisu, koska UserAgent-arvoa voi väärentää ja jaetuissa verkoissa raja voi tulla vastaan. Suuremmissa tuotantoympäristöissä tämä olisi yleensä toteutettu palomuurilla tai esimerkiksi Cloudflaren kautta, mutta tässä ominaisuus on mukana maininnan vuoksi.

Jos täysin sama viesti lähetetään uudelleen, järjestelmä tunnistaa sen hash-arvolla (MD5, nykysuositus SHA256) ja se merkataan roskapostiksi. (admin sivuilla voi poistaa, lukea, vastata).

Jos käyttäjä lähettää lomakkeen alle 2 sekunnissa, lähetys estetään. Tämä ei pitäisi haitata normaalia käyttöä ja on toteutettu selain kohtaisesti: SESSION-tietoon perustuen. Lisäksi onnistuneen lähetyksen jälkeen on 20 sekunnin viive ennen uuden viestin lähettämistä. Lomakkeissa on myös CSRF-suojaus.

SESSION-pohjainen toteutus vaatii evästeet (cookies) toimiakseen, mikä voi vaikuttaa pieneen määrään käyttäjiä, joilla evästeet on tarkoituksella poistettu käytöstä. Mahdollinen vaihtoehto olisi toteuttaa antiflood ominaisuuksia Redisillä, jolloin evästeriippuvuutta ei olisi, mutta käytettävällä palvelimella ei ole Redis, APCu tai Memcached tukea.

Lomakkeessa on viestille minimipituus (8 merkkiä) ja maksimipituus (3000 merkkiä), jotta tyhjät tai liian pitkät viestit eivät mene järjestelmään.

Etusivu:
<img width="954" height="405" alt="kuva" src="https://github.com/user-attachments/assets/3b08d6cd-6132-4b5a-bd21-2e2c405727d6" />

Ohjatut liikuntatunnit:
<img width="992" height="455" alt="kuva2" src="https://github.com/user-attachments/assets/2fdaa5dd-dd62-415b-899a-6d09a94e0a40" />

Personal trainer esimerkki:
<img width="956" height="475" alt="trainer" src="https://github.com/user-attachments/assets/d79888c9-ee20-4cec-9c67-2c500625b36c" />

Admin sivu:

<img width="888" height="629" alt="kuva" src="https://github.com/user-attachments/assets/a641d58e-89a2-4ea7-bddc-b84f4eedf9dd" />


