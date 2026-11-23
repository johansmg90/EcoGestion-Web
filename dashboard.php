<?php
session_start();
require_once 'conexion.php';

if (!isset($_SESSION['id_usuario']) || $_SESSION['id_perfil'] != 1) {
    header("Location: index.php?auth=1");
    exit();
}

$mensaje = "";
$tipo_alerta = "";

// Procesar Formulario 1: Registro de Residuo
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar_residuo'])) {
    $peso_kg      = floatval($_POST['peso_kg'] ?? 0);
    $fecha        = $_POST['fecha'] ?? date('Y-m-d');
    $id_sucursal  = intval($_POST['id_sucursal'] ?? 0);
    $id_categoria = intval($_POST['id_categoria'] ?? 0);
    $id_usuario   = $_SESSION['id_usuario'];

    if ($peso_kg <= 0) {
        $mensaje = "Error: El peso del residuo debe ser mayor a 0 kg.";
        $tipo_alerta = "alert-error";
    } else {
        $stmt = $conn->prepare("INSERT INTO residuo (peso_kg, fecha, id_categoria, id_sucursal, id_usuario_registra) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("dsiii", $peso_kg, $fecha, $id_categoria, $id_sucursal, $id_usuario);
        if ($stmt->execute()) {
            $mensaje = "Residuo registrado exitosamente en el inventario de acopio.";
            $tipo_alerta = "alert-success";
        }
    }
}

// Prueba de Integridad Referencial: Intentar eliminar categoría
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['eliminar_categoria'])) {
    $id_cat_del = intval($_POST['id_categoria_del']);
    try {
        $stmt = $conn->prepare("DELETE FROM categoria_residuo WHERE id_categoria = ?");
        $stmt->bind_param("i", $id_cat_del);
        $stmt->execute();
        $mensaje = "Categoría eliminada correctamente.";
        $tipo_alerta = "alert-success";
    } catch (mysqli_sql_exception $e) {
        $mensaje = "Integridad Referencial Activa: No se puede eliminar esta categoría porque tiene residuos asociados.";
        $tipo_alerta = "alert-error";
    }
}

$sucursales = $conn->query("SELECT * FROM sucursal ORDER BY nombre_sucursal");
$categorias = $conn->query("SELECT * FROM categoria_residuo ORDER BY tipo_residuo");
$categorias_list = $conn->query("SELECT * FROM categoria_residuo ORDER BY id_categoria");

$residuos = $conn->query("
    SELECT r.id_residuo, r.peso_kg, r.fecha, c.tipo_residuo, c.peligrosidad, s.nombre_sucursal, u.nombre AS registrador
    FROM residuo r
    INNER JOIN categoria_residuo c ON r.id_categoria = c.id_categoria
    INNER JOIN sucursal s ON r.id_sucursal = s.id_sucursal
    INNER JOIN usuario u ON r.id_usuario_registra = u.id_usuario
    ORDER BY r.id_residuo DESC
");
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel Administrador - EcoGestión</title>
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
                <a href="dashboard.php" class="active">Acopio Residuos</a>
                <a href="solicitudes.php">Solicitudes Retiro</a>
                <a href="usuarios.php">Usuarios y Perfiles</a>
                <a href="auditoria.php">Auditoría y Comprobantes</a>
                <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
            </div>
        </div>
    </nav>

    <div class="container grid-2">
        <div class="card">
            <h2>1. Registrar Acopio de Residuo</h2>
            <?php if ($mensaje): ?>
                <div class="alert <?= $tipo_alerta ?>"><?= htmlspecialchars($mensaje) ?></div>
            <?php endif; ?>

            <form method="POST" action="dashboard.php">
                <div class="form-group">
                    <label for="peso_kg">Peso del Residuo (kg)</label>
                    <input type="number" id="peso_kg" name="peso_kg" step="0.01" min="0.01" placeholder="Ej: 185.50" required>
                </div>

                <div class="form-group">
                    <label for="fecha">Fecha de Ingreso</label>
                    <input type="date" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" required>
                </div>

                <div class="form-group">
                    <label for="id_sucursal">Sucursal de Acopio</label>
                    <select id="id_sucursal" name="id_sucursal" required>
                        <option value="">-- Seleccione Sucursal --</option>
                        <?php while ($s = $sucursales->fetch_assoc()): ?>
                            <option value="<?= $s['id_sucursal'] ?>"><?= htmlspecialchars($s['nombre_sucursal']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="form-group">
                    <label for="id_categoria">Categoría del Residuo</label>
                    <select id="id_categoria" name="id_categoria" required>
                        <option value="">-- Seleccione Categoría --</option>
                        <?php while ($c = $categorias->fetch_assoc()): ?>
                            <option value="<?= $c['id_categoria'] ?>">
                                <?= htmlspecialchars($c['tipo_residuo']) ?> (Peligrosidad: <?= htmlspecialchars($c['peligrosidad']) ?>)
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <button type="submit" name="registrar_residuo" class="btn btn-primary">Guardar Residuo en Inventario</button>
            </form>
        </div>

        <div style="display: flex; flex-direction: column; gap: 20px;">
            <div class="card">
                <h2>Inventario de Residuos Acopiados</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Fecha</th>
                                <th>Sucursal</th>
                                <th>Categoría</th>
                                <th>Peligrosidad</th>
                                <th>Peso (kg)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($r = $residuos->fetch_assoc()): ?>
                                <?php $badge = 'badge-' . strtolower($r['peligrosidad']); ?>
                                <tr>
                                    <td>#<?= $r['id_residuo'] ?></td>
                                    <td><?= htmlspecialchars($r['fecha']) ?></td>
                                    <td><?= htmlspecialchars($r['nombre_sucursal']) ?></td>
                                    <td><?= htmlspecialchars($r['tipo_residuo']) ?></td>
                                    <td><span class="badge <?= $badge ?>"><?= htmlspecialchars($r['peligrosidad']) ?></span></td>
                                    <td><strong><?= number_format($r['peso_kg'], 2) ?> kg</strong></td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="card">
                <h2>Catálogo de Categorías (Control Integridad Referencial)</h2>
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Tipo de Residuo</th>
                                <th>Nivel Peligrosidad</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($cat = $categorias_list->fetch_assoc()): ?>
                                <tr>
                                    <td><?= $cat['id_categoria'] ?></td>
                                    <td><?= htmlspecialchars($cat['tipo_residuo']) ?></td>
                                    <td><?= htmlspecialchars($cat['peligrosidad']) ?></td>
                                    <td>
                                        <form method="POST" style="display:inline;">
                                            <input type="hidden" name="id_categoria_del" value="<?= $cat['id_categoria'] ?>">
                                            <button type="submit" name="eliminar_categoria" class="btn btn-danger">Eliminar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>