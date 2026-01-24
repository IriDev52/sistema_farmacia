<?php
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: ../index.php");
    exit();
}

include("../conexion/conex.php");
include("tasa_bcv.php");

$fecha_inicio = isset($_GET['desde']) ? $_GET['desde'] : '';
$fecha_fin = isset($_GET['hasta']) ? $_GET['hasta'] : '';

$tasa_cambio_dolar_a_bs = 36.50; 
$tasa_api = obtenerTasaBCV_API_Anidada(); 
if (is_float($tasa_api) && $tasa_api > 0) { $tasa_cambio_dolar_a_bs = $tasa_api; }

$where_clause = "";
if (!empty($fecha_inicio) && !empty($fecha_fin)) {
    $where_clause = " WHERE v.fecha_venta BETWEEN '$fecha_inicio 00:00:00' AND '$fecha_fin 23:59:59' ";
}

$sql = "SELECT v.id, v.fecha_venta, v.total_usd, v.cantidad, p.nombre_producto 
        FROM ventas v 
        INNER JOIN productos p ON v.id_producto = p.id 
        $where_clause
        ORDER BY v.fecha_venta DESC";

$resultado = $conn->query($sql);

$total_usd = 0;
$conteo = 0;
if ($resultado) {
    while($row = $resultado->fetch_assoc()) {
        $total_usd += $row['total_usd'];
        $conteo++;
    }
    $resultado->data_seek(0); 
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Ejecutivo | Farmacia Barrancas</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #ffffff;
            --dark-blue: #1e3a8a;
            --dark-blue-soft: #172554;
            --accent-blue: #eff6ff;
            --text-main: #1e293b;
            --text-muted: #64748b;
            --success: #059669;
            --success-bg: #ecfdf5;
            --border: #f1f5f9;
        }

        body { font-family: 'Inter', sans-serif; background-color: var(--bg-body); margin: 0; color: var(--text-main); }
        .container { max-width: 1000px; margin: 0 auto; padding: 20px; }

        .header-filter {
            background: #f8fafc;
            padding: 20px 30px;
            border-radius: 20px;
            display: flex;
            gap: 20px;
            align-items: center;
            margin-bottom: 25px;
        }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
        .filter-group input { border: 1.5px solid #e2e8f0; padding: 8px 12px; border-radius: 12px; font-size: 14px; outline: none; }
        .btn-filter { background: var(--dark-blue); color: white; border: none; padding: 10px 20px; border-radius: 12px; font-weight: 700; cursor: pointer; margin-top: 16px; }

        .summary-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 30px; }
        .summary-card { 
            background: #fff; padding: 25px; border-radius: 24px; text-align: center; 
            border: 1px solid var(--border); box-shadow: 0 4px 12px rgba(0,0,0,0.03);
            display: flex; flex-direction: column; align-items: center;
        }
        .summary-card i { font-size: 24px; padding: 12px; border-radius: 18px; margin-bottom: 10px; background: var(--accent-blue); color: var(--dark-blue); }
        .summary-card span { font-size: 11px; font-weight: 700; color: var(--text-muted); text-transform: uppercase; }
        .summary-card h2 { margin: 8px 0 0; font-size: 26px; font-weight: 800; color: var(--dark-blue-soft); }

        .report-toolbar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px; }
        .brand { display: flex; align-items: center; gap: 8px; font-weight: 800; color: var(--dark-blue); font-size: 16px; }
        .action-buttons { display: flex; gap: 10px; }
        .btn-action { display: flex; align-items: center; gap: 6px; padding: 10px 18px; border-radius: 14px; text-decoration: none; font-size: 13px; font-weight: 700; }
        .btn-excel { background: var(--success-bg); color: var(--success); }
        .btn-print { background: var(--accent-blue); color: var(--dark-blue); }
        .btn-back { background: #f8fafc; color: var(--text-muted); border: 1px solid var(--border); }

        .report-list { background: #fff; border-radius: 24px; padding: 10px 25px; border: 1px solid var(--border); }
        .table-header { display: grid; grid-template-columns: 80px 1fr 100px 140px; padding: 15px 0; border-bottom: 2px solid var(--border); font-size: 11px; font-weight: 800; color: #cbd5e1; text-transform: uppercase; }
        .row-item { display: grid; grid-template-columns: 80px 1fr 100px 140px; padding: 20px 0; border-bottom: 1px solid var(--border); align-items: center; }
        .row-item:last-child { border-bottom: none; }

        .date-badge { width: 50px; height: 60px; background: #fff; border: 2px solid var(--border); border-radius: 14px; display: flex; flex-direction: column; overflow: hidden; text-align: center; }
        .date-badge .day-name { background: var(--dark-blue); color: white; font-size: 9px; font-weight: 800; padding: 4px 0; text-transform: uppercase; }
        .date-badge .day-num { font-size: 18px; font-weight: 800; padding: 2px 0; color: var(--dark-blue-soft); }
        .date-badge .month { font-size: 9px; color: var(--text-muted); font-weight: 700; padding-bottom: 4px; }

        .prod-info h4 { margin: 0; font-size: 15px; font-weight: 700; color: var(--dark-blue-soft); }
        .prod-info p { margin: 4px 0 0; font-size: 12px; color: var(--text-muted); font-weight: 500; }
        .price-usd { font-weight: 800; font-size: 15px; color: var(--dark-blue-soft); }
        .price-bs { background: var(--success-bg); color: var(--success); padding: 8px 14px; border-radius: 12px; font-weight: 800; font-size: 13px; text-align: center; border: 1px solid rgba(5, 150, 105, 0.1); }

        @media print { .header-filter, .action-buttons, .btn-back { display: none !important; } }
    </style>
</head>
<body>

<div class="container">
    <form class="header-filter" method="GET">
        <div class="filter-group"><label>Desde</label><input type="date" name="desde" value="<?php echo $fecha_inicio; ?>"></div>
        <div class="filter-group"><label>Hasta</label><input type="date" name="hasta" value="<?php echo $fecha_fin; ?>"></div>
        <button type="submit" class="btn-filter">Filtrar</button>
    </form>

    <div class="summary-grid">
        <div class="summary-card">
            <i class="ri-shopping-bag-3-line"></i>
            <span>Ventas</span>
            <h2><?php echo $conteo; ?></h2>
        </div>
        <div class="summary-card">
            <i class="ri-money-dollar-circle-line"></i>
            <span>Total USD</span>
            <h2>$ <?php echo number_format($total_usd, 2); ?></h2>
        </div>
        <div class="summary-card">
            <i class="ri-line-chart-line"></i>
            <span>Tasa</span>
            <h2>Bs. <?php echo number_format($tasa_cambio_dolar_a_bs, 2); ?></h2>
        </div>
    </div>

    <div class="report-toolbar">
        <div class="brand"><i class="ri-shield-cross-fill"></i> Farmacia Barrancas</div>
        <div class="action-buttons">
            <a href="reporte_excel.php?desde=<?php echo $fecha_inicio; ?>&hasta=<?php echo $fecha_fin; ?>" class="btn-action btn-excel"><i class="ri-file-excel-fill"></i> Excel</a>
            <button onclick="window.print()" class="btn-action btn-print"><i class="ri-printer-fill"></i> Imprimir</button>
            <a href="inicio.php" class="btn-action btn-back"><i class="ri-arrow-left-line"></i></a>
        </div>
    </div>

    <div class="report-list">
        <div class="table-header">
            <div>Fecha</div>
            <div>Producto</div>
            <div>Monto USD</div>
            <div style="text-align: center;">Monto Bs.</div>
        </div>

        <?php 
        $meses = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
        $dias = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
        if($resultado && $resultado->num_rows > 0):
            while($f = $resultado->fetch_assoc()): 
                $ts = strtotime($f['fecha_venta']);
        ?>
        <div class="row-item">
            <div>
                <div class="date-badge">
                    <span class="day-name"><?php echo $dias[date('w', $ts)]; ?></span>
                    <span class="day-num"><?php echo date('d', $ts); ?></span>
                    <span class="month"><?php echo $meses[(int)date('m', $ts)]; ?></span>
                </div>
            </div>
            <div class="prod-info">
                <h4><?php echo htmlspecialchars($f['nombre_producto']); ?></h4>
                <p>Cant: <?php echo $f['cantidad']; ?> | <?php echo date('h:i A', $ts); ?></p>
            </div>
            <div class="price-usd">$ <?php echo number_format($f['total_usd'], 2); ?></div>
            <div>
                <div class="price-bs">Bs. <?php echo number_format($f['total_usd'] * $tasa_cambio_dolar_a_bs, 2, ',', '.'); ?></div>
            </div>
        </div>
        <?php endwhile; endif; ?>
    </div>
</div>

</body>
</html>