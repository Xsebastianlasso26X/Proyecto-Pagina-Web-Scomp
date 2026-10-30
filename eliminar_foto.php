<?php
$servidor = "localhost";
$base_datos = "scomp_db";
$usuario = "root";
$password = "";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (isset($_GET['id_foto']) && isset($_GET['id_solicitud'])) {
        $id_foto = $_GET['id_foto'];
        $id_solicitud = $_GET['id_solicitud'];

        // Buscar el nombre del archivo para borrarlo físicamente de la carpeta uploads
        $stmt = $conexion->prepare("SELECT nombre_archivo FROM fotos_equipo WHERE id = ?");
        $stmt->execute([$id_foto]);
        $foto = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($foto) {
            $ruta = "uploads/" . $foto['nombre_archivo'];
            if (file_exists($ruta)) {
                unlink($ruta); // Borra el archivo de la carpeta
            }
            
            // Borrar el registro de la base de datos
            $stmt_del = $conexion->prepare("DELETE FROM fotos_equipo WHERE id = ?");
            $stmt_del->execute([$id_foto]);
        }

        // Redirigir de vuelta al panel de edición de esa misma solicitud
        header("Location: editar.php?id=" . $id_solicitud);
        exit();
    }
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>