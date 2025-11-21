<?php
// Incluye la conexión a la base de datos
include('conexion/conex.php'); 

$mensaje = '';
$token = $_GET['token'] ?? ''; // Obtiene el token de la URL
$user_id = 0; // Inicializa el ID del usuario

// --- Lógica de Validación del Token ---
if (empty($token)) {
    $mensaje = "❌ Token de recuperación faltante o enlace inválido.";
} else {
    $token_seguro = mysqli_real_escape_string($conn, $token);
    $now = date("Y-m-d H:i:s");
    
    // 1. Buscar usuario por token y verificar expiración
    $query = "SELECT id FROM usuarios WHERE reset_token = '{$token_seguro}' AND reset_expira > '{$now}' LIMIT 1";
    $result = mysqli_query($conn, $query);

    if (mysqli_num_rows($result) === 0) {
        $mensaje = "❌ El enlace de recuperación es inválido o ha expirado. Por favor, solicite uno nuevo.";
        $token = ''; // Anula el token para ocultar el formulario
    } else {
        $user_data = mysqli_fetch_assoc($result);
        $user_id = $user_data['id'];

        // --- Lógica para Procesar la Nueva Contraseña ---
        if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['password'])) {
            $nueva_pass = $_POST['password'];
            $confirm_pass = $_POST['confirm_password'];

            if ($nueva_pass !== $confirm_pass) {
                $mensaje = "❌ Las contraseñas no coinciden.";
            } elseif (strlen($nueva_pass) < 6) {
                $mensaje = "❌ La contraseña debe tener al menos 6 caracteres.";
            } else {
                // ** CRÍTICO: Cifrar la nueva contraseña **
                $pass_hashed = password_hash($nueva_pass, PASSWORD_DEFAULT);
                
                // 2. Actualizar la contraseña y limpiar los campos del token
                $update_pass_query = "UPDATE usuarios SET clave = ?, reset_token = NULL, reset_expira = NULL WHERE id = ?";
                $stmt = $conn->prepare($update_pass_query);
                $stmt->bind_param("si", $pass_hashed, $user_id);
                
                if ($stmt->execute()) {
                    $mensaje = "✅ ¡Contraseña restablecida con éxito! Ya puedes iniciar sesión.";
                    $token = ''; // Ocultar el formulario después del éxito
                } else {
                    $mensaje = "❌ Error al actualizar la contraseña en la base de datos.";
                }
                $stmt->close();
            }
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Restablecer Contraseña</title>
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
        input[type="password"] { width: 100%; padding: 12px; margin-bottom: 20px; border: 1px solid #ccc; border-radius: 8px; box-sizing: border-box; }
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
        <h2>Restablecer Contraseña</h2>
        
        <?php if (!empty($mensaje)): ?>
            <div class="<?php echo (strpos($mensaje, '❌') !== false) ? 'error-message' : 'success-message'; ?>">
                <?php echo htmlspecialchars($mensaje); ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($token) && strpos($mensaje, '❌') === false): // Muestra el formulario solo si el token es válido ?>
            <form method="POST">
                <p>Por favor, ingrese su nueva contraseña.</p>
                <input type="password" name="password" placeholder="Nueva Contraseña" required>
                <input type="password" name="confirm_password" placeholder="Confirmar Contraseña" required>
                
                <button type="submit">Cambiar Contraseña</button>
            </form>
        <?php endif; ?>

        <a href="index.php">Volver al Inicio de Sesión</a>
    </div>
</body>
</html>