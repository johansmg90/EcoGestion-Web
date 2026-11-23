<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 1) {
    header("Location: index.php?auth=1");
    exit();
}

$mensaje = "";
$tipo_alerta = "";

// Crear nuevo usuario con contraseña Bcrypt
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_usuario'])) {
    $rut       = trim($_POST['rut']);
    $nombre    = trim($_POST['nombre']);
    $pass_raw  = $_POST['password'];
    $id_perfil = intval($_POST['id_perfil']);

    $hash = password_hash($pass_raw, PASSWORD_BCRYPT);

    try {
        $stmt = $conn->prepare("INSERT INTO usuario (rut, nombre, password, id_perfil) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("sssi", $rut, $nombre, $hash, $id_perfil);
        $stmt->execute();
        $mensaje = "Usuario '$nombre' creado exitosamente con contraseña encriptada (Bcrypt).";
        $tipo_alerta = "alert-success";
    } catch (mysqli_sql_exception $e) {
        $mensaje = "Error: El RUT ingresado ya se encuentra registrado en el sistema.";
        $tipo_alerta = "alert-error";
    }
}

// Cambiar perfil de usuario existente
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cambiar_perfil'])) {
    $id_u = intval($_POST['id_usuario_edit']);
    $nuevo_perfil = intval($_POST['nuevo_perfil']);
    $stmt = $conn->prepare("UPDATE usuario SET id_perfil = ? WHERE id_usuario = ?");
    $stmt->bind_param("ii", $nuevo_perfil, $id_u);
    $stmt->execute();
    $mensaje = "Perfil de usuario actualizado correctamente.";
    $tipo_alerta = "alert-success";
}

$perfiles = $conn->query("SELECT * FROM perfil ORDER BY id_perfil");
$usuarios = $conn->query("
    SELECT u.id_usuario, u.rut, u.nombre, u.id_perfil, p.nombre_rol 
    FROM usuario u 
    INNER JOIN perfil p ON u.id_perfil = p.id_perfil 
    ORDER BY u.id_usuario ASC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Usuarios - EcoGestión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h1>EcoGestión</h1>
                <span>Admin: <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            </div>
            <div class="nav-links">
                <a href="dashboard.php">Acopio Residuos</a>
                <a href="solicitudes.php">Solicitudes Retiro</a>
                <a href="usuarios.php" class="active">Usuarios y Perfiles</a>
                <a href="auditoria.php">Auditoría y Comprobantes</a>
                <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container grid-2">
        <div class="card">
            <h2>Registrar Cuenta de Usuario</h2>
            <?php if ($mensaje): ?>
                <div class="alert <?= $tipo_alerta ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="POST" action="usuarios.php">
                <div class="form-group">
                    <label for="rut">RUT (con guion)</label>
                    <input type="text" id="rut" name="rut" placeholder="Ej: 15432876-5" required>
                </div>

                <div class="form-group">
                    <label for="nombre">Nombre Completo</label>
                    <input type="text" id="nombre" name="nombre" placeholder="Ej: María González" required>
                </div>

                <div class="form-group">
                    <label for="password">Contraseña Inicial</label>
                    <input type="password" id="password" name="password" minlength="4" placeholder="Mínimo 4 caracteres" required>
                </div>

                <div class="form-group">
                    <label for="id_perfil">Perfil / Rol del Sistema</label>
                    <select id="id_perfil" name="id_perfil" required>
                        <?php while ($p = $perfiles->fetch_assoc()): ?>
                            <option value="<?= $p['id_perfil'] ?>"><?= htmlspecialchars($p['nombre_rol']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" name="crear_usuario" class="btn btn-primary">Crear Usuario</button>
            </form>
        </div>

        <div class="card">
            <h2>Cuentas y Perfiles Registrados</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>RUT</th>
                            <th>Nombre</th>
                            <th>Rol Actual</th>
                            <th>Cambiar Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($u = $usuarios->fetch_assoc()): ?>
                            <tr>
                                <td><?= $u['id_usuario'] ?></td>
                                <td><?= htmlspecialchars($u['rut']) ?></td>
                                <td><?= htmlspecialchars($u['nombre']) ?></td>
                                <td><strong><?= htmlspecialchars($u['nombre_rol']) ?></strong></td>
                                <td>
                                    <form method="POST" style="display:flex; gap:6px; align-items:center;">
                                        <input type="hidden" name="id_usuario_edit" value="<?= $u['id_usuario'] ?>">
                                        <select name="nuevo_perfil" style="min-height:36px; padding:4px 8px; font-size:0.85rem;">
                                            <option value="1" <?= $u['id_perfil'] == 1 ? 'selected' : '' ?>>Administrador</option>
                                            <option value="2" <?= $u['id_perfil'] == 2 ? 'selected' : '' ?>>Reciclador</option>
                                        </select>
                                        <button type="submit" name="cambiar_perfil" class="btn btn-primary btn-small">Guardar</button>
                                    </form>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>