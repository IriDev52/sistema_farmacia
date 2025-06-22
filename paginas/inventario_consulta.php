<?php
include("../recursos/header.php"); // Asegúrate de que este header incluya Bootstrap 5 CSS y JS
include("../conexion/conex.php"); // Tu archivo de conexión a la BD

session_start(); // Inicia la sesión para mensajes de feedback

// --- Lógica para procesar el formulario de registro de entrada (dentro del modal) ---
if (isset($_POST['registrar_entrada'])) {
    $id_producto = (int)$_POST['id_producto'];
    $id_ubicacion = (int)$_POST['id_ubicacion'];
    $cantidad_entrada = (int)$_POST['cantidad'];

    // Validar datos básicos
    if (empty($id_producto) || empty($id_ubicacion) || $cantidad_entrada <= 0) {
        $_SESSION['message'] = "Por favor, complete todos los campos y asegúrese de que la cantidad sea un número positivo.";
        $_SESSION['message_type'] = "danger";
        header("Location: inventario_consulta.php");
        exit();
    }

    // Iniciar transacción para asegurar la integridad de los datos
    mysqli_begin_transaction($conn);
    $success = true;
    $message = "";

    try {
        // 1. Verificar si la combinación producto-ubicación ya existe en producto_ubicacion
        $check_query = "SELECT cantidad FROM producto_ubicacion WHERE ID_Producto = ? AND ID_Ubicacion = ?";
        $stmt_check = mysqli_prepare($conn, $check_query);
        mysqli_stmt_bind_param($stmt_check, "ii", $id_producto, $id_ubicacion);
        mysqli_stmt_execute($stmt_check);
        mysqli_stmt_bind_result($stmt_check, $current_cantidad_en_ubicacion);
        mysqli_stmt_fetch($stmt_check);
        mysqli_stmt_close($stmt_check);

        if ($current_cantidad_en_ubicacion !== null) { // Si ya existe la combinación, actualizar
            $new_cantidad_en_ubicacion = $current_cantidad_en_ubicacion + $cantidad_entrada;
            $update_pu_query = "UPDATE producto_ubicacion SET cantidad = ? WHERE ID_Producto = ? AND ID_Ubicacion = ?";
            $stmt_pu = mysqli_prepare($conn, $update_pu_query);
            mysqli_stmt_bind_param($stmt_pu, "iii", $new_cantidad_en_ubicacion, $id_producto, $id_ubicacion);
            if (!mysqli_stmt_execute($stmt_pu)) {
                $success = false;
                $message = "Error al actualizar la cantidad en la ubicación: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_pu);
        } else { // Si la combinación no existe, insertar
            $insert_pu_query = "INSERT INTO producto_ubicacion (ID_Producto, ID_Ubicacion, cantidad) VALUES (?, ?, ?)";
            $stmt_pu = mysqli_prepare($conn, $insert_pu_query);
            mysqli_stmt_bind_param($stmt_pu, "iii", $id_producto, $id_ubicacion, $cantidad_entrada); // "iii" para tres enteros
            if (!mysqli_stmt_execute($stmt_pu)) {
                $success = false;
                $message = "Error al insertar nueva entrada en la ubicación: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_pu);
        }

        // 2. Actualizar el stock_actual total en la tabla 'productos'
        // NOTA: Tu tabla se llama 'prductos' en tu código. Asumo que es un error tipográfico y debería ser 'productos'.
        // Si no es así, por favor, ajusta 'productos' a 'prductos' en esta línea y en el resto del script.
        if ($success) {
            $update_p_query = "UPDATE productos SET stock_actual = stock_actual + ? WHERE id = ?";
            $stmt_p = mysqli_prepare($conn, $update_p_query);
            mysqli_stmt_bind_param($stmt_p, "ii", $cantidad_entrada, $id_producto);
            if (!mysqli_stmt_execute($stmt_p)) {
                $success = false;
                $message = "Error al actualizar el stock total del producto: " . mysqli_error($conn);
            }
            mysqli_stmt_close($stmt_p);
        }

        // 3. Confirmar o revertir la transacción
        if ($success) {
            mysqli_commit($conn);
            $_SESSION['message'] = "Entrada de stock registrada correctamente.";
            $_SESSION['message_type'] = "success";
        } else {
            mysqli_rollback($conn);
            $_SESSION['message'] = $message . " Error al registrar la entrada de stock. Por favor, intente de nuevo.";
            $_SESSION['message_type'] = "danger";
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $_SESSION['message'] = "Error inesperado: " . $e->getMessage();
        $_SESSION['message_type'] = "danger";
    }
    header("Location: inventario_consulta.php");
    exit();
}

// --- Obtener productos y ubicaciones para los selectores del modal ---
$productos = [];
$query_productos = "SELECT id, nombre_producto, stock_actual, stock_minimo FROM productos ORDER BY nombre_producto"; // Añadido stock_minimo
$result_productos = mysqli_query($conn, $query_productos);
if ($result_productos) {
    while ($row = mysqli_fetch_assoc($result_productos)) {
        $productos[] = $row;
    }
} else {
    // Manejo de errores más amigable para el usuario final
    $_SESSION['message'] = "Error al cargar productos para el formulario: " . mysqli_error($conn);
    $_SESSION['message_type'] = "danger";
    $productos = []; // Asegura que la variable esté definida aunque haya un error
}

$ubicaciones = [];
// Asumo que tu tabla de ubicaciones se llama 'ubicacion' y tiene 'id_ubicacion' y 'descripcion_ubicacion'
$query_ubicaciones = "SELECT id_ubicacion, descripcion_ubicacion FROM ubicacion ORDER BY descripcion_ubicacion";
$result_ubicaciones = mysqli_query($conn, $query_ubicaciones);
if ($result_ubicaciones) {
    while ($row = mysqli_fetch_assoc($result_ubicaciones)) {
        $ubicaciones[] = $row;
    }
} else {
    $_SESSION['message'] = "Error al cargar ubicaciones para el formulario: " . mysqli_error($conn);
    $_SESSION['message_type'] = "danger";
    $ubicaciones = []; // Asegura que la variable esté definida aunque haya un error
}

// --- Lógica para obtener el resumen del inventario ---
$total_productos = count($productos);
$total_ubicaciones = count($ubicaciones);

$total_stock_actual = 0;
$query_total_stock = "SELECT SUM(stock_actual) AS total_stock FROM productos";
$result_total_stock = mysqli_query($conn, $query_total_stock);
if ($result_total_stock && mysqli_num_rows($result_total_stock) > 0) {
    $row_total_stock = mysqli_fetch_assoc($result_total_stock);
    $total_stock_actual = $row_total_stock['total_stock'] ?? 0;
}
?>

<header class="d-flex justify-content-between align-items-center p-3 bg-primary text-white">
    <h2 class="mb-0"><i class="bi bi-box-seam"></i> Gestión de Inventario</h2>
    <a href="../paginas/inicio.php" class="btn btn-outline-light"><i class="bi bi-arrow-left-circle"></i> Regresar a Inicio</a>
</header>

<main class="container mt-4">
    <?php
    // Mostrar mensajes de sesión
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show" role="alert">';
        echo $_SESSION['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card text-center bg-info text-white shadow-sm">
                <div class="card-body">
                    <i class="bi bi-boxes fs-1"></i>
                    <h5 class="card-title mt-2">Productos Registrados</h5>
                    <p class="card-text fs-3"><?php echo $total_productos; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-warning text-dark shadow-sm">
                <div class="card-body">
                    <i class="bi bi-geo-alt fs-1"></i>
                    <h5 class="card-title mt-2">Ubicaciones Activas</h5>
                    <p class="card-text fs-3"><?php echo $total_ubicaciones; ?></p>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card text-center bg-success text-white shadow-sm">
                <div class="card-body">
                    <i class="bi bi-box-seam-fill fs-1"></i>
                    <h5 class="card-title mt-2">Stock Total General</h5>
                    <p class="card-text fs-3"><?php echo $total_stock_actual; ?></p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h4>Detalle del Stock en Ubicaciones</h4>
                <button type="button" class="btn btn-success" data-bs-toggle="modal" data-bs-target="#entradaStockModal">
                    <i class="bi bi-box-arrow-in-right"></i> Registrar Entrada de Stock
                </button>
            </div>

            <div class="table-responsive">
                <table class="table table-striped table-bordered table-hover" id="tablaInventario">
                    <thead class="table-dark">
                        <tr>
                            <th scope="col">ID Producto</th> <th scope="col">Producto</th>
                            <th scope="col">Ubicación</th>
                            <th scope="col">Cantidad en Ubicación</th>
                            <th scope="col">Stock Total Producto</th>
                            <th scope="col">Stock Mínimo</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Consulta para obtener el inventario detallado por ubicación
                        $query_inventario = "
                            SELECT
                                p.id AS producto_id,
                                p.nombre_producto,
                                p.stock_actual AS total_stock_producto,
                                p.stock_minimo,
                                u.descripcion_ubicacion AS nombre_ubicacion,
                                pu.cantidad AS cantidad_en_ubicacion
                            FROM
                                producto_ubicacion pu
                            JOIN
                                productos p ON pu.ID_Producto = p.id
                            JOIN
                                ubicacion u ON pu.ID_Ubicacion = u.id_ubicacion
                            ORDER BY
                                p.nombre_producto, u.descripcion_ubicacion;
                        ";

                        $resultado_inventario = mysqli_query($conn, $query_inventario);

                        if ($resultado_inventario) {
                            if (mysqli_num_rows($resultado_inventario) > 0) {
                                while ($row = mysqli_fetch_assoc($resultado_inventario)) {
                                    $estado_stock = '';
                                    $clase_estado = '';
                                    $porcentaje_stock = ($row['stock_minimo'] > 0) ? round(($row['total_stock_producto'] / $row['stock_minimo']) * 100) : 100;

                                    if ($row['total_stock_producto'] <= $row['stock_minimo']) {
                                        $estado_stock = 'Bajo';
                                        $clase_estado = 'text-danger fw-bold';
                                        $barra_clase = 'bg-danger';
                                    } elseif ($row['total_stock_producto'] > $row['stock_minimo'] && $row['total_stock_producto'] <= ($row['stock_minimo'] * 1.5)) { // Por ejemplo, 50% por encima
                                        $estado_stock = 'Medio';
                                        $clase_estado = 'text-warning fw-bold';
                                        $barra_clase = 'bg-warning';
                                    } else {
                                        $estado_stock = 'Óptimo';
                                        $clase_estado = 'text-success fw-bold';
                                        $barra_clase = 'bg-success';
                                    }
                                    ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($row['producto_id']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                                        <td><?php echo htmlspecialchars($row['nombre_ubicacion']); ?></td>
                                        <td><?php echo htmlspecialchars($row['cantidad_en_ubicacion']); ?></td>
                                        <td><?php echo htmlspecialchars($row['total_stock_producto']); ?></td>
                                        <td><?php echo htmlspecialchars($row['stock_minimo']); ?></td>
                                        <td class="<?php echo $clase_estado; ?>">
                                            <?php echo $estado_stock; ?>
                                            <div class="progress mt-1" style="height: 10px;">
                                                <div class="progress-bar <?php echo $barra_clase; ?>" role="progressbar" style="width: <?php echo min(100, $porcentaje_stock); ?>%;" aria-valuenow="<?php echo $porcentaje_stock; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                            </div>
                                        </td>
                                        <td>
                                            <button class="btn btn-sm btn-info text-white" title="Mover Stock" onclick="abrirModalMoverStock(<?php echo $row['producto_id']; ?>, <?php echo $row['cantidad_en_ubicacion']; ?>)"><i class="bi bi-arrows-move"></i></button>
                                            <button class="btn btn-sm btn-danger" title="Registrar Salida" onclick="abrirModalSalidaStock(<?php echo $row['producto_id']; ?>, <?php echo $row['cantidad_en_ubicacion']; ?>)"><i class="bi bi-box-arrow-left"></i></button>
                                        </td>
                                    </tr>
                                    <?php
                                }
                            } else {
                                echo '<tr><td colspan="8" class="text-center">No hay productos registrados en el inventario por ubicación.</td></tr>';
                            }
                        } else {
                            echo '<tr><td colspan="8" class="text-center text-danger">Error al consultar el inventario: ' . mysqli_error($conn) . '</td></tr>';
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="entradaStockModal" tabindex="-1" aria-labelledby="entradaStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="entradaStockModalLabel"><i class="bi bi-box-arrow-in-right"></i> Registrar Nueva Entrada de Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="inventario_consulta.php" method="POST">
                    <div class="mb-3">
                        <label for="modal_id_producto" class="form-label">Producto:</label>
                        <select class="form-select" id="modal_id_producto" name="id_producto" required>
                            <option value="">-- Seleccione un Producto --</option>
                            <?php foreach ($productos as $producto) : ?>
                                <option value="<?php echo htmlspecialchars($producto['id']); ?>">
                                    <?php echo htmlspecialchars($producto['nombre_producto']); ?> (Stock Total: <?php echo htmlspecialchars($producto['stock_actual']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="modal_id_ubicacion" class="form-label">Ubicación:</label>
                        <select class="form-select" id="modal_id_ubicacion" name="id_ubicacion" required>
                            <option value="">-- Seleccione una Ubicación --</option>
                            <?php foreach ($ubicaciones as $ubicacion) : ?>
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
                        <button type="submit" class="btn btn-success" name="registrar_entrada"><i class="bi bi-plus-circle"></i> Registrar Entrada</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="salidaStockModal" tabindex="-1" aria-labelledby="salidaStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="salidaStockModalLabel"><i class="bi bi-box-arrow-left"></i> Registrar Salida de Stock</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="inventario_consulta.php" method="POST">
                    <input type="hidden" name="action" value="registrar_salida">
                    <input type="hidden" name="salida_producto_id" id="salida_producto_id">

                    <div class="mb-3">
                        <label for="salida_nombre_producto" class="form-label">Producto:</label>
                        <input type="text" class="form-control" id="salida_nombre_producto" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="salida_id_ubicacion" class="form-label">Ubicación de Salida:</label>
                        <select class="form-select" id="salida_id_ubicacion" name="salida_id_ubicacion" required>
                            <option value="">-- Seleccione una Ubicación --</option>
                            <?php foreach ($ubicaciones as $ubicacion) : ?>
                                <option value="<?php echo htmlspecialchars($ubicacion['id_ubicacion']); ?>">
                                    <?php echo htmlspecialchars($ubicacion['descripcion_ubicacion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="salida_cantidad" class="form-label">Cantidad a Retirar:</label>
                        <input type="number" class="form-control" id="salida_cantidad" name="salida_cantidad" min="1" required>
                        <small class="form-text text-muted" id="salida_stock_disponible"></small>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-danger" name="registrar_salida_btn"><i class="bi bi-dash-circle"></i> Registrar Salida</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="moverStockModal" tabindex="-1" aria-labelledby="moverStockModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="moverStockModalLabel"><i class="bi bi-arrows-move"></i> Mover Stock entre Ubicaciones</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="inventario_consulta.php" method="POST">
                    <input type="hidden" name="action" value="mover_stock">
                    <input type="hidden" name="mover_producto_id" id="mover_producto_id">

                    <div class="mb-3">
                        <label for="mover_nombre_producto" class="form-label">Producto:</label>
                        <input type="text" class="form-control" id="mover_nombre_producto" readonly>
                    </div>

                    <div class="mb-3">
                        <label for="mover_ubicacion_origen" class="form-label">Desde Ubicación:</label>
                        <select class="form-select" id="mover_ubicacion_origen" name="mover_ubicacion_origen" required>
                            <option value="">-- Seleccione Ubicación Origen --</option>
                            </select>
                        <small class="form-text text-muted" id="mover_stock_origen_disponible"></small>
                    </div>

                    <div class="mb-3">
                        <label for="mover_ubicacion_destino" class="form-label">Hacia Ubicación:</label>
                        <select class="form-select" id="mover_ubicacion_destino" name="mover_ubicacion_destino" required>
                            <option value="">-- Seleccione Ubicación Destino --</option>
                            <?php foreach ($ubicaciones as $ubicacion) : ?>
                                <option value="<?php echo htmlspecialchars($ubicacion['id_ubicacion']); ?>">
                                    <?php echo htmlspecialchars($ubicacion['descripcion_ubicacion']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="mover_cantidad" class="form-label">Cantidad a Mover:</label>
                        <input type="number" class="form-control" id="mover_cantidad" name="mover_cantidad" min="1" required>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                        <button type="submit" class="btn btn-info text-white" name="mover_stock_btn"><i class="bi bi-truck"></i> Mover Stock</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php
// Cierra la conexión a la base de datos al final del script.
mysqli_close($conn);
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<script>
    $(document).ready(function() {
        // Inicializa DataTables
        $('#tablaInventario').DataTable({
            language: {
                url: '//cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json' // Idioma español
            },
            paging: true,      // Habilita la paginación
            searching: true,   // Habilita la búsqueda
            ordering: true,    // Habilita el ordenamiento de columnas
            info: true         // Muestra información de paginación
        });
    });

    // Función para abrir el modal de Salida de Stock
    function abrirModalSalidaStock(productoId, cantidadActual) {
        // Puedes hacer una llamada AJAX aquí para obtener el nombre del producto
        // y las ubicaciones con stock para ese producto si no quieres depender solo de la tabla visible.
        // Por simplicidad, aquí solo pasamos el ID del producto y la cantidad total.
        $('#salida_producto_id').val(productoId);
        // Aquí deberías obtener el nombre del producto de tu lista de productos cargada
        // Ejemplo (necesitarías una estructura de datos más accesible para productos en JS):
        // const productName = 'Nombre del Producto (ID: ' + productoId + ')';
        // $('#salida_nombre_producto').val(productName);

        // Para poblar el nombre del producto y las ubicaciones con stock disponible:
        $.ajax({
            url: 'get_product_data.php', // Un nuevo archivo PHP que crearemos
            type: 'GET',
            data: { product_id: productoId },
            dataType: 'json',
            success: function(data) {
                $('#salida_nombre_producto').val(data.nombre_producto);
                $('#salida_stock_disponible').text('Stock disponible total: ' + data.stock_actual);

                // Llenar el selector de ubicaciones con stock para este producto
                let ubicacionesOptions = '<option value="">-- Seleccione una Ubicación --</option>';
                data.ubicaciones_con_stock.forEach(function(ubic) {
                    ubicacionesOptions += `<option value="${ubic.ID_Ubicacion}">${ubic.descripcion_ubicacion} (Cantidad: ${ubic.cantidad})</option>`;
                });
                $('#salida_id_ubicacion').html(ubicacionesOptions);

                // Actualizar la cantidad máxima para el input de salida al seleccionar una ubicación
                $('#salida_id_ubicacion').on('change', function() {
                    const selectedUbicacionId = $(this).val();
                    const selectedUbicacion = data.ubicaciones_con_stock.find(ubic => ubic.ID_Ubicacion == selectedUbicacionId);
                    if (selectedUbicacion) {
                        $('#salida_cantidad').attr('max', selectedUbicacion.cantidad);
                        $('#salida_stock_disponible').text('Stock disponible en esta ubicación: ' + selectedUbicacion.cantidad);
                    } else {
                        $('#salida_cantidad').removeAttr('max');
                        $('#salida_stock_disponible').text('Stock disponible total: ' + data.stock_actual);
                    }
                });

                // Mostrar el modal
                var salidaStockModal = new bootstrap.Modal(document.getElementById('salidaStockModal'));
                salidaStockModal.show();
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener datos del producto:", status, error);
                alert("No se pudo cargar la información del producto. Intente de nuevo.");
            }
        });
    }

    // Función para abrir el modal de Mover Stock
    function abrirModalMoverStock(productoId, cantidadActual) {
        $('#mover_producto_id').val(productoId);

        $.ajax({
            url: 'get_product_data.php', // Reutilizamos el mismo archivo PHP
            type: 'GET',
            data: { product_id: productoId },
            dataType: 'json',
            success: function(data) {
                $('#mover_nombre_producto').val(data.nombre_producto);

                // Llenar el selector de ubicación de origen
                let ubicacionesOrigenOptions = '<option value="">-- Seleccione Ubicación Origen --</option>';
                data.ubicaciones_con_stock.forEach(function(ubic) {
                    ubicacionesOrigenOptions += `<option value="${ubic.ID_Ubicacion}">${ubic.descripcion_ubicacion} (Cantidad: ${ubic.cantidad})</option>`;
                });
                $('#mover_ubicacion_origen').html(ubicacionesOrigenOptions);

                // Actualizar la cantidad máxima para el input de mover al seleccionar una ubicación de origen
                $('#mover_ubicacion_origen').on('change', function() {
                    const selectedUbicacionId = $(this).val();
                    const selectedUbicacion = data.ubicaciones_con_stock.find(ubic => ubic.ID_Ubicacion == selectedUbicacionId);
                    if (selectedUbicacion) {
                        $('#mover_cantidad').attr('max', selectedUbicacion.cantidad);
                        $('#mover_stock_origen_disponible').text('Stock disponible en origen: ' + selectedUbicacion.cantidad);
                    } else {
                        $('#mover_cantidad').removeAttr('max');
                        $('#mover_stock_origen_disponible').text('');
                    }
                    // Deshabilitar la ubicación de origen en el selector de destino
                    $('#mover_ubicacion_destino option').prop('disabled', false);
                    if (selectedUbicacionId) {
                        $('#mover_ubicacion_destino option[value="' + selectedUbicacionId + '"]').prop('disabled', true);
                    }
                });

                // Mostrar el modal
                var moverStockModal = new bootstrap.Modal(document.getElementById('moverStockModal'));
                moverStockModal.show();
            },
            error: function(xhr, status, error) {
                console.error("Error al obtener datos del producto para mover:", status, error);
                alert("No se pudo cargar la información para mover stock. Intente de nuevo.");
            }
        });
    }
</script>