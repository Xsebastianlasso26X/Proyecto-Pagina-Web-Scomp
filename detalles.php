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

    if (isset($_GET['id'])) {
        $id = $_GET['id'];
        $stmt = $conexion->prepare("SELECT * FROM solicitudes WHERE id = ?");
        $stmt->execute([$id]);
        $solicitud = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$solicitud) {
            header("Location: admin.php");
            exit();
        }
    } else {
        header("Location: admin.php");
        exit();
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalles de la Solicitud - SCOMP</title>
    <style>
        body { background: #0f172a; color: #fff; font-family: 'Roboto', sans-serif; padding: 30px; }
        .details-container { max-width: 700px; background: #1e293b; padding: 35px; border-radius: 12px; border: 1px solid #334155; margin: auto; box-shadow: 0 4px 6px rgba(0,0,0,0.3); }
        h2 { color: #38bdf8; font-family: 'Orbitron', sans-serif; margin-bottom: 20px; text-align: center; border-bottom: 2px solid #334155; padding-bottom: 15px; }
        .info-group { margin-bottom: 15px; border-bottom: 1px solid #334155; padding-bottom: 10px; }
        .info-group label { display: block; color: #94a3b8; font-size: 0.8rem; text-transform: uppercase; margin-bottom: 3px; font-family: 'Orbitron', sans-serif; }
        .info-group p { color: #f8fafc; font-size: 1rem; margin: 0; }
        .btn-volver { display: block; text-align: center; margin-top: 25px; background: #38bdf8; color: #0f172a; padding: 10px; border-radius: 6px; text-decoration: none; font-weight: bold; }
        .btn-volver:hover { background: #0ea5e9; }
    </style>
</head>
<body>
    <div class="details-container">
        <h2>Detalles del Cliente #<?php echo $solicitud['id']; ?></h2>
        
        <div class="info-group">
            <label>Nombre del Cliente</label>
            <p><?php echo htmlspecialchars($solicitud['nombre']); ?></p>
        </div>

        <div class="info-group">
            <label>Cédula</label>
            <p><?php echo htmlspecialchars($solicitud['cedula']); ?></p>
        </div>

        <div class="info-group">
            <label>Correo Electrónico</label>
            <p><?php echo htmlspecialchars($solicitud['correo']); ?></p>
        </div>

        <div class="info-group">
            <label>Teléfono</label>
            <p><?php echo htmlspecialchars($solicitud['telefono']); ?></p>
        </div>

        <div class="info-group">
            <label>Dirección</label>
            <p><?php echo htmlspecialchars($solicitud['direccion']); ?></p>
        </div>

        <div class="info-group">
            <label>Tipo de Servicio Solicitado</label>
            <p><?php echo htmlspecialchars($solicitud['tipo_servicio']); ?></p>
        </div>

        <div class="info-group">
            <label>Mensaje / Falla Reportada Inicialmente</label>
            <p style="background: #0f172a; padding: 10px; border-radius: 6px; border: 1px solid #475569;"><?php echo htmlspecialchars($solicitud['mensaje']); ?></p>
        </div>

        <div class="info-group">
            <label>Fecha de Solicitud</label>
            <p><?php echo htmlspecialchars($solicitud['fecha']); ?></p>
        </div>

        <a href="admin.php" class="btn-volver">← Volver al Panel de Control</a>
    </div>
</body>
</html>