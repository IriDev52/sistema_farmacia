<?php
include("../recursos/header.php");
include("../conexion/conex.php");

session_start();

if (isset($_POST['registrar_producto'])) {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
    $laboratorio = mysqli_real_escape_string($conn, $_POST['laboratorio']);

    $cantidad = (int)$_POST['cantidad'];
    if ($cantidad < 0) {
        $_SESSION['message'] = "La cantidad no puede ser negativa.";
        $_SESSION['message_type'] = "danger";
        header("Location: productos.php");
        exit();
    }

    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    if (!strtotime($fecha_vencimiento)) {
        $_SESSION['message'] = "Formato de fecha de vencimiento inválido.";
        $_SESSION['message_type'] = "danger";
        header("Location: productos.php");
        exit();
    }

    $requiere_refrigeracion = strtolower(mysqli_real_escape_string($conn, $_POST['requiere_refrigeracion']));
    if (!in_array($requiere_refrigeracion, ['si', 'no'])) {
        $_SESSION['message'] = "El campo 'Requiere refrigeración' debe ser 'Sí' o 'No'.";
        $_SESSION['message_type'] = "danger";
        header("Location: productos.php");
        exit();
    }

    $precio_venta = (float)$_POST['precio_venta'];
    if ($precio_venta < 0) {
        $_SESSION['message'] = "El precio de venta no puede ser negativo.";
        $_SESSION['message_type'] = "danger";
        header("Location: productos.php");
        exit();
    }

    // Asegúrate de que el nombre del campo POST coincida con el 'name' en tu HTML
    $ubicacion = mysqli_real_escape_string($conn, $_POST['ubicacion_producto']); // CAMBIO: Usar 'ubicacion_producto'

    // CAMBIO: Quitar 'id' de la lista de columnas porque es autoincremental
    // CAMBIO: Asegurarse de que la columna 'ubicacion' o 'ubicacion_texto' exista en tu DB
    $query = "INSERT INTO `productos`(`nombre_producto`, `descripcion`, `laboratorio_fabrica`, `stock_actual`, `fecha_vencimiento`, `requiere_refrigeracion`, `precio_venta`, `ubicacion`) VALUES(?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query); // CAMBIO: Mover la preparación de la consulta aquí

    if ($stmt) {
        // CAMBIO: El tipo de parámetro para 'ubicacion' debe ser 's' (string)
        mysqli_stmt_bind_param($stmt, "sssissss", $nombre, $descripcion, $laboratorio, $cantidad, $fecha_vencimiento, $requiere_refrigeracion, $precio_venta, $ubicacion);
        
        $result = mysqli_stmt_execute($stmt); // CAMBIO: Ejecutar la sentencia preparada

        if ($result) { // CAMBIO: Verificar el resultado de la ejecución de la sentencia preparada
            $_SESSION['message'] = "Producto registrado exitosamente.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error al registrar el producto: " . mysqli_error($conn);
            $_SESSION['message_type'] = "danger";
        }
        mysqli_stmt_close($stmt);
    } else {
        $_SESSION['message'] = "Error en la preparación de la consulta: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }

    header("Location: productos.php");
    exit();
}
?>

<header class="d-flex justify-content-between align-items-center p-3 bg-purple">
    <h2 class="text-white">Gestión de Productos</h2>
    <a href="../paginas/inicio.php" class="btn btn-light "><i class="bi bi-arrow-left"></i> Regresar a Inicio</a>
</header>

<main class="d-flex justify-content-center align-items-center flex-column">

    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show mt-3" role="alert">';
        echo $_SESSION['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <div class="p-2 d-flex flex-column col-5 border border-1 border-secondary mx-4 mt-4 mb-4 rounded-2 ">
        <h4 class="text-center mb-3">Registrar Nuevo Producto</h4>
        <form action="productos.php" method="POST" class="d-flex flex-column p-2">
            <label class="fw-semibold" for="nombre">Nombre del producto</label>
            <input class="form-control mb-2" type="text" name="nombre" id="nombre" required>

            <label class="fw-semibold" for="descripcion">Descripción</label>
            <textarea class="form-control mb-2" name="descripcion" id="descripcion" rows="3"></textarea>

            <label class="fw-semibold" for="laboratorio">Laboratorio/Fábrica</label>
            <input class="form-control mb-2" type="text" name="laboratorio" id="laboratorio">

            <label class="fw-semibold" for="cantidad">Cantidad</label>
            <input class="form-control mb-2" type="number" name="cantidad" id="cantidad" min="0" required>

            <label class="fw-semibold" for="fecha_vencimiento">Fecha de vencimiento</label>
            <input class="form-control mb-2" type="date" name="fecha_vencimiento" id="fecha_vencimiento" required>

            <label class="fw-semibold" for="requiere_refrigeracion">Requiere refrigeración</label>
            <select class="form-select mb-2" name="requiere_refrigeracion" id="requiere_refrigeracion" required>
                <option value="">Seleccione...</option>
                <option value="si">Sí</option>
                <option value="no">No</option>
            </select>

            <label class="fw-semibold" for="precio_venta">Precio de venta</label>
            <input class="form-control mb-2" type="number" step="0.01" name="precio_venta" id="precio_venta" min="0" required>

            <label class="fw-semibold" for="ubicacion_producto">Ubicación del Producto</label>
            <input class="form-control mb-3" type="text" name="ubicacion_producto" id="ubicacion_producto" placeholder="Ej: Almacén Principal, Estante B2" required>

            <button type="submit" class="btn btn-secondary bg-purple" name="registrar_producto">Registrar Producto</button>
        </form>
    </div>

    <div class="mx-4 w-75">
        <h4 class="mb-3">Productos Registrados</h4>
        <table class="table table-striped table-bordered" id="productos">
            <thead class="table-dark">
                <tr>
                    <th scope="col">Nombre</th>
                    <th scope="col">Descripción</th>
                    <th scope="col">Laboratorio/Fábrica</th>
                    <th scope="col">Cantidad</th>
                    <th scope="col">Fecha Vencimiento</th>
                    <th scope="col">Refrigeración</th>
                    <th scope="col">Precio Venta</th>
                    <th scope="col">Ubicación</th>
                    <th scope="col">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php
                // CAMBIO: Seleccionar la columna 'ubicacion' que almacena el texto
                $query = "SELECT * FROM productos ORDER BY nombre_producto ASC"; 
                $resultado_productos = mysqli_query($conn, $query);

                if ($resultado_productos) { // CAMBIO: Verificar si la consulta de selección fue exitosa
                    while ($row = mysqli_fetch_array($resultado_productos)) { ?>
                        <tr>
                            <td><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                            <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                            <td><?php echo htmlspecialchars($row['laboratorio_fabrica']); ?></td>
                            <td><?php echo htmlspecialchars($row['stock_actual']); ?></td>
                            <td><?php echo htmlspecialchars($row['fecha_vencimiento']); ?></td>
                            <td><?php echo htmlspecialchars(ucfirst($row['requiere_refrigeracion'])); ?></td>
                            <td><?php echo htmlspecialchars(number_format($row['precio_venta'], 2, ',', '.')); ?></td>
                            <td><?php echo htmlspecialchars($row['ubicacion'] ?? 'N/A'); ?></td> <td class="d-flex ">
                                <a href="editarProducto.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="d-flex m-1 text-decoration-none btn btn-secondary"><i class="bi bi-pencil-fill"></i></a>
                                <a href="eliminarProducto.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este producto?');">
                                    <i class="bi bi-trash"></i>
                                </a>
                            </td>
                        </tr>
                    <?php }
                } else { // CAMBIO: El mensaje de error solo se muestra si la consulta de selección falla
                    echo '<tr><td colspan="9" class="text-center text-danger">Error al cargar los productos: ' . mysqli_error($conn) . '</td></tr>';
                }
                ?>
            </tbody>
        </table>
    </div>
</main>

<?php
mysqli_close($conn);
?>