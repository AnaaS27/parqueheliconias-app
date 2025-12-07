<?php
// =====================================
//  🔵 CONEXIÓN ANTIGUA (MySQL LOCAL)
// =====================================
// $host = "localhost";
// $usuario = "root";
// $contrasena = "";
// $base_datos = "parqueheliconias";
// $conn = new mysqli($host, $usuario, $contrasena, $base_datos);
// if ($conn->connect_error) {
//     die("Error de conexión: " . $conn->connect_error);
// }

// =====================================
//  🟢 NUEVA CONEXIÓN A SUPABASE (POSTGRES)
// =====================================

// Datos de Supabase (reemplaza con los reales)

//$host = "aws-1-us-east-2.pooler.supabase.com";
//$port = "5432";
//$dbname = "postgres";
//$user = "postgres.umncnddwzmjxgmvisvqz"; // debe ser EXACTAMENTE el usuario de Supabase
//$password = "angela12";

// conexión forzando SSL
//$conn = pg_connect("host=$host port=$port dbname=$dbname user=$user password=$password sslmode=require");

//if (!$conn) {
   // die("❌ Error en la conexión: " . pg_last_error());
//}

// =====================================
//  🔐 CONEXIÓN A SUPABASE DESDE RENDER
// =====================================

// Variables de entorno (configuradas en Render)
//$database_url = getenv("DATABASE_URL");

//if (!$database_url) {
 //   die("❌ ERROR: La variable DATABASE_URL no está configurada en Render.");
//}

// Parsear DATABASE_URL
//$parsed = parse_url($database_url);

//$host = $parsed["host"];
//$port = $parsed["port"] ?? 5432;
//$user = $parsed["user"];
//$password = $parsed["pass"];
//$dbname = ltrim($parsed["path"], '/');

// Conectar usando SSL
//$conn_string = "host=$host port=$port dbname=$dbname user=$user password=$password sslmode=require";

//$conn = pg_connect($conn_string);

//if (!$conn) {
//    die("❌ Error en la conexión: " . pg_last_error());
//}
//?>
