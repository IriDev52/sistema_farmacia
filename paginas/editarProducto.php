<?php
include("../recursos/header.php"); // Incluye tu cabecera HTML y CSS
include("../conexion/conex.php"); // Incluye tu archivo de conexión a la BD

$id_producto_editar = null; // Variable para almacenar el ID del producto a editar
$producto = null; // Variable para almacenar los datos del producto

// --- 1. PROCESAR EL FORMULARIO DE EDICIÓN (cuando se envía) ---
if (isset($_POST['actualizar_productos'])) {
    $id_producto_actualizar = $_POST['id']; // El ID del producto a actualizar (oculto en el formulario)
    $nuevo_nombre = $_POST['nombre_producto'];
    $nuevo_stock_actual = $_POST['stock_actual']; // Asumo que también se puede editar el stock inicial/actual
    // Agrega aquí cualquier otro campo que quieras editar (descripción, precio, etc.)

    // Validar datos básicos
    if (empty($id_producto_actualizar) || empty($nuevo_nombre) || !is_numeric($nuevo_stock_actual) || $nuevo_stock_actual < 0) {
        echo '<script>alert("Por favor, complete todos los campos y asegúrese de que el stock sea un número válido y no negativo."); window.history.back();</script>';
        exit();
    }

    // Preparar la consulta UPDATE
    $query = "UPDATE productos SET nombre_producto = ?, stock_actual = ? WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        // "si" para string, "i" para integer, "i" para integer (para el ID)
        mysqli_stmt_bind_param($stmt, "sii", $nuevo_nombre, $nuevo_stock_actual, $id_producto_actualizar);

        if (mysqli_stmt_execute($stmt)) {
            echo '<script>alert("Producto actualizado correctamente."); window.location.href = "productos.php";</script>';
            exit();
        } else {
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
if (isset($_GET['id'])) {
    $id_producto_editar = $_GET['id'];

    $query = "SELECT id, nombre_producto, stock_actual FROM productos WHERE id = ?";
    $stmt = mysqli_prepare($conn, $query);

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "i", $id_producto_editar);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if (mysqli_num_rows($result) == 1) {
            $producto= mysqli_fetch_assoc($result);
        } else {
            echo '<script>alert("Producto no encontrado."); window.location.href = "productos.php";</script>';
            exit();
        }
        mysqli_stmt_close($stmt);
    } else {
        echo '<script>alert("Error al preparar la consulta de selección: ' . addslashes(mysqli_error($conn)) . '"); window.location.href = "productos.php";</script>';
        exit();
    }
} else {
    // Si no se proporcionó un ID, redirigir a la lista de productos
    echo '<script>alert("No se especificó un producto para editar."); window.location.href = "productos.php";</script>';
    exit();
}

// Si $producto es null aquí, significa que no se encontró el producto o hubo un error.
// La lógica anterior ya debería haber redirigido en esos casos, pero es una buena verificación.
if (!$producto) {
    echo '<script>alert("Error inesperado al cargar el producto."); window.location.href = "productos.php";</script>';
    exit();
}
?>

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

                    <button type="submit" class="btn btn-primary bg-purple" name="actualizar_producto">Actualizar Producto</button>
                </form>
            </div>
        </div>
    </div>
</main>

<?php include("../recursos/footer.php"); // Incluye tu pie de página HTML y scripts ?>