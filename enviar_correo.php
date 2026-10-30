<?php
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

require_once 'dompdf/autoload.inc.php';
use Dompdf\Dompdf;
use Dompdf\Options;

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreoScomp($destinatario, $nombreCliente, $servicio, $precio, $abono, $saldo, $marca, $modelo, $serial, $sistema_operativo, $accesorios, $programas, $falla, $observaciones, $estado_pago, $estado_servicio, $cedula, $telefono, $direccion, $idSolicitud) {
    $mail = new PHPMailer(true);

    try {
        // Configuración del servidor SMTP de Gmail
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'sebastianlasso13@gmail.com'; 
        $mail->Password   = 'bdmu njzj xlrm bhlp'; 
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->SMTPOptions = array(
            'ssl' => array(
                'verify_peer' => false,
                'verify_peer_name' => false,
                'allow_self_signed' => true
            )
        );

        // Remitente y Destinatario
        $mail->setFrom('sebastianlasso13@gmail.com', 'SCOMP Soporte Técnico');
        $mail->addAddress($destinatario, $nombreCliente);

        // Contenido del correo principal
        $mail->isHTML(true);
        $mail->CharSet = 'UTF-8';
        $mail->Subject = 'SCOMP - Resumen de su Servicio Tecnico y Cuenta de Cobro';
        
        $mail->Body   = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h2 style='color: #0284c7;'>Hola, <b>{$nombreCliente}</b></h2>
                <p>Te saludamos de <b>SCOMP Soporte Técnico</b>. Adjunto a este correo encontrarás el documento oficial en formato <b>PDF</b> con la Hoja de Vida de tu equipo, su galería fotográfica y la cuenta de cobro.</p>
                <hr style='border: 1px solid #eee;'>
                <ul>
                    <li><b>Servicio:</b> {$servicio}</li>
                    <li><b>Total del Servicio:</b> $" . number_format($precio, 0, ',', '.') . "</li>
                    <li><b>Abono realizado:</b> $" . number_format($abono, 0, ',', '.') . "</li>
                    <li><b>Saldo Pendiente:</b> <span style='color:red; font-weight:bold;'>$" . number_format($saldo, 0, ',', '.') . "</span></li>
                </ul>
                <hr style='border: 1px solid #eee;'>
                <p>¡Gracias por confiar en nuestros servicios!</p>
            </div>
        ";

        // 1. Convertir el Logo a Base64 para que Dompdf lo muestre sin fallar
        $rutaLogo = __DIR__ . "/logo-scomp.jpg";
        $imgLogoHtml = "<h1>SCOMP ®</h1>";
        if (file_exists($rutaLogo)) {
            $tipoLogo = pathinfo($rutaLogo, PATHINFO_EXTENSION);
            $datosLogo = file_get_contents($rutaLogo);
            $base64Logo = 'data:image/' . $tipoLogo . ';base64,' . base64_encode($datosLogo);
            $imgLogoHtml = "<img src='{$base64Logo}' style='max-height: 55px; width: auto;'/>";
        }

        // 2. Consultar y convertir las fotos de la galería a Base64
        $htmlFotos = "";
        if ($idSolicitud) {
            $servidor = "localhost";
            $base_datos = "scomp_db";
            $usuario = "root";
            $password = "";
            try {
                $con_correo = new PDO("mysql:host=$servidor;dbname=$base_datos;charset=utf8", $usuario, $password);
                $stmt_f = $con_correo->prepare("SELECT * FROM fotos_equipo WHERE solicitud_id = ?");
                $stmt_f->execute([$idSolicitud]);
                $fotos_correo = $stmt_f->fetchAll(PDO::FETCH_ASSOC);

                if (count($fotos_correo) > 0) {
                    $htmlFotos .= "
                        <div style='color: #000; border-bottom: 2px solid #000; padding-bottom: 5px; margin-top: 25px; margin-bottom: 15px; font-family: sans-serif; font-size: 1rem; font-weight: bold;'>3. Fotografías del Estado del Equipo</div>
                        <div style='text-align: center; margin-bottom: 20px;'>
                    ";
                    foreach ($fotos_correo as $fc) {
                        $rutaFisica = __DIR__ . "/uploads/" . $fc['nombre_archivo'];
                        if (file_exists($rutaFisica)) {
                            $tipoImg = pathinfo($rutaFisica, PATHINFO_EXTENSION);
                            $datosImg = file_get_contents($rutaFisica);
                            $base64Img = 'data:image/' . $tipoImg . ';base64,' . base64_encode($datosImg);
                            
                            $htmlFotos .= "<img src='{$base64Img}' style='width: 100%; max-width: 400px; height: auto; display: block; margin: 15px auto; border: 1px solid #000; border-radius: 4px;'>";
                        }
                    }
                    $htmlFotos .= "</div>";
                }
            } catch (Exception $ex) {
                // Error silencioso
            }
        }

        // Construir el HTML final para Dompdf
        $htmlContenido = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <style>
                    body { font-family: Helvetica, Arial, sans-serif; padding: 10px; color: #000; font-size: 0.9rem; }
                    .company-header { text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 10px; margin-bottom: 15px; }
                    h2 { font-size: 1rem; text-align: center; margin-bottom: 5px; }
                    .section-title { color: #000; border-bottom: 2px solid #000; padding-bottom: 4px; margin-top: 20px; margin-bottom: 10px; font-weight: bold; font-size: 0.95rem; }
                    table { width: 100%; border-collapse: collapse; margin-bottom: 10px; }
                    td { border: 1px solid #ccc; padding: 6px; background: #f8fafc; font-size: 0.85rem; }
                </style>
            </head>
            <body>
                <div class='company-header'>
                    <div style='text-align: center;'>{$imgLogoHtml}</div>
                    <p style='margin: 4px 0; font-size: 0.8rem;'>Soporte Técnico y Soluciones a tu Alcance</p>
                    <p style='margin: 0; font-size: 0.7rem; color: #555;'>Carrera 28F # 4-12 Barrio Tulipanes de la Italia Palmira Valle | Tel: +57 300 366 6441</p>
                </div>

                <h2>HOJA DE VIDA Y CUENTA DE COBRO #" . ($idSolicitud ?: '') . "</h2>
                
                <p style='margin-bottom: 15px; text-align: center; font-size: 0.8rem; line-height: 1.4;'>
                    Cliente: <b>{$nombreCliente}</b> | Cédula: <b>{$cedula}</b> | Teléfono: <b>{$telefono}</b><br>
                    Correo: <b>{$destinatario}</b> | Dirección: <b>{$direccion}</b>
                </p>

                <div class='section-title'>1. Especificaciones del Equipo (Hoja de Vida)</div>
                <table>
                    <tr>
                        <td><b>Marca PC:</b><br>{$marca}</td>
                        <td><b>Modelo:</b><br>{$modelo}</td>
                        <td><b>Número de Serie:</b><br>{$serial}</td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style='width:50%;'><b>Sistema Operativo:</b><br>{$sistema_operativo}</td>
                        <td style='width:50%;'><b>Accesorios:</b><br>{$accesorios}</td>
                    </tr>
                </table>
                <div style='border:1px solid #ccc; padding:6px; background:#f8fafc; border-radius:4px; margin-bottom:10px; font-size: 0.85rem;'>
                    <b>Programas Instalados / Requeridos:</b><br>" . nl2br($programas) . "
                </div>

                {$htmlFotos}

                <div class='section-title'>2. Diagnóstico, Servicio y Cuenta de Cobro</div>
                <table>
                    <tr>
                        <td style='width:50%;'><b>Falla Reportada (Cliente):</b><br>" . nl2br($falla) . "</td>
                        <td style='width:50%;'><b>Observaciones / Diagnóstico Técnico:</b><br>" . nl2br($observaciones) . "</td>
                    </tr>
                </table>
                <table>
                    <tr>
                        <td style='width:50%;'><b>Estado de Pago:</b> <span style='color:#0284c7; font-weight:bold;'>{$estado_pago}</span></td>
                        <td style='width:50%;'><b>Estado del Servicio:</b> <span style='color:#10b981; font-weight:bold;'>{$estado_servicio}</span></td>
                    </tr>
                </table>

                <div style='border: 1px solid #000; padding: 10px; border-radius: 6px; background: #f8fafc; margin-top: 15px;'>
                    <table style='border:none; text-align:center; margin-bottom:0;'>
                        <tr>
                            <td style='border:none; background:transparent;'>
                                <span style='font-size:0.7rem; color:#555; display:block;'>Total Servicio</span>
                                <strong style='font-size:0.95rem; color:#000;'>$" . number_format($precio, 0, ',', '.') . "</strong>
                            </td>
                            <td style='border:none; background:transparent;'>
                                <span style='font-size:0.7rem; color:#555; display:block;'>Abono Realizado</span>
                                <strong style='font-size:0.95rem; color:#000;'>$" . number_format($abono, 0, ',', '.') . "</strong>
                            </td>
                            <td style='border:none; background:transparent;'>
                                <span style='font-size:0.7rem; color:#555; display:block;'>Saldo Pendiente</span>
                                <strong style='font-size:0.95rem; color:red;'>$" . number_format($saldo, 0, ',', '.') . "</strong>
                            </td>
                        </tr>
                    </table>
                </div>
            </body>
            </html>
        ";

        // Configurar Dompdf
        $options = new Options();
        $options->set('isRemoteEnabled', true);
        $dompdf = new Dompdf($options);
        
        $dompdf->loadHtml($htmlContenido);
        $dompdf->setPaper('letter', 'portrait');
        $dompdf->render();
        
        $salidaPdf = $dompdf->output();

        // Adjuntar el PDF generado correctamente al correo
        $mail->addStringAttachment($salidaPdf, 'HojaDeVida_SCOMP.pdf', 'base64', 'application/pdf');

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
?>