<?php
// Incluye la conexión a la base de datos
include('conexion/conex.php'); 

$mensaje = ''; 
$email_recuperacion = '';

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['email'])) {
    $email = $_POST['email'];
    $email_recuperacion = htmlspecialchars($email); // Para mantenerlo en el formulario

    // Sanitizar el email
    $email_seguro = mysqli_real_escape_string($conn, $email);
    
    // 1. Verificar si el usuario existe
    $query = "SELECT id, correo FROM usuarios WHERE correo = '{$email_seguro}' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) > 0) {
        $usuario_data = mysqli_fetch_assoc($result);
        $user_id = $usuario_data['id'];

        // 2. Generar Token y Fecha de Expiración (1 hora)
        $token = bin2hex(random_bytes(50));
        $expira = date("Y-m-d H:i:s", time() + 3600); 
        
        // 3. Guardar el token en la base de datos
        $update_query = "UPDATE usuarios SET reset_token = '{$token}', reset_expira = '{$expira}' WHERE id = {$user_id}";
        
        if (mysqli_query($conn, $update_query)) {
            // 4. ENVIAR CORREO ELECTRÓNICO
            // **IMPORTANTE**: Reemplaza 'http://tusistema.com' por la URL real de tu aplicación
            $enlace = "http://tusistema.com/restablecer_contrasena.php?token=" . $token;
            
            $asunto = "Restablecimiento de Contraseña - Farmacia C.A.";
            $cuerpo = "Hola,\n\nPara restablecer tu contraseña, haz clic en el siguiente enlace. Este enlace expirará en 1 hora:\n\n" . $enlace . "\n\nSi no solicitaste este cambio, ignora este correo.";
            
            // --- INICIO SIMULACIÓN DE ENVÍO DE CORREO ---
            // DEBES IMPLEMENTAR AQUÍ EL CÓDIGO REAL PARA ENVIAR EL EMAIL
            // $headers = "From: no-responder@tufarmacia.com\r\n";
            // mail($email, $asunto, $cuerpo, $headers);
            // --- FIN SIMULACIÓN DE ENVÍO DE CORREO ---

            $mensaje = "✅ Se ha enviado un correo electrónico con las instrucciones de recuperación. Por favor, revise su bandeja de entrada. (Enlace generado: {$enlace})"; // Muestra el enlace para pruebas. EN PRODUCCIÓN, NO LO MUESTRES.
        } else {
             $mensaje = "❌ Error interno al generar el token. Intente de nuevo más tarde.";
        }
    } else {
        // Mensaje genérico por seguridad para no revelar si el email existe o no.
        $mensaje = "✅ Si el correo electrónico proporcionado se encuentra en nuestros registros, recibirá un enlace de restablecimiento.";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña</title>
    <style>
         /* ESTILOS BÁSICOS PARA ESTA PÁGINA */
        :root {
            --left-bg-color: #00838F;
            --right-bg-color: #FAFAFA;
            --text-dark: #333333;
            --body-bg: #B2EBF2;
            --error-red: #dc3545;
        }
        body { font-family: 'Roboto', sans-serif; background-color: var(--body-bg); display: flex; justify-content: center; align-items: center; min-height: 100vh; margin: 0; }
        .container { background-color: var(--right-bg-color); padding: 40px; border-radius: 10px; box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2); max-width: 400px; width: 90%; text-align: center; }
        h2 { color: var(--text-dark); margin-bottom: 20px; }
        input[type="email"] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
        button { background-color: var(--left-bg-color); color: white; padding: 12px 20px; border: none; border-radius: 8px; cursor: pointer; font-size: 1rem; transition: background-color 0.3s; }
        button:hover { background-color: #006064; }
        a { color: var(--left-bg-color); text-decoration: none; display: block; margin-top: 20px; }
        .error-message, .success-message { padding: 10px; margin-bottom: 15px; border-radius: 8px; font-weight: bold; }
        .error-message { background-color: #f8d7da; color: var(--error-red); }
        .success-message { background-color: #d4edda; color: #155724; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Recuperación de Contraseña</h2>
        
        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo (strpos($mensaje, '❌') !== false) ? 'error-message' : 'success-message'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <p>Ingrese su correo electrónico para buscar su cuenta.</p>
            <input type="email" name="email" id="email" placeholder="Correo electrónico" value="<?php echo $email_recuperacion; ?>" required>
            <button type="submit">Enviar Instrucciones</button>
        </form>
        
        <a href="index.php">Volver al Inicio de Sesión</a>
    </div>
</body>
</html>