<?php
session_start();
require 'conexion.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $rut = $_POST['rut'];
    $password = $_POST['password'];

    // Consulta real a la BD usando consultas preparadas (Seguridad)
    $stmt = $conn->prepare("SELECT id_usuario, nombre, id_perfil FROM USUARIO WHERE rut = ? AND password = ?");
    $stmt->bind_param("ss", $rut, $password);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows == 1) {
        $row = $result->fetch_assoc();
        $_SESSION['usuario_id'] = $row['id_usuario'];
        $_SESSION['nombre'] = $row['nombre'];
        $_SESSION['perfil'] = $row['id_perfil'];
        
        header("Location: dashboard.php");
    } else {
        echo "<script>alert('Credenciales incorrectas'); window.location.href='index.php';</script>";
    }
}
?>