<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>EcoGestión - Inicio de Sesión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <h2>EcoGestión</h2>
            <p>Trazabilidad de Residuos Industriales</p>
            <form action="login.php" method="POST">
                <label for="rut">RUT</label>
                <input type="text" id="rut" name="rut" placeholder="Ej: 12345678-9" required>
                
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" placeholder="******" required>
                
                <button type="submit">Iniciar Sesión</button>
            </form>
        </div>
    </div>
</body>
</html>