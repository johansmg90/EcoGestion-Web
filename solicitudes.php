<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 1) {
    header("Location: index.php?auth=1");
    exit();
}

$mensaje = "";
$tipo_alerta = "";

// Procesar Formulario 2: Crear Solicitud de Retiro + Registro inicial en Audit Trail (Transacción ACID)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['crear_solicitud'])) {
    $id_residuo    = intval($_POST['id_residuo']);
    $id_reciclador = intval($_POST['id_usuario_reciclador']);
    $fecha_prog    = $_POST['fecha_prog'];
    $id_admin      = $_SESSION['id_usuario'];

    $conn->begin_transaction();
    try {
        $stmt1 = $conn->prepare("INSERT INTO solicitud_retiro (fecha_prog, estado, id_residuo, id_usuario_reciclador) VALUES (?, 'Pendiente', ?, ?)");
        $stmt1->bind_param("sii", $fecha_prog, $id_residuo, $id_reciclador);
        $stmt1->execute();
        $id_solicitud_nueva = $conn->insert_id;

        $stmt2 = $conn->prepare("INSERT INTO historial_estado (estado_nuevo, fecha_cambio, id_solicitud, id_usuario_cambio) VALUES ('Pendiente', NOW(), ?, ?)");
        $stmt2->bind_param("ii", $id_solicitud_nueva, $id_admin);
        $stmt2->execute();

        $conn->commit();
        $mensaje = "Solicitud de retiro #$id_solicitud_nueva asignada correctamente y registrada en auditoría.";
        $tipo_alerta = "alert-success";
    } catch (Exception $e) {
        $conn->rollback();
        $mensaje = "Error transaccional al crear la solicitud: " . $e->getMessage();
        $tipo_alerta = "alert-error";
    }
}

$residuos_disp = $conn->query("
    SELECT r.id_residuo, r.peso_kg, c.tipo_residuo, s.nombre_sucursal
    FROM residuo r
    INNER JOIN categoria_residuo c ON r.id_categoria = c.id_categoria
    INNER JOIN sucursal s ON r.id_sucursal = s.id_sucursal
    ORDER BY r.id_residuo DESC
");

$recicladores = $conn->query("SELECT id_usuario, rut, nombre FROM usuario WHERE id_perfil = 2 ORDER BY nombre");

$solicitudes = $conn->query("
    SELECT sr.id_solicitud, sr.fecha_prog, sr.estado, r.peso_kg, c.tipo_residuo, s.nombre_sucursal, u.nombre AS reciclador
    FROM solicitud_retiro sr
    INNER JOIN residuo r ON sr.id_residuo = r.id_residuo
    INNER JOIN categoria_residuo c ON r.id_categoria = c.id_categoria
    INNER JOIN sucursal s ON r.id_sucursal = s.id_sucursal
    INNER JOIN usuario u ON sr.id_usuario_reciclador = u.id_usuario
    ORDER BY sr.id_solicitud DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitudes de Retiro - EcoGestión</title>
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
                <a href="solicitudes.php" class="active">Solicitudes Retiro</a>
                <a href="usuarios.php">Usuarios y Perfiles</a>
                <a href="auditoria.php">Auditoría y Comprobantes</a>
                <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container grid-2">
        <div class="card">
            <h2>2. Asignar Solicitud de Retiro</h2>
            <?php if ($mensaje): ?>
                <div class="alert <?= $tipo_alerta ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="POST" action="solicitudes.php">
                <div class="form-group">
                    <label for="id_residuo">Residuo Acopiado</label>
                    <select id="id_residuo" name="id_residuo" required>
                        <option value="">-- Seleccione Residuo --</option>
                        <?php while ($res = $residuos_disp->fetch_assoc()): ?>
                            <option value="<?= $res['id_residuo'] ?>">
                                #<?= $res['id_residuo'] ?> - <?= htmlspecialchars($res['tipo_residuo']) ?> (<?= $res['peso_kg'] ?> kg - <?= htmlspecialchars($res['nombre_sucursal']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_usuario_reciclador">Reciclador Certificado Asignado</label>
                    <select id="id_usuario_reciclador" name="id_usuario_reciclador" required>
                        <option value="">-- Seleccione Reciclador --</option>
                        <?php while ($rec = $recicladores->fetch_assoc()): ?>
                            <option value="<?= $rec['id_usuario'] ?>">
                                <?= htmlspecialchars($rec['nombre']) ?> (<?= htmlspecialchars($rec['rut']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="fecha_prog">Fecha Programada de Retiro</label>
                    <input type="date" id="fecha_prog" name="fecha_prog" value="<?= date('Y-m-d', strtotime('+1 day')) ?>" required>
                </div>

                <button type="submit" name="crear_solicitud" class="btn btn-primary">Generar Orden de Retiro</button>
            </form>
        </div>

        <div class="card">
            <h2>Órdenes de Retiro Programadas</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Fecha Prog.</th>
                            <th>Sucursal</th>
                            <th>Residuo</th>
                            <th>Reciclador</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($sol = $solicitudes->fetch_assoc()): ?>
                            <?php $clase_est = 'badge-' . str_replace(' ', '-', strtolower($sol['estado'])); ?>
                            <tr>
                                <td><strong>#<?= $sol['id_solicitud'] ?></strong></td>
                                <td><?= htmlspecialchars($sol['fecha_prog']) ?></td>
                                <td><?= htmlspecialchars($sol['nombre_sucursal']) ?></td>
                                <td><?= htmlspecialchars($sol['tipo_residuo']) ?> (<?= $sol['peso_kg'] ?> kg)</td>
                                <td><?= htmlspecialchars($sol['reciclador']) ?></td>
                                <td><span class="badge <?= $clase_est ?>"><?= htmlspecialchars($sol['estado']) ?></span></td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>