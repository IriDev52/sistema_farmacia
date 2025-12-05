<?php
// procesar_login.php

// 1. Iniciar la sesión
session_start();

// 2. Incluir la conexión a la base de datos (contiene $conn)
include("../conexion/conex.php"); 

// Función para manejar redirecciones y mensajes (la misma que usamos en el registro)
function redirigirConMensaje($mensaje, $tipo = 'error', $destino = 'ecomerce.php') {
    $_SESSION['mensaje_tipo'] = $tipo;
    $_SESSION['mensaje_texto'] = $mensaje;
    header("Location: $destino");
    exit();
}

// 3. Verificar método de solicitud y conexión
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigirConMensaje("Método de solicitud no válido.", 'error', 'ecomerce.php');
}

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// 4. Obtener y sanear los datos de login
$cedula = htmlspecialchars(trim($_POST['cedula']));
$clave_ingresada = $_POST['clave']; // Mantener la contraseña sin sanear para la verificación

// 5. Validaciones básicas
if (empty($cedula) || empty($clave_ingresada)) {
    redirigirConMensaje("Por favor, ingresa tu cédula y contraseña.", 'warning', 'ecomerce.php');
}

// 6. Preparar la Sentencia SQL para buscar al usuario por cédula
// Solo necesitamos la cédula y la clave_hash
$sql = "SELECT id, cedula, clave FROM usuarios_client WHERE cedula = ?";

// Usar Sentencias Preparadas
if ($stmt = $conn->prepare($sql)) {
    
    // Vincular el parámetro de la cédula (1 string)
    $stmt->bind_param("s", $cedula);

    // 7. Ejecutar la sentencia
    $stmt->execute();
    
    // Obtener el resultado
    $resultado = $stmt->get_result();
    
    // 8. Verificar si se encontró al usuario
    if ($resultado->num_rows === 1) {
        
        $usuario = $resultado->fetch_assoc();
        $clave_hash_almacenada = $usuario['clave'];
        
        // 9. VERIFICACIÓN CRÍTICA: Comparar la contraseña ingresada con el hash almacenado
        if (password_verify($clave_ingresada, $clave_hash_almacenada)) {
            
            // Éxito: Contraseña correcta. Iniciar sesión.
            
            // Regenerar el ID de sesión para prevenir ataques de Fijación de Sesión (Session Fixation)
            session_regenerate_id(true); 

            // Almacenar información esencial en la sesión
            $_SESSION['usuario_id'] = $usuario['id'];
            $_SESSION['usuario_cedula'] = $usuario['cedula'];
            $_SESSION['logeado'] = true;

            // Redirigir al usuario al ecomerce o a una página de bienvenida
            redirigirConMensaje("¡Bienvenido de nuevo!", 'success', 'ecomerce.php');
            
        } else {
            // Falla: Contraseña incorrecta
            redirigirConMensaje("Cédula o contraseña incorrectas.", 'error', 'ecomerce.php');
        }
        
    } else {
        // Falla: Usuario no encontrado
        redirigirConMensaje("Cédula o contraseña incorrectas.", 'error', 'ecomerce.php');
    }

    // 10. Cerrar la sentencia
    $stmt->close();

} else {
    // Error al preparar la sentencia
    error_log("Error al preparar la consulta: " . $conn->error);
    redirigirConMensaje("Error interno del servidor. Por favor, contacta a soporte.", 'error', 'ecomerce.php');
}

// 11. Cerrar la conexión
$conn->close();

?>