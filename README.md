https://rosebasic.fi/KuntokeskusKuntospurtti/

https://rosebasic.fi/KuntokeskusKuntospurtti/admin

Käyttäjätunnus: admin

Salasana: 52,5koP3kj,3mc0X906

## Toteutuksessa hyödynnetty
- OpenAI Moderation
- ReCaptcha

## Muut ominaisuudet
3 sekunnin viive käyttäjälle selaimessa onnistuneen lomakkeen lähetyksen jälkeen. Jos käyttäjä lähetti lomakkeen nopeammin kuin 3 sekunnissa, lähetys estyy. Lomakkeessa piilofieldi, jos botti täyttää = lomakkeen lähetys estyy.
POST pyyntöraja, 10 pyyntöä per ip / per minuutti tässä versiossa. Onko sama viesti md5 tarkistus, jos on merkitään meneväksi spam laatikkoon admin sivulle, josta voi lukea/poistaa. Onko POST pyyntö laitettu sivujen lomakkeen kautta tarkistus, $SESSION avulla toteutettu. CSRF tarkistus. Lomakeviestin minimipituus 8 merkkiä, max pituus 4000..
