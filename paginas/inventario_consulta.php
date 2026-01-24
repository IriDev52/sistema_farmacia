<?php 
header('Content-Type: text/html; charset=utf-8');
include("../conexion/conex.php"); 
mysqli_set_charset($conn, "utf8");

$hoy = date('Y-m-d');
$f90 = date('Y-m-d', strtotime('+90 days'));

$res_prod = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM productos WHERE estado = 'Activo'"));
$res_stock = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock_actual) as t FROM productos WHERE estado = 'Activo'"));
$res_prox = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM productos WHERE fecha_vencimiento >= '$hoy' AND fecha_vencimiento <= '$f90' AND estado = 'Activo'"));
$res_venc = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM productos WHERE fecha_vencimiento < '$hoy' AND estado = 'Activo'"));

$query_tabla = mysqli_query($conn, "SELECT * FROM productos WHERE estado = 'Activo' ORDER BY nombre_producto ASC");
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Inventario Farmacia</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/remixicon/4.2.0/remixicon.min.css">
    <style>
        body { background-color: #f8f9fc; font-family: 'Segoe UI', sans-serif; }
        .stat-card { padding: 20px; border-radius: 12px; color: white; margin-bottom: 20px; border: none; }
        .bg-productos { background: #4e73df; }
        .bg-stock { background: #1cc88a; }
        .bg-alerta { background: #f6c23e; color: #000; }
        .bg-vencido { background: #e74a3b; }
        .stat-num { font-size: 2.2rem; font-weight: bold; display: block; }
        .table-container { background: white; padding: 25px; border-radius: 15px; box-shadow: 0 0.15rem 1.75rem rgba(0,0,0,0.1); }
        .dt-buttons { margin-bottom: 15px; }
    </style>
</head>
<body>

<nav class="navbar navbar-dark bg-primary mb-4 shadow-sm">
    <div class="container-fluid px-4">
        <span class="navbar-brand fw-bold"><i class="ri-capsule-fill"></i> Panel de Inventario</span>
        <a href="inicio.php" class="btn btn-light btn-sm fw-bold">Volver</a>
    </div>
</nav>

<div class="container-fluid px-4">
    <div class="row">
        <div class="col-md-3"><div class="stat-card bg-productos"><small>PRODUCTOS</small><span class="stat-num"><?php echo $res_prod['t']; ?></span></div></div>
        <div class="col-md-3"><div class="stat-card bg-stock"><small>STOCK TOTAL</small><span class="stat-num"><?php echo (int)$res_stock['t']; ?></span></div></div>
        <div class="col-md-3"><div class="stat-card bg-alerta"><small>POR VENCER</small><span class="stat-num"><?php echo $res_prox['t']; ?></span></div></div>
        <div class="col-md-3"><div class="stat-card bg-vencido"><small>VENCIDOS</small><span class="stat-num"><?php echo $res_venc['t']; ?></span></div></div>
    </div>

    <div class="table-container">
        <table id="tablaInventario" class="table table-hover w-100">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Nombre Producto</th>
                    <th>Laboratorio</th>
                    <th>Stock</th>
                    <th>Estado</th>
                    <th>Vencimiento</th>
                    <th>Ubicación</th>
                </tr>
            </thead>
            <tbody>
                <?php while($row = mysqli_fetch_assoc($query_tabla)): ?>
                <tr>
                    <td><?php echo $row['id']; ?></td>
                    <td><strong><?php echo htmlspecialchars($row['nombre_producto']); ?></strong></td>
                    <td><?php echo htmlspecialchars($row['laboratorio_fabrica']); ?></td>
                    <td><?php echo $row['stock_actual']; ?></td>
                    <td><span class="badge bg-success"><?php echo $row['estado']; ?></span></td>
                    <td><?php echo $row['fecha_vencimiento']; ?></td>
                    <td><?php echo htmlspecialchars($row['ubicacion']); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.3/js/bootstrap.bundle.min.js"></script>

<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>



<script>
$(document).ready(function() {
    $('#tablaInventario').DataTable({
        "dom": 'Bfrtip',
        "buttons": [
            {
                extend: 'excelHtml5',
                text: '<i class="ri-file-excel-2-line"></i> Excel',
                className: 'btn btn-success btn-sm',
                title: 'Inventario de Farmacia'
            },
            {
                extend: 'pdfHtml5',
                text: '<i class="ri-file-pdf-line"></i> PDF',
                className: 'btn btn-danger btn-sm',
                title: 'Inventario de Farmacia'
            },
            {
                extend: 'print',
                text: '<i class="ri-printer-line"></i> Imprimir',
                className: 'btn btn-dark btn-sm'
            }
        ],
        "language": {
            "url": "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json"
        },
        "pageLength": 10,
        "responsive": true
    });
});
</script>
</body>
</html>