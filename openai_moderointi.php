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

        $vastaus = curl_exec($moderointiSivu);

        if (!$vastaus) {
            return [
                "ok" => false,
                "havaittu" => false,
                "virhe" => "OpenAI API ei tullu vastausta."
            ];
        }

        $tila = curl_getinfo($moderointiSivu, CURLINFO_HTTP_CODE);
        curl_close($moderointiSivu);

        if ($tila !== 200) {
            return [
                "ok" => false,
                "havaittu" => false,
                "virhe" => "OpenAI API -virhe (HTTP $tila)."
            ];
        }

        $data = json_decode($vastaus, true);

        return [
            "ok" => true,
            "havaittu" => $data["results"][0]["flagged"] ?? false,
            "kategoriat" => $data["results"][0]["categories"] ?? [],
            "pisteet" => $data["results"][0]["category_scores"] ?? []
        ];
    }
}

?>