<?php
// includes/supabase.php

date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$supabase_url = getenv("DATABASE_URL");
$supabase_key = getenv("SUPABASE_KEY");

if (!$supabase_url || !$supabase_key) {
    die("❌ ERROR: Variables DATABASE_URL o SUPABASE_KEY no configuradas.");
}

// Normalizamos base URL
$supabase_url = rtrim($supabase_url, '/');

if (!function_exists('supabase_get')) {

    /* ===============================
       SELECT (GET)
       =============================== */
    function supabase_get($endpoint) {
        global $supabase_url, $supabase_key;

        $url = $supabase_url . "/rest/v1/" . ltrim($endpoint, '/');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HEADER, true); // 🔥 LEER HEADERS
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $supabase_key",
            "Authorization: Bearer $supabase_key",
            "Accept: application/json",
            "Content-Type: application/json",
            "Prefer: count=exact"
        ]);

        $response   = curl_exec($ch);
        $headerSize = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
        $headers    = substr($response, 0, $headerSize);
        $body       = substr($response, $headerSize);
        $code       = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        // Convertir JSON del body
        $data = json_decode($body, true);

        // 🔥 Extraer TOTAL desde Content-Range
        $total = null;
        if (preg_match('/Content-Range:\s*\d+-\d+\/(\d+)/i', $headers, $match)) {
            $total = intval($match[1]);
        }

        return [
            $code,
            $data ?: [],
            $total // 👉 ahora tienes el total correcto de registros
        ];
    }

    /* ===============================
       INSERT (POST)
       =============================== */
    function supabase_insert($table, $data) {
        global $supabase_url, $supabase_key;

        $url = $supabase_url . "/rest/v1/" . $table;

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $supabase_key",
            "Authorization: Bearer $supabase_key",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        return [$code, $data];
    }

    /* ===============================
       UPDATE (PATCH)
       endpoint ejemplo: "usuarios?id_usuario=eq.5"
       =============================== */
    function supabase_update($endpoint, $data) {
        global $supabase_url, $supabase_key;

        $url = $supabase_url . "/rest/v1/" . ltrim($endpoint, '/');

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "PATCH");
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            "apikey: $supabase_key",
            "Authorization: Bearer $supabase_key",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ]);

        $response = curl_exec($ch);
        $code     = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        $data = json_decode($response, true);

        return [$code, $data];
    }
}
?>
