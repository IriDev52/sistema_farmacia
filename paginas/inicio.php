<?php
session_start();
include('../conexion/conex.php');

// 1. Control de Sesión
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    header("Location: ../index.php");
    exit();
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: ../index.php");
    exit();
}

// 2. Consultas de Datos
$query_total_productos = "SELECT COUNT(id) AS total FROM productos WHERE estado = 'Activo'";
$result_total_productos = mysqli_query($conn, $query_total_productos);
$total_productos = ($result_total_productos) ? mysqli_fetch_assoc($result_total_productos)['total'] : 0;

$query_stock_total = "SELECT SUM(stock_actual) AS total_stock FROM productos WHERE estado = 'Activo'";
$result_stock_total = mysqli_query($conn, $query_stock_total);
$stock_total = ($result_stock_total) ? mysqli_fetch_assoc($result_stock_total)['total_stock'] : 0;

$query_productos_criticos = "SELECT COUNT(id) AS total FROM productos WHERE stock_actual <= 50 AND estado = 'Activo'";
$result_productos_criticos = mysqli_query($conn, $query_productos_criticos);
$productos_criticos = ($result_productos_criticos) ? mysqli_fetch_assoc($result_productos_criticos)['total'] : 0;

$hoy = date('Y-m-d');
$query_productos_vencidos = "SELECT COUNT(id) AS total FROM productos WHERE fecha_vencimiento < '{$hoy}' AND fecha_vencimiento != '0000-00-00' AND estado = 'Activo'";
$result_productos_vencidos = mysqli_query($conn, $query_productos_vencidos);
$productos_vencidos = ($result_productos_vencidos) ? mysqli_fetch_assoc($result_productos_vencidos)['total'] : 0;

// Datos de ejemplo para ventas (puedes conectarlos a tu BD luego)
$ventas_hoy = 45;
$ventas_mes_meta = 500;
$ventas_mes_actual = 385;
$porcentaje_ventas_mes = ($ventas_mes_actual / $ventas_mes_meta) * 100;

$nombre_farmacia = "Farmacia Barrancas"; 
mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?php echo htmlspecialchars($nombre_farmacia); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #4F46E5;
            --primary-dark: #3730A3;
            --secondary: #64748B;
            --success: #10B981;
            --danger: #EF4444;
            --warning: #F59E0B;
            --sidebar-dark: #0F172A;
            --bg-main: #F8FAFC;
            --sidebar-width: 260px;
            --sidebar-collapsed: 85px;
            --transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-main);
            color: #1E293B;
            margin: 0;
            display: flex;
            min-height: 100vh;
        }

        /* --- SIDEBAR --- */
        .sidebar {
            width: var(--sidebar-width);
            background: var(--sidebar-dark);
            height: 100vh;
            position: fixed;
            left: 0;
            top: 0;
            transition: var(--transition);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            overflow: hidden;
        }

        body.collapsed .sidebar { width: var(--sidebar-collapsed); }

        .brand {
            padding: 2rem 1.5rem;
            display: flex;
            align-items: center;
            gap: 12px;
            color: white;
            text-decoration: none;
        }

        .brand i {
            font-size: 2.2rem;
            color: var(--primary);
            transition: var(--transition);
        }

        .brand span {
            font-weight: 800;
            font-size: 1.2rem;
            letter-spacing: -0.5px;
            white-space: nowrap;
            transition: opacity 0.3s;
        }

        body.collapsed .brand span { opacity: 0; pointer-events: none; }

        .nav-menu { flex: 1; padding: 0 1rem; }

        .nav-link {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 12px 15px;
            color: #94A3B8;
            text-decoration: none;
            border-radius: 12px;
            margin-bottom: 5px;
            transition: var(--transition);
            white-space: nowrap;
        }

        .nav-link i { font-size: 1.4rem; min-width: 24px; }

        .nav-link:hover, .nav-link.active {
            background: rgba(255, 255, 255, 0.1);
            color: white;
        }

        .nav-link.active { background: var(--primary); box-shadow: 0 10px 15px -3px rgba(79, 70, 229, 0.4); }

        .logout-section {
            padding: 1.5rem;
            border-top: 1px solid rgba(255,255,255,0.1);
        }

        /* --- MAIN CONTENT --- */
        .main-content {
            margin-left: var(--sidebar-width);
            flex: 1;
            padding: 2.5rem;
            transition: var(--transition);
        }

        body.collapsed .main-content { margin-left: var(--sidebar-collapsed); }

        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2.5rem;
        }

        .toggle-btn {
            background: white;
            border: 1px solid #E2E8F0;
            padding: 8px;
            border-radius: 8px;
            cursor: pointer;
            color: var(--secondary);
            transition: 0.2s;
        }

        .toggle-btn:hover { background: #F1F5F9; color: var(--primary); }

        /* --- GRID & CARDS --- */
        .grid-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 1.5rem;
        }

        .card {
            background: white;
            border-radius: 20px;
            padding: 1.5rem;
            border: 1px solid #E2E8F0;
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.05);
            transition: var(--transition);
            position: relative;
        }

        .card:hover { transform: translateY(-5px); box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); }

        .card-header {
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
            margin-bottom: 1.5rem;
        }

        .icon-box {
            width: 48px;
            height: 48px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.5rem;
        }

        .blue { background: #EEF2FF; color: var(--primary); }
        .green { background: #ECFDF5; color: var(--success); }
        .purple { background: #FAF5FF; color: #8B5CF6; }
        .orange { background: #FFF7ED; color: var(--warning); }

        .card-title { font-size: 0.875rem; color: var(--secondary); font-weight: 600; text-transform: uppercase; margin: 0; }
        .card-value { font-size: 2.25rem; font-weight: 800; color: #0F172A; margin: 0.5rem 0; }

        .alert-badge {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 6px 12px;
            border-radius: 8px;
            font-size: 0.8rem;
            font-weight: 600;
            margin-top: 1rem;
            width: 100%;
        }

        .alert-danger { background: #FEF2F2; color: var(--danger); }

        /* --- PROGRESS BAR --- */
        .progress-wrapper { margin-top: 1.5rem; }
        .progress-label { display: flex; justify-content: space-between; font-size: 0.85rem; margin-bottom: 8px; }
        .progress-bg { background: #E2E8F0; height: 8px; border-radius: 10px; overflow: hidden; }
        .progress-fill { background: var(--success); height: 100%; border-radius: 10px; transition: width 1s ease; }

        .btn-view {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 1.5rem;
            color: var(--primary);
            text-decoration: none;
            font-weight: 700;
            font-size: 0.9rem;
        }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); }
            body.mobile-open .sidebar { transform: translateX(0); }
            .main-content { margin-left: 0 !important; }
        }
    </style>
</head>
<body>

    <aside class="sidebar">
        <a href="inicio.php" class="brand">
            <i class="ri-pulse-fill"></i>
            <span>PHARMACORE</span>
        </a>

        <nav class="nav-menu">
            <a href="inicio.php" class="nav-link active">
                <i class="ri-layout-grid-fill"></i> <span>Dashboard</span>
            </a>
            <a href="productos.php" class="nav-link">
                <i class="ri-capsule-line"></i> <span>Productos</span>
            </a>
            <a href="inventario_consulta.php" class="nav-link">
                <i class="ri-stack-line"></i> <span>Inventario</span>
            </a>
            <a href="ventas.php" class="nav-link">
                <i class="ri-shopping-bag-3-line"></i> <span>Ventas</span>
            </a>
            <a href="reporte_ventas.php" class="nav-link">
                <i class="ri-bar-chart-box-line"></i> <span>Reportes</span>
            </a>
        </nav>

        <div class="logout-section">
            <a href="?logout=true" class="nav-link" style="color: #F87171;">
                <i class="ri-logout-box-r-line"></i> <span>Cerrar Sesión</span>
            </a>
        </div>
    </aside>

    <main class="main-content">
        <header class="top-bar">
            <button class="toggle-btn" onclick="toggleSidebar()">
                <i class="ri-menu-2-line"></i>
            </button>
            <div style="text-align: right;">
                <span style="color: var(--secondary); font-size: 0.9rem;">Bienvenido de nuevo,</span>
                <div style="font-weight: 700;">Administrador</div>
            </div>
        </header>

        <h1 style="font-size: 1.8rem; font-weight: 800; margin-bottom: 2rem;">Panel de Control</h1>

        <div class="grid-stats">
            <div class="card">
                <div class="card-header">
                    <div>
                        <p class="card-title">Inventario</p>
                        <h2 class="card-value"><?php echo number_format($stock_total); ?></h2>
                    </div>
                    <div class="icon-box blue"><i class="ri-archive-stack-line"></i></div>
                </div>
                <p style="font-size: 0.85rem; color: var(--secondary); margin: 0;">Unidades totales en stock</p>
                
                <?php if ($productos_vencidos > 0): ?>
                    <div class="alert-badge alert-danger">
                        <i class="ri-alarm-warning-line"></i>
                        <span><?php echo $productos_vencidos; ?> productos vencidos</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <p class="card-title">Ventas Hoy</p>
                        <h2 class="card-value"><?php echo $ventas_hoy; ?></h2>
                    </div>
                    <div class="icon-box green"><i class="ri-shopping-cart-2-line"></i></div>
                </div>
                
                <div class="progress-wrapper">
                    <div class="progress-label">
                        <span>Meta Mensual</span>
                        <span><?php echo round($porcentaje_ventas_mes); ?>%</span>
                    </div>
                    <div class="progress-bg">
                        <div class="progress-fill" style="width: <?php echo $porcentaje_ventas_mes; ?>%"></div>
                    </div>
                    <p style="font-size: 0.75rem; color: var(--secondary); margin-top: 8px;">
                        <?php echo $ventas_mes_actual; ?> de <?php echo $ventas_mes_meta; ?> ventas
                    </p>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <div>
                        <p class="card-title">Catálogo</p>
                        <h2 class="card-value"><?php echo $total_productos; ?></h2>
                    </div>
                    <div class="icon-box purple"><i class="ri-medicine-bottle-line"></i></div>
                </div>
                <p style="font-size: 0.85rem; color: var(--secondary); margin: 0;">Productos activos registrados</p>
                <a href="productos.php" class="btn-view">Gestionar productos <i class="ri-arrow-right-s-line"></i></a>
            </div>

            <div class="card" style="border-left: 4px solid var(--danger);">
                <div class="card-header">
                    <div>
                        <p class="card-title" style="color: var(--danger);">Stock Crítico</p>
                        <h2 class="card-value" style="color: var(--danger);"><?php echo $productos_criticos; ?></h2>
                    </div>
                    <div class="icon-box orange" style="background: #FEF2F2; color: var(--danger);"><i class="ri-error-warning-fill"></i></div>
                </div>
                <p style="font-size: 0.85rem; color: var(--secondary); margin: 0;">Productos por debajo del mínimo (50 unid.)</p>
                <a href="inventario_consulta.php" class="btn-view" style="color: var(--danger);">Revisar inventario <i class="ri-arrow-right-s-line"></i></a>
            </div>
        </div>
    </main>

    <script>
        function toggleSidebar() {
            document.body.classList.toggle('collapsed');
        }

        // Para móviles
        if (window.innerWidth < 768) {
            document.body.classList.add('collapsed');
        }
    </script>
</body>
</html>