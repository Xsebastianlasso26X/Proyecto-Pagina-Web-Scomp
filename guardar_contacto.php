<?php
$servidor = "localhost";
$base_datos = "scomp_db";
$usuario = "root";
$password = "";

try {
    // Conexión con PDO
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Recoger los datos enviados desde el formulario HTML
    $nombre = $_POST['nombre'];
    $cedula = $_POST['cedula'];
    $tipo_servicio = $_POST['tipo_servicio'];
    $correo = $_POST['correo'];
    $mensaje = $_POST['mensaje'];
    $telefono = $_POST['telefono'];
    $direccion = $_POST['direccion'];

    // Consulta preparada con PDO (incluyendo falla_reportada con el mismo valor del mensaje)
    $sql = "INSERT INTO solicitudes (nombre, cedula, tipo_servicio, correo, mensaje, telefono, direccion, falla_reportada) 
            VALUES (:nombre, :cedula, :tipo_servicio, :correo, :mensaje, :telefono, :direccion, :mensaje)";
    
    $stmt = $conexion->prepare($sql);
    $stmt->execute([
        ':nombre' => $nombre,
        ':cedula' => $cedula,
        ':tipo_servicio' => $tipo_servicio,
        ':correo' => $correo,
        ':mensaje' => $mensaje,
        ':telefono' => $telefono,
        ':direccion' => $direccion
    ]);

    // Redirigir de regreso a la página principal con el aviso de éxito
    header("Location: index.html?status=success");
    exit();

} catch(PDOException $e) {
    echo "Error en la conexión o inserción: " . $e->getMessage();
}
?>