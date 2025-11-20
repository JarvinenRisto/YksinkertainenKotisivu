<?php

class OpenAI_Moderointi
{
    private string $apiAvain;

    public function __construct(string $apiAvain)
    {
        if (empty($apiAvain)) {
            throw new Exception("OpenAI API avain puuttuu.");
        }

        $this->apiAvain = $apiAvain;
    }

    public function tarkista(string $teksti): array
    {
        if (trim($teksti) === "") {
            return [
                "ok" => false,
                "havaittu" => false,
                "virhe" => "Teksti ei voi olla tyhjä."
            ];
        }

        $jsonData = json_encode([
            "model" => "omni-moderation-latest",
            "input" => $teksti
        ]);

        $moderointiSivu = curl_init("https://api.openai.com/v1/moderations");
        curl_setopt($moderointiSivu, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($moderointiSivu, CURLOPT_POST, true);
        curl_setopt($moderointiSivu, CURLOPT_HTTPHEADER, [
            "Content-Type: application/json",
            "Authorization: " . "Bearer " . $this->apiAvain
        ]);
        curl_setopt($moderointiSivu, CURLOPT_POSTFIELDS, $jsonData);

        curl_setopt($moderointiSivu, CURLOPT_CONNECTTIMEOUT, 3); 
        curl_setopt($moderointiSivu, CURLOPT_TIMEOUT, 6);
        
        $vastaus = curl_exec($moderointiSivu);

        if (!$vastaus) {
            return [
                "ok" => false,
                "havaittu" => false,
                "virhe" => "OpenAI API ei vastannut."
            ];
        }

        $http = curl_getinfo($moderointiSivu, CURLINFO_HTTP_CODE);
        curl_close($moderointiSivu);

        if ($http !== 200) {
            return [
                "ok" => false,
                "havaittu" => false,
                "virhe" => "HTTP $http",
                "raw" => $vastaus
            ];
        }

        $data = json_decode($vastaus, true);

        $kategoriat = $data["results"][0]["categories"] ?? [];

        $havaittu = false;
        foreach ($kategoriat as $kategoria => $onkoHavaittu) {
            if ($onkoHavaittu) {
                $havaittu = true;
                break;
            }
        }

        return [
            "ok" => true,
            "havaittu" => $havaittu,
            "kategoriat" => $kategoriat,
            "pisteet" => $data["results"][0]["category_scores"] ?? []
        ];
    }
}

?>
