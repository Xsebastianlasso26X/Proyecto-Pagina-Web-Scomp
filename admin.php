<?php
// Inicializar variables para evitar advertencias del editor
$busqueda = '';
$solicitudes = [];
$total_paginas = 1;
$pagina_actual = 1;
$total_registros = 0;

// Conexión a la base de datos
$servidor = "localhost";
$base_datos = "scomp_db";
$usuario = "root";
$password = "";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Configuración de Paginación
    $por_pagina = 10;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if($pagina_actual < 1) $pagina_actual = 1;
    $inicio = ($pagina_actual - 1) * $por_pagina;

    // Recoger el texto de búsqueda si el usuario escribió algo
    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

    if ($busqueda != '') {
        // Solo buscamos entre los que NO están eliminados (estado_eliminado = 0)
        $sql_total = "SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0 AND (nombre LIKE ? OR cedula LIKE ? OR tipo_servicio LIKE ? OR telefono LIKE ? OR fecha LIKE ? OR estado_pago LIKE ? OR estado_servicio LIKE ?)";
        $stmt_total = $conexion->prepare($sql_total);
        $param_busqueda = "%$busqueda%";
        $stmt_total->execute([$param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda]);
        $total_registros = $stmt_total->fetchColumn();

        $sql = "SELECT * FROM solicitudes WHERE estado_eliminado = 0 AND (nombre LIKE ? OR cedula LIKE ? OR tipo_servicio LIKE ? OR telefono LIKE ? OR fecha LIKE ? OR estado_pago LIKE ? OR estado_servicio LIKE ?) ORDER BY id DESC LIMIT $inicio, $por_pagina";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda]);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    } else {
        // Contar solo los activos
        $total_registros = $conexion->query("SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0")->fetchColumn();

        $sql = "SELECT * FROM solicitudes WHERE estado_eliminado = 0 ORDER BY id DESC LIMIT $inicio, $por_pagina";
        $stmt = $conexion->query($sql);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    $total_paginas = ceil($total_registros / $por_pagina);
    if($total_paginas == 0) $total_paginas = 1;

} catch(PDOException $e) {
    echo "Error de conexión: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Panel de Control SCOMP</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #0b0f19; 
            color: #f8fafc; 
            font-family: 'Roboto', sans-serif; 
            padding: 20px; 
            background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .admin-container { max-width: 1200px; margin: auto; background: #121826; padding: 30px; border-radius: 12px; border: 1px solid #1e293b; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        .admin-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px; border-bottom: 1px solid #1e293b; padding-bottom: 15px; }
        
        .logo-img { 
            max-height: 75px; 
            width: auto; 
            display: block;
        }
        
        h3 { color: #38bdf8; font-family: 'Orbitron', sans-serif; font-size: 1.1rem; margin-bottom: 15px; }
        
        .search-form { display: flex; gap: 10px; margin-bottom: 25px; }
        .search-form input { flex: 1; padding: 12px; background: #0b0f19; border: 1px solid #334155; color: #fff; border-radius: 8px; font-size: 0.95rem; }
        .search-form input:focus { border-color: #38bdf8; outline: none; }
        .search-form button { padding: 12px 22px; background: #38bdf8; color: #0b0f19; font-weight: bold; border: none; border-radius: 8px; cursor: pointer; font-family: 'Orbitron', sans-serif; font-size: 0.85rem; }
        .search-form button:hover { background: #0ea5e9; }
        .btn-limpiar { padding: 12px 18px; background: #334155; color: #fff; text-decoration: none; border-radius: 8px; display: flex; align-items: center; font-weight: 500; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #0b0f19; border-radius: 8px; overflow: hidden; border: 1px solid #1e293b; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #1e293b; font-size: 0.9rem; }
        th { background: #121826; color: #38bdf8; font-family: 'Orbitron', sans-serif; font-size: 0.75rem; letter-spacing: 0.5px; }
        tr:hover { background: #161f33; }

        .acciones-btns a { display: inline-block; padding: 6px 10px; border-radius: 6px; color: #fff; text-decoration: none; margin-right: 4px; font-size: 0.85rem; transition: transform 0.1s; }
        .acciones-btns a:hover { transform: scale(1.05); }
        .btn-ver { background: #10b981; }
        .btn-editar { background: #38bdf8; color: #0b0f19; font-weight: bold; }
        .btn-eliminar { background: #ef4444; }

        .pagination { display: flex; justify-content: space-between; align-items: center; margin-top: 25px; padding-top: 15px; border-top: 1px solid #1e293b; }
        .pagination a { background: #1e293b; color: #38bdf8; padding: 10px 18px; border-radius: 8px; text-decoration: none; font-weight: bold; font-family: 'Orbitron', sans-serif; font-size: 0.85rem; border: 1px solid #334155; }
        .pagination a:hover { background: #334155; color: #fff; }
        .pagination span { color: #94a3b8; font-size: 0.9rem; }

        .top-buttons a { background: #1e293b; color: #cbd5e1; padding: 9px 16px; border-radius: 8px; text-decoration: none; margin-left: 10px; font-size: 0.85rem; border: 1px solid #334155; font-weight: 500; }
        .top-buttons a:hover { border-color: #38bdf8; color: #fff; }
        .top-buttons a.btn-salir { background: #7f1d1d; color: #fca5a5; border-color: #991b1b; }
    </style>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <a href="admin.php">
                <img src="logo-scomp.jpg" alt="SCOMP Logo" class="logo-img">
            </a>
            <div class="top-buttons">
                <a href="papelera.php">Papelera</a>
                <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
            </div>
        </div>

        <h3>Solicitudes de Soporte Recibidas</h3>

        <form method="GET" class="search-form">
            <input type="text" name="busqueda" placeholder="Buscar por nombre, cédula, servicio, teléfono, fecha o estado..." value="<?php echo htmlspecialchars($busqueda); ?>">
            <button type="submit">Buscar</button>
            <?php if($busqueda != ''): ?>
                <a href="admin.php" class="btn-limpiar">Limpiar</a>
            <?php endif; ?>
        </form>

        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Servicio</th>
                    <th>Teléfono</th>
                    <th>Fecha</th>
                    <th>Precio</th>
                    <th>Abono</th>
                    <th>Saldo</th>
                    <th>Pago</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (count($solicitudes) > 0): ?>
                    <?php foreach ($solicitudes as $row): ?>
                    <tr>
                        <td>#<?php echo $row['id']; ?></td>
                        <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                        <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                        <td><?php echo htmlspecialchars($row['tipo_servicio']); ?></td>
                        <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                        <td><?php echo $row['fecha']; ?></td>
                        <td>$<?php echo number_format($row['precio_servicio'], 0, ',', '.'); ?></td>
                        <td>$<?php echo number_format($row['abono'], 0, ',', '.'); ?></td>
                        <td>$<?php echo number_format($row['saldo_pendiente'], 0, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($row['estado_pago']); ?></td>
                        <td><?php echo htmlspecialchars($row['estado_servicio']); ?></td>
                        <td class="acciones-btns">
                            <a href="ver.php?id=<?php echo $row['id']; ?>" class="btn-ver" title="Ver">👁️</a>
                            <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn-editar" title="Editar">✏️</a>
                            <!-- CORREGIDO AQUÍ: Apunta a enviar_papelera.php -->
                            <a href="enviar_papelera.php?id=<?php echo $row['id']; ?>" class="btn-eliminar" title="Eliminar" onclick="return confirm('¿Estás seguro de enviar a la papelera?');">🗑️</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="12" style="text-align: center; color: #94a3b8; padding: 20px;">No se encontraron registros coincidentes.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>

        <div class="pagination">
            <div>
                <?php if ($pagina_actual > 1): ?>
                    <a href="admin.php?pagina=<?php echo $pagina_actual - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>">← Anterior</a>
                <?php endif; ?>
            </div>

            <span>Página <strong><?php echo $pagina_actual; ?></strong> de <strong><?php echo $total_paginas; ?></strong> (Total registros: <?php echo $total_registros; ?>)</span>

            <div>
                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="admin.php?pagina=<?php echo $pagina_actual + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?>">Siguiente →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>