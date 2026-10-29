<?php
require 'phpmailer/Exception.php';
require 'phpmailer/PHPMailer.php';
require 'phpmailer/SMTP.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

function enviarCorreoScomp($destinatario, $nombreCliente, $servicio, $precio, $abono, $saldo, $marca, $modelo, $serial, $falla, $observaciones) {
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

        // Opcional para evitar problemas de certificados SSL en XAMPP local
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
        
        $mail->Body    = "
            <div style='font-family: Arial, sans-serif; color: #333;'>
                <h2 style='color: #0284c7;'>Hola, <b>{$nombreCliente}</b></h2>
                <p>Te saludamos de <b>SCOMP Soporte Técnico</b>. Adjunto a este correo encontrarás el documento detallado con la Hoja de Vida de tu equipo y la cuenta de cobro.</p>
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

        // Crear el contenido del archivo adjunto optimizado para guardarse como PDF
        $htmlAdjunto = "
            <html>
            <head>
                <meta charset='UTF-8'>
                <title>Cuenta de Cobro y Hoja de Vida - SCOMP</title>
                <style>
                    body { font-family: Arial, sans-serif; padding: 30px; color: #111; background: #fff; }
                    h1 { color: #0284c7; text-align: center; margin-bottom: 5px; }
                    .header { text-align: center; border-bottom: 2px dashed #ccc; padding-bottom: 15px; margin-bottom: 20px; }
                    .section { margin-bottom: 20px; }
                    .section-title { background: #f1f5f9; padding: 8px; font-weight: bold; border-left: 4px solid #0284c7; margin-bottom: 10px; }
                    .table-data { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                    .table-data td { padding: 8px; border-bottom: 1px solid #eee; }
                    .financial-box { border: 2px solid #0284c7; padding: 15px; border-radius: 8px; margin-top: 20px; background: #f8fafc; }
                    @media print {
                        .no-print { display: none; }
                    }
                </style>
            </head>
            <body onload='window.print();'>
                <div class='header'>
                    <h1>SCOMP ®</h1>
                    <p><b>Soporte Técnico y Soluciones a tu Alcance</b><br>
                    Carrera 28F # 4-12 Barrio Tulipanes de la Italia Palmira Valle | Tel: +57 300 366 6441</p>
                </div>

                <h2 style='text-align: center;'>HOJA DE VIDA Y CUENTA DE COBRO</h2>
                <p><b>Cliente:</b> {$nombreCliente} | <b>Fecha:</b> " . date('d/m/Y') . "</p>

                <div class='section'>
                    <div class='section-title'>1. Especificaciones del Equipo</div>
                    <table class='table-data'>
                        <tr><td><b>Marca PC:</b> {$marca}</td><td><b>Modelo:</b> {$modelo}</td></tr>
                        <tr><td colspan='2'><b>Número de Serie:</b> {$serial}</td></tr>
                    </table>
                </div>

                <div class='section'>
                    <div class='section-title'>2. Diagnóstico y Servicio</div>
                    <table class='table-data'>
                        <tr><td><b>Falla Reportada:</b> {$falla}</td></tr>
                        <tr><td><b>Observaciones / Diagnóstico:</b> {$observaciones}</td></tr>
                    </table>
                </div>

                <div class='financial-box'>
                    <h3 style='margin-top:0; color:#0284c7;'>Resumen Financiero</h3>
                    <p><b>Tipo de Servicio:</b> {$servicio}</p>
                    <p><b>Precio Total:</b> $" . number_format($precio, 0, ',', '.') . "</p>
                    <p><b>Abono Realizado:</b> $" . number_format($abono, 0, ',', '.') . "</p>
                    <p style='font-size: 1.2em;'><b>Saldo Pendiente:</b> <span style='color:red;'>$" . number_format($saldo, 0, ',', '.') . "</span></p>
                </div>
                
                <p class='no-print' style='text-align:center; margin-top:30px; color:#666; font-size:0.9rem;'>
                    <i>(Nota: Se ha abierto automáticamente el diálogo de impresión. Puede elegir 'Guardar como PDF').</i>
                </p>
            </body>
            </html>
        ";

        // Adjuntar el archivo listo para exportar a PDF
        $mail->addStringAttachment($htmlAdjunto, 'CuentaDeCobro_SCOMP.html', 'base64', 'text/html');

        $mail->send();
        return true;
    } catch (Exception $e) {
        return $mail->ErrorInfo;
    }
}
?>