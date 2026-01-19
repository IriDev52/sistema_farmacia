<?php
include("../conexion/conex.php");
include("../recursos/header.php");

if (isset($_POST['registrar_user'])) {
    $correo = trim($_POST['correo']);
    $clave = $_POST['clave'];
    $clave2 = $_POST['clave2'];

    if ($clave !== $clave2) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "error",
                    title: "Error de coincidencia",
                    text: "Las contraseñas ingresadas no son iguales.",
                    confirmButtonColor: "#3b82f6"
                });
            });
        </script>';
    } elseif (strlen($clave) < 8) {
        echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                Swal.fire({
                    icon: "warning",
                    title: "Contraseña débil",
                    text: "La seguridad es prioridad. Usa al menos 8 caracteres.",
                    confirmButtonColor: "#3b82f6"
                });
            });
        </script>';
    } else {
        $sql_check = "SELECT id FROM usuarios WHERE correo = ?";
        if ($stmt_check = $conn->prepare($sql_check)) {
            $stmt_check->bind_param("s", $correo);
            $stmt_check->execute();
            $stmt_check->store_result();
            if ($stmt_check->num_rows > 0) {
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            icon: "info",
                            title: "Usuario existente",
                            text: "Este correo ya está registrado en el sistema.",
                            confirmButtonText: "Ir al Login"
                        }).then(() => { window.location.href = "../index.php"; });
                    });
                </script>';
                $stmt_check->close();
                exit(); 
            }
            $stmt_check->close();
        }

        $clave_hasheada = password_hash($clave, PASSWORD_DEFAULT);
        $sql_insert = "INSERT INTO usuarios(correo, clave) VALUES(?, ?)";
        if ($stmt_insert = $conn->prepare($sql_insert)) {
            $stmt_insert->bind_param("ss", $correo, $clave_hasheada);
            if ($stmt_insert->execute()) {
                echo '<script>
                    document.addEventListener("DOMContentLoaded", function() {
                        Swal.fire({
                            icon: "success",
                            title: "Registro Completo",
                            text: "Acceso concedido. Bienvenido al sistema.",
                            confirmButtonColor: "#10b981"
                        }).then(() => { window.location.href = "../index.php"; });
                    });
                </script>';
            }
            $stmt_insert->close();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP | Registro de Seguridad</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        :root {
            --brand-primary: #2563eb;
            --brand-secondary: #64748b;
            --bg-body: #f8fafc;
            --glass-bg: rgba(255, 255, 255, 0.9);
            --text-main: #1e293b;
        }

        body {
            margin: 0;
            font-family: 'Inter', sans-serif;
            background: radial-gradient(circle at top right, #dbeafe, #f8fafc);
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .auth-card {
            background: var(--glass-bg);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.5);
            width: 100%;
            max-width: 440px;
            padding: 2.5rem;
            border-radius: 24px;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.08);
        }

        .header { text-align: center; margin-bottom: 2rem; }
        
        .header .icon-box {
            background: #eff6ff;
            color: var(--brand-primary);
            width: 60px; height: 60px;
            border-radius: 16px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; margin: 0 auto 1rem;
        }

        h1 { color: var(--text-main); font-size: 1.5rem; font-weight: 700; margin: 0; }
        p { color: var(--brand-secondary); font-size: 0.9rem; margin-top: 0.5rem; }

        .input-group { margin-bottom: 1.2rem; }
        .input-group label {
            display: block;
            font-size: 0.8rem;
            font-weight: 600;
            color: var(--brand-secondary);
            margin-bottom: 0.5rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .input-wrapper { position: relative; }
        .input-wrapper i {
            position: absolute; left: 16px; top: 50%;
            transform: translateY(-50%); color: #cbd5e1;
        }

        input {
            width: 100%;
            padding: 12px 16px 12px 45px;
            border: 1.5px solid #e2e8f0;
            border-radius: 12px;
            font-size: 0.95rem;
            box-sizing: border-box;
            transition: all 0.2s;
            background: #fff;
        }

        input:focus {
            outline: none;
            border-color: var(--brand-primary);
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
        }

        .check-container {
            display: flex; align-items: center; gap: 8px;
            margin: 1rem 0 1.5rem; font-size: 0.85rem; color: var(--brand-secondary);
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            background: var(--brand-primary);
            color: white;
            border: none;
            padding: 14px;
            border-radius: 12px;
            font-weight: 600;
            font-size: 1rem;
            cursor: pointer;
            transition: transform 0.2s, background 0.2s;
        }

        .btn-submit:hover { background: #1d4ed8; transform: translateY(-1px); }

        .footer-link {
            text-align: center; margin-top: 1.5rem;
            font-size: 0.85rem; color: var(--brand-secondary);
        }
        .footer-link a { color: var(--brand-primary); text-decoration: none; font-weight: 600; }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="header">
        <div class="icon-box"><i class="fa-solid fa-shield-halved"></i></div>
        <h1>Registro de Personal</h1>
        <p>Sistema de Gestión Farmacéutica</p>
    </div>

    <form method="POST">
        <div class="input-group">
            <label>Correo Institucional</label>
            <div class="input-wrapper">
                <i class="fa-regular fa-envelope"></i>
                <input required type="email" name="correo" placeholder="correo@farmacia.com">
            </div>
        </div>

        <div class="input-group">
            <label>Contraseña Acceso</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-lock"></i>
                <input required type="password" name="clave" id="clave" placeholder="••••••••">
            </div>
        </div>

        <div class="input-group">
            <label>Confirmar Credencial</label>
            <div class="input-wrapper">
                <i class="fa-solid fa-circle-check"></i>
                <input required type="password" name="clave2" id="clave2" placeholder="Repetir contraseña">
            </div>
        </div>

        <label class="check-container">
            <input type="checkbox" id="showPass"> Visualizar campos de clave
        </label>

        <button type="submit" name="registrar_user" class="btn-submit">
            Finalizar Registro
        </button>
    </form>

    <div class="footer-link">
        ¿Ya posee una cuenta? <a href="../index.php">Acceder aquí</a>
    </div>
</div>

<script>
    const pass1 = document.getElementById('clave');
    const pass2 = document.getElementById('clave2');
    const show = document.getElementById('showPass');

    show.addEventListener('change', () => {
        const type = show.checked ? 'text' : 'password';
        pass1.type = type;
        pass2.type = type;
    });
</script>

</body>
</html>