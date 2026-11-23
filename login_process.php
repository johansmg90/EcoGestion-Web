<?php
session_start();
require_once 'conexion.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $rut = trim($_POST['rut'] ?? '');
    $password = $_POST['password'] ?? '';

    $stmt = $conn->prepare("
        SELECT u.id_usuario, u.rut, u.nombre, u.password, u.id_perfil, p.nombre_rol 
        FROM usuario u 
        INNER JOIN perfil p ON u.id_perfil = p.id_perfil 
        WHERE u.rut = ?
    ");
    $stmt->bind_param("s", $rut);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($user = $result->fetch_assoc()) {
        if (password_verify($password, $user['password'])) {
            session_regenerate_id(true);
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['rut']        = $user['rut'];
            $_SESSION['nombre']     = $user['nombre'];
            $_SESSION['id_perfil']  = $user['id_perfil'];
            $_SESSION['rol']        = $user['nombre_rol'];

            if ($user['id_perfil'] == 1) {
                header("Location: dashboard.php");
            } else {
                header("Location: reciclador.php");
            }
            exit();
        }
    }

    header("Location: index.php?error=1");
    exit();
}
header("Location: index.php");
exit();
?>