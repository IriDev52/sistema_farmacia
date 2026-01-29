<?php
ini_set('display_errors', 0); 
include("../recursos/header.php");
include("../conexion/conex.php");
mysqli_set_charset($conn, "utf8");
session_start();

$fecha_hoy = date('Y-m-d');

// 1. LÓGICA DE ACTUALIZACIÓN: Desactivar vencidos automáticamente
mysqli_query($conn, "UPDATE productos SET estado = 'inactivo' WHERE fecha_vencimiento < '$fecha_hoy'");

// 2. PROCESAR REGISTRO DE PRODUCTO
if (isset($_POST['registrar_producto'])) {
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $sustancia = mysqli_real_escape_string($conn, $_POST['sustancia_activa']); // NUEVO
    $descripcion = mysqli_real_escape_string($conn, $_POST['descripcion']);
    $laboratorio = mysqli_real_escape_string($conn, $_POST['laboratorio']);
    $clasificacion = mysqli_real_escape_string($conn, $_POST['clasificacion']); // NUEVO
    $cantidad = (int)$_POST['cantidad'];
    $lote = mysqli_real_escape_string($conn, $_POST['numero_lote']); // Ajustado a tu BD
    $stock_minimo = (int)$_POST['stock_minimo'];
    $fecha_vencimiento = $_POST['fecha_vencimiento'];
    $precio_usd = (float)$_POST['precio_venta_usd'];
    $ubicacion = mysqli_real_escape_string($conn, $_POST['estante']);
    $estado = 'activo';

    $nombre_imagen = "";
    if (isset($_FILES['imagen']) && $_FILES['imagen']['error'] == 0) {
        $ruta_destino = "../img/";
        if (!file_exists($ruta_destino)) mkdir($ruta_destino, 0777, true);
        $ext = pathinfo($_FILES['imagen']['name'], PATHINFO_EXTENSION);
        $nombre_imagen = "prod_" . time() . "." . $ext;
        move_uploaded_file($_FILES['imagen']['tmp_name'], $ruta_destino . $nombre_imagen);
    }

    // Query actualizada con los nuevos campos
    $query = "INSERT INTO productos (nombre_producto, sustancia_activa, descripcion, laboratorio_fabrica, clasificacion, stock_actual, stock_minimo, fecha_vencimiento, numero_lote, precio_venta, ubicacion, estado, imagen) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conn, $query);
    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "sssssiississs", 
            $nombre, $sustancia, $descripcion, $laboratorio, $clasificacion, 
            $cantidad, $stock_minimo, $fecha_vencimiento, $lote, 
            $precio_usd, $ubicacion, $estado, $nombre_imagen
        );
        mysqli_stmt_execute($stmt);
        $_SESSION['message'] = "Producto guardado correctamente";
        $_SESSION['message_type'] = "success";
        mysqli_stmt_close($stmt);
    }
    header("Location: productos.php");
    exit();
}

// 3. OBTENER PRODUCTOS (Mostramos todos para ver alertas de vencimiento)
$res = mysqli_query($conn, "SELECT * FROM productos ORDER BY id DESC");
$productos = mysqli_fetch_all($res, MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inventario | Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <style>
        :root { --primary: #0d6efd; --dark: #1e293b; --bg: #f1f5f9; }
        body { background-color: var(--bg); font-family: 'Segoe UI', sans-serif; }
        .navbar-custom { background: var(--dark); color: white; padding: 1rem 2rem; }
        .card { border: none; border-radius: 12px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
        .img-thumb { width: 55px; height: 55px; object-fit: cover; border-radius: 8px; }
        .badge-vence { font-size: 0.7rem; padding: 4px 8px; }
    </style>
</head>
<body>

<nav class="navbar-custom d-flex justify-content-between align-items-center mb-4">
    <h4 class="mb-0"><i class="bi bi-capsule me-2"></i> Gestión de Productos</h4>
    <div>
        <button class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#modalRegistro">
            <i class="bi bi-plus-circle me-1"></i> Nuevo Producto
        </button>
        <a href="inicio.php" class="btn btn-outline-light ms-2">Regresar</a>
    </div>
</nav>

<div class="container-fluid px-4">
    <?php if(isset($_SESSION['message'])): ?>
        <div class="alert alert-<?php echo $_SESSION['message_type']; ?> alert-dismissible fade show">
            <?php echo $_SESSION['message']; unset($_SESSION['message']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card overflow-hidden">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="tablaMain">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Detalle del Producto</th>
                            <th>Categoría</th>
                            <th>Stock / Lote</th>
                            <th>Vencimiento</th>
                            <th>Precio</th>
                            <th class="text-end pe-4">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($productos as $p): ?>
                        <tr>
                            <td class="ps-4">
                                <div class="d-flex align-items-center">
                                    <?php $foto = !empty($p['imagen']) ? "../img/".$p['imagen'] : "../img/default.png"; ?>
                                    <img src="<?php echo $foto; ?>" class="img-thumb me-3 border">
                                    <div>
                                        <div class="fw-bold text-dark"><?php echo $p['nombre_producto']; ?></div>
                                        <div class="text-primary small" style="font-size: 0.8rem;">
                                            <i class="bi bi-microscope"></i> <?php echo $p['sustancia_activa']; ?>
                                        </div>
                                        <div class="text-muted small" style="font-size: 0.7rem;"><?php echo $p['laboratorio_fabrica']; ?></div>
                                    </div>
                                </div>
                            </td>
                            <td>
                                <span class="badge rounded-pill <?php 
                                    echo ($p['clasificacion'] == 'Antibiótico') ? 'bg-danger-subtle text-danger' : 
                                         (($p['clasificacion'] == 'Controlado') ? 'bg-dark text-white' : 'bg-success-subtle text-success'); 
                                ?>">
                                    <?php echo $p['clasificacion']; ?>
                                </span>
                            </td>
                            <td>
                                <div class="fw-bold <?php echo ($p['stock_actual'] <= $p['stock_minimo']) ? 'text-danger' : ''; ?>">
                                    Qty: <?php echo $p['stock_actual']; ?>
                                </div>
                                <div class="text-muted small">Lote: <?php echo $p['numero_lote']; ?></div>
                            </td>
                            <td>
                                <?php 
                                    $vence = strtotime($p['fecha_vencimiento']);
                                    $hoy = strtotime(date("Y-m-d"));
                                    $tres_meses = strtotime("+3 months");
                                    
                                    if ($vence < $hoy) {
                                        echo '<span class="badge bg-danger badge-vence">VENCIDO</span>';
                                    } elseif ($vence <= $tres_meses) {
                                        echo '<span class="badge bg-warning text-dark badge-vence">PRÓX. A VENCER</span>';
                                    } else {
                                        echo '<span class="text-muted small">'.date('d/m/Y', $vence).'</span>';
                                    }
                                ?>
                            </td>
                            <td><span class="fw-bold text-success">$<?php echo number_format($p['precio_venta'], 2); ?></span></td>
                            <td class="text-end pe-4">
                                <a href="editar_producto.php?id=<?php echo $p['id']; ?>" class="btn btn-outline-secondary btn-sm"><i class="bi bi-pencil"></i></a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRegistro" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <form action="productos.php" method="POST" enctype="multipart/form-data" class="modal-content border-0">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="bi bi-plus-circle me-2"></i>Registrar Nuevo Medicamento</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">NOMBRE COMERCIAL</label>
                        <input type="text" name="nombre" class="form-control bg-light" placeholder="Ej: Amoxil" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">SUSTANCIA ACTIVA (GENÉRICO)</label>
                        <input type="text" name="sustancia_activa" class="form-control bg-light" placeholder="Ej: Amoxicilina" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">LABORATORIO</label>
                        <input type="text" name="laboratorio" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label small fw-bold text-muted">CLASIFICACIÓN</label>
                        <select name="clasificacion" class="form-select bg-light">
                            <option value="Libre Venta">Libre Venta (OTC)</option>
                            <option value="Antibiótico">Antibiótico</option>
                            <option value="Controlado">Controlado / Psicotrópico</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">PRECIO VENTA ($)</label>
                        <input type="number" step="0.01" name="precio_venta_usd" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">NÚMERO DE LOTE</label>
                        <input type="text" name="numero_lote" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">VENCIMIENTO</label>
                        <input type="date" name="fecha_vencimiento" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">STOCK INICIAL</label>
                        <input type="number" name="cantidad" class="form-control bg-light" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">AVISO STOCK MÍNIMO</label>
                        <input type="number" name="stock_minimo" class="form-control bg-light" value="5" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label small fw-bold text-muted">UBICACIÓN (ESTANTE)</label>
                        <select name="estante" class="form-select bg-light">
                            <?php foreach(range('A','F') as $L) echo "<option value='Estante $L'>Estante $L</option>"; ?>
                        </select>
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">IMAGEN DEL PRODUCTO</label>
                        <input type="file" name="imagen" class="form-control bg-light" accept="image/*">
                    </div>
                    <div class="col-12">
                        <label class="form-label small fw-bold text-muted">DESCRIPCIÓN ADICIONAL</label>
                        <textarea name="descripcion" class="form-control bg-light" rows="2"></textarea>
                    </div>
                </div>
            </div>
            <div class="modal-footer bg-light">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                <button type="submit" name="registrar_producto" class="btn btn-primary px-4">Guardar Producto</button>
            </div>
        </form>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    $(document).ready(function() {
        $('#tablaMain').DataTable({
            language: { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json' },
            order: [[0, 'asc']],
            pageLength: 10
        });
    });
</script>
</body>
</html>