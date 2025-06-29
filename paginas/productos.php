<?php
include("../recursos/header.php");
include("../conexion/conex.php");

session_start();

if (isset($_POST['registrar_producto'])) {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
    $laboratorio = mysqli_real_escape_string($conn, $_POST['laboratorio']);
    $cantidad = (int)$_POST['cantidad'];
    $stock_minimo = (int)$_POST['stock_minimo'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $requiere_refrigeracion = strtolower(mysqli_real_escape_string($conn, $_POST['requiere_refrigeracion']));
    $precio_venta = (float)$_POST['precio_venta'];
    $ubicacion = mysqli_real_escape_string($conn, $_POST['ubicacion_produ']);
    $estado = 'Activo';

    if (empty($nombre) || empty($laboratorio) || $cantidad <= 0 || empty($fecha_vencimiento) || empty($ubicacion) || $precio_venta <= 0) {
        $_SESSION['message'] = "Error: Por favor, complete todos los campos obligatorios.";
        $_SESSION['message_type'] = "danger";
    } else {
        $query = "INSERT INTO productos (nombre_producto, descripcion, laboratorio_fabrica, stock_actual, stock_minimo, fecha_vencimiento, requiere_refrigeracion, precio_venta, ubicacion, estado) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssiisssss", $nombre, $descripcion, $laboratorio, $cantidad, $stock_minimo, $fecha_vencimiento, $requiere_refrigeracion, $precio_venta, $ubicacion, $estado);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Producto '{$nombre}' registrado exitosamente.";
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
    }
    header("Location: productos.php");
    exit();
}

if (isset($_POST['desactivar_producto'])) {
    $id = (int)$_POST['desactivar_id'];

    $update_query = "UPDATE productos SET estado = 'inactivo' WHERE id = ?";
    $stmt = mysqli_prepare($conn, $update_query);
    mysqli_stmt_bind_param($stmt, "i", $id);

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['message'] = "Producto desactivado correctamente. Ya no aparecerá en el inventario activo.";
        $_SESSION['message_type'] = "success";
    } else {
        $_SESSION['message'] = "Error al desactivar el producto: " . mysqli_error($conn);
        $_SESSION['message_type'] = "danger";
    }
    mysqli_stmt_close($stmt);
    header("Location: productos.php");
    exit();
}

$productos = [];
$query = "SELECT * FROM productos ORDER BY estado DESC, nombre_producto ASC";
$resultado = mysqli_query($conn, $query);

if ($resultado && mysqli_num_rows($resultado) > 0) {
    while ($fila = mysqli_fetch_assoc($resultado)) {
        $productos[] = $fila;
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Productos - Farmacia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">
    <style>
        :root {
            --primary-visual: #4a69bd;
            --secondary-visual: #88c0d0;
            --success-visual: #2ecc71;
            --danger-visual: #e74c3c;
            --light-bg-visual: #f4f4f4;
            --text-dark-visual: #2c3e50;
            --text-light-visual: #ffffff;
            --border-light-visual: #dbe6fd;
            --card-bg-visual: #ffffff;
            --shadow-subtle-visual: 0 2px 10px rgba(0, 0, 0, 0.08);
            --border-radius-lg-visual: 0.5rem;
            --border-radius-sm-visual: 0.3rem;
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--light-bg-visual);
            color: var(--text-dark-visual);
            line-height: 1.6;
            padding-bottom: 30px;
        }
        .fade-in {
            animation: fadeIn 0.5s ease-out;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .header-bar {
            background-color: var(--primary-visual);
            color: var(--text-light-visual);
            padding: 1.5rem 3rem;
            border-bottom: 4px solid #3d56b2;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .header-bar h2 {
            font-weight: 700;
            letter-spacing: -0.5px;
            margin: 0;
            font-size: 1.8rem;
        }
        .header-bar h2 i {
            margin-right: 0.5rem;
            font-size: 1.5em;
        }
        .btn-outline-light-styled {
            color: var(--text-light-visual);
            border: 2px solid rgba(255, 255, 255, 0.8);
            font-weight: 500;
            padding: 0.6rem 1.5rem;
            border-radius: var(--border-radius-sm-visual);
            transition: all 0.2s ease;
            font-size: 0.9rem;
        }
        .btn-outline-light-styled:hover {
            background-color: var(--text-light-visual);
            color: var(--primary-visual);
            border-color: var(--text-light-visual);
        }
        .main-container {
            padding: 2rem 3rem;
        }
        .card {
            border: 1px solid var(--border-light-visual);
            border-radius: var(--border-radius-lg-visual);
            background-color: var(--secondary-visual);
            box-shadow: var(--shadow-subtle-visual);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            overflow: hidden;
            margin-bottom: 1.5rem;
        }
        .card:hover {
            transform: translateY(-3px);
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
        }
        .card-header-styled {
            background-color: var(--primary-visual);
            color: var(--text-light-visual);
            padding: 1rem 1.5rem;
            font-weight: 600;
            font-size: 1.2rem;
            border-bottom: 3px solid #3d56b2;
            border-top-left-radius: var(--border-radius-lg-visual);
            border-top-right-radius: var(--border-radius-lg-visual);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .card-header-styled button.btn {
            font-size: 0.9rem;
            padding: 0.4rem 0.8rem;
        }
        .alert {
            border-left: 5px solid;
            font-weight: 500;
            border-radius: var(--border-radius-sm-visual);
            padding: 1rem;
            margin-bottom: 1rem;
        }
        .alert-success { background-color: #d1f2e4; border-color: var(--success-visual); color: #1e8449; }
        .alert-danger { background-color: #fdecea; border-color: var(--danger-visual); color: #b03a2e; }
        .form-control, .form-select {
            border-radius: var(--border-radius-sm-visual);
            padding: 0.7rem;
            border: 1px solid #bdc3c7;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-visual);
            box-shadow: 0 0 0 0.2rem rgba(74, 105, 189, 0.25);
        }
        .table {
            border-collapse: collapse;
            width: 100%;
            border-radius: var(--border-radius-lg-visual);
            overflow: hidden;
            box-shadow: var(--shadow-subtle-visual);
            margin-bottom: 1rem;
        }
        .table thead th {
            background-color: #e9ecef;
            color: var(--text-dark-visual);
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.3px;
            font-size: 0.85rem;
            padding: 0.8rem;
            text-align: left;
        }
        .table tbody td {
            padding: 0.8rem;
            font-size: 0.9rem;
            border-bottom: 1px solid var(--border-light-visual);
            background-color: var(--card-bg-visual);
        }
        .table tbody tr:nth-child(even) td {
            background-color: #f8f9fa;
        }
        .table tbody tr:last-child td {
            border-bottom: none;
        }
        .btn-action {
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            justify-content: center;
            align-items: center;
            color: var(--text-light-visual);
            font-size: 0.8rem;
            transition: transform 0.15s ease-in-out;
            margin: 0 0.15rem;
        }
        .btn-action:hover {
            transform: scale(1.1);
        }
        .btn-info.btn-action {
            background-color: var(--primary-visual);
        }
        .btn-danger.btn-action {
            background-color: var(--danger-visual);
        }
        .estado-activo {
            color: var(--success-visual);
            font-weight: bold;
        }
        .estado-inactivo {
            color: var(--danger-visual);
            font-weight: bold;
        }
        .modal-header {
            background-color: var(--primary-visual);
            color: var(--text-light-visual);
            border-bottom: 2px solid #3d56b2;
        }
        .modal-title {
            font-weight: 600;
        }
        .modal-footer button.btn-secondary {
            background-color: #6c757d;
            border-color: #6c757d;
            color: var(--text-light-visual);
        }
        .modal-footer button.btn-secondary:hover {
            background-color: #5a6268;
            border-color: #5a6268;
        }
        .btn-primary {
            background-color: var(--primary-visual);
            border-color: var(--primary-visual);
        }
        .btn-primary:hover {
            background-color: #3d56b2;
            border-color: #3d56b2;
        }
    </style>
</head>
<body class="fade-in">

<header class="header-bar d-flex justify-content-between align-items-center">
    <h2 class="mb-0"><i class="bi bi-capsule-pill"></i>Gestión de Productos</h2>
    <a href="../paginas/inicio.php" class="btn btn-outline-light-styled"><i class="bi bi-arrow-left-circle me-2"></i>Regresar a Inicio</a>
</header>

<main class="main-container container-fluid mt-3">
    <?php
    if (isset($_SESSION['message'])) {
        echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show mb-3 py-2" role="alert">';
        echo $_SESSION['message'];
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
        echo '</div>';
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
    }
    ?>

    <div class="card fade-in">
        <div class="card-header-styled">
            <h4 class="mb-0"><i class="bi bi-list-ul me-2"></i>Lista de Productos</h4>
            <button type="button" class="btn btn-sm btn-light" data-bs-toggle="modal" data-bs-target="#registroProductoModal">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Producto
            </button>
        </div>
        <div class="card-body p-3">
            <div class="table-responsive">
                <table class="table table-bordered table-hover responsive" id="productosTable" width="100%">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nombre</th>
                            <th>Laboratorio</th>
                            <th>Stock</th>
                            <th>Fecha Venc.</th>
                            <th>Precio (Bs.)</th>
                            <th>Ubicación</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto) : ?>
                            <tr>
                                <td><?php echo htmlspecialchars($producto['id']); ?></td>
                                <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($producto['laboratorio_fabrica']); ?></td>
                                <td><?php echo htmlspecialchars($producto['stock_actual']); ?></td>
                                <td><?php echo htmlspecialchars($producto['fecha_vencimiento']); ?></td>
                                <td><?php echo number_format($producto['precio_venta'], 2); ?></td>
                                <td><?php echo htmlspecialchars($producto['ubicacion']); ?></td>
                                <td><span class="<?php echo ($producto['estado'] == 'Activo') ? 'estado-activo' : 'estado-inactivo'; ?>"><?php echo htmlspecialchars($producto['estado']); ?></span></td>
                                <td class="text-center">
                                    <a href="editar_producto.php?id=<?php echo $producto['id']; ?>" class="btn btn-info btn-action me-1" data-bs-toggle="tooltip" title="Editar">
                                        <i class="bi bi-pencil"></i>
                                    </a>
                                    <button class="btn btn-danger btn-action btn-desactivar" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>" data-bs-toggle="tooltip" title="Desactivar">
                                        <i class="bi bi-archive"></i>
                                    </button>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</main>

<div class="modal fade" id="registroProductoModal" tabindex="-1" aria-labelledby="registroProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header">
                <h5 class="modal-title" id="registroProductoModalLabel"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="productos.php" method="POST">
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label for="nombre" class="form-label fw-bold">Nombre</label>
                            <input type="text" class="form-control" id="nombre" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label for="laboratorio" class="form-label fw-bold">Laboratorio</label>
                            <input type="text" class="form-control" id="laboratorio" name="laboratorio" required>
                        </div>
                        <div class="col-12">
                            <label for="descripcion" class="form-label fw-bold">Descripción (Opcional)</label>
                            <textarea class="form-control" id="descripcion" name="descripcion" rows="2"></textarea>
                        </div>
                        <div class="col-md-4">
                            <label for="cantidad" class="form-label fw-bold">Cantidad Inicial</label>
                            <input type="number" class="form-control" id="cantidad" name="cantidad" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label for="stock_minimo" class="form-label fw-bold">Stock Mínimo</label>
                            <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" min="0" required>
                        </div>
                        <div class="col-md-4">
                            <label for="fecha_vencimiento" class="form-label fw-bold">Vencimiento</label>
                            <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" required>
                        </div>
                        <div class="col-md-6">
                            <label for="precio_venta" class="form-label fw-bold">Precio (Bs.)</label>
                            <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" min="0.01" required>
                        </div>
                        <div class="col-md-6">
                            <label for="ubicacion_produ" class="form-label fw-bold">Ubicación</label>
                            <select class="form-select" id="ubicacion_produ" name="ubicacion_produ" required>
                                <option value="">Seleccione un estante</option>
                                <option value="Estante A1">Estante A1</option>
                                <option value="Estante A2">Estante A2</option>
                                <option value="Estante B1">Estante B1</option>
                                <option value="Estante B2">Estante B2</option>
                                <option value="Estante C1">Estante C1</option>
                                <option value="Estante C2">Estante C2</option>
                            </select>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">¿Requiere Refrigeración?</label>
                            <div class="d-flex gap-3">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="requiere_refrigeracion" id="refrigeracion_si" value="si" required>
                                    <label class="form-check-label" for="refrigeracion_si">Sí</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="requiere_refrigeracion" id="refrigeracion_no" value="no" required>
                                    <label class="form-check-label" for="refrigeracion_no">No</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-sm" name="registrar_producto">Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="eliminarProductoModal" tabindex="-1" aria-labelledby="eliminarProductoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm">
        <div class="modal-content rounded-3 shadow">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="eliminarProductoModalLabel"><i class="bi bi-exclamation-triangle-fill me-2"></i>Confirmar Desactivación</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="productos.php" method="POST">
                <div class="modal-body p-3 text-center">
                    <input type="hidden" name="desactivar_id" id="desactivar_id">
                    <p class="mb-0">¿Seguro que desea desactivar el producto <strong id="producto_a_desactivar"></strong>?</p>
                    <small class="text-muted">Esta acción ocultará el producto del inventario activo.</small>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">No, Cancelar</button>
                    <button type="submit" class="btn btn-danger btn-sm" name="desactivar_producto">Sí, Desactivar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php
mysqli_close($conn);
?>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#productosTable').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json',
            },
            responsive: true
        });

        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        });

        $('#productosTable tbody').on('click', '.btn-desactivar', function () {
            var id_producto = $(this).data('id');
            var nombre_producto = $(this).data('nombre');

            $('#desactivar_id').val(id_producto);
            $('#producto_a_desactivar').text(nombre_producto);

            var desactivarModal = new bootstrap.Modal(document.getElementById('eliminarProductoModal'));
            desactivarModal.show();
        });
    });
</script>
</body>
</html>