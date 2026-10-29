<?php
require_once 'enviar_correo.php';

$servidor = "localhost";
$base_datos = "scomp_db";
$usuario = "root";
$password = "";

try {
    $conexion = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
    $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['enviar_email_auto'])) {
        $id = $_POST['id'];
        $marca = $_POST['marca_pc'];
        $modelo = $_POST['modelo_pc'];
        $serial = $_POST['serial_pc'];
        $so = $_POST['sistema_operativo'];
        $accesorios = $_POST['accesorios'];
        $programas = $_POST['programas_instalados'];
        $falla = $_POST['falla_reportada'];
        $observaciones = $_POST['observaciones_pc'];
        $precio = floatval($_POST['precio_servicio']);
        $abono = floatval($_POST['abono']);
        $saldo_pendiente = $precio - $abono;
        $estado_pago = $_POST['estado_pago'];
        $estado_servicio = $_POST['estado_servicio'];

        $sql_update = "UPDATE solicitudes SET marca_pc = ?, modelo_pc = ?, serial_pc = ?, sistema_operativo = ?, accesorios = ?, programas_instalados = ?, falla_reportada = ?, observaciones_pc = ?, precio_servicio = ?, abono = ?, saldo_pendiente = ?, estado_pago = ?, estado_servicio = ? WHERE id = ?";
        $stmt = $conexion->prepare($sql_update);
        $stmt->execute([$marca, $modelo, $serial, $so, $accesorios, $programas, $falla, $observaciones, $precio, $abono, $saldo_pendiente, $estado_pago, $estado_servicio, $id]);

        header("Location: admin.php?actualizado=1");
        exit();
    }

    if (isset($_GET['id']) || isset($_POST['id'])) {
        $id = isset($_GET['id']) ? $_GET['id'] : $_POST['id'];
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

if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['enviar_email_auto'])) {
    $resultado = enviarCorreoScomp(
        $solicitud['correo'], 
        $solicitud['nombre'], 
        $solicitud['tipo_servicio'], 
        $precio_total, 
        $valor_abono, 
        $saldo_pendiente,
        $solicitud['marca_pc'],
        $solicitud['modelo_pc'],
        $solicitud['serial_pc'],
        $solicitud['falla_reportada'],
        $solicitud['observaciones_pc']
    );

    if ($resultado === true) {
        $mensaje_exito_correo = "¡Correo y Hoja de Vida adjunta enviados con éxito al cliente!";
    } else {
        $error_correo = "Error detallado: " . $resultado;
    }
}

$telefono_limpio = preg_replace('/[^0-9]/', '', $solicitud['telefono']);
$mensaje_whatsapp = "Hola *{$solicitud['nombre']}*, le saludamos de *SCOMP Soporte Técnico*. Le recordamos que este canal de WhatsApp es exclusivo para programar la agenda de su servicio. Si aún no ha registrado su equipo, por favor hágalo previamente desde la opción 'Solicita tu Soporte Técnico' en el siguiente enlace: http://localhost/Proyecto%20Pagina%20Web%20Scomp/index.html#contacto opción contacto. Adjunto encontrará el resumen de su equipo (*{$solicitud['tipo_servicio']}*) con un Saldo Pendiente de: $" . number_format($saldo_pendiente, 0, ',', '.');
$link_whatsapp = "https://wa.me/{$telefono_limpio}?text=" . urlencode($mensaje_whatsapp);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Hoja de Vida y Cuenta de Cobro - SCOMP</title>
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
        .edit-container { max-width: 800px; background: #121826; padding: 35px; border-radius: 12px; border: 1px solid #1e293b; margin: auto; box-shadow: 0 10px 30px rgba(0,0,0,0.5); }
        
        .logo-container { text-align: center; margin-bottom: 15px; }
        .logo-img { max-height: 70px; width: auto; }

        h2 { color: #38bdf8; font-family: 'Orbitron', sans-serif; font-size: 1.2rem; margin-bottom: 5px; text-align: center; }
        .company-header { text-align: center; border-bottom: 2px dashed #1e293b; padding-bottom: 15px; margin-bottom: 20px; display: none; }
        .section-title { color: #38bdf8; border-bottom: 1px solid #1e293b; padding-bottom: 8px; margin-top: 25px; margin-bottom: 15px; font-family: 'Orbitron', sans-serif; font-size: 0.95rem; }
        .form-row { display: flex; gap: 15px; margin-bottom: 15px; }
        .form-group { flex: 1; }
        label { display: block; margin-bottom: 6px; color: #cbd5e1; font-size: 0.85rem; font-weight: 500; }
        input, select, textarea { width: 100%; padding: 10px; background: #0b0f19; border: 1px solid #334155; color: #fff; border-radius: 8px; font-size: 0.9rem; }
        input:focus, select:focus, textarea:focus { border-color: #38bdf8; outline: none; }
        
        .financial-box { background: #0b0f19; border: 1px solid #1e293b; padding: 15px; border-radius: 8px; margin-top: 15px; display: flex; justify-content: space-around; text-align: center; }
        .financial-item span { display: block; color: #94a3b8; font-size: 0.8rem; margin-bottom: 5px; }
        .financial-item strong { font-size: 1.1rem; color: #38bdf8; font-family: 'Orbitron', sans-serif; }
        
        .btn-guardar { background: #38bdf8; color: #0b0f19; font-weight: bold; border: none; padding: 12px; width: 100%; border-radius: 8px; cursor: pointer; margin-top: 20px; font-size: 0.95rem; font-family: 'Orbitron', sans-serif; }
        .btn-guardar:hover { background: #0ea5e9; }
        .btn-imprimir { background: #10b981; color: #fff; font-weight: bold; border: none; padding: 11px; width: 100%; border-radius: 8px; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; }
        .btn-imprimir:hover { background: #059669; }
        .btn-correo { background: #ea4335; color: #fff; font-weight: bold; border: none; padding: 11px; width: 100%; border-radius: 8px; cursor: pointer; margin-top: 10px; text-align: center; }
        .btn-correo:hover { background: #d33828; }
        .btn-whatsapp { background: #25d366; color: #fff; font-weight: bold; border: none; padding: 11px; width: 100%; border-radius: 8px; cursor: pointer; margin-top: 10px; text-align: center; text-decoration: none; display: block; }
        .btn-whatsapp:hover { background: #22bf5b; }
        .btn-volver { display: block; text-align: center; margin-top: 18px; color: #94a3b8; text-decoration: none; font-size: 0.9rem; }
        .btn-volver:hover { color: #fff; }

        @media print {
            body { background: #fff; color: #000; padding: 0; background-image: none; }
            .edit-container { background: #fff; border: none; box-shadow: none; width: 100%; max-width: 100%; padding: 10px; }
            input, select, textarea { background: transparent; border: none; border-bottom: 1px solid #ccc; color: #000; padding: 2px; }
            label { color: #555; font-weight: bold; }
            .btn-guardar, .btn-imprimir, .btn-correo, .btn-whatsapp, .btn-volver, hr, form.form-correo { display: none !important; }
            .company-header { display: block; }
            h2 { color: #000; }
            .section-title { color: #000; border-bottom: 2px solid #000; }
            .financial-box { border: 1px solid #000; background: #f8fafc; }
            .financial-item strong { color: #000; }
        }
    </style>
    <script>
        function calcularSaldo() {
            let precio = parseFloat(document.getElementById('precio_servicio').value) || 0;
            let abono = parseFloat(document.getElementById('abono').value) || 0;
            let saldo = precio - abono;

            document.getElementById('txt_total').innerText = '$' + precio.toLocaleString('es-CO');
            document.getElementById('txt_abono').innerText = '$' + abono.toLocaleString('es-CO');
            document.getElementById('txt_saldo').innerText = '$' + saldo.toLocaleString('es-CO');
        }
    </script>
</head>
<body>
    <div class="edit-container">
        <div class="company-header">
            <img src="logo-scomp.jpg" alt="SCOMP Logo" style="max-height: 60px; width: auto; margin-bottom: 5px;">
            <p style="margin: 5px 0; font-size: 0.9rem;">Soporte Técnico y Soluciones a tu Alcance</p>
            <p style="margin: 0; font-size: 0.8rem; color: #555;">Carrera 28F # 4-12 Barrio Tulipanes de la Italia Palmira Valle | Tel: +57 300 366 6441</p>
            <hr style="border: 0; border-top: 1px solid #ccc; margin: 15px 0;">
            <h3 style="margin: 0; font-family: 'Orbitron', sans-serif;">HOJA DE VIDA Y CUENTA DE COBRO #<?php echo $solicitud['id']; ?></h3>
        </div>

        <div class="logo-container">
            <img src="logo-scomp.jpg" alt="SCOMP Logo" class="logo-img">
        </div>

        <h2>Hoja de Vida y Soporte #<?php echo $solicitud['id']; ?></h2>
        
        <p style="margin-bottom: 20px; color: #94a3b8; text-align: center; line-height: 1.6;">
            Cliente: <strong><?php echo htmlspecialchars($solicitud['nombre']); ?></strong> | 
            Cédula: <strong><?php echo htmlspecialchars($solicitud['cedula']); ?></strong> | 
            Teléfono: <strong><?php echo htmlspecialchars($solicitud['telefono']); ?></strong><br>
            Correo: <strong><?php echo htmlspecialchars($solicitud['correo']); ?></strong> | 
            Dirección: <strong><?php echo htmlspecialchars($solicitud['direccion']); ?></strong>
        </p>
        
        <?php if (isset($mensaje_exito_correo)): ?>
            <div style="background: #065f46; color: #6ee7b7; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-weight: bold;"><?php echo $mensaje_exito_correo; ?></div>
        <?php endif; ?>
        <?php if (isset($error_correo)): ?>
            <div style="background: #7f1d1d; color: #fca5a5; padding: 10px; border-radius: 8px; margin-bottom: 15px; text-align: center; font-weight: bold;"><?php echo $error_correo; ?></div>
        <?php endif; ?>

        <form action="editar.php" method="POST">
            <input type="hidden" name="id" value="<?php echo $solicitud['id']; ?>">

            <div class="section-title">1. Especificaciones del Equipo (Hoja de Vida)</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Marca PC</label>
                    <input type="text" name="marca_pc" value="<?php echo htmlspecialchars($solicitud['marca_pc']); ?>">
                </div>
                <div class="form-group">
                    <label>Modelo</label>
                    <input type="text" name="modelo_pc" value="<?php echo htmlspecialchars($solicitud['modelo_pc']); ?>">
                </div>
                <div class="form-group">
                    <label>Número de Serie</label>
                    <input type="text" name="serial_pc" value="<?php echo htmlspecialchars($solicitud['serial_pc']); ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Sistema Operativo</label>
                    <input type="text" name="sistema_operativo" value="<?php echo htmlspecialchars($solicitud['sistema_operativo']); ?>">
                </div>
                <div class="form-group">
                    <label>Accesorios</label>
                    <input type="text" name="accesorios" value="<?php echo htmlspecialchars($solicitud['accesorios']); ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Programas Instalados / Requeridos</label>
                <textarea name="programas_instalados" rows="2"><?php echo htmlspecialchars($solicitud['programas_instalados']); ?></textarea>
            </div>

            <div class="section-title">2. Diagnóstico, Servicio y Cuenta de Cobro</div>
            <div class="form-row">
                <div class="form-group">
                    <label>Falla Reportada (Cliente)</label>
                    <textarea name="falla_reportada" rows="2"><?php echo htmlspecialchars($solicitud['falla_reportada']); ?></textarea>
                </div>
                <div class="form-group">
                    <label>Observaciones / Diagnóstico del Técnico</label>
                    <textarea name="observaciones_pc" rows="2"><?php echo htmlspecialchars($solicitud['observaciones_pc']); ?></textarea>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Precio Total Servicio ($)</label>
                    <input type="number" step="100" id="precio_servicio" name="precio_servicio" value="<?php echo $solicitud['precio_servicio']; ?>" oninput="calcularSaldo()">
                </div>
                <div class="form-group">
                    <label>Abono del Cliente ($)</label>
                    <input type="number" step="100" id="abono" name="abono" value="<?php echo $solicitud['abono']; ?>" oninput="calcularSaldo()">
                </div>
                <div class="form-group">
                    <label>Estado de Pago</label>
                    <select name="estado_pago">
                        <option value="Pendiente" <?php if($solicitud['estado_pago']=='Pendiente') echo 'selected'; ?>>Pendiente</option>
                        <option value="Abono" <?php if($solicitud['estado_pago']=='Abono') echo 'selected'; ?>>Abono</option>
                        <option value="Pagó" <?php if($solicitud['estado_pago']=='Pagó') echo 'selected'; ?>>Pagó</option>
                    </select>
                </div>
                <div class="form-group">
                    <label>Estado del Servicio</label>
                    <select name="estado_servicio">
                        <option value="Pendiente" <?php if($solicitud['estado_servicio']=='Pendiente') echo 'selected'; ?>>Pendiente</option>
                        <option value="En Taller / Proceso" <?php if($solicitud['estado_servicio']=='En Taller / Proceso') echo 'selected'; ?>>En Taller / Proceso</option>
                        <option value="Completado" <?php if($solicitud['estado_servicio']=='Completado') echo 'selected'; ?>>Completado</option>
                        <option value="Entregado" <?php if($solicitud['estado_servicio']=='Entregado') echo 'selected'; ?>>Entregado</option>
                    </select>
                </div>
            </div>

            <div class="financial-box">
                <div class="financial-item">
                    <span>Total Servicio</span>
                    <strong id="txt_total">$<?php echo number_format($precio_total, 0, ',', '.'); ?></strong>
                </div>
                <div class="financial-item">
                    <span>Abono Realizado</span>
                    <strong id="txt_abono">$<?php echo number_format($valor_abono, 0, ',', '.'); ?></strong>
                </div>
                <div class="financial-item">
                    <span>Saldo Pendiente</span>
                    <strong id="txt_saldo" style="color: #f87171;">$<?php echo number_format($saldo_pendiente, 0, ',', '.'); ?></strong>
                </div>
            </div>

            <button type="submit" class="btn-guardar">Guardar Hoja de Vida y Cuenta de Cobro</button>
        </form>

        <a href="<?php echo $link_whatsapp; ?>" target="_blank" class="btn-whatsapp">
            💬 Enviar Mensaje por WhatsApp (Agendamiento)
        </a>

        <form action="editar.php?id=<?php echo $solicitud['id']; ?>" method="POST" class="form-correo">
            <input type="hidden" name="enviar_email_auto" value="1">
            <button type="submit" class="btn-correo">✉️ Enviar Correo con Hoja de Vida Adjunta</button>
        </form>

        <button onclick="window.print();" class="btn-imprimir">🖨️ Imprimir / Guardar Comprobante PDF</button>
        <a href="admin.php" class="btn-volver">← Volver al Panel de Control</a>
    </div>
</body>
</html>