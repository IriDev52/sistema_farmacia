<?php
session_start();

if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: ../index.php");
    exit();
}

include("../conexion/conex.php");
include("tasa_bcv.php");

// 1. Manejo de Filtros de Fecha
$fecha_inicio = isset($_GET['desde']) ? $_GET['desde'] : '';
$fecha_fin = isset($_GET['hasta']) ? $_GET['hasta'] : '';

// 2. Obtener tasa de cambio
$tasa_cambio_dolar_a_bs = 36.50; 
$tasa_api = obtenerTasaBCV_API_Anidada(); 
if (is_float($tasa_api) && $tasa_api > 0) { $tasa_cambio_dolar_a_bs = $tasa_api; }

// 3. SQL robusto con Filtro Dinámico
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

// 4. Totales para las tarjetas
$total_usd = 0;
$conteo = 0;
if ($resultado) {
    while($row = $resultado->fetch_assoc()) {
        $total_usd += $row['total_usd'];
        $conteo++;
    }
    $resultado->data_seek(0); // Reiniciar el puntero para la tabla
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reportes | Farmacia Barrancas</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-pastel: #f0f9ff;
            --mint-soft: #ecfdf5;
            --mint-dark: #10b981;
            --blue-soft: #eff6ff;
            --blue-dark: #3b82f6;
            --text-main: #334155;
            --white: #ffffff;
        }

        body { font-family: 'Poppins', sans-serif; background-color: #f8fafc; margin: 0; color: var(--text-main); }

        /* Navbar */
        .navbar {
            background: var(--white);
            padding: 1rem 5%;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.02);
            position: sticky; top: 0; z-index: 100;
        }
        .logo { display: flex; align-items: center; gap: 10px; font-weight: 700; color: var(--blue-dark); }

        /* Contenedores */
        .container { max-width: 1200px; margin: 2rem auto; padding: 0 1.5rem; }

        /* Filtros */
        .filter-card {
            background: var(--white);
            padding: 1.5rem;
            border-radius: 20px;
            margin-bottom: 2rem;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: flex-end;
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
        }
        .filter-group { display: flex; flex-direction: column; gap: 5px; }
        .filter-group label { font-size: 0.75rem; font-weight: 600; color: #64748b; }
        .filter-group input { 
            padding: 8px 12px; border: 1px solid #e2e8f0; border-radius: 10px; font-family: inherit;
        }

        /* Botones */
        .btn { 
            padding: 0.6rem 1.2rem; border-radius: 12px; text-decoration: none; 
            font-weight: 600; font-size: 0.9rem; display: flex; align-items: center; gap: 8px; transition: 0.3s;
            border: none; cursor: pointer;
        }
        .btn-blue { background: var(--blue-soft); color: var(--blue-dark); }
        .btn-blue:hover { background: var(--blue-dark); color: white; }
        .btn-mint { background: var(--mint-soft); color: var(--mint-dark); border: 1px solid var(--mint-dark); }
        .btn-mint:hover { background: var(--mint-dark); color: white; }

        /* Estadísticas */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.5rem; margin-bottom: 2rem; }
        .stat-card { background: var(--white); padding: 1.2rem; border-radius: 18px; display: flex; align-items: center; gap: 15px; }
        .stat-icon { width: 50px; height: 50px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }

        /* Tabla */
        .table-wrapper { background: var(--white); border-radius: 24px; padding: 1.5rem; box-shadow: 0 10px 30px rgba(0,0,0,0.02); }
        table { width: 100%; border-collapse: separate; border-spacing: 0 8px; }
        th { padding: 1rem; text-align: left; color: #64748b; font-size: 0.8rem; text-transform: uppercase; border-bottom: 2px solid #f1f5f9; }
        td { padding: 1rem; background: #fff; border-top: 1px solid #f1f5f9; border-bottom: 1px solid #f1f5f9; }
        
        /* Badge Calendario */
        .date-badge {
            background: var(--white); border: 1px solid #e2e8f0; border-radius: 12px;
            text-align: center; width: 60px; overflow: hidden; display: flex; flex-direction: column;
        }
        .date-badge .day-name { background: var(--blue-soft); color: var(--blue-dark); font-size: 0.6rem; font-weight: 800; padding: 2px 0; }
        .date-badge .day-num { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
        .date-badge .month { font-size: 0.65rem; color: #64748b; padding-bottom: 3px; }

        .price-bs { background: var(--mint-soft); color: var(--mint-dark); padding: 4px 10px; border-radius: 8px; font-weight: 700; }

        @media print {
            .navbar, .filter-card, .btn-excel { display: none !important; }
            .container { margin: 0; padding: 0; width: 100%; }
        }
    </style>
</head>
<body>

<nav class="navbar">
    <div class="logo"><i class="ri-capsule-fill"></i> <span>Farmacia Barrancas</span></div>
    <div style="display: flex; gap: 10px;">
        <a href="reporte_excel.php?desde=<?php echo $fecha_inicio; ?>&hasta=<?php echo $fecha_fin; ?>" class="btn btn-mint">
            <i class="ri-file-excel-2-line"></i> Excel
        </a>
        <button onclick="window.print()" class="btn btn-blue">
            <i class="ri-printer-line"></i> Imprimir
        </button>
        <a href="inicio.php" class="btn btn-blue"><i class="ri-arrow-left-line"></i></a>
    </div>
</nav>

<div class="container">
    
    <form class="filter-card" method="GET">
        <div class="filter-group">
            <label>Desde:</label>
            <input type="date" name="desde" value="<?php echo $fecha_inicio; ?>">
        </div>
        <div class="filter-group">
            <label>Hasta:</label>
            <input type="date" name="hasta" value="<?php echo $fecha_fin; ?>">
        </div>
        <button type="submit" class="btn btn-blue">Filtrar Reporte</button>
        <?php if(!empty($fecha_inicio)): ?>
            <a href="reportes.php" style="font-size: 0.8rem; color: #94a3b8;">Limpiar</a>
        <?php endif; ?>
    </form>

    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--blue-soft); color:var(--blue-dark);"><i class="ri-funds-line"></i></div>
            <div class="stat-info"><h3>Ventas</h3><p><?php echo $conteo; ?></p></div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:var(--mint-soft); color:var(--mint-dark);"><i class="ri-money-dollar-circle-line"></i></div>
            <div class="stat-info"><h3>Total USD</h3><p>$ <?php echo number_format($total_usd, 2); ?></p></div>
        </div>
    </div>

    <div class="table-wrapper">
        <table>
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Producto</th>
                    <th>USD</th>
                    <th>Bolívares</th>
                </tr>
            </thead>
            <tbody>
                <?php 
                $meses = ["", "Ene", "Feb", "Mar", "Abr", "May", "Jun", "Jul", "Ago", "Sep", "Oct", "Nov", "Dic"];
                $dias = ["Dom", "Lun", "Mar", "Mié", "Jue", "Vie", "Sáb"];
                
                if($resultado && $resultado->num_rows > 0):
                    while($f = $resultado->fetch_assoc()): 
                        $ts = strtotime($f['fecha_venta']);
                ?>
                <tr>
                    <td>
                        <div class="date-badge">
                            <span class="day-name"><?php echo $dias[date('w', $ts)]; ?></span>
                            <span class="day-num"><?php echo date('d', $ts); ?></span>
                            <span class="month"><?php echo $meses[(int)date('m', $ts)]; ?></span>
                        </div>
                    </td>
                    <td><span style="color:#94a3b8; font-size:0.85rem;"><?php echo date('h:i A', $ts); ?></span></td>
                    <td>
                        <strong style="display:block; color:#1e293b;"><?php echo htmlspecialchars($f['nombre_producto']); ?></strong>
                        <small style="color:#94a3b8;">Cantidad: <?php echo $f['cantidad']; ?></small>
                    </td>
                    <td><span style="font-weight:700;">$ <?php echo number_format($f['total_usd'], 2); ?></span></td>
                    <td><span class="price-bs">Bs. <?php echo number_format($f['total_usd'] * $tasa_cambio_dolar_a_bs, 2, ',', '.'); ?></span></td>
                </tr>
                <?php endwhile; else: ?>
                    <tr><td colspan="5" style="text-align:center; padding:3rem; color:#94a3b8;">No hay registros para este período.</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

</body>
</html>