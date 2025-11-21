<?php
include("../conexion/conex.php");
include("../recursos/header.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro - Farmacia Barrancas</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@400;600;700&family=Open+Sans:wght@400;600&display=swap" rel="stylesheet">
    <link rel="icon" href="../recursos/img/favicon-pharmacy.ico" type="image/x-icon">
    
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --primary-green: #28a745;
            --dark-green: #1f7d32;
            --primary-blue: #007bff;
            --light-blue: #e8f0fe;
            --text-dark: #343a40;
            --text-light: #ffffff;
            --input-border: #ced4da;
            --input-focus: #80bdff;
            --error-red: #dc3545;
            --bg-gradient-start: #e2f7ea;
            --bg-gradient-end: #d1f3e0;
            --card-bg: #ffffff;
            --shadow-light: rgba(0, 0, 0, 0.1);
            --shadow-strong: rgba(0, 0, 0, 0.25);
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Open Sans', sans-serif;
            background: linear-gradient(135deg, var(--bg-gradient-start), var(--bg-gradient-end));
            background-image: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZlcnNpb249IjEuMSIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj4KICA8ZmlsdGVyIGlkPSJhIj4KICAgIDxmZWdhdXNzaWFuYmx1ciBpbj0iU291cmNlR3JhcGhpYyIgcmVzdWx0PSJiIiBzdGREZXZpYXRpb249IjEuNSIvPgogICAgPGZlQ29sb3JNYXRyaXggaW49ImIiIHJlc3VsdD0iYyIgdHlwZT0ibWF0cml4IiB2YWx1ZXM9IjAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIDAgMCAwIC43NCAwIi8+CiAgICA8ZmVPZmZzZXQgZHk9IjIiIGluPSJjIiByZXN1bHQ9ImQiLz4KICAgIDxmZUJsZW5kIGluPSJTb3VyY2VHcmFwaGljIiBpbj0iZCIvPgogIDwvZmlsdGVyPgoKICA8cGF0aCBkPSJtMCAwaDQwdjQwaC00MHoiIGZpbGw9InJnYmEoMjU1LDI1NSwyNTUsLjA0KSIvPgogIDxwYXRoIGQ9Im00MCAwaC00MHYtNDBoNDB6IiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LC4wNCkiLz4KICA8cGF0aCBkPSJtMCA0MHYtNDBoNDB2NDBoLTQwemIgZmlsbD0icmdiYSgyNTUsMjU1LDI1NSwuMDQpIi8+CiAgPHBhdGggZD0ibTQwIDQwdjQwdi00MGwtNDAtNDB6IiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LC4wNCkiLz4KPC9zdmc+'); /* Patrón sutil */
            background-size: 70px; 
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .register-container {
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 1.5rem;
        }
        
        .register-card {
            background-color: var(--card-bg);
            border-radius: 15px;
            box-shadow: 0 15px 40px var(--shadow-strong); 
            overflow: hidden;
            width: 100%;
            max-width: 480px;
            padding: 40px;
            text-align: center;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.35);
        }

        .register-card .logo-icon {
            font-size: 70px;
            margin-bottom: 1.5rem;
            color: var(--primary-green);
            animation: bounceIn 0.8s ease-out;
        }
        
        @keyframes bounceIn {
            0%, 20%, 40%, 60%, 80%, 100% {
                transition-timing-function: cubic-bezier(0.215, 0.610, 0.355, 1.000);
            }
            0% {
                opacity: 0;
                transform: scale3d(.3, .3, .3);
            }
            20% {
                transform: scale3d(1.1, 1.1, 1.1);
            }
            40% {
                transform: scale3d(.9, .9, .9);
            }
            60% {
                opacity: 1;
                transform: scale3d(1.03, 1.03, 1.03);
            }
            80% {
                transform: scale3d(.97, .97, .97);
            }
            100% {
                opacity: 1;
                transform: scale3d(1, 1, 1);
            }
        }

        .register-card h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            color: var(--text-dark);
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .register-card h3 {
            font-weight: 400;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
            text-align: left;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: var(--text-dark);
            font-weight: 600;
            font-size: 0.95em;
        }

        .form-group input {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--input-border);
            border-radius: 8px;
            font-size: 1em;
            color: var(--text-dark);
            box-sizing: border-box;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        
        .form-group input::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }
        
        .form-group input:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px rgba(0, 123, 255, 0.25);
        }

        .input-icon-inside {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            pointer-events: none;
            font-size: 1.1em;
        }

        .show-password-toggle {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.5rem;
            margin-bottom: 25px;
            font-size: 0.9em;
            color: var(--text-muted);
            cursor: pointer;
        }
        
        .show-password-toggle input[type="checkbox"] {
            cursor: pointer;
            width: 1.1rem;
            height: 1.1rem;
            accent-color: var(--primary-green);
            border-radius: 0.25rem;
        }
        
        .btn-register {
            width: 100%;
            background-color: var(--primary-green);
            border: none;
            border-radius: 8px;
            padding: 15px;
            font-size: 1.15em;
            font-weight: 700;
            color: white;
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.25);
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            text-transform: uppercase;
        }
        .btn-register:hover {
            background-color: var(--dark-green);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(40, 167, 69, 0.35);
        }
        .btn-register:active {
            transform: translateY(1px);
            box-shadow: 0 4px 10px rgba(40, 167, 69, 0.2);
        }

        .login-link {
            font-size: 0.9em;
            margin-top: 30px;
            color: var(--text-muted);
            text-align: center;
        }
        
        .login-link a {
            color: var(--primary-blue);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: var(--dark-green);
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .register-card {
                max-width: 400px;
                padding: 30px;
            }
            .register-card h1 {
                font-size: 2em;
            }
            .register-card h3 {
                font-size: 1em;
            }
        }
        @media (max-width: 480px) {
            .register-card {
                width: 95%;
                padding: 25px;
            }
            .register-card .logo-icon {
                font-size: 60px;
            }
            .register-card h1 {
                font-size: 1.8em;
            }
            .btn-register {
                padding: 12px;
                font-size: 1em;
            }
        }
    </style>
</head>
<body>

<?php
if (isset($_POST['registrar_user'])) {
    $correo = $_POST['correo'];
    $clave = $_POST['clave'];

    $clave_hasheada = password_hash($clave, PASSWORD_DEFAULT);


    $stmt = $conn->prepare("INSERT INTO usuarios(correo, clave) VALUES(?, ?)");
    $stmt->bind_param("ss", $correo, $clave_hasheada);

    if ($stmt->execute()) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "success",
                    title: "¡Registro Exitoso!",
                    text: "El usuario ha sido registrado correctamente. Ahora puedes iniciar sesión.",
                    confirmButtonText: "Aceptar",
                    confirmButtonColor: "#28a745",
                    customClass: {
                        popup: "rounded-5",
                        confirmButton: "rounded-pill"
                    }
                }).then(() => {
                    window.location.href = "../index.php"; 
                });
            });
        </script>';
    } else {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "error",
                    title: "Error en el Registro",
                    text: "Hubo un problema al registrar el usuario o el correo ya está en uso. Por favor, inténtalo de nuevo.",
                    confirmButtonText: "Entendido",
                    confirmButtonColor: "#dc3545",
                    customClass: {
                        popup: "rounded-5",
                        confirmButton: "rounded-pill"
                    }
                });
            });
        </script>';
    }
    $stmt->close();
}
?>

<main class="register-container">
    <div class="register-card">
        <i class="fa-solid fa-user-plus logo-icon"></i>
        
        <h1>Crear Cuenta</h1>
        <h3>Únete a nuestro sistema de gestión de farmacia</h3>
        
        <form action="registro.php" method="POST">
            <div class="form-group">
                <label for="correo">Correo Electrónico</label>
                <input required type="email" name="correo" id="correo" class="form-control" placeholder="nombre@ejemplo.com" autocomplete="email">
            </div>
            
            <div class="form-group">
                <label for="clave">Contraseña</label>
                <input required type="password" name="clave" id="clave" class="form-control" placeholder="••••••••" autocomplete="new-password">
            </div>
            
            <div class="show-password-toggle">
                <input type="checkbox" id="showPasswordToggle">
                <label for="showPasswordToggle">Mostrar contraseña</label>
            </div>
            
            <button type="submit" class="btn-register" name="registrar_user">
                Registrarse
            </button>
        </form>
        
        <p class="login-link">
            ¿Ya tienes una cuenta? <a href="../index.php">Inicia sesión aquí</a>
        </p>
    </div>
</main>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('clave');
    const showPasswordToggle = document.getElementById('showPasswordToggle');
    
    if (showPasswordToggle && passwordInput) {
        showPasswordToggle.addEventListener('change', function() {
            if (this.checked) {
                passwordInput.type = 'text';
            } else {
                passwordInput.type = 'password';
            }
        });
    }
});
</script>
</body>
</html>
<?php

