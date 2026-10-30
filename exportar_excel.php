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

    // Recoger el texto de búsqueda si viene desde el panel
    $busqueda = isset($_GET['busqueda']) ? trim($_GET['busqueda']) : '';

    if ($busqueda != '') {
        // Si hay una búsqueda activa, exportamos solo los que coincidan y no estén eliminados
        $sql = "SELECT id, nombre, cedula, tipo_servicio, telefono, correo, direccion, marca_pc, modelo_pc, serial_pc, sistema_operativo, accesorios, programas_instalados, falla_reportada, observaciones_pc, precio_servicio, abono, saldo_pendiente, estado_pago, estado_servicio, fecha 
                FROM solicitudes 
                WHERE estado_eliminado = 0 
                AND (nombre LIKE ? OR cedula LIKE ? OR tipo_servicio LIKE ? OR telefono LIKE ? OR fecha LIKE ? OR estado_pago LIKE ? OR estado_servicio LIKE ?) 
                ORDER BY id DESC";
        $stmt = $conexion->prepare($sql);
        $param_busqueda = "%$busqueda%";
        $stmt->execute([$param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda, $param_busqueda]);
    } else {
        // Si no hay búsqueda, exportamos todos los activos
        $stmt = $conexion->query("SELECT id, nombre, cedula, tipo_servicio, telefono, correo, direccion, marca_pc, modelo_pc, serial_pc, sistema_operativo, accesorios, programas_instalados, falla_reportada, observaciones_pc, precio_servicio, abono, saldo_pendiente, estado_pago, estado_servicio, fecha 
                                  FROM solicitudes 
                                  WHERE estado_eliminado = 0 
                                  ORDER BY id DESC");
    }

    $registros = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Configurar las cabeceras HTTP para forzar la descarga del archivo Excel (CSV)
    header('Content-Type: text/csv; charset=utf-8');
    header('Content-Disposition: attachment; filename=SCOMP_Reporte_Solicitudes_' . date('Y-m-d') . '.csv');

    $output = fopen('php://output', 'w');

    // Escribir el BOM de UTF-8 para las tildes y eñes en Excel
    fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

    // Encabezados de columnas
    fputcsv($output, [
        'ID', 'Nombre', 'Cédula', 'Tipo Servicio', 'Teléfono', 'Correo', 'Dirección', 
        'Marca PC', 'Modelo PC', 'Serial PC', 'Sistema Operativo', 'Accesorios', 
        'Programas Instalados', 'Falla Reportada', 'Diagnóstico Técnico', 
        'Precio Servicio', 'Abono', 'Saldo Pendiente', 'Estado Pago', 'Estado Servicio', 'Fecha Registro'
    ], ';');

    // Escribir los registros obtenidos
    foreach ($registros as $row) {
        fputcsv($output, [
            $row['id'],
            $row['nombre'],
            $row['cedula'],
            $row['tipo_servicio'],
            $row['telefono'],
            $row['correo'],
            $row['direccion'],
            $row['marca_pc'],
            $row['modelo_pc'],
            $row['serial_pc'],
            $row['sistema_operativo'],
            $row['accesorios'],
            $row['programas_instalados'],
            $row['falla_reportada'],
            $row['observaciones_pc'],
            $row['precio_servicio'],
            $row['abono'],
            $row['saldo_pendiente'],
            $row['estado_pago'],
            $row['estado_servicio'],
            $row['fecha']
        ], ';');
    }

    fclose($output);
    exit();

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>