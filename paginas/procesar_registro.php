<?php
// procesar_registro.php

// 1. Iniciar la sesión (útil si quieres redirigir al usuario logeado inmediatamente)
session_start();

// 2. Incluir la conexión a la base de datos (contiene $conn)
include("../conexion/conex.php"); 

// Función para manejar redirecciones y mensajes de error
function redirigirConMensaje($mensaje, $tipo = 'error', $destino = 'ecomerce.php') {
    // Usamos variables de sesión temporales para mostrar mensajes (ej: con SweetAlert2 o Bootstrap Alert)
    $_SESSION['mensaje_tipo'] = $tipo;
    $_SESSION['mensaje_texto'] = $mensaje;
    
    // Redirigir al destino (típicamente de vuelta al formulario o a la página principal)
    header("Location: $destino");
    exit();
}

// 3. Verificar si la solicitud es por POST y si hay conexióny
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    redirigirConMensaje("Método de solicitud no válido.", 'error', 'ecomerce.php');
}

if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// 4. Obtener y sanear los datos del formulario
// Importante: mysqli_real_escape_string no es estrictamente necesario si usamos sentencias preparadas,
// pero htmlspecialchars es crucial para prevenir XSS si los datos se reintroducen en el HTML.

$nombre_completo = htmlspecialchars(trim($_POST['nombre_completo']));
$cedula          = htmlspecialchars(trim($_POST['cedula']));
$email           = htmlspecialchars(trim($_POST['email']));
$clave           = $_POST['clave']; // La contraseña se manejará aparte
$telefono        = htmlspecialchars(trim($_POST['telefono']));
$direccion       = htmlspecialchars(trim($_POST['direccion']));

// 5. Validaciones básicas de PHP (puedes añadir más validación JS en el frontend)
if (empty($nombre_completo) || empty($cedula) || empty($email) || empty($clave)) {
    redirigirConMensaje("Todos los campos obligatorios deben ser llenados.", 'warning', 'ecomerce.php');
}

// Validación de formato de email simple
if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    redirigirConMensaje("El formato del correo electrónico no es válido.", 'warning', 'ecomerce.php');
}

// 6. Cifrar la Contraseña de forma SEGURA
// Usa PASSWORD_DEFAULT que siempre apunta al mejor algoritmo de hashing disponible (actualmente Argon2 o bcrypt)
$clave_hash = password_hash($clave, PASSWORD_DEFAULT);

// 7. Preparar la Sentencia SQL para la Inserción
$sql = "INSERT INTO usuarios_client (nombre_completo, cedula, email, clave, telefono, direccion) 
        VALUES (?, ?, ?, ?, ?, ?)";

// Usar Sentencias Preparadas para prevenir Inyección SQL
if ($stmt = $conn->prepare($sql)) {
    
    // Vincular parámetros: 'ssssss' define los tipos de datos (6 strings)
    $stmt->bind_param("ssssss", $nombre_completo, $cedula, $email, $clave_hash, $telefono, $direccion);

    // 8. Ejecutar la sentencia
    if ($stmt->execute()) {
        
        // Registro exitoso: Mostrar mensaje de éxito y redirigir
        
        // Opcional: Iniciar la sesión del usuario inmediatamente después del registro
        // $_SESSION['usuario_id'] = $stmt->insert_id; 
        // $_SESSION['usuario_cedula'] = $cedula;

        redirigirConMensaje("¡Registro exitoso! Ya puedes iniciar sesión.", 'success', 'ecomerce.php');
        
    } else {
        // Error de ejecución: Posiblemente cédula o email duplicado (UNIQUE constraint)
        
        // 9. Manejo de Errores Específicos (para CEDULA o EMAIL duplicados)
        if ($conn->errno == 1062) { // 1062 es el código de error para entrada duplicada
            redirigirConMensaje("Error: La cédula o el email ya están registrados.", 'error', 'ecomerce.php');
        } else {
            // Error general de base de datos
            error_log("Error de inserción en DB: " . $stmt->error); // Registra el error real en el log
            redirigirConMensaje("Ocurrió un error al intentar registrar el usuario. Por favor, inténtalo de nuevo.", 'error', 'ecomerce.php');
        }
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