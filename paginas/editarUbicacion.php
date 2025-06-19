<?php
// Habilitar la visualización de errores para depuración (QUÍTALA EN PRODUCCIÓN)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include("../recursos/header.php"); // Incluye tu cabecera HTML y CSS
include("../conexion/conex.php"); // Incluye tu archivo de conexión a la BD

$id_ubicacion_editar = null; // Variable para almacenar el ID de la ubicación a editar
$ubicacion = null; // Variable para almacenar los datos de la ubicación

// --- 1. PROCESAR EL FORMULARIO DE EDICIÓN (cuando se envía) ---
// Se verifica si el formulario fue enviado (botón con name="actualizar_ubicacion" presionado)
if (isset($_POST['actualizar_ubicacion'])) {
    $id_ubicacion_actualizar = $_POST['id_ubicacion']; // El ID de la ubicación a actualizar (oculto en el formulario)
    $nueva_descripcion = $_POST['descripcion_ubicacion'];

    // Validar datos básicos
    // Comprueba que el ID y la nueva descripción no estén vacíos.
    if (empty($id_ubicacion_actualizar) || empty($nueva_descripcion)) {
        echo '<script>alert("Por favor, complete todos los campos."); window.history.back();</script>';
        exit(); // Detiene la ejecución del script
    }

    // Preparar la consulta UPDATE
    // Se utiliza una sentencia preparada para seguridad (previene inyección SQL)
    $query = "UPDATE ubicacion SET descripcion_ubicacion = ? WHERE id_ubicacion = ?";
    $stmt = mysqli_prepare($conn, $query);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt) {
        // Enlazar los parámetros a la consulta preparada
        // "s" para string (descripción), "i" para integer (ID de ubicación)
        mysqli_stmt_bind_param($stmt, "si", $nueva_descripcion, $id_ubicacion_actualizar);

        // Ejecutar la consulta
        if (mysqli_stmt_execute($stmt)) {
            // Si la actualización fue exitosa, mostrar mensaje y redirigir
            echo '<script>alert("Ubicación actualizada correctamente."); window.location.href = "ubicacion.php";</script>';
            exit(); // Detiene la ejecución del script
        } else {
            // Si hubo un error en la ejecución, mostrarlo
            echo '<script>alert("Error al actualizar la ubicación: ' . addslashes(mysqli_error($conn)) . '"); window.history.back();</script>';
            exit();
        }
        mysqli_stmt_close($stmt); // Cerrar la sentencia preparada
    } else {
        // Si la preparación de la consulta falló, mostrar error
        echo '<script>alert("Error al preparar la consulta de actualización: ' . addslashes(mysqli_error($conn)) . '"); window.history.back();</script>';
        exit();
    }
}

// --- 2. CARGAR LOS DATOS DE LA UBICACIÓN (cuando se accede a la página con un ID) ---
// Se verifica si se recibió un ID en la URL a través del método GET
if (isset($_GET['id'])) {
    $id_ubicacion_editar = $_GET['id'];

    // Consulta para seleccionar la descripción de la ubicación por su ID
    $query = "SELECT id_ubicacion, descripcion_ubicacion FROM ubicacion WHERE id_ubicacion = ?";
    $stmt = mysqli_prepare($conn, $query);

    // Verificar si la preparación de la consulta fue exitosa
    if ($stmt) {
        // Enlazar el parámetro ID a la consulta preparada
        mysqli_stmt_bind_param($stmt, "i", $id_ubicacion_editar);
        mysqli_stmt_execute($stmt);
        // Obtener el resultado de la consulta
        $result = mysqli_stmt_get_result($stmt);

        // Verificar si se encontró exactamente una ubicación con ese ID
        if (mysqli_num_rows($result) == 1) {
            $ubicacion = mysqli_fetch_assoc($result); // Obtener los datos como un array asociativo
        } else {
            // Si no se encuentra la ubicación, mostrar mensaje y redirigir
            echo '<script>alert("Ubicación no encontrada."); window.location.href = "ubicacion.php";</script>';
            exit();
        }
        mysqli_stmt_close($stmt); // Cerrar la sentencia preparada
    } else {
        // Si la preparación de la consulta falló, mostrar error
        echo '<script>alert("Error al preparar la consulta de selección: ' . addslashes(mysqli_error($conn)) . '"); window.location.href = "ubicacion.php";</script>';
        exit();
    }
} else {
    // Si no se proporcionó un ID en la URL, mostrar mensaje y redirigir a la lista de ubicaciones
    echo '<script>alert("No se especificó una ubicación para editar."); window.location.href = "ubicacion.php";</script>';
    exit();
}

// Esta verificación final es una red de seguridad, aunque los 'exit()' anteriores deberían manejarlo.
if (!$ubicacion) {
    echo '<script>alert("Error inesperado al cargar la ubicación."); window.location.href = "ubicacion.php";</script>';
    exit();
}
?>

<header class="d-flex justify-content-between align-items-center p-3 bg-purple text-white">
    <h2>Editar Ubicación</h2>
    <a href="ubicacion.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver a Ubicaciones</a>
</header>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <form action="editarUbicacion.php" method="POST">
                    <input type="hidden" name="id_ubicacion" value="<?php echo htmlspecialchars($ubicacion['id_ubicacion']); ?>">

                    <div class="mb-3">
                        <label for="descripcion_ubicacion" class="form-label">Descripción de la Ubicación:</label>
                        <input type="text" class="form-control" id="descripcion_ubicacion" name="descripcion_ubicacion" value="<?php echo htmlspecialchars($ubicacion['descripcion_ubicacion']); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-info bg-purple" name="actualizar_ubicacion">Actualizar Ubicación</button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include("../recursos/footer.php"); // Incluye tu pie de página HTML y scripts ?>