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

if (isset($_GET['id'])) {
    try {
        $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
        $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        // Actualizamos el estado a 1 (enviado a papelera)
        $stmt = $conexion->prepare("UPDATE solicitudes SET estado_eliminado = 1 WHERE id = ?");
        $stmt->execute([$_GET['id']]);

        header("Location: admin.php?enviado_papelera=1");
        exit();
    } catch(PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
} else {
    header("Location: admin.php");
    exit();
}
?>