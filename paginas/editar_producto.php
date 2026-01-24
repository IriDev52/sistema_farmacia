<?php
include("../conexion/conex.php");
session_start();

if (!isset($_GET['id'])) { header("Location: productos.php"); exit(); }
$id_producto = (int)$_GET['id'];

$res = mysqli_query($conn, "SELECT * FROM productos WHERE id = $id_producto");
$p = mysqli_fetch_assoc($res);

if (!$p) { header("Location: productos.php"); exit(); }

$fecha_hoy = date('Y-m-d');
if ($p['fecha_vencimiento'] < $fecha_hoy) {
    mysqli_query($conn, "DELETE FROM productos WHERE id = $id_producto");
    $_SESSION['message'] = "El producto venció y fue eliminado.";
    $_SESSION['message_type'] = "danger";
    header("Location: productos.php");
    exit();
}

if (isset($_POST['actualizar_producto'])) {
    $id = (int)$_POST['id'];
    $nombre = mysqli_real_escape_string($conn, $_POST['nombre']);
    $lab = mysqli_real_escape_string($conn, $_POST['laboratorio']);
    $stock = (int)$_POST['stock_actual'];
    $precio = (float)$_POST['precio_venta'];
    $vence = $_POST['fecha_vencimiento'];
    $ubi = mysqli_real_escape_string($conn, $_POST['ubicacion_produ']);
    $estado = $_POST['estado'];

    $sql = "UPDATE productos SET nombre_producto='$nombre', laboratorio_fabrica='$lab', stock_actual=$stock, precio_venta=$precio, fecha_vencimiento='$vence', ubicacion='$ubi', estado='$estado' WHERE id=$id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Actualizado correctamente";
        $_SESSION['message_type'] = "success";
        header("Location: productos.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar | Farmacia</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { background: #f4f7f6; font-family: sans-serif; }
        .header-blue { background: #1e3a8a; color: white; padding: 20px; border-radius: 10px 10px 0 0; }
        .card { border: none; border-radius: 10px; margin-top: 50px; }
    </style>
</head>
<body>
<div class="container">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow">
                <div class="header-blue"><h4>Editar Producto</h4></div>
                <form action="" method="POST" class="card-body p-4">
                    <input type="hidden" name="id" value="<?php echo $p['id']; ?>">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Nombre</label>
                            <input type="text" name="nombre" class="form-control" value="<?php echo $p['nombre_producto']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Laboratorio</label>
                            <input type="text" name="laboratorio" class="form-control" value="<?php echo $p['laboratorio_fabrica']; ?>" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Stock</label>
                            <input type="number" name="stock_actual" class="form-control" value="<?php echo $p['stock_actual']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Precio ($)</label>
                            <input type="number" step="0.01" name="precio_venta" class="form-control" value="<?php echo $p['precio_venta']; ?>">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Vencimiento</label>
                            <input type="date" name="fecha_vencimiento" class="form-control" value="<?php echo $p['fecha_vencimiento']; ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Ubicación</label>
                            <input type="text" name="ubicacion_produ" class="form-control" value="<?php echo $p['ubicacion']; ?>">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold">Estado</label>
                            <select name="estado" class="form-select">
                                <option value="Activo" <?php if($p['estado']=='Activo') echo 'selected'; ?>>Activo</option>
                                <option value="Inactivo" <?php if($p['estado']=='Inactivo') echo 'selected'; ?>>Inactivo</option>
                            </select>
                        </div>
                    </div>
                    <div class="mt-4 d-flex justify-content-between">
                        <a href="productos.php" class="btn btn-light">Regresar</a>
                        <button type="submit" name="actualizar_producto" class="btn btn-primary px-4">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
</body>
</html>