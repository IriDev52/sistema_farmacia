<?php
include("../recursos/header.php");
include("../conexion/conex.php");
session_start();

// --- LÓGICA DE DESCARTE ---
if (isset($_GET['descartar_id'])) {
    $id = (int)$_GET['descartar_id'];
    $update = "UPDATE productos SET estado = 'Vencido' WHERE id = $id";
    mysqli_query($conn, $update);
    header("Location: inventario_consulta.php?msg=descartado");
    exit();
}

// 1. CONSULTA DE PRODUCTOS ACTIVOS
$query_activos = "SELECT * FROM productos WHERE estado = 'Activo' ORDER BY fecha_vencimiento ASC";
$res_activos = mysqli_query($conn, $query_activos);
$activos = [];
$vencidos_count = 0;
$por_vencer_count = 0;

while ($f = mysqli_fetch_assoc($res_activos)) {
    $hoy = new DateTime();
    $vence = new DateTime($f['fecha_vencimiento']);
    $diff = (int)$hoy->diff($vence)->format("%r%a");
    if ($diff <= 0) $vencidos_count++;
    elseif ($diff <= 30) $por_vencer_count++;
    $activos[] = $f;
}

$query_descartados = "SELECT * FROM productos WHERE estado = 'Vencido' ORDER BY id DESC";
$res_descartados = mysqli_query($conn, $query_descartados);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Control de Inventario Pro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        :root {
            --bs-warning-rgb: 255, 193, 7;
            --bs-danger-rgb: 220, 53, 69;
        }
        body { background-color: #f8f9fa; font-family: 'Segoe UI', Roboto, sans-serif; }
        
        /* Estilos de Filas DataTables */
        .row-vencido { background-color: rgba(var(--bs-danger-rgb), 0.15) !important; }
        .row-por-vencer { background-color: rgba(var(--bs-warning-rgb), 0.15) !important; }
        
        .card-dash { 
            border: none; 
            border-radius: 15px; 
            transition: all 0.3s ease; 
            box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        }
        .card-dash:hover { transform: translateY(-5px); box-shadow: 0 8px 15px rgba(0,0,0,0.1); }
        
        .stat-icon {
            font-size: 2.5rem;
            opacity: 0.3;
            position: absolute;
            right: 15px;
            top: 15px;
        }

        .table thead { background-color: #f1f3f5; }
        .btn-descartar { border-radius: 20px; font-weight: 600; font-size: 0.75rem; }
        .badge-status { font-size: 0.7rem; padding: 0.4em 0.8em; border-radius: 50px; }
    </style>
</head>
<body>

<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark m-0">Control de Inventario</h2>
            <p class="text-muted">Monitoreo de productos y fechas de caducidad</p>
        </div>
        <button onclick="location.reload()" class="btn btn-white shadow-sm border rounded-circle"><i class="bi bi-arrow-clockwise"></i></button>
    </div>

    <div class="row g-4 mb-5">
        <div class="col-md-4">
            <div class="card card-dash bg-white text-primary h-100 p-3" onclick="resetFiltro()" style="cursor:pointer">
                <div class="card-body position-relative">
                    <i class="bi bi-box-seam stat-icon"></i>
                    <h6 class="text-uppercase fw-bold small">Total Activos</h6>
                    <h2 class="display-5 fw-bold"><?php echo count($activos); ?></h2>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dash bg-warning text-dark h-100 p-3" onclick="filtrar('MARCA_POR_VENCER')" style="cursor:pointer">
                <div class="card-body position-relative">
                    <i class="bi bi-exclamation-triangle stat-icon"></i>
                    <h6 class="text-uppercase fw-bold small">Próximos a Vencer</h6>
                    <h2 class="display-5 fw-bold"><?php echo $por_vencer_count; ?></h2>
                    <span class="badge bg-dark-subtle text-dark">Próximos 30 días</span>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card card-dash bg-danger text-white h-100 p-3" onclick="filtrar('MARCA_VENCIDO')" style="cursor:pointer">
                <div class="card-body position-relative">
                    <i class="bi bi-x-circle stat-icon"></i>
                    <h6 class="text-uppercase fw-bold small">Vencidos</h6>
                    <h2 class="display-5 fw-bold"><?php echo $vencidos_count; ?></h2>
                    <span class="badge bg-white text-danger">Retirar de estante</span>
                </div>
            </div>
        </div>
    </div>

    <div class="card card-dash mb-5">
        <div class="card-header bg-white border-0 pt-4 px-4">
            <h5 class="fw-bold"><i class="bi bi-list-stars me-2 text-primary"></i>Productos en Estante</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table align-middle" id="tablaActivos">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Vencimiento</th>
                            <th>Estado</th>
                            <th class="text-end">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($activos as $p): 
                            $hoy = new DateTime();
                            $vence = new DateTime($p['fecha_vencimiento']);
                            $dias = (int)$hoy->diff($vence)->format("%r%a");
                            
                            $clase = ""; $marca = "OK"; $badge = '<span class="badge badge-status bg-success">AL DÍA</span>';
                            if ($dias <= 0) { 
                                $clase = "row-vencido"; $marca = "MARCA_VENCIDO"; 
                                $badge = '<span class="badge badge-status bg-danger">VENCIDO</span>';
                            }
                            elseif ($dias <= 30) { 
                                $clase = "row-por-vencer"; $marca = "MARCA_POR_VENCER"; 
                                $badge = '<span class="badge badge-status bg-warning text-dark">POR VENCER</span>';
                            }
                        ?>
                        <tr class="<?php echo $clase; ?>">
                            <td>
                                <div class="fw-bold"><?php echo $p['nombre_producto']; ?></div>
                                <span class="d-none"><?php echo $marca; ?></span>
                            </td>
                            <td>
                                <i class="bi bi-calendar3 me-2 text-muted small"></i><?php echo date("d/m/Y", strtotime($p['fecha_vencimiento'])); ?>
                            </td>
                            <td><?php echo $badge; ?></td>
                            <td class="text-end">
                                <?php if($dias <= 0): ?>
                                    <a href="?descartar_id=<?php echo $p['id']; ?>" 
                                       onclick="return confirm('¿Confirma que el producto ha sido retirado?')" 
                                       class="btn btn-dark btn-sm btn-descartar px-3">
                                       <i class="bi bi-trash3 me-1"></i>DESCARTAR
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="card card-dash border-0 shadow-sm overflow-hidden">
        <div class="card-header bg-secondary bg-opacity-10 border-0 pt-4 px-4">
            <h6 class="fw-bold text-secondary"><i class="bi bi-archive me-2"></i>Historial de Descartados</h6>
        </div>
        <div class="card-body p-4">
            <table class="table table-sm table-hover border-light">
                <thead class="small text-muted text-uppercase">
                    <tr>
                        <th>Producto</th>
                        <th>Fecha Venc.</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody class="small">
                    <?php while($d = mysqli_fetch_assoc($res_descartados)): ?>
                    <tr>
                        <td class="text-muted"><?php echo $d['nombre_producto']; ?></td>
                        <td class="text-muted"><?php echo date("d/m/Y", strtotime($d['fecha_vencimiento'])); ?></td>
                        <td><span class="text-danger fw-bold" style="font-size: 0.65rem;">FUERA DE STOCK</span></td>
                    </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        var table = $('#tablaActivos').DataTable({ 
            language: { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json' },
            pageLength: 10,
            dom: '<"d-flex justify-content-between mb-3"f>rt<"d-flex justify-content-between mt-3"ip>'
        });
        window.filtrar = function(t) { table.search(t).draw(); };
        window.resetFiltro = function() { table.search('').draw(); };
    });
</script>
</body>
</html>