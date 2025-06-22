<?php
// Asegúrate de que las rutas a tus archivos de recursos y conexión sean correctas
include("../recursos/header.php"); // Incluye tu cabecera HTML y CSS
include("../conexion/conex.php"); // Incluye tu archivo de conexión a la BD

$id_producto_editar = null; // Variable para almacenar el ID del producto a editar
$producto = null; // Variable para almacenar los datos del producto

// --- 1. PROCESAR EL FORMULARIO DE EDICIÓN (cuando se envía) ---
// CAMBIO IMPORTANTE AQUÍ: name="actualizar_productos" ahora coincide con el HTML
if (isset($_POST['actualizar_productos'])) {
    $id_producto_actualizar = $_POST['id']; // El ID del producto a actualizar (oculto en el formulario)
    $nuevo_nombre = $_POST['nombre_producto'];
    $nuevo_stock_actual = $_POST['stock_actual'];
    $precio_venta_actual = $_POST['precio_venta']; // Precio de venta

    // Validar datos básicos
    if (empty($id_producto_actualizar) || empty($nuevo_nombre) || !is_numeric($nuevo_stock_actual) || $nuevo_stock_actual < 0 || !is_numeric($precio_venta_actual) || $precio_venta_actual < 0) {
        echo '<script>alert("Por favor, complete todos los campos y asegúrese de que el stock y el precio sean números válidos y no negativos."); window.history.back();</script>';
        exit();
    }

    // Preparar la consulta UPDATE
    // CAMBIO IMPORTANTE AQUÍ: Corrección en la sentencia SQL y los tipos de bind_param
    // Asegúrate de que los nombres de las columnas (nombre_producto, stock_actual, precio_venta)
    // coincidan exactamente con los de tu tabla 'productos'.
    $query = "UPDATE productos SET nombre_producto = ?, stock_actual = ?, precio_venta = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // 's' para string (nombre_producto)
        // 'i' para integer (stock_actual)
        // 'd' para double/decimal (precio_venta) - Si en tu BD es FLOAT o DECIMAL
        // 'i' para integer (id)
        mysqli_stmt_bind_param($stmt, "sdis", $nuevo_nombre, $nuevo_stock_actual, $precio_venta_actual, $id_producto_actualizar);

        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Producto actualizado correctamente."); window.location.href = "productos.php";</script>';
            exit();
        } else {
            // Se usa mysqli_error($conn) para obtener el error específico de la base de datos
            echo '<script>alert("Error al actualizar el producto: ' . addslashes(mysqli_error($conn)) . '"); window.history.back();</script>';
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Error al preparar la consulta de actualización: ' . addslashes(mysqli_error($conn)) . '"); window.history.back();</script>';
        exit();
    }
}

// --- 2. CARGAR LOS DATOS DEL PRODUCTO (cuando se accede a la página con un ID) ---
// Esto se ejecuta cuando haces clic en "Editar" desde la lista de productos
if (isset($_GET['id'])) {
    $id_producto_editar = $_GET['id'];

    $query = "SELECT id, nombre_producto, stock_actual, precio_venta FROM productos WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_producto_editar);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $producto = mysqli_fetch_assoc($result);
        } else {
            // Si no se encuentra el producto, redirigir
            echo '<script>alert("Producto no encontrado."); window.location.href = "productos.php";</script>';
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Error al preparar la consulta de selección: ' . addslashes(mysqli_error($conn)) . '"); window.location.href = "productos.php";</script>';
        exit();
    }
} else {
    // Si no se proporcionó un ID al cargar la página (por ejemplo, alguien escribe la URL directamente sin ID), redirigir.
    // Este es el mensaje que veías antes.
    echo '<script>alert("No se especificó un producto para editar."); window.location.href = "productos.php";</script>';
    exit();
}

// Si $producto es null aquí, significa que no se encontró el producto o hubo un error antes.
// La lógica anterior ya debería haber redirigido en esos casos, pero es una buena verificación de seguridad.
if (!$producto) {
    echo '<script>alert("Error inesperado al cargar el producto. Vuelva a intentarlo."); window.location.href = "productos.php";</script>';
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .bg-purple {
            background-color: #6f42c1; /* Un color morado de ejemplo */
        }
        .text-white {
            color: #fff;
        }
    </style>
</head>
<body>

<header class="d-flex justify-content-between align-items-center p-3 bg-purple text-white">
    <h2>Editar Producto</h2>
    <a href="productos.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver a Productos</a>
</header>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <form action="editarProducto.php" method="POST">
                    <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto['id']); ?>">

                    <div class="mb-3">
                        <label for="nombre_producto" class="form-label">Nombre del Producto:</label>
                        <input type="text" class="form-control" id="nombre_producto" name="nombre_producto" value="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="stock_actual" class="form-label">Stock Actual:</label>
                        <input type="number" class="form-control" id="stock_actual" name="stock_actual" value="<?php echo htmlspecialchars($producto['stock_actual']); ?>" min="0" required>
                    </div>

                    <div class="mb-3">
                        <label for="precio_venta" class="form-label">Precio de Venta:</label>
                        <input type="number" class="form-control" id="precio_venta" name="precio_venta" value="<?php echo htmlspecialchars($producto['precio_venta']); ?>" min="0" step="0.01" required>
                    </div>

                    <button type="submit" class="btn btn-primary bg-purple" name="actualizar_productos">Actualizar Producto</button>
                </form>
            </div>
        </div>
    </div>
</main>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>

