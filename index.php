<?php
session_start();
if (isset($_SESSION['id_usuario'])) {
    header("Location: " . ($_SESSION['id_perfil'] == 1 ? "dashboard.php" : "reciclador.php"));
    exit();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGestión - Acceso al Sistema</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-card">
            <div class="login-header">
                <h1>EcoGestión</h1>
                <p>Trazabilidad de Residuos Industriales<br><strong>Logística Verde Sur</strong></p>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-error">RUT o contraseña incorrectos. Intente nuevamente.</div>
            <?php endif; ?>

            <?php if (isset($_GET['logout'])): ?>
                <div class="alert alert-success">Sesión cerrada correctamente.</div>
            <?php endif; ?>

            <?php if (isset($_GET['auth'])): ?>
                <div class="alert alert-error">Debe iniciar sesión para acceder al sistema.</div>
            <?php endif; ?>

            <form action="login_process.php" method="POST">
                <div class="form-group">
                    <label for="rut">RUT Usuario</label>
                    <input type="text" id="rut" name="rut" placeholder="Ej: 12345678-9" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña</label>
                    <input type="password" id="password" name="password" placeholder="Ingrese su clave" required>
                </div>

                <button type="submit" class="btn btn-primary">Iniciar Sesión</button>
            </form>

            <div class="demo-box">
                <strong>Perfiles de Acceso:</strong><br>
                • Admin: <code>12345678-9</code> | Clave: <code>123456</code><br>
                • Reciclador: <code>98765432-1</code> | Clave: <code>123456</code>
            </div>
        </div>
    </div>
</body>
</html>