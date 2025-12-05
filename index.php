<?php
// index.php

// CRÍTICO: Iniciar el búfer de salida para permitir la redirección
ob_start(); 

// 1. Iniciar la sesión (Siempre al inicio)
session_start();

// Rutas
include("conexion/conex.php"); 

$error_message = "";

// 2. Procesamiento del Formulario de Login
if (isset($_POST['comprobar']) && $_POST['comprobar'] == 1) {
    
    $correo_ingresado = htmlspecialchars(trim($_POST['correo']));
    $clave_ingresada = $_POST['clave']; 
    
    $sql_login = "SELECT id, correo, clave FROM usuarios WHERE correo = ?";
    
    if ($stmt = $conn->prepare($sql_login)) {
        $stmt->bind_param("s", $correo_ingresado);
        $stmt->execute();
        $result_login = $stmt->get_result();
        
        if ($result_login->num_rows === 1) {
            $usuario_data = $result_login->fetch_assoc();
            $clave_hash_almacenada = $usuario_data['clave'];
            
            if (password_verify($clave_ingresada, $clave_hash_almacenada)) {
                
                // --- ÉXITO ---
                session_regenerate_id(true); 
                
                $_SESSION['usuario_id'] = $usuario_data['id']; 
                $_SESSION['usuario_correo'] = $usuario_data['correo'];
                $_SESSION['logeado'] = true;
                
                // Redirección exitosa (PRG)
                header("Location: paginas/inicio.php"); 
                $stmt->close();
                exit(); 
                
            } else {
                // --- FALLO 1: Contraseña incorrecta ---
                $_SESSION['error_login'] = "Correo o Contraseña incorrecta. Por favor, inténtelo de nuevo.";
            }
            
        } else {
            // --- FALLO 2: Usuario no encontrado ---
            $_SESSION['error_login'] = "Correo o Contraseña incorrecta. Por favor, inténtelo de nuevo.";
        }
        
        $stmt->close();
        
    } else {
        // --- FALLO 3: Error del sistema ---
        $_SESSION['error_login'] = "Error interno del sistema (E001).";
        error_log("Error al preparar la consulta de login: " . $conn->error);
    }

    // SI LLEGA AQUÍ, HUBO UN FALLO, FORZAMOS LA REDIRECCIÓN A GET (PRG)
    // Usé la ruta root-relative que corrigió tu problema anterior
    header("Location: /index.php"); 
    exit(); 
}

// 3. RECUPERAR EL ERROR DE LA SESIÓN (Sólo si venimos de un fallo POST)
if (isset($_SESSION['error_login'])) {
    $error_message = $_SESSION['error_login'];
    // Borrar la variable de sesión inmediatamente después de usarla
    unset($_SESSION['error_login']); 
}

ob_end_flush(); 
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Iniciar Sesión - Farmacia C.A.</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --left-bg-color: #00838F;
            --right-bg-color: #FAFAFA;
            --text-dark: #333333;
            --text-light: #FFFFFF;
            --accent-color: #006064;
            --body-bg: #B2EBF2;
            --shadow-light: 0 4px 15px rgba(0, 0, 0, 0.1);
            --shadow-strong: 0 10px 30px rgba(0, 0, 0, 0.2);
            --error-red: #dc3545;
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 15px;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 950px;
            min-height: 550px;
            background-color: var(--right-bg-color);
            border-radius: 20px;
            box-shadow: var(--shadow-strong);
            overflow: hidden;
            flex-wrap: wrap;
        }

        .left-section {
            flex: 1;
            min-width: 300px;
            background-color: var(--left-bg-color);
            color: var(--text-light);
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            position: relative;
            z-index: 1;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255, 255, 255, 0.1);
            z-index: -1;
        }

        .left-section .logo {
            max-width: 140px;
            width: 100%;
            height: auto;
            margin-bottom: 1.5rem;
            border-radius: 50%;
            border: 5px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 20px rgba(255, 255, 255, 0.4);
            transition: transform 0.3s ease-in-out;
        }
        
        .left-section .logo:hover {
            transform: scale(1.05);
        }

        .left-section h1 {
            font-size: clamp(1.8rem, 4vw, 2.8rem);
            margin-bottom: 1rem;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
            line-height: 1.2;
        }

        .left-section p {
            font-size: clamp(1rem, 2.5vw, 1.2rem);
            opacity: 0.9;
            line-height: 1.5;
            max-width: 90%;
        }

        .right-section {
            flex: 1;
            min-width: 300px;
            background-color: var(--right-bg-color);
            padding: 2.5rem 2rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .login-header h2 {
            font-size: clamp(1.8rem, 4vw, 2.4rem);
            margin-bottom: 1.5rem;
            color: var(--text-dark);
            text-align: center;
            font-weight: 700;
            line-height: 1.2;
        }

        .input-group {
            margin-bottom: 20px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            border: 2px solid #ddd;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
            font-size: 1rem;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--left-bg-color);
            box-shadow: 0 0 0 4px rgba(0, 131, 143, 0.2);
        }

        .btn {
            width: 100%;
            padding: 1.1rem;
            border-radius: 12px;
            background-color: var(--left-bg-color);
            color: var(--text-light);
            font-size: 1.1rem;
            font-weight: bold;
            border: none;
            cursor: pointer;
            transition: background-color 0.3s ease, transform 0.2s, box-shadow 0.3s;
            box-shadow: 0 6px 15px rgba(0, 131, 143, 0.3);
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .btn:hover {
            background-color: var(--accent-color);
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(0, 96, 100, 0.4);
        }

        .error-message {
            color: var(--error-red);
            background-color: #f8d7da;
            border: 1px solid #f5c6cb;
            padding: 0.8rem 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
            font-size: 0.95rem;
        }

        .link-text {
            text-align: center;
            margin-top: 1.8rem;
            font-size: 0.95rem;
        }

        .link-text a {
            color: var(--left-bg-color);
            text-decoration: none;
            font-weight: bold;
        }
        
        .link-text a:hover {
            text-decoration: underline;
        }
        
        .checkbox-container {
            display: flex;
            align-items: center;
            margin-top: -8px;
            margin-bottom: 20px;
        }
        
        .checkbox-container input[type="checkbox"] {
            margin-right: 8px;
            width: auto;
            transform: scale(1.2);
            cursor: pointer;
        }
        
        .checkbox-container label {
            font-size: 0.9rem;
            color: #6c757d;
            cursor: pointer;
        }

        /* Media Queries para dispositivos pequeños */
        @media (max-width: 768px) {
            body, html {
                padding: 10px;
                height: auto;
                min-height: 100vh;
                align-items: flex-start;
                padding-top: 20px;
                padding-bottom: 20px;
            }
            
            .container {
                flex-direction: column;
                max-width: 100%;
                min-height: auto;
                border-radius: 15px;
                margin: 0 auto;
            }
            
            .left-section, .right-section {
                padding: 2rem 1.5rem;
                min-width: 100%;
            }
            
            .left-section {
                padding-top: 2.5rem;
                padding-bottom: 2rem;
            }
            
            .left-section h1 {
                font-size: 2rem;
            }
            
            .left-section p {
                font-size: 1rem;
            }
            
            .left-section .logo {
                max-width: 120px;
                margin-bottom: 1.2rem;
            }
            
            .login-header h2 {
                font-size: 1.8rem;
                margin-bottom: 1.2rem;
            }
            
            .btn {
                padding: 1rem;
                font-size: 1rem;
            }
        }

        @media (max-width: 480px) {
            .left-section, .right-section {
                padding: 1.8rem 1.2rem;
            }
            
            .left-section {
                padding-top: 2rem;
                padding-bottom: 1.8rem;
            }
            
            .left-section .logo {
                max-width: 100px;
                border-width: 4px;
            }
            
            .left-section h1 {
                font-size: 1.6rem;
                margin-bottom: 0.8rem;
            }
            
            .left-section p {
                font-size: 0.9rem;
                line-height: 1.4;
            }
            
            .login-header h2 {
                font-size: 1.6rem;
                margin-bottom: 1rem;
            }
            
            .input-group input {
                padding: 0.9rem;
                font-size: 0.95rem;
            }
            
            .btn {
                padding: 0.9rem;
                font-size: 0.95rem;
            }
            
            .checkbox-container label {
                font-size: 0.85rem;
            }
            
            .link-text {
                font-size: 0.9rem;
                margin-top: 1.5rem;
            }
            
            .error-message {
                padding: 0.7rem 0.9rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 360px) {
            .left-section, .right-section {
                padding: 1.5rem 1rem;
            }
            
            .left-section .logo {
                max-width: 90px;
            }
            
            .left-section h1 {
                font-size: 1.4rem;
            }
            
            .input-group input {
                padding: 0.8rem;
            }
        }

        /* Para pantallas muy altas en móviles */
        @media (max-height: 700px) and (max-width: 768px) {
            body, html {
                align-items: flex-start;
                padding-top: 10px;
            }
            
            .container {
                margin-top: 10px;
                margin-bottom: 10px;
            }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="left-section">
        <img src="logo_farmacia.jpeg" alt="Logo de Farmacia C.A." class="logo">
        <h1>Bienvenido</h1>
        <p>Inicia sesión para acceder a tu sistema de inventario de farmacia.</p>
    </div>
    <div class="right-section">
        <div class="login-header">
            <h2>Acceso al Sistema</h2>
        </div>
        <?php
        // Mostrar mensaje de error si existe
        if ($error_message) {
            echo '<div class="error-message">' . htmlspecialchars($error_message) . '</div>';
        }
        ?>
        <form action="index.php" method="POST">
            <div class="input-group">
                <input type="email" name="correo" id="correo" placeholder="Correo electrónico" required>
            </div>
            <div class="input-group">
                <input type="password" name="clave" id="clave" placeholder="Contraseña" required>
            </div>
            <div class="checkbox-container">
                <input type="checkbox" id="mostrar_contrasena_checkbox">
                <label for="mostrar_contrasena_checkbox">Mostrar contraseña</label>
            </div>
            <button type="submit" class="btn" name="comprobar" value="1">Ingresar</button>
        </form>
        <div class="link-text">
            <span>¿No tienes una cuenta? <a href="paginas/registro.php">Regístrate aquí</a></span>
        </div>
    </div>
</div>

<script>
    const passwordInput = document.getElementById('clave');
    const showPasswordCheckbox = document.getElementById('mostrar_contrasena_checkbox');
    showPasswordCheckbox.addEventListener('change', function() {
        if (this.checked) {
            passwordInput.type = 'text';
        } else {
            passwordInput.type = 'password';
        }
    });
    
    // Mejora para móviles: evitar zoom en inputs
    document.addEventListener('DOMContentLoaded', function() {
        if (/iPhone|iPad|iPod|Android/i.test(navigator.userAgent)) {
            const inputs = document.querySelectorAll('input[type="email"], input[type="password"]');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    window.setTimeout(() => {
                        document.body.style.transform = 'scale(1)';
                    }, 100);
                });
            });
        }
    });
</script>

</body>
</html