<?php
include("../recursos/header.php");
include("../conexion/conex.php");
session_start();

// 1. Verificar si se recibió un ID de producto para editar
if (!isset($_GET['id'])) {
    // Si no se recibe un ID, redirigir de vuelta a la lista de productos
    $_SESSION['message'] = "Error: No se ha especificado un producto para editar.";
    $_SESSION['message_type'] = "danger";
    header("Location: productos.php");
    exit();
}

$id_producto = (int)$_GET['id'];

// 2. Procesar la actualización del producto si el formulario ha sido enviado
if (isset($_POST['actualizar_producto'])) {
    $id = (int)$_POST['id'];
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
    $laboratorio = mysqli_real_escape_string($conn, $_POST['laboratorio']);
    $stock_actual = (int)$_POST['stock_actual']; // Usar el campo correcto para stock
    $stock_minimo = (int)$_POST['stock_minimo'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $requiere_refrigeracion = strtolower(mysqli_real_escape_string($conn, $_POST['requiere_refrigeracion']));
    $precio_venta = (float)$_POST['precio_venta'];
    $ubicacion = mysqli_real_escape_string($conn, $_POST['ubicacion_produ']);
    $estado = mysqli_real_escape_string($conn, $_POST['estado']);

    // Validar datos
    if (empty($nombre) || empty($laboratorio) || $stock_actual < 0 || empty($fecha_vencimiento) || empty($ubicacion) || $precio_venta <= 0) {
        $_SESSION['message'] = "Error: Por favor, complete todos los campos obligatorios.";
        $_SESSION['message_type'] = "danger";
    } else {
        // Preparar la consulta UPDATE
        $query = "UPDATE productos SET nombre_producto = ?, descripcion = ?, laboratorio_fabrica = ?, stock_actual = ?, stock_minimo = ?, fecha_vencimiento = ?, requiere_refrigeracion = ?, precio_venta = ?, ubicacion = ?, estado = ? WHERE id = ?";
        $stmt = mysqli_prepare($conn, $query);

        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "sssiisssdsi", $nombre, $descripcion, $laboratorio, $stock_actual, $stock_minimo, $fecha_vencimiento, $requiere_refrigeracion, $precio_venta, $ubicacion, $estado, $id);
            
            if (mysqli_stmt_execute($stmt)) {
                $_SESSION['message'] = "Producto '{$nombre}' actualizado exitosamente.";
                $_SESSION['message_type'] = "success";
            } else {
                $_SESSION['message'] = "Error al actualizar el producto: " . mysqli_error($conn);
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

// 3. Obtener los datos del producto a editar para mostrarlos en el formulario
$producto_a_editar = null;
$query_editar = "SELECT * FROM productos WHERE id = ?";
$stmt_editar = mysqli_prepare($conn, $query_editar);
mysqli_stmt_bind_param($stmt_editar, "i", $id_producto);
mysqli_stmt_execute($stmt_editar);
$resultado_editar = mysqli_stmt_get_result($stmt_editar);

if ($resultado_editar && mysqli_num_rows($resultado_editar) > 0) {
    $producto_a_editar = mysqli_fetch_assoc($resultado_editar);
} else {
    // Si no se encuentra el producto, redirigir
    $_SESSION['message'] = "Error: El producto no fue encontrado.";
    $_SESSION['message_type'] = "danger";
    header("Location: productos.php");
    exit();
}
mysqli_stmt_close($stmt_editar);

// CÓDIGO AÑADIDO: VALIDACIÓN PARA NO EDITAR PRODUCTOS VENCIDOS
$fecha_actual = date('Y-m-d');
if ($producto_a_editar['fecha_vencimiento'] < $fecha_actual) {
    $_SESSION['message'] = "Error: No se puede editar un producto vencido. Por favor, desactívelo si es necesario.";
    $_SESSION['message_type'] = "danger";
    header("Location: productos.php");
    exit();
}
// FIN DEL CÓDIGO AÑADIDO

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Producto - Farmacia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
        .form-control, .form-select {
            border-radius: var(--border-radius-sm-visual);
            padding: 0.7rem;
            border: 1px solid #bdc3c7;
        }
        .form-control:focus, .form-select:focus {
            border-color: var(--primary-visual);
            box-shadow: 0 0 0 0.2rem rgba(74, 105, 189, 0.25);
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
    <h2 class="mb-0"><i class="bi bi-capsule-pill"></i>Editar Producto</h2>
    <a href="productos.php" class="btn btn-outline-light-styled"><i class="bi bi-arrow-left-circle me-2"></i>Regresar a Productos</a>
</header>

<main class="main-container container mt-5">
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
            <h4 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Editando: <?php echo htmlspecialchars($producto_a_editar['nombre_producto']); ?></h4>
        </div>
        <div class="card-body p-4">
            <form action="editar_producto.php?id=<?php echo $id_producto; ?>" method="POST">
                <input type="hidden" name="id" value="<?php echo htmlspecialchars($producto_a_editar['id']); ?>">
                
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="nombre" class="form-label fw-bold">Nombre</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" value="<?php echo htmlspecialchars($producto_a_editar['nombre_producto']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="laboratorio" class="form-label fw-bold">Laboratorio</label>
                        <input type="text" class="form-control" id="laboratorio" name="laboratorio" value="<?php echo htmlspecialchars($producto_a_editar['laboratorio_fabrica']); ?>" required>
                    </div>
                    <div class="col-12">
                        <label for="descripcion" class="form-label fw-bold">Descripción (Opcional)</label>
                        <textarea class="form-control" id="descripcion" name="descripcion" rows="2"><?php echo htmlspecialchars($producto_a_editar['descripcion']); ?></textarea>
                    </div>
                    <div class="col-md-4">
                        <label for="stock_actual" class="form-label fw-bold">Stock Actual</label>
                        <input type="number" class="form-control" id="stock_actual" name="stock_actual" value="<?php echo htmlspecialchars($producto_a_editar['stock_actual']); ?>" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label for="stock_minimo" class="form-label fw-bold">Stock Mínimo</label>
                        <input type="number" class="form-control" id="stock_minimo" name="stock_minimo" value="<?php echo htmlspecialchars($producto_a_editar['stock_minimo']); ?>" min="0" required>
                    </div>
                    <div class="col-md-4">
                        <label for="fecha_vencimiento" class="form-label fw-bold">Vencimiento</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento" value="<?php echo htmlspecialchars($producto_a_editar['fecha_vencimiento']); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label for="precio_venta" class="form-label fw-bold">Precio (Bs.)</label>
                        <input type="number" step="0.01" class="form-control" id="precio_venta" name="precio_venta" value="<?php echo htmlspecialchars($producto_a_editar['precio_venta']); ?>" min="0.01" required>
                    </div>
                    <div class="col-md-6">
                        <label for="ubicacion_produ" class="form-label fw-bold">Ubicación</label>
                        <select class="form-select" id="ubicacion_produ" name="ubicacion_produ" required>
                            <option value="">Seleccione un estante</option>
                            <option value="Estante A1" <?php echo ($producto_a_editar['ubicacion'] == 'Estante A1') ? 'selected' : ''; ?>>Estante A1</option>
                            <option value="Estante A2" <?php echo ($producto_a_editar['ubicacion'] == 'Estante A2') ? 'selected' : ''; ?>>Estante A2</option>
                            <option value="Estante B1" <?php echo ($producto_a_editar['ubicacion'] == 'Estante B1') ? 'selected' : ''; ?>>Estante B1</option>
                            <option value="Estante B2" <?php echo ($producto_a_editar['ubicacion'] == 'Estante B2') ? 'selected' : ''; ?>>Estante B2</option>
                            <option value="Estante C1" <?php echo ($producto_a_editar['ubicacion'] == 'Estante C1') ? 'selected' : ''; ?>>Estante C1</option>
                            <option value="Estante C2" <?php echo ($producto_a_editar['ubicacion'] == 'Estante C2') ? 'selected' : ''; ?>>Estante C2</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="estado" class="form-label fw-bold">Estado</label>
                        <select class="form-select" id="estado" name="estado" required>
                            <option value="Activo" <?php echo ($producto_a_editar['estado'] == 'Activo') ? 'selected' : ''; ?>>Activo</option>
                            <option value="Inactivo" <?php echo ($producto_a_editar['estado'] == 'Inactivo') ? 'selected' : ''; ?>>Inactivo</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">¿Requiere Refrigeración?</label>
                        <div class="d-flex gap-3">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="requiere_refrigeracion" id="refrigeracion_si" value="si" <?php echo ($producto_a_editar['requiere_refrigeracion'] == 'si') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="refrigeracion_si">Sí</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="requiere_refrigeracion" id="refrigeracion_no" value="no" <?php echo ($producto_a_editar['requiere_refrigeracion'] == 'no') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="refrigeracion_no">No</label>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary btn-sm" name="actualizar_producto">
                        <i class="bi bi-save"></i> Actualizar Producto
                    </button>
                    <a href="productos.php" class="btn btn-secondary btn-sm">
                        <i class="bi bi-x-circle"></i> Cancelar
                    </a>
                </div>
            </form>
        </div>
    </div>
</main>

<?php
mysqli_close($conn);
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>