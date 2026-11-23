<?php
// Configuración para entorno local (XAMPP)
// Al subir a InfinityFree, reemplaza estos 4 valores por los de tu panel de hosting:
$host = "localhost";
$user = "root";
$pass = "";
$db   = "ecogestion_db";

mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli($host, $user, $pass, $db);
    $conn->set_charset("utf8mb4");
} catch (mysqli_sql_exception $e) {
    die("Error de conexión con la base de datos: " . $e->getMessage());
}
?>