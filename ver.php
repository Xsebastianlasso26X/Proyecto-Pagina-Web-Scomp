<?php
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

$precio_total = floatval($solicitud['precio_servicio']);
$valor_abono = floatval($solicitud['abono']);
$saldo_pendiente = $precio_total - $valor_abono;

$telefono_limpio = preg_replace('/[^0-9]/', '', $solicitud['telefono']);
$mensaje_whatsapp = "Hola *{$solicitud['nombre']}*, le saludamos de *SCOMP Soporte Técnico*. Le recordamos que este canal de WhatsApp es exclusivo para programar la agenda de su servicio. Si aún no ha registrado su equipo, por favor hágalo previamente desde la opción 'Solicita tu Soporte Técnico' en el siguiente enlace: http://localhost/Proyecto%20Pagina%20Web%20Scomp/index.html#contacto opción contacto. Adjunto encontrará el resumen de su equipo (*{$solicitud['tipo_servicio']}*) con un Saldo Pendiente de: $" . number_format($saldo_pendiente, 0, ',', '.');
$link_whatsapp = "https://wa.me/{$telefono_limpio}?text=" . urlencode($mensaje_whatsapp);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Vista de Hoja de Vida - SCOMP</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #0b0f19; 
            color: #f8fafc; 
            font-family: 'Roboto', sans-serif; 
            padding: 30px; 
            background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        .view-container { max-width: 800px; background: #121826; padding: 35px; border-radius: 12px; border: 1px solid #1e293b; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        
        .logo-container { text-align: center; margin-bottom: 15px; }
        .logo-img { max-height: 70px; width: auto; }

        h2 { color: #38bdf8; font-family: 'Orbitron', sans-serif; font-size: 1.2rem; margin-bottom: 5px; text-align: center; }
        .section-title { color: #38bdf8; border-bottom: 1px solid #1e293b; padding-bottom: 8px; margin-top: 25px; margin-bottom: 15px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem; }
        
        .info-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .info-group { flex: 1; background: #0b0f19; padding: 12px; border-radius: 8px; border: 1px solid #334155; }
        .info-group label { display: block; color: #94a3b8; font-size: 0.75rem; font-family: 'Orbitron', sans-serif; margin-bottom: 4px; }
        .info-group span { color: #fff; font-size: 0.95rem; }

        .financial-box { background: #0b0f19; border: 1px solid #1e293b; padding: 15px; border-radius: 8px; margin-top: 15px; display: flex; justify-content: space-around; text-align: center; }
        .financial-item span { display: block; color: #94a3b8; font-size: 0.8rem; margin-bottom: 5px; }
        .financial-item strong { font-size: 1.1rem; color: #38bdf8; font-family: 'Orbitron', sans-serif; }
        
        .btn-editar { background: #38bdf8; color: #0b0f19; font-weight: bold; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; margin-top: 20px; font-size: 0.95rem; font-family: 'Orbitron', sans-serif; text-align: center; text-decoration: none; display: block; }
        .btn-editar:hover { background: #0ea5e9; }
        .btn-imprimir { background: #10b981; color: #fff; font-weight: bold; border: none; padding: 11px; width: 100%; border-radius: 8px; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; }
        .btn-imprimir:hover { background: #059669; }
        .btn-whatsapp { background: #25d366; color: #fff; font-weight: bold; border: none; padding: 11px; width: 100%; border-radius: 8px; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; }
        .btn-whatsapp:hover { background: #22bf5b; }
        .btn-volver { display: block; text-align: center; margin-top: 18px; color: #94a3b8; text-decoration: none; font-size: 0.9rem; }
        .btn-volver:hover { color: #fff; }

        @media print {
            body { background: #fff; color: #000; padding: 0; background-image: none; }
            .view-container { background: #fff; border: none; box-shadow: none; width: 100%; max-width: 100%; padding: 10px; }
            .info-group { background: #f8fafc; border: 1px solid #ccc; }
            .info-group span { color: #000; }
            .info-group label { color: #555; }
            .btn-editar, .btn-imprimir, .btn-whatsapp, .btn-volver { display: none !important; }
            h2 { color: #000; }
            .section-title { color: #000; border-bottom: 2px solid #000; }
            .financial-box { border: 1px solid #000; background: #f8fafc; }
            .financial-item strong { color: #000; }
        }
    </style>
</head>
<body>
    <div class="view-container">
        <div class="logo-container">
            <img src="logo-scomp.jpg" alt="SCOMP Logo" class="logo-img">
        </div>

        <h2>Detalle de Hoja de Vida y Soporte #<?php echo $solicitud['id']; ?></h2>
        
        <p style="margin-bottom: 20px; color: #94a3b8; text-align: center; line-height: 1.6;">
            Cliente: <strong><?php echo htmlspecialchars($solicitud['nombre']); ?></strong> | 
            Cédula: <strong><?php echo htmlspecialchars($solicitud['cedula']); ?></strong> | 
            Teléfono: <strong><?php echo htmlspecialchars($solicitud['telefono']); ?></strong><br>
            Correo: <strong><?php echo htmlspecialchars($solicitud['correo']); ?></strong> | 
            Dirección: <strong><?php echo htmlspecialchars($solicitud['direccion']); ?></strong>
        </p>

        <div class="section-title">1. Especificaciones del Equipo (Hoja de Vida)</div>
        <div class="info-row">
            <div class="info-group">
                <label>MARCA PC</label>
                <span><?php echo htmlspecialchars($solicitud['marca_pc'] ?: 'No especificado'); ?></span>
            </div>
            <div class="info-group">
                <label>MODELO</label>
                <span><?php echo htmlspecialchars($solicitud['modelo_pc'] ?: 'No especificado'); ?></span>
            </div>
            <div class="info-group">
                <label>NÚMERO DE SERIE</label>
                <span><?php echo htmlspecialchars($solicitud['serial_pc'] ?: 'No especificado'); ?></span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-group">
                <label>SISTEMA OPERATIVO</label>
                <span><?php echo htmlspecialchars($solicitud['sistema_operativo'] ?: 'No especificado'); ?></span>
            </div>
            <div class="info-group">
                <label>ACCESORIOS</label>
                <span><?php echo htmlspecialchars($solicitud['accesorios'] ?: 'Ninguno'); ?></span>
            </div>
        </div>

        <div class="info-group" style="margin-bottom: 15px;">
            <label>PROGRAMAS INSTALADOS / REQUERIDOS</label>
            <span><?php echo nl2br(htmlspecialchars($solicitud['programas_instalados'] ?: 'Ninguno')); ?></span>
        </div>

        <div class="section-title">2. Diagnóstico, Servicio y Cuenta de Cobro</div>
        <div class="info-row">
            <div class="info-group">
                <label>FALLA REPORTADA (CLIENTE)</label>
                <span><?php echo nl2br(htmlspecialchars($solicitud['falla_reportada'] ?: 'Ninguna')); ?></span>
            </div>
            <div class="info-group">
                <label>OBSERVACIONES / DIAGNÓSTICO TÉCNICO</label>
                <span><?php echo nl2br(htmlspecialchars($solicitud['observaciones_pc'] ?: 'Pendiente de diagnóstico')); ?></span>
            </div>
        </div>

        <div class="info-row">
            <div class="info-group">
                <label>ESTADO DE PAGO</label>
                <span style="color: #38bdf8; font-weight: bold;"><?php echo htmlspecialchars($solicitud['estado_pago']); ?></span>
            </div>
            <div class="info-group">
                <label>ESTADO DEL SERVICIO</label>
                <span style="color: #10b981; font-weight: bold;"><?php echo htmlspecialchars($solicitud['estado_servicio']); ?></span>
            </div>
        </div>

        <div class="financial-box">
            <div class="financial-item">
                <span>Total Servicio</span>
                <strong>$<?php echo number_format($precio_total, 0, ',', '.'); ?></strong>
            </div>
            <div class="financial-item">
                <span>Abono Realizado</span>
                <strong>$<?php echo number_format($valor_abono, 0, ',', '.'); ?></strong>
            </div>
            <div class="financial-item">
                <span>Saldo Pendiente</span>
                <strong style="color: #f87171;">$<?php echo number_format($saldo_pendiente, 0, ',', '.'); ?></strong>
            </div>
        </div>

        <a href="editar.php?id=<?php echo $solicitud['id']; ?>" class="btn-editar">✏️ Editar este Registro</a>
        <a href="<?php echo $link_whatsapp; ?>" target="_blank" class="btn-whatsapp">💬 Enviar Mensaje por WhatsApp (Agendamiento)</a>
        <button onclick="window.print();" class="btn-imprimir">🖨️ Imprimir / Guardar Comprobante PDF</button>
        <a href="admin.php" class="btn-volver">← Volver al Panel de Control</a>
    </div>
</body>
</html>