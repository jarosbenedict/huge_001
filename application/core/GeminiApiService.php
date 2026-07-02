<?php

class GeminiApiService
{
    public static function ask($prompt)
    {
        // 1) Prompt validieren
        $prompt = trim($prompt);
        if (empty($prompt)) {
            return self::FALLBACK_MESSAGE;
        }

        // API-Key aus der Konfiguration laden (nur einmal sonnst geht nicht)
        if (self::$apiKey === null) {
            self::$apiKey = Config::get('GEMINI_API_KEY');
        }

        // 2) Request-Payload zusammenbauen (Gemini API Format von aistudio.google.com)
        $payload = json_encode([
            'contents' => [
                [
                    'parts' => [
                        ['text' => $prompt]
                    ]
                ]
            ]
        ]);

        // 3) cURL initialisieren
        $ch = curl_init();

        // 4) cURL-Optionen setzen
        curl_setopt_array($ch, [
            CURLOPT_URL            => self::API_URL . '?key=' . self::$apiKey,
            CURLOPT_POST           => true,
            CURLOPT_POSTFIELDS     => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => self::CURL_TIMEOUT,
            CURLOPT_CONNECTTIMEOUT => 10,
            CURLOPT_HTTPHEADER     => [
                'Content-Type: aypplication/json',
            ],
            // XAMPP/Windows: SSL-Zertifikatsprüfung deaktivieren von Stackoverflow
            CURLOPT_SSL_VERIFYPEER => false,
            CURLOPT_SSL_VERIFYHOST => false,
        ]);

        // 5) Request ausführen
        $response   = curl_exec($ch);
        $httpCode   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError  = curl_error($ch);
        $curlErrNo  = curl_errno($ch);
        curl_close($ch);

        // 6) Stackoverflow cURL-Fehler prüfen
        if ($response === false || !empty($curlError)) {
            error_log('GeminiApiService cURL Error #' . $curlErrNo . ': ' . $curlError);
            // Debug: zeige den genauen Fehler statt Fallback
            return self::FALLBACK_MESSAGE . ' [cURL #' . $curlErrNo . ': ' . $curlError . ']';
        }

        // 7) Fehlerbehandlung: HTTP-Status prüfen
        if ($httpCode !== 200) {
            $apiError = json_decode($response, true);
            $apiMsg   = $apiError['error']['message'] ?? substr($response, 0, 300);
            error_log('GeminiApiService HTTP ' . $httpCode . ': ' . $apiMsg);

            // API fehler kein Fallback
            return self::FALLBACK_MESSAGE . ' [HTTP ' . $httpCode . ': ' . $apiMsg . ']';
        }

        $data = json_decode($response, true);

        // 8) Antwort-Text aus dem verschachtelten Gemini-Response-Format extrahieren
        $text = $data['candidates'][0]['content']['parts'][0]['text'] ?? null;

        if (empty($text)) {
            error_log('GeminiApiService: Leere oder unerwartete Antwort. ' . substr($response, 0, 500));
            return self::FALLBACK_MESSAGE;
        }

        return trim($text);
    }
}
