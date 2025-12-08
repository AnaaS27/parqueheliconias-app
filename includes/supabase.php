<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/* ===========================================================
   CONFIGURACIÓN SUPABASE CORRECTA
   =========================================================== */
$supabase_url = getenv("DATABASE_URL");     // 👉 ESTA ES LA URL REAL
$supabase_key = getenv("SUPABASE_KEY");     // 👉 CLAVE API

if (!$supabase_url || !$supabase_key) {
    die("❌ ERROR: Las variables SUPABASE_URL y SUPABASE_KEY no están configuradas.");
}

/* ===========================================================
   GET (SELECT)
   =========================================================== */
function supabase_get($endpoint) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . "/rest/v1/" . $endpoint;

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Content-Type: application/json",
        "Accept: application/json"
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [$code, json_decode($response, true)];
}

/* ===========================================================
   POST (INSERT)
   =========================================================== */
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
        "Accept: application/json",
        "Prefer: return=representation"
    ]);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    curl_close($ch);

    return [$code, json_decode($response, true)];
}
