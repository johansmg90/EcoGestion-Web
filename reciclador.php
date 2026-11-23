<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 2) {
    header("Location: index.php?auth=1");
    exit();
}

$mensaje = "";
$tipo_alerta = "";
$id_reciclador = $_SESSION['id_usuario'];

// Procesar Formulario 3: Actualizar Estado Logístico + Historial Inmutable + Comprobante
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['actualizar_estado'])) {
    $id_solicitud = intval($_POST['id_solicitud']);
    $nuevo_estado = $_POST['nuevo_estado'];

    $conn->begin_transaction();
    try {
        // 1. Actualizar estado en solicitud_retiro
        $stmt1 = $conn->prepare("UPDATE solicitud_retiro SET estado = ? WHERE id_solicitud = ? AND id_usuario_reciclador = ?");
        $stmt1->bind_param("sii", $nuevo_estado, $id_solicitud, $id_reciclador);
        $stmt1->execute();

        // 2. Insertar registro inmutable en historial_estado (Audit Trail)
        $stmt2 = $conn->prepare("INSERT INTO historial_estado (estado_nuevo, fecha_cambio, id_solicitud, id_usuario_cambio) VALUES (?, NOW(), ?, ?)");
        $stmt2->bind_param("sii", $nuevo_estado, $id_solicitud, $id_reciclador);
        $stmt2->execute();

        // 3. Si el estado es 'Completado', generar Comprobante único si aún no existe
        $msg_extra = "";
        if ($nuevo_estado === 'Completado') {
            $check = $conn->prepare("SELECT codigo_trazabilidad FROM comprobante WHERE id_solicitud = ?");
            $check->bind_param("i", $id_solicitud);
            $check->execute();
            $res_check = $check->get_result();

            if ($res_check->num_rows === 0) {
                $codigo_unico = "ECO-" . date("Y") . "-" . str_pad($id_solicitud, 4, "0", STR_PAD_LEFT) . "-" . strtoupper(substr(md5(uniqid()), 0, 4));
                $stmt3 = $conn->prepare("INSERT INTO comprobante (codigo_trazabilidad, id_solicitud) VALUES (?, ?)");
                $stmt3->bind_param("si", $codigo_unico, $id_solicitud);
                $stmt3->execute();
                $msg_extra = " Se ha emitido el Certificado de Trazabilidad: $codigo_unico.";
            }
        }

        $conn->commit();
        $mensaje = "Estado de la orden #$id_solicitud actualizado a '$nuevo_estado'.$msg_extra";
        $tipo_alerta = "alert-success";
    } catch (Exception $e) {
        $conn->rollback();
        $mensaje = "Error al actualizar el estado logístico: " . $e->getMessage();
        $tipo_alerta = "alert-error";
    }
}

$mis_solicitudes = $conn->prepare("
    SELECT sr.id_solicitud, sr.fecha_prog, sr.estado, r.peso_kg, c.tipo_residuo, c.peligrosidad, s.nombre_sucursal, s.direccion
    FROM solicitud_retiro sr
    INNER JOIN residuo r ON sr.id_residuo = r.id_residuo
    INNER JOIN categoria_residuo c ON r.id_categoria = c.id_categoria
    INNER JOIN sucursal s ON r.id_sucursal = s.id_sucursal
    WHERE sr.id_usuario_reciclador = ?
    ORDER BY sr.id_solicitud DESC
");
$mis_solicitudes->bind_param("i", $id_reciclador);
$mis_solicitudes->execute();
$lista_ordenes = $mis_solicitudes->get_result();
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Reciclador - EcoGestión</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <div class="nav-brand">
                <h1>EcoGestión</h1>
                <span>Reciclador: <?= htmlspecialchars($_SESSION['nombre']) ?></span>
            </div>
            <div class="nav-links">
                <a href="reciclador.php" class="active">Mis Retiros Asignados</a>
                <a href="auditoria.php">Auditoría y Comprobantes</a>
                <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="card">
            <h2>3. Gestión Logística de Retiros Asignados</h2>
            <?php if ($mensaje): ?>
                <div class="alert <?= $tipo_alerta ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <div class="table-responsive">
                <table>
                    <thead>
                        <tr>
                            <th>Orden</th>
                            <th>Fecha Prog.</th>
                            <th>Sucursal y Dirección</th>
                            <th>Residuo y Peso</th>
                            <th>Estado Actual</th>
                            <th>Actualizar Estado Logístico</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($ord = $lista_ordenes->fetch_assoc()): ?>
                            <?php $clase_est = 'badge-' . str_replace(' ', '-', strtolower($ord['estado'])); ?>
                            <tr>
                                <td><strong>#<?= $ord['id_solicitud'] ?></strong></td>
                                <td><?= htmlspecialchars($ord['fecha_prog']) ?></td>
                                <td>
                                    <strong><?= htmlspecialchars($ord['nombre_sucursal']) ?></strong><br>
                                    <small><?= htmlspecialchars($ord['direccion']) ?></small>
                                </td>
                                <td><?= htmlspecialchars($ord['tipo_residuo']) ?> (<strong><?= $ord['peso_kg'] ?> kg</strong>)</td>
                                <td><span class="badge <?= $clase_est ?>"><?= htmlspecialchars($ord['estado']) ?></span></td>
                                <td>
                                    <form method="POST" style="display:flex; gap:8px; align-items:center;">
                                        <input type="hidden" name="id_solicitud" value="<?= $ord['id_solicitud'] ?>">
                                        <select name="nuevo_estado" style="min-height:38px; padding:5px 8px; font-size:0.88rem;">
                                            <option value="Pendiente" <?= $ord['estado'] == 'Pendiente' ? 'selected' : '' ?>>Pendiente</option>
                                            <option value="En Ruta" <?= $ord['estado'] == 'En Ruta' ? 'selected' : '' ?>>En Ruta</option>
                                            <option value="Completado" <?= $ord['estado'] == 'Completado' ? 'selected' : '' ?>>Completado</option>
                                        </select>
                                        <button type="submit" name="actualizar_estado" class="btn btn-primary btn-small">Actualizar</button>
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