https://rosebasic.fi/KuntokeskusKuntospurtti/

https://rosebasic.fi/KuntokeskusKuntospurtti/admin

Käyttäjätunnus: admin

Salasana: 52,5koP3kj,3mc0X906

## Toteutuksessa hyödynnetty
- OpenAI Moderation
- ReCaptcha

## Muut ominaisuudet muun muassa
3 sekunnin viive käyttäjälle selaimessa onnistuneen lomakkeen lähetyksen jälkeen. Lomakkeessa piilofieldi, jos botti täyttää = lomakkeen lähetys estyy.
POST pyyntöraja, 10 pyyntöä per ip / per minuutti ja jossa huonoa voi olla jos jaetussa verkossa samaan aikaan lähetetään lomake että raja tulee vastaan. Onko sama viesti md5 tarkistus, jos on merkitään meneväksi spam laatikkoon admin sivulle, josta voi vastata/lukea/poistaa. Jos käyttäjä lähetti lomakkeen nopeammin kuin 3 sekunnissa, lähetys estyy. Onko POST pyyntö laitettu sivujen lomakkeen kautta tarkistus, $SESSION avulla toteutettu. $SESSION kohtainen 20 sekunnin viive edellisestä käyttäjän lomakkeen lähetyksestä. $SESSION voi olla huonoa että käyttäjä tarvitsee selaimen keksit (cookies) päällä. CSRF tarkistus. Käyttäjän lomakeviestin minimipituus 8 merkkiä, max pituus 4000. 

Admin sivu:
<img width="888" height="629" alt="kuva" src="https://github.com/user-attachments/assets/a641d58e-89a2-4ea7-bddc-b84f4eedf9dd" />


