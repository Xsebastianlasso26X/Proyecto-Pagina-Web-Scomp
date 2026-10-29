<?php
session_start();
if (!isset($_SESSION['admin_logueado']) || $_SESSION['admin_logueado'] !== true) {
    header("Location: login.php");
    exit();
}

$servidor = "localhost";
$base_datos = "scomp_db";
$usuario = "root";
$password = "";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Procesar acciones masivas o individuales si se envía el formulario POST
    if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['ids'])) {
        $ids = $_POST['ids']; // Array de IDs seleccionados
        $accion = $_POST['accion'];

        if (!empty($ids)) {
            $placeholders = implode(',', array_fill(0, count($ids), '?'));

            if ($accion == 'restaurar') {
                $sql = "UPDATE solicitudes SET estado_eliminado = 0 WHERE id IN ($placeholders)";
                $stmt = $conexion->prepare($sql);
                $stmt->execute($ids);
                header("Location: papelera.php?restaurado=1");
                exit();
            } 
            elseif ($accion == 'eliminar_definitivo') {
                $sql = "DELETE FROM solicitudes WHERE id IN ($placeholders)";
                $stmt = $conexion->prepare($sql);
                $stmt->execute($ids);
                header("Location: papelera.php?eliminado=1");
                exit();
            }
        }
    }

    // Consultar solo los registros que están en la papelera (estado_eliminado = 1)
    $stmt = $conexion->query("SELECT * FROM solicitudes WHERE estado_eliminado = 1 ORDER BY id DESC");
    $papelera_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch(PDOException $e) {
    $error_db = "Error de conexión: " . $e->getMessage();
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Papelera - SCOMP</title>
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

        h1 { color: #f87171; margin: 0; font-family: 'Orbitron', sans-serif; font-size: 1.2rem; }
        
        .top-buttons a { background: #1e293b; color: #cbd5e1; padding: 9px 16px; border-radius: 8px; text-decoration: none; font-size: 0.85rem; border: 1px solid #334155; font-weight: 500; }
        .top-buttons a:hover { border-color: #38bdf8; color: #fff; }
        
        .acciones-bar { margin-bottom: 20px; display: flex; gap: 10px; }
        .btn-accion-masiva { padding: 10px 16px; border: none; border-radius: 8px; font-weight: bold; cursor: pointer; font-size: 0.85rem; font-family: 'Orbitron', sans-serif; }
        .btn-restaurar-todos { background: #10b981; color: white; }
        .btn-restaurar-todos:hover { background: #059669; }
        .btn-borrar-todos { background: #ef4444; color: white; }
        .btn-borrar-todos:hover { background: #dc2626; }

        table { width: 100%; border-collapse: collapse; margin-top: 10px; background: #0b0f19; border-radius: 8px; overflow: hidden; border: 1px solid #1e293b; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid #1e293b; font-size: 0.9rem; }
        th { background: #121826; color: #f87171; font-family: 'Orbitron', sans-serif; font-size: 0.75rem; letter-spacing: 0.5px; }
        tr:hover { background: #161f33; }
        
        .alerta-exito { background: #065f46; color: #6ee7b7; padding: 12px 15px; border-radius: 8px; margin-bottom: 20px; font-size: 0.9rem; font-weight: bold; }
        .no-data { text-align: center; padding: 30px; color: #94a3b8; font-size: 1rem; }
    </style>
    <script>
        // Seleccionar o deseleccionar todos los checkboxes
        function seleccionarTodo(source) {
            checkboxes = document.getElementsByName('ids[]');
            for(var i=0, n=checkboxes.length; i<n; i++) {
                checkboxes[i].checked = source.checked;
            }
        }

        function prepararAccion(accion) {
            document.getElementById('input_accion').value = accion;
            if (accion === 'eliminar_definitivo') {
                if (!confirm("¿Estás seguro de eliminar definitivamente los registros seleccionados?")) {
                    return false;
                }
            }
            document.getElementById('form_papelera').submit();
        }
    </script>
</head>
<body>

    <div class="admin-container">
        <div class="admin-header">
            <a href="admin.php">
                <img src="logo-scomp.jpg" alt="SCOMP Logo" class="logo-img">
            </a>
            <h1>🗑️ Papelera de Registros</h1>
            <div class="top-buttons">
                <a href="admin.php">← Volver al Panel</a>
            </div>
        </div>

        <?php if (isset($_GET['restaurado'])): ?>
            <div class="alerta-exito">Registro(s) restaurado(s) exitosamente al panel principal.</div>
        <?php endif; ?>
        <?php if (isset($_GET['eliminado'])): ?>
            <div class="alerta-exito" style="background: #7f1d1d; color: #fca5a5;">Registro(s) eliminado(s) definitivamente de la base de datos.</div>
        <?php endif; ?>

        <form id="form_papelera" method="POST" action="papelera.php">
            <input type="hidden" name="accion" id="input_accion" value="">

            <?php if (!empty($papelera_items)): ?>
                <div class="acciones-bar">
                    <button type="button" class="btn-accion-masiva btn-restaurar-todos" onclick="prepararAccion('restaurar')">♻️ Restaurar Seleccionados</button>
                    <button type="button" class="btn-accion-masiva btn-borrar-todos" onclick="prepararAccion('eliminar_definitivo')">🔥 Eliminar Definitivamente</button>
                </div>
            <?php endif; ?>

            <div>
                <table>
                    <thead>
                        <tr>
                            <th style="width: 3%;"><input type="checkbox" onclick="seleccionarTodo(this)"></th>
                            <th style="width: 5%;">ID</th>
                            <th style="width: 17%;">Nombre</th>
                            <th style="width: 12%;">Cédula</th>
                            <th style="width: 15%;">Servicio</th>
                            <th style="width: 12%;">Teléfono</th>
                            <th style="width: 18%;">Fecha</th>
                            <th style="width: 18%;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($papelera_items)): ?>
                            <?php foreach ($papelera_items as $row): ?>
                                <tr>
                                    <td><input type="checkbox" name="ids[]" value="<?php echo $row['id']; ?>"></td>
                                    <td><strong>#<?php echo htmlspecialchars($row['id']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($row['nombre']); ?></td>
                                    <td><?php echo htmlspecialchars($row['cedula']); ?></td>
                                    <td><?php echo htmlspecialchars($row['tipo_servicio']); ?></td>
                                    <td><?php echo htmlspecialchars($row['telefono']); ?></td>
                                    <td><?php echo htmlspecialchars($row['fecha']); ?></td>
                                    <td>
                                        <a href="enviar_papelera.php?id=<?php echo $row['id']; ?>&restaurar=1" style="color: #10b981; text-decoration: none; font-weight: bold; margin-right: 10px;">Restaurar</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="no-data">La papelera está vacía.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </form>
    </div>

</body>
</html>