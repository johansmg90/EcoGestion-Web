<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario'])) {
    header("Location: index.php?auth=1");
    exit();
}

$historial = $conn->query("
    SELECT h.id_historial, h.estado_nuevo, h.fecha_cambio, h.id_solicitud, u.nombre AS responsable, p.nombre_rol
    FROM historial_estado h
    INNER JOIN usuario u ON h.id_usuario_cambio = u.id_usuario
    INNER JOIN perfil p ON u.id_perfil = p.id_perfil
    ORDER BY h.id_historial DESC
");

$comprobantes = $conn->query("
    SELECT comp.id_comprobante, comp.codigo_trazabilidad, comp.id_solicitud, r.peso_kg, c.tipo_residuo, s.nombre_sucursal, u.nombre AS reciclador
    FROM comprobante comp
    INNER JOIN solicitud_retiro sr ON comp.id_solicitud = sr.id_solicitud
    INNER JOIN residuo r ON sr.id_residuo = r.id_residuo
    INNER JOIN categoria_residuo c ON r.id_categoria = c.id_categoria
    INNER JOIN sucursal s ON r.id_sucursal = s.id_sucursal
    INNER JOIN usuario u ON sr.id_usuario_reciclador = u.id_usuario
    ORDER BY comp.id_comprobante DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Auditoría y Comprobantes - EcoGestión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h1>EcoGestión</h1>
                <span><?= htmlspecialchars($_SESSION['rol']) ?>: <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            </div>
            <div class="nav-links">
                <?php if ($_SESSION['id_perfil'] == 1): ?>
                    <a href="dashboard.php">Acopio Residuos</a>
                    <a href="solicitudes.php">Solicitudes Retiro</a>
                    <a href="usuarios.php">Usuarios y Perfiles</a>
                <?php else: ?>
                    <a href="reciclador.php">Mis Retiros Asignados</a>
                <?php endif; ?>
                <a href="auditoria.php" class="active">Auditoría y Comprobantes</a>
                <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>Comprobantes de Trazabilidad Emitidos</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Código Trazabilidad</th>
                            <th>Orden</th>
                            <th>Sucursal Origen</th>
                            <th>Residuo</th>
                            <th>Peso Certificado</th>
                            <th>Reciclador</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($comprobantes->num_rows === 0): ?>
                            <tr><td colspan="6">Aún no existen retiros completados con certificado emitido.</td></tr>
                        <?php else: ?>
                            <?php while ($comp = $comprobantes->fetch_assoc()): ?>
                                <tr>
                                    <td><strong><code><?= htmlspecialchars($comp['codigo_trazabilidad']) ?></code></strong></td>
                                    <td>#<?= $comp['id_solicitud'] ?></td>
                                    <td><?= htmlspecialchars($comp['nombre_sucursal']) ?></td>
                                    <td><?= htmlspecialchars($comp['tipo_residuo']) ?></td>
                                    <td><?= $comp['peso_kg'] ?> kg</td>
                                    <td><?= htmlspecialchars($comp['reciclador']) ?></td>
                                </tr>
                            <?php endwhile; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <h2>Audit Trail — Historial Inmutable de Estados</h2>
            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>ID Evento</th>
                            <th>Orden Retiro</th>
                            <th>Nuevo Estado Registrado</th>
                            <th>Fecha y Hora Exacta</th>
                            <th>Usuario Responsable</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($h = $historial->fetch_assoc()): ?>
                            <?php $clase_est = 'badge-' . str_replace(' ', '-', strtolower($h['estado_nuevo'])); ?>
                            <tr>
                                <td>#<?= $h['id_historial'] ?></td>
                                <td>Orden #<?= $h['id_solicitud'] ?></td>
                                <td><span class="badge <?= $clase_est ?>"><?= htmlspecialchars($h['estado_nuevo']) ?></span></td>
                                <td><?= htmlspecialchars($h['fecha_cambio']) ?></td>
                                <td><?= htmlspecialchars($h['responsable']) ?> (<?= htmlspecialchars($h['nombre_rol']) ?>)</td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>
</html>