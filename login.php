<?php
// Iniciar sesión para el manejo de acceso del administrador
session_start();

$error = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $usuario_ingresado = $_POST['usuario'] ?? '';
    $password_ingresado = $_POST['password'] ?? '';

    // Credenciales de acceso del administrador de SCOMP
    $usuario_admin = "admin";
    $password_admin = "12345"; 

    if ($usuario_ingresado === $usuario_admin && $password_ingresado === $password_admin) {
        $_SESSION['admin_logueado'] = true;
        header("Location: admin.php");
        exit();
    } else {
        $error = "Usuario o contraseña incorrectos.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Login Administrativo - SCOMP</title>
    <link href="https://fonts.googleapis.com/css2?family=Orbitron:wght@400;700&family=Roboto:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        body { 
            background-color: #0b0f19; 
            color: #f8fafc; 
            font-family: 'Roboto', sans-serif; 
            display: flex; 
            justify-content: center; 
            align-items: center; 
            height: 100vh; 
            margin: 0; 
            background-image: linear-gradient(rgba(255,255,255,0.02) 1px, transparent 1px), linear-gradient(90deg, rgba(255,255,255,0.02) 1px, transparent 1px);
            background-size: 30px 30px;
        }
        
        .login-card { 
            width: 100%; 
            max-width: 400px; 
            background: #121826; 
            padding: 40px; 
            border-radius: 12px; 
            border: 1px solid #1e293b; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.5); 
            text-align: center;
        }

        .logo-img { 
            max-height: 85px; /* Logo más grande */
            width: auto; 
            margin-bottom: 20px; 
        }

        h2 { 
            color: #38bdf8; 
            font-family: 'Orbitron', sans-serif; 
            font-size: 1.1rem; 
            margin-bottom: 25px; 
            letter-spacing: 0.5px;
        }

        .form-group { margin-bottom: 18px; text-align: left; }
        
        label { display: block; margin-bottom: 6px; color: #cbd5e1; font-size: 0.85rem; font-weight: 500; }
        
        input { 
            width: 100%; 
            padding: 12px; 
            background: #0b0f19; 
            border: 1px solid #334155; 
            color: #fff; 
            border-radius: 8px; 
            font-size: 0.95rem; 
            box-sizing: border-box;
        }
        input:focus { border-color: #38bdf8; outline: none; }

        .btn-ingresar { 
            background: #38bdf8; 
            color: #0b0f19; 
            font-weight: bold; 
            border: none; 
            padding: 12px; 
            width: 100%; 
            border-radius: 8px; 
            cursor: pointer; 
            margin-top: 15px; 
            font-size: 0.95rem; 
            font-family: 'Orbitron', sans-serif; 
            transition: background 0.2s;
        }
        .btn-ingresar:hover { background: #0ea5e9; }

        .error-msg { 
            background: #7f1d1d; 
            color: #fca5a5; 
            padding: 10px; 
            border-radius: 8px; 
            margin-bottom: 20px; 
            font-size: 0.85rem; 
            font-weight: 500; 
            border: 1px solid #991b1b;
        }

        .volver-web { 
            display: block; 
            margin-top: 20px; 
            color: #94a3b8; 
            text-decoration: none; 
            font-size: 0.85rem; 
        }
        .volver-web:hover { color: #fff; }
    </style>
</head>
<body>
    <div class="login-card">
        <div>
            <a href="index.html">
                <img src="logo-scomp.jpg" alt="SCOMP Logo" class="logo-img">
            </a>
        </div>
        <h2>ACCESO ADMINISTRATIVO</h2>

        <?php if (!empty($error)): ?>
            <div class="error-msg"><?php echo $error; ?></div>
        <?php endif; ?>

        <form action="login.php" method="POST">
            <div class="form-group">
                <label>Usuario</label>
                <input type="text" name="usuario" placeholder="Ingresa tu usuario" required autocomplete="off">
            </div>

            <div class="form-group">
                <label>Contraseña</label>
                <input type="password" name="password" placeholder="••••••••" required>
            </div>

            <button type="submit" class="btn-ingresar">Ingresar</button>
        </form>

        <a href="index.html" class="volver-web">← Volver al sitio principal</a>
    </div>
</body>
</html>