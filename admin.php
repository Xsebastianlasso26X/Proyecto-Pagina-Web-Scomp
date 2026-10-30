<?php
// Inicializar variables para evitar advertencias del editor
$busqueda = '';
$filtro_estado = isset($_GET['filtro']) ? $_GET['filtro'] : '';
$solicitudes = [];
$total_paginas = 1;
$pagina_actual = 1;
$total_registros = 0;

// Variables para el Dashboard (Contadores independientes)
$total_activas = 0;
$total_pendientes = 0;
$total_taller = 0;
$total_completados = 0;
$total_entregados = 0;
$saldo_total_pendiente = 0;

// Conexión a la base de datos
$servidor = "localhost";
$base_datos = "scomp_db";
$usuario = "root";
$password = "";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Procesar borrado masivo (enviar a la papelera)
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ids_seleccionados']) && is_array($_POST['ids_seleccionados'])) {
        $ids = $_POST['ids_seleccionados'];
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        
        $stmt_masivo = $conexion->prepare("UPDATE solicitudes SET estado_eliminado = 1 WHERE id IN ($placeholders)");
        $stmt_masivo->execute($ids);
        
        header("Location: admin.php" . ($filtro_estado ? "?filtro=$filtro_estado" : ""));
        exit();
    }

    // 1. Obtener contadores independientes para cada estado
    $total_activas = $conexion->query("SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0")->fetchColumn();
    
    $total_pendientes = $conexion->query("SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0 AND estado_servicio = 'Pendiente'")->fetchColumn();
    $total_taller = $conexion->query("SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0 AND estado_servicio = 'En Taller / Proceso'")->fetchColumn();
    $total_completados = $conexion->query("SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0 AND estado_servicio = 'Completado'")->fetchColumn();
    $total_entregados = $conexion->query("SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0 AND estado_servicio = 'Entregado'")->fetchColumn();
    
    // Saldo pendiente total
    $saldo_total_pendiente = $conexion->query("SELECT SUM(saldo_pendiente) FROM solicitudes WHERE estado_eliminado = 0")->fetchColumn() ?: 0;

    // Configuración de Paginación
    $por_pagina = 10;
    $pagina_actual = isset($_GET['pagina']) ? (int)$_GET['pagina'] : 1;
    if($pagina_actual < 1) $pagina_actual = 1;
    $inicio = ($pagina_actual - 1) * $por_pagina;

    // Recoger texto de búsqueda y filtro por tarjeta
    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

    // Construcción de la consulta según el filtro seleccionado
    if ($busqueda != '') {
        $sql_total = "SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0 AND (nombre LIKE ? OR cedula LIKE ? OR tipo_servicio LIKE ? OR telefono LIKE ? OR fecha LIKE ? OR estado_pago LIKE ? OR estado_servicio LIKE ?)";
        $stmt_total = $conexion->prepare($sql_total);
        $param_busqueda = "%$busqueda%";
        $stmt_total->execute([$param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda]);
        $total_registros = $stmt_total->fetchColumn();

        $sql = "SELECT * FROM solicitudes WHERE estado_eliminado = 0 AND (nombre LIKE ? OR cedula LIKE ? OR tipo_servicio LIKE ? OR telefono LIKE ? OR fecha LIKE ? OR estado_pago LIKE ? OR estado_servicio LIKE ?) ORDER BY id DESC LIMIT $inicio, $por_pagina";
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda]);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($filtro_estado == 'pendientes') {
        $total_registros = $total_pendientes;
        $sql = "SELECT * FROM solicitudes WHERE estado_eliminado = 0 AND estado_servicio = 'Pendiente' ORDER BY id DESC LIMIT $inicio, $por_pagina";
        $stmt = $conexion->query($sql);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($filtro_estado == 'taller') {
        $total_registros = $total_taller;
        $sql = "SELECT * FROM solicitudes WHERE estado_eliminado = 0 AND estado_servicio = 'En Taller / Proceso' ORDER BY id DESC LIMIT $inicio, $por_pagina";
        $stmt = $conexion->query($sql);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($filtro_estado == 'completados') {
        $total_registros = $total_completados;
        $sql = "SELECT * FROM solicitudes WHERE estado_eliminado = 0 AND estado_servicio = 'Completado' ORDER BY id DESC LIMIT $inicio, $por_pagina";
        $stmt = $conexion->query($sql);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($filtro_estado == 'entregados') {
        $total_registros = $total_entregados;
        $sql = "SELECT * FROM solicitudes WHERE estado_eliminado = 0 AND estado_servicio = 'Entregado' ORDER BY id DESC LIMIT $inicio, $por_pagina";
        $stmt = $conexion->query($sql);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } elseif ($filtro_estado == 'saldo') {
        $total_registros = $conexion->query("SELECT COUNT(*) FROM solicitudes WHERE estado_eliminado = 0 AND saldo_pendiente > 0")->fetchColumn();
        $sql = "SELECT * FROM solicitudes WHERE estado_eliminado = 0 AND saldo_pendiente > 0 ORDER BY id DESC LIMIT $inicio, $por_pagina";
        $stmt = $conexion->query($sql);
        $solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } else {
        $total_registros = $total_activas;
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
        
        .logo-img { max-height: 75px; width: auto; display: block; }
        
        /* Tarjetas del Dashboard distribuidas */
        .dashboard-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .card {
            background: #0b0f19;
            border: 1px solid #1e293b;
            padding: 16px;
            border-radius: 10px;
            display: flex;
            flex-direction: column;
            text-decoration: none;
            transition: all 0.2s ease;
            box-shadow: 0 4px 6px rgba(0,0,0,0.2);
        }
        .card:hover {
            border-color: #38bdf8;
            transform: translateY(-3px);
            background: #101726;
        }
        .card.active-card {
            border-color: #38bdf8;
            background: #162238;
        }
        .card h4 {
            color: #94a3b8;
            font-size: 0.7rem;
            font-weight: 500;
            margin: 0 0 6px 0;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .card .card-value {
            color: #38bdf8;
            font-family: 'Orbitron', sans-serif;
            font-size: 1.4rem;
            font-weight: bold;
        }
        .card.warning .card-value {
            color: #f87171;
        }

        .filtro-activo-aviso {
            background: #1e293b;
            padding: 10px 15px;
            border-radius: 8px;
            margin-bottom: 15px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.9rem;
            color: #38bdf8;
            border: 1px solid #334155;
        }
        .filtro-activo-aviso a {
            background: #ef4444;
            color: white;
            padding: 5px 10px;
            border-radius: 6px;
            text-decoration: none;
            font-size: 0.8rem;
            font-weight: bold;
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

        .acciones-btns {
            display: flex;
            flex-direction: row;
            gap: 4px;
            align-items: center;
        }
        .acciones-btns a { 
            display: inline-block; 
            padding: 6px 8px; 
            border-radius: 6px; 
            color: #fff; 
            text-decoration: none; 
            font-size: 0.8rem; 
            text-align: center;
            transition: transform 0.1s; 
        }
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
        .top-buttons a.btn-excel { background: #10b981; color: white; border-color: #059669; font-weight: bold; }
        .top-buttons a.btn-salir { background: #7f1d1d; color: #fca5a5; border-color: #991b1b; }

        .btn-lote-papelera { background: #ef4444; color: white; border: none; padding: 10px 16px; border-radius: 8px; font-weight: bold; cursor: pointer; margin-bottom: 15px; font-size: 0.85rem; }
        .btn-lote-papelera:hover { background: #dc2626; }
    </style>
    <script>
        function toggleSelectAll(source) {
            let checkboxes = document.getElementsByName('ids_seleccionados[]');
            for(let i=0, n=checkboxes.length; i<n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }
    </script>
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <a href="admin.php">
                <img src="logo-scomp.jpg" alt="SCOMP Logo" class="logo-img">
            </a>
            <div class="top-buttons">
                <a href="exportar_excel.php?busqueda=<?php echo urlencode($busqueda); ?>" class="btn-excel">📊 Exportar Excel</a>
                <a href="papelera.php">Papelera</a>
                <a href="logout.php" class="btn-salir">Cerrar Sesión</a>
            </div>
        </div>

        <!-- TARJETAS DEL DASHBOARD INDEPENDIENTES -->
        <div class="dashboard-cards">
            <!-- 1. Total Activas -->
            <a href="admin.php" class="card <?php echo ($filtro_estado == '' && $busqueda == '') ? 'active-card' : ''; ?>">
                <h4>Total Activas</h4>
                <div class="card-value"><?php echo $total_activas; ?></div>
            </a>

            <!-- 2. Pendientes -->
            <a href="admin.php?filtro=pendientes" class="card <?php echo ($filtro_estado == 'pendientes') ? 'active-card' : ''; ?>">
                <h4>Pendientes</h4>
                <div class="card-value"><?php echo $total_pendientes; ?></div>
            </a>

            <!-- 3. En Taller / Proceso -->
            <a href="admin.php?filtro=taller" class="card <?php echo ($filtro_estado == 'taller') ? 'active-card' : ''; ?>">
                <h4>En Taller</h4>
                <div class="card-value"><?php echo $total_taller; ?></div>
            </a>

            <!-- 4. Completados -->
            <a href="admin.php?filtro=completados" class="card <?php echo ($filtro_estado == 'completados') ? 'active-card' : ''; ?>">
                <h4>Completados</h4>
                <div class="card-value"><?php echo $total_completados; ?></div>
            </a>

            <!-- 5. Entregados -->
            <a href="admin.php?filtro=entregados" class="card <?php echo ($filtro_estado == 'entregados') ? 'active-card' : ''; ?>">
                <h4>Entregados</h4>
                <div class="card-value"><?php echo $total_entregados; ?></div>
            </a>
        </div>

        <?php if ($filtro_estado != ''): ?>
            <div class="filtro-activo-aviso">
                <span>🔍 Filtro activo: <strong><?php echo ucfirst($filtro_estado); ?></strong></span>
                <a href="admin.php">Quitar Filtro</a>
            </div>
        <?php endif; ?>

        <h3>Solicitudes de Soporte Recibidas</h3>

        <form method="GET" class="search-form">
            <input type="text" name="busqueda" placeholder="Buscar por nombre, cédula, servicio, teléfono, fecha o estado..." value="<?php echo htmlspecialchars($busqueda); ?>">
            <?php if($filtro_estado != ''): ?>
                <input type="hidden" name="filtro" value="<?php echo htmlspecialchars($filtro_estado); ?>">
            <?php endif; ?>
            <button type="submit">Buscar</button>
            <?php if($busqueda != '' || $filtro_estado != ''): ?>
                <a href="admin.php" class="btn-limpiar">Limpiar</a>
            <?php endif; ?>
        </form>

        <form action="admin.php<?php echo $filtro_estado ? '?filtro='.$filtro_estado : ''; ?>" method="POST" id="form-masivo">
            <button type="submit" onclick="return confirm('¿Estás seguro de enviar los elementos seleccionados a la papelera?');" class="btn-lote-papelera">
                🗑️ Enviar Seleccionados a la Papelera
            </button>

            <table>
                <thead>
                    <tr>
                        <th style="width: 40px; text-align: center;">
                            <input type="checkbox" id="seleccionar-todo" onclick="toggleSelectAll(this)">
                        </th>
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
                            <td style="text-align: center;">
                                <input type="checkbox" name="ids_seleccionados[]" value="<?php echo $row['id']; ?>">
                            </td>
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
                                <a href="ver.php?id=<?php echo $row['id']; ?>" class="btn-ver" title="Ver">👁️ Ver</a>
                                <a href="editar.php?id=<?php echo $row['id']; ?>" class="btn-editar" title="Editar">✏️ Editar</a>
                                <a href="enviar_papelera.php?id=<?php echo $row['id']; ?>" class="btn-eliminar" title="Eliminar" onclick="return confirm('¿Estás seguro de enviar a la papelera?');">🗑️ Borrar</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="13" style="text-align: center; color: #94a3b8; padding: 20px;">No se encontraron registros coincidentes.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </form>

        <div class="pagination">
            <div>
                <?php if ($pagina_actual > 1): ?>
                    <a href="admin.php?pagina=<?php echo $pagina_actual - 1; ?>&busqueda=<?php echo urlencode($busqueda); ?><?php echo $filtro_estado ? '&filtro='.$filtro_estado : ''; ?>">← Anterior</a>
                <?php endif; ?>
            </div>

            <span>Página <strong><?php echo $pagina_actual; ?></strong> de <strong><?php echo $total_paginas; ?></strong> (Total registros: <?php echo $total_registros; ?>)</span>

            <div>
                <?php if ($pagina_actual < $total_paginas): ?>
                    <a href="admin.php?pagina=<?php echo $pagina_actual + 1; ?>&busqueda=<?php echo urlencode($busqueda); ?><?php echo $filtro_estado ? '&filtro='.$filtro_estado : ''; ?>">Siguiente →</a>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>