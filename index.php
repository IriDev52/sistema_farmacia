<?php
include("recursos/header.php");
include("conexion/conex.php");

session_start();

$error_message = "";

if (!empty($_POST['comprobar'])) {
    $usuario = $_POST['correo'];
    $clave = $_POST['clave'];

    $sql_usuario_existe = $conn->prepare("SELECT * FROM usuarios WHERE correo = ?");
    $sql_usuario_existe->bind_param("s", $usuario);
    $sql_usuario_existe->execute();
    $result_usuario_existe = $sql_usuario_existe->get_result();

    if ($result_usuario_existe->num_rows > 0) {
        $sql_login = $conn->prepare("SELECT * FROM usuarios WHERE correo = ? AND clave = ?");
        $sql_login->bind_param("ss", $usuario, $clave);
        $sql_login->execute();
        $result_login = $sql_login->get_result();

        if ($result_login->num_rows > 0) {
            $_SESSION['usuario'] = $usuario;
            header("Location: paginas/inicio.php");
            exit();
        } else {
            $error_message = "Contraseña incorrecta. Por favor, inténtelo de nuevo.";
        }
    } else {
        $error_message = "Este usuario no está registrado. Por favor, regístrese.";
    }
}
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

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background-color: var(--body-bg);
            color: var(--text-dark);
            display: flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
        }

        .container {
            display: flex;
            width: 100%;
            max-width: 950px;
            background-color: var(--right-bg-color);
            border-radius: 20px;
            box-shadow: var(--shadow-strong);
            overflow: hidden;
            box-sizing: border-box;
        }

        .left-section {
            flex: 1;
            background-color: var(--left-bg-color);
            color: var(--text-light);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            box-sizing: border-box;
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
            max-width: 180px;
            height: auto;
            margin-bottom: 2rem;
            border-radius: 50%;
            border: 6px solid rgba(255, 255, 255, 0.5);
            box-shadow: 0 0 25px rgba(255, 255, 255, 0.4);
            transition: transform 0.3s ease-in-out;
        }
        
        .left-section .logo:hover {
            transform: scale(1.05);
        }

        .left-section h1 {
            font-size: 2.8rem;
            margin-bottom: 1rem;
            font-weight: 900;
            text-shadow: 2px 2px 4px rgba(0,0,0,0.2);
        }

        .left-section p {
            font-size: 1.2rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        .right-section {
            flex: 1;
            background-color: var(--right-bg-color);
            padding: 3rem;
            display: flex;
            flex-direction: column;
            justify-content: center;
            box-sizing: border-box;
        }

        .login-header h2 {
            font-size: 2.4rem;
            margin-bottom: 1.5rem;
            color: var(--text-dark);
            text-align: center;
            font-weight: 700;
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 1rem;
            border-radius: 12px;
            border: 2px solid #ddd;
            box-shadow: inset 0 2px 5px rgba(0, 0, 0, 0.05);
            font-size: 1rem;
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .input-group input:focus {
            outline: none;
            border-color: var(--left-bg-color);
            box-shadow: 0 0 0 4px rgba(0, 131, 143, 0.2);
        }

        .btn {
            width: 100%;
            padding: 1.2rem;
            border-radius: 12px;
            background-color: var(--left-bg-color);
            color: var(--text-light);
            font-size: 1.2rem;
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
            padding: 1rem;
            border-radius: 10px;
            margin-bottom: 1.5rem;
            font-weight: 500;
            text-align: center;
        }

        .link-text {
            text-align: center;
            margin-top: 2rem;
            font-size: 1rem;
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
            margin-top: -10px;
            margin-bottom: 25px;
        }
        .checkbox-container input[type="checkbox"] {
            margin-right: 8px;
            width: auto;
            transform: scale(1.2);
            cursor: pointer;
        }
        .checkbox-container label {
            font-size: 0.95rem;
            color: #6c757d;
            cursor: pointer;
        }

        @media (max-width: 850px) {
            .container {
                flex-direction: column;
                max-width: 500px;
                border-radius: 15px;
            }
            .left-section, .right-section {
                padding: 2.5rem;
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
</script>

</body>
</html>