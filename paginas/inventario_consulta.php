<?php
include("../recursos/header.php"); // Incluye tu cabecera HTML y CSS (asegúrate de que incluya Bootstrap JS y CSS)
include("../conexion/conex.php"); // Incluye tu archivo de conexión a la BD, que ahora define $conex

// --- Lógica para procesar el formulario de registro de entrada (dentro del modal) ---
if (isset($_POST['registrar_entrada'])) {
    $id_producto = $_POST['id_producto'];
    $id_ubicacion = $_POST['id_ubicacion'];
    $cantidad_entrada = $_POST['cantidad'];
    // $fecha_vencimiento = !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : NULL; // Aceptar NULL si está vacío

    // Validar datos básicos
    if (empty($id_producto) || empty($id_ubicacion) || !is_numeric($cantidad_entrada) || $cantidad_entrada <= 0) {
        echo '<script>alert("Por favor, complete todos los campos y asegúrese de que la cantidad sea un número positivo.");</script>';
    } else {
        // Asegúrate de usar $conex aquí, que es la variable de conexión de tu archivo incluido.
        mysqli_begin_transaction($conex); 
        $success = true;
        $message = "";

        try {
            // 1. Verificar si la combinación producto-ubicación ya existe en producto_ubicacion
            $check_query = "SELECT cantidad FROM producto_ubicacion WHERE ID_Producto = ? AND ID_Ubicacion = ?";
            // Usamos $conex en mysqli_prepare
            $stmt_check = mysqli_prepare($conex, $check_query); 
            mysqli_stmt_bind_param($stmt_check, "ii", $id_producto, $id_ubicacion);
            mysqli_stmt_execute($stmt_check);
            mysqli_stmt_bind_result($stmt_check, $current_cantidad_en_ubicacion);
            mysqli_stmt_fetch($stmt_check);
            mysqli_stmt_close($stmt_check);

            if ($current_cantidad_en_ubicacion !== null) { // Si ya existe la combinación
                $new_cantidad_en_ubicacion = $current_cantidad_en_ubicacion + $cantidad_entrada;
                $update_pu_query = "UPDATE producto_ubicacion SET cantidad = ? WHERE ID_Producto = ? AND ID_Ubicacion = ?";
                // Usamos $conex en mysqli_prepare
                $stmt_pu = mysqli_prepare($conex, $update_pu_query); 
                mysqli_stmt_bind_param($stmt_pu, "iii", $new_cantidad_en_ubicacion, $id_producto, $id_ubicacion);
                if (!mysqli_stmt_execute($stmt_pu)) {
                    $success = false;
                    $message = "Error al actualizar la cantidad en la ubicación.";
                }
                mysqli_stmt_close($stmt_pu);
            } else { // Si la combinación no existe, insertar
                $insert_pu_query = "INSERT INTO producto_ubicacion (ID_Producto, ID_Ubicacion, cantidad) VALUES (?, ?, ? )";
                // Usamos $conex en mysqli_prepare
                $stmt_pu = mysqli_prepare($conex, $insert_pu_query); 
                mysqli_stmt_bind_param($stmt_pu, "iis", $id_producto, $id_ubicacion, $cantidad_entrada);
                if (!mysqli_stmt_execute($stmt_pu)) {
                    $success = false;
                    $message = "Error al insertar nueva entrada en la ubicación.";
                }
                mysqli_stmt_close($stmt_pu);
            }

            // 2. Actualizar el stock_actual total en la tabla prductos
            if ($success) {
                $update_p_query = "UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?";
                // Usamos $conex en mysqli_prepare
                $stmt_p = mysqli_prepare($conex, $update_p_query); 
                mysqli_stmt_bind_param($stmt_p, "ii", $cantidad_entrada, $id_producto);
                if (!mysqli_stmt_execute($stmt_p)) {
                    $success = false;
                    $message = "Error al actualizar el stock total del producto.";
                }
                mysqli_stmt_close($stmt_p);
            }

            // 3. Confirmar o revertir la transacción
            if ($success) {
                // Usamos $conex en mysqli_commit
                mysqli_commit($conex); 
                echo '<script>alert("Entrada de stock registrada correctamente."); window.location.href = "inventario_consulta.php";</script>';
            } else {
                // Usamos $conex en mysqli_rollback
                mysqli_rollback($conex); 
                echo '<script>alert("' . $message . ' Error al registrar la entrada de stock. Por favor, intente de nuevo.");</script>';
            }

        } catch (Exception $e) {
            // Usamos $conex en mysqli_rollback
            mysqli_rollback($conex); 
            echo '<script>alert("Error inesperado: ' . $e->getMessage() . '");</script>';
        }
    }
}

// --- Obtener productos y ubicaciones para los selectores del modal ---
$productos = [];
$query_productos = "SELECT id, nombre_producto FROM productos ORDER BY nombre_producto";
// Usamos $conex en mysqli_query
$result_productos = mysqli_query($conex, $query_productos); 
if ($result_productos) {
    while ($row = mysqli_fetch_assoc($result_productos)) {
        $productos[] = $row;
    }
} else {
    // Usamos $conex en mysqli_error
    die("Error al cargar productos para el modal: " . mysqli_error($conex)); 
}

$ubicaciones = [];
$query_ubicaciones = "SELECT id_ubicacion, descripcion_ubicacion FROM ubicacion ORDER BY descripcion_ubicacion";
// Usamos $conex en mysqli_query
$result_ubicaciones = mysqli_query($conex, $query_ubicaciones); 
if ($result_ubicaciones) {
    while ($row = mysqli_fetch_assoc($result_ubicaciones)) {
        $ubicaciones[] = $row;
    }
} else {
    // Usamos $conex en mysqli_error
    die("Error al cargar ubicaciones para el modal: " . mysqli_error($conex)); 
}

?>

<header class="d-flex justify-content-between align-items-center p-3 bg-purple">
    <h2 class="text-white">Inventario Actual por Ubicación</h2>
    <a href="../paginas/inicio.php" class="btn btn-success"><i class="bi bi-arrow-left"></i> Regresar a Inicio</a>
</header>

<main class="container mt-4">
    <div class="row">
        <div class="col-12">
            <div class="mb-3">
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#entradaStockModal">
                    <i class="bi bi-box-arrow-in-right"></i> Registrar Entrada de Stock
                </button>
            </div>

            <h4>Detalle del Stock en Ubicaciones</h4>
            <table class="table table-striped table-bordered" id="tablaInventario">
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Ubicación</th>
                        <th scope="col">Cantidad</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $query_inventario = "
                        SELECT
                            p.nombre_producto,
                            u.descripcion_ubicacion AS nombre_ubicacion,
                            pu.cantidad
                        FROM
                            producto_ubicacion pu
                        JOIN
                            productos p ON pu.ID_Producto = p.id
                        JOIN
                            ubicacion u ON pu.ID_Ubicacion = u.id_ubicacion
                        ORDER BY
                            p.nombre_producto, u.descripcion_ubicacion;
                    ";

                    // Usamos $conex en mysqli_query
                    $resultado_inventario = mysqli_query($conex, $query_inventario); 

                    if (!$resultado_inventario) {
                        // Usamos $conex en mysqli_error
                        die("Error al consultar el inventario: " . mysqli_error($conex)); 
                    }

                    if (mysqli_num_rows($resultado_inventario) > 0) {
                        while ($row = mysqli_fetch_assoc($resultado_inventario)) {
                            ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($row['nombre_ubicacion']); ?></td>
                                <td><?php echo htmlspecialchars($row['cantidad']); ?></td>
                            </tr>
                            <?php
                        }
                    } else {
                        echo '<tr><td colspan="4" class="text-center">No hay productos registrados en el inventario por ubicación.</td></tr>';
                    }
                    ?>
                </tbody>
            </table>
        </div>
    </div>
</main>

<div class="modal fade" id="entradaStockModal" tabindex="-1" aria-labelledby="entradaStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="entradaStockModalLabel">Registrar Nueva Entrada de Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="inventario_consulta.php" method="POST"> <div class="mb-3">
                        <label for="modal_id_producto" class="form-label">Producto:</label>
                        <select class="form-select" id="modal_id_producto" name="id_producto" required>
                            <option value="">-- Seleccione un Producto --</option>
                            <?php foreach ($productos as $producto): ?>
                                <option value="<?php echo htmlspecialchars($producto['id']); ?>"> 
                                    <?php echo htmlspecialchars($producto['nombre_producto']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_id_ubicacion" class="form-label">Ubicación:</label>
                        <select class="form-select" id="modal_id_ubicacion" name="id_ubicacion" required>
                            <option value="">-- Seleccione una Ubicación --</option>
                            <?php foreach ($ubicaciones as $ubicacion): ?>
                                <option value="<?php echo htmlspecialchars($ubicacion['id_ubicacion']); ?>">
                                    <?php echo htmlspecialchars($ubicacion['descripcion_ubicacion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_cantidad" class="form-label">Cantidad a Ingresar:</label>
                        <input type="number" class="form-control" id="modal_cantidad" name="cantidad" min="1" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-success" name="registrar_entrada">Registrar Entrada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php include("../recursos/footer.php"); // Incluye tu pie de página HTML y scripts ?>