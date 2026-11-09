<?php
$host = 'localhost';
$user = 'root';
$password = ''; // Tu contraseña de XAMPP
$database = 'ecogestion_db';

$conn = new mysqli($host, $user, $password, $database);

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}
// echo "Conexión exitosa"; // Descomentar para probar
?>