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

    // --- LÓGICA DE IMAGEN ---
    $nombre_imagen = "";
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ruta_destino = "../img/";
        $nombre_imagen = time() . "_" . basename($_FILES['imagen']['name']);
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino . $nombre_imagen);
    }

    if (empty($nombre) || empty($laboratorio) || $cantidad <= 0 || empty($fecha_vencimiento) || empty($ubicacion) || $precio_venta <= 0) {
        $_SESSION['message'] = "Error: Por favor, complete todos los campos obligatorios.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Añadida la columna 'imagen' y un parámetro 's' más al final
        $query = "INSERT INTO productos (nombre_producto, descripcion, laboratorio_fabrica, stock_actual, stock_minimo, fecha_vencimiento, requiere_refrigeracion, precio_venta, ubicacion, estado, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssiissssss", $nombre, $descripcion, $laboratorio, $cantidad, $stock_minimo, $fecha_vencimiento, $requiere_refrigeracion, $precio_venta, $ubicacion, $estado, $nombre_imagen);
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Producto '{$nombre}' registrado exitosamente.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error al registrar el producto: " . mysqli_error($conn);
                $_SESSION['message_type'] = "danger";
            }
            mysqli_stmt_close($stmt);
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
        $_SESSION['message'] = "Producto desactivado correctamente.";
        $_SESSION['message_type'] = "success";
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
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">
    <style>
        :root {
            --primary-visual: #4a69bd; --secondary-visual: #88c0d0; --success-visual: #2ecc71;
            --danger-visual: #e74c3c; --light-bg-visual: #f4f4f4; --text-dark-visual: #2c3e50;
            --text-light-visual: #ffffff; --border-light-visual: #dbe6fd; --card-bg-visual: #ffffff;
            --shadow-subtle-visual: 0 2px 10px rgba(0, 0, 0, 0.08); --border-radius-lg-visual: 0.5rem;
            --border-radius-sm-visual: 0.3rem;
        }
        body { font-family: 'Inter', sans-serif; background-color: var(--light-bg-visual); color: var(--text-dark-visual); padding-bottom: 30px; }
        .header-bar { background-color: var(--primary-visual); color: var(--text-light-visual); padding: 1.5rem 3rem; display: flex; justify-content: space-between; align-items: center; }
        .main-container { padding: 2rem 3rem; }
        .card { border-radius: var(--border-radius-lg-visual); background-color: var(--secondary-visual); box-shadow: var(--shadow-subtle-visual); }
        .card-header-styled { background-color: var(--primary-visual); color: var(--text-light-visual); padding: 1rem 1.5rem; display: flex; justify-content: space-between; align-items: center; border-radius: var(--border-radius-lg-visual) var(--border-radius-lg-visual) 0 0; }
        .table { background-color: var(--card-bg-visual); }
        .estado-activo { color: var(--success-visual); font-weight: bold; }
        .estado-inactivo { color: var(--danger-visual); font-weight: bold; }
        .btn-action { width: 30px; height: 30px; border-radius: 50%; display: inline-flex; justify-content: center; align-items: center; color: white; }
    </style>
</head>
<body class="fade-in">

<header class="header-bar">
    <h2 class="mb-0"><i class="bi bi-capsule-pill"></i> Gestión de Productos</h2>
    <a href="../paginas/inicio.php" class="btn btn-outline-light"><i class="bi bi-arrow-left-circle me-2"></i>Regresar</a>
</header>

<main class="main-container container-fluid">
    <?php if (isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show mb-3">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header-styled">
            <h4 class="mb-0">Lista de Productos</h4>
            <button type="button" class="btn btn-light btn-sm" data-bs-toggle="modal" data-bs-target="#registroProductoModal">
                <i class="bi bi-plus-circle me-1"></i>Nuevo Producto
            </button>
        </div>
        <div class="card-body p-3 bg-white">
            <div class="table-responsive">
                <table class="table table-bordered table-hover" id="productosTable" width="100%">
                    <thead>
                        <tr>
                            <th>Imagen</th>
                            <th>Nombre</th>
                            <th>Laboratorio</th>
                            <th>Stock</th>
                            <th>Precio</th>
                            <th>Estado</th>
                            <th class="text-center">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($productos as $producto) : ?>
                            <tr>
                                <td class="text-center">
                                    <?php if(!empty($producto['imagen'])): ?>
                                        <img src="../img/<?php echo $producto['imagen']; ?>" style="width: 40px; height: 40px; object-fit: cover; border-radius: 4px;">
                                    <?php else: ?>
                                        <i class="bi bi-image text-muted" style="font-size: 20px;"></i>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo htmlspecialchars($producto['nombre_producto']); ?></td>
                                <td><?php echo htmlspecialchars($producto['laboratorio_fabrica']); ?></td>
                                <td><?php echo $producto['stock_actual']; ?></td>
                                <td>$<?php echo number_format($producto['precio_venta'], 2); ?></td>
                                <td><span class="<?php echo ($producto['estado'] == 'Activo') ? 'estado-activo' : 'estado-inactivo'; ?>"><?php echo $producto['estado']; ?></span></td>
                                <td class="text-center">
                                    <button class="btn btn-danger btn-action btn-desactivar" data-id="<?php echo $producto['id']; ?>" data-nombre="<?php echo htmlspecialchars($producto['nombre_producto']); ?>">
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

<div class="modal fade" id="registroProductoModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Registrar Nuevo Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="productos.php" method="POST" enctype="multipart/form-data">
                <div class="modal-body p-3">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre</label>
                            <input type="text" class="form-control" name="nombre" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Laboratorio</label>
                            <input type="text" class="form-control" name="laboratorio" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold">Imagen del Producto</label>
                            <input type="file" class="form-control" name="imagen" accept="image/*">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Cantidad</label>
                            <input type="number" class="form-control" name="cantidad" min="1" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Precio</label>
                            <input type="number" step="0.01" class="form-control" name="precio_venta" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Vencimiento</label>
                            <input type="date" class="form-control" name="fecha_vencimiento" required>
                        </div>
                        <input type="hidden" name="descripcion" value="">
                        <input type="hidden" name="stock_minimo" value="5">
                        <input type="hidden" name="ubicacion_produ" value="Estante A1">
                        <input type="hidden" name="requiere_refrigeracion" value="no">
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

<div class="modal fade" id="eliminarProductoModal" tabindex="-1">
    <div class="modal-dialog modal-sm">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">Confirmar</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="productos.php" method="POST">
                <div class="modal-body text-center">
                    <input type="hidden" name="desactivar_id" id="desactivar_id">
                    <p>¿Desactivar <strong id="producto_a_desactivar"></strong>?</p>
                </div>
                <div class="modal-footer justify-content-center">
                    <button type="submit" class="btn btn-danger btn-sm" name="desactivar_producto">Sí, Desactivar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#productosTable').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json' }
        });
        $('#productosTable tbody').on('click', '.btn-desactivar', function () {
            $('#desactivar_id').val($(this).data('id'));
            $('#producto_a_desactivar').text($(this).data('nombre'));
            new bootstrap.Modal(document.getElementById('eliminarProductoModal')).show();
        });
    });
</script>
</body>
</html>