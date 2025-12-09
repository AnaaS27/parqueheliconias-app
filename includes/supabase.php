<?php

date_default_timezone_set('America/Bogota');

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$supabase_url = getenv("DATABASE_URL");
$supabase_key = getenv("SUPABASE_KEY");

if (!$supabase_url || !$supabase_key) {
    die("❌ ERROR: Variables DATABASE_URL o SUPABASE_KEY no configuradas.");
}

/* ===============================
   SELECT (GET)
   =============================== */
function supabase_get($endpoint, $limit = 10, $offset = 0) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . "/rest/v1/" . $endpoint;

    // Calcular rangos
    $start = $offset;
    $end = $offset + $limit - 1;

    $headers = [
        "apikey: $supabase_key",
        "Authorization: Bearer $supabase_key",
        "Accept: application/json",
        "Prefer: count=exact",
        "Range: $start-$end",
        "Range-Unit: items"
    ];

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

    $response = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

    // Supabase devuelve el total en el header Content-Range
    $headers_info = [];
    $header_size = curl_getinfo($ch, CURLINFO_HEADER_SIZE);
    $header_text = substr($response, 0, $header_size);

    foreach (explode("\r\n", $header_text) as $header_line) {
        if (stripos($header_line, "content-range:") === 0) {
            $headers_info["content-range"] = trim(substr($header_line, 15));
        }
    }

    curl_close($ch);

    // Extraer total de la BD
    $total = null;
    if (!empty($headers_info["content-range"])) {
        // Ejemplo: "0-9/57"
        $parts = explode("/", $headers_info["content-range"]);
        if (count($parts) === 2) {
            $total = intval($parts[1]);
        }
    }

    // El cuerpo es solo JSON (sin headers)
    $json = json_decode($response, true);

    return [$code, $json, $total];
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
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, json_decode($response, true)];
}

/* ===============================
   UPDATE (PATCH)
   =============================== */
function supabase_update($endpoint, $data) {
    global $supabase_url, $supabase_key;

    $url = $supabase_url . "/rest/v1/" . $endpoint;

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
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    return [$code, json_decode($response, true)];
}
?>
