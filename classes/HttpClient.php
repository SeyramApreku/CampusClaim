<?php

class HttpClient
{
    public function request($method, $url, array $headers = [], $body = null)
    {
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_TIMEOUT => 25,
            CURLOPT_HTTPHEADER => array_merge(['Content-Type: application/json'], $headers),
        ]);

        if ($body !== null) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($body, JSON_THROW_ON_ERROR));
        }

        $response = curl_exec($ch);
        $status = (int) curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($response === false || $error !== '') {
            throw new RuntimeException("HTTP request failed: $error");
        }

        $decoded = $response === '' ? [] : json_decode($response, true);
        if ($status < 200 || $status >= 300) {
            $message = is_array($decoded)
                ? ($decoded['error']['message'] ?? $decoded['status']['error'] ?? $response)
                : $response;
            throw new RuntimeException("Remote service returned HTTP $status: $message");
        }

        if ($response !== '' && !is_array($decoded)) {
            throw new RuntimeException('Remote service returned invalid JSON.');
        }

        return $decoded;
    }
}
