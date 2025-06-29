<?php
include("../conexion/conex.php");
include("../recursos/header.php");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrar Usuario</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        :root {
            --bg-color: #f8f9fa;
            --card-bg: #ffffff;
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --border-color: #e9ecef;
            --shadow-light: 0 8px 30px rgba(0, 0, 0, 0.08);
            --shadow-subtle: 0 2px 10px rgba(0, 0, 0, 0.05);
            --focus-border: #007bff;
        }

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-color);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
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
            border: 1px solid var(--border-color);
            border-radius: 1.5rem;
            padding: 3.5rem 3rem;
            width: 100%;
            max-width: 480px;
            box-shadow: var(--shadow-light);
            text-align: center;
            animation: fadeIn 0.8s ease-out;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0, 0, 0, 0.1);
        }

        .register-card .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 1.5rem;
            color: var(--primary-color);
            font-size: 3rem;
        }

        .register-card h1 {
            font-weight: 800;
            color: var(--text-dark);
            font-size: 2.2rem;
            margin-bottom: 0.5rem;
        }

        .register-card h3 {
            font-weight: 500;
            color: var(--text-muted);
            margin-bottom: 2.5rem;
            font-size: 1.1rem;
        }

        .form-label-elegant {
            font-weight: 600;
            color: var(--text-dark);
            font-size: 0.95rem;
            margin-bottom: 0.5rem;
            text-align: left;
            width: 100%;
        }

        .input-group-elegant {
            position: relative;
            margin-bottom: 1.5rem;
            text-align: left;
        }

        .input-group-elegant .form-control {
            background-color: var(--bg-color);
            border: 2px solid var(--border-color);
            border-radius: 0.75rem;
            padding: 1.2rem 1.5rem 1.2rem 3.5rem;
            font-size: 1rem;
            color: var(--text-dark);
            transition: all 0.3s ease;
        }
        
        .input-group-elegant .form-control::placeholder {
            color: #adb5bd;
            font-weight: 400;
        }
        
        .input-group-elegant .form-control:focus {
            background-color: #ffffff;
            border-color: var(--focus-border);
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.1);
            outline: none;
        }

        .input-group-elegant .input-icon {
            position: absolute;
            left: 1.2rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--secondary-color);
            pointer-events: none;
            font-size: 1.3rem;
            transition: color 0.3s ease;
        }
        
        .input-group-elegant .form-control:focus + .input-icon {
            color: var(--primary-color);
        }
        
        .show-password-toggle {
            display: flex;
            align-items: center;
            justify-content: flex-start;
            gap: 0.5rem;
            margin-bottom: 2rem;
            font-size: 0.9rem;
            color: var(--text-muted);
            cursor: pointer;
        }
        
        .show-password-toggle input[type="checkbox"] {
            cursor: pointer;
            width: 1.1rem;
            height: 1.1rem;
            accent-color: var(--primary-color);
            border-radius: 0.25rem;
        }
        
        .btn-register-elegant {
            width: 100%;
            background-color: var(--primary-color);
            border: none;
            border-radius: 2rem;
            padding: 1.2rem;
            font-size: 1.1rem;
            font-weight: 700;
            color: white;
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.25);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-transform: uppercase;
        }
        .btn-register-elegant:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0, 123, 255, 0.35);
        }
        .btn-register-elegant:active {
            transform: translateY(1px);
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.2);
        }

        .login-link {
            font-size: 0.9rem;
            margin-top: 2rem;
            color: var(--text-muted);
        }
        
        .login-link a {
            color: var(--primary-color);
            font-weight: 600;
            text-decoration: none;
            transition: color 0.3s ease;
        }
        
        .login-link a:hover {
            color: #0056b3;
            text-decoration: underline;
        }
    </style>
</head>
<body>

<?php
if (isset($_POST['registrar_user'])) {
    $correo = $_POST['correo'];
    $clave = $_POST['clave'];
    $query = "INSERT INTO usuarios(correo, clave) VALUES('$correo','$clave')";
    $result = mysqli_query($conn, $query);

    if ($result) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "success",
                    title: "¡Registro Exitoso!",
                    text: "El usuario ha sido registrado correctamente.",
                    confirmButtonText: "Aceptar",
                    confirmButtonColor: "#28a745",
                    customClass: {
                        popup: "rounded-5",
                        confirmButton: "rounded-pill"
                    }
                }).then(() => {
                    window.location.href = "login.php"; // Redirige al login después de la alerta
                });
            });
        </script>';
    } else {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "error",
                    title: "Error en el Registro",
                    text: "Hubo un problema al registrar el usuario. Por favor, inténtalo de nuevo.",
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
}
?>

<main class="register-container">
    <div class="register-card">
        <i class="bi bi-person-add logo"></i>
        <h1>Crear Cuenta</h1>
        <h3>Únete a nuestro sistema de gestión</h3>
        
        <form action="registro.php" method="POST">
            <div class="input-group-elegant">
                <input required type="email" name="correo" class="form-control" placeholder="Correo electrónico">
                <span class="input-icon"><i class="bi bi-envelope"></i></span>
            </div>
            
            <div class="input-group-elegant">
                <input required type="password" name="clave" class="form-control" placeholder="Contraseña" id="passwordInput">
                <span class="input-icon"><i class="bi bi-lock"></i></span>
            </div>
            
            <div class="show-password-toggle">
                <input type="checkbox" id="showPasswordToggle">
                <label for="showPasswordToggle">Mostrar contraseña</label>
            </div>
            
            <button type="submit" class="btn btn-register-elegant" name="registrar_user">
                Registrarse
            </button>
        </form>
        
        <p class="login-link">
            ¿Ya tienes una cuenta? <a href="login.php">Inicia sesión aquí</a>
        </p>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('passwordInput');
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
<?php include("../recursos/footer.php")?>