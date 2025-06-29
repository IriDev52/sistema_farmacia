<?php
session_start();
include('../conexion/conex.php');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === null) {
    header("Location: login.php");
    exit();
}

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

$ventas_hoy = 45;
$ventas_mes_meta = 500;
$ventas_mes_actual = 385;
$porcentaje_ventas_mes = ($ventas_mes_actual / $ventas_mes_meta) * 100;

$nombre_farmacia = "Farmacia C.A.";

mysqli_close($conn);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - <?php echo htmlspecialchars($nombre_farmacia); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --bg-body: #F4F7FE;
            --bg-card: #FFFFFF;
            --text-dark: #1A202C;
            --text-muted: #718096;
            --primary-blue-light: #4A90E2; /* Azul Claro */
            --primary-blue-dark: #00008B; /* Azul Rey */
            --accent-green: #2ecc71;
            --accent-red: #e74c3c;
            --shadow-base: 0 4px 15px rgba(0, 0, 0, 0.08);
            --border-light: 1px solid #E2E8F0;
            --border-radius: 15px;
        }

        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-body);
            color: var(--text-dark);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }

        .header {
            background-color: var(--primary-blue-dark);
            color: white;
            padding: 1.5rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-base);
            position: sticky;
            top: 0;
            z-index: 1000;
        }

        .header-logo {
            display: flex;
            align-items: center;
            gap: 15px;
            text-decoration: none;
            color: white;
        }

        .header-logo i {
            font-size: 2.5rem;
            color: var(--primary-blue-light);
        }

        .header-logo h1 {
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }

        .header-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 2.5rem;
        }

        .header-nav-link {
            padding: 0.75rem 1.25rem;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            font-size: 1rem;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .header-nav-link:hover, .header-nav-link.active {
            background-color: rgba(255, 255, 255, 0.15);
            color: white;
        }
        
        .user-actions a {
            padding: 0.8rem 2rem;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            color: white;
            background-color: var(--primary-blue-light);
            border: 2px solid var(--primary-blue-light);
            transition: all 0.3s ease;
        }

        .user-actions a:hover {
            background-color: #357ABD;
            border-color: #357ABD;
        }

        .dashboard-container {
            flex-grow: 1;
            padding: 4rem;
            max-width: 1400px;
            margin: 0 auto;
            width: 100%;
        }

        .grid-container {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
        }

        .card {
            background-color: var(--bg-card);
            border-radius: var(--border-radius);
            padding: 3rem;
            box-shadow: var(--shadow-base);
            border: var(--border-light);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        
        .card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 10px;
            background-color: var(--primary-blue-light);
        }
        .card.card-green::before { background-color: var(--accent-green); }
        .card.card-purple::before { background-color: #8e44ad; }
        .card.card-orange::before { background-color: #f39c12; }
        
        .card:hover {
            transform: translateY(-8px);
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.15);
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 2rem;
        }

        .card-icon {
            font-size: 3.5rem;
            color: var(--primary-blue-light);
            transition: transform 0.3s ease;
        }
        .card:hover .card-icon {
            transform: scale(1.1) rotate(5deg);
        }
        
        .card-icon.green { color: var(--accent-green); }
        .card-icon.purple { color: #8e44ad; }
        .card-icon.orange { color: #f39c12; }
        
        .card-title {
            font-size: 1.5rem;
            font-weight: 700;
            margin: 0;
            color: var(--text-dark);
        }
        
        .card-value {
            font-size: 4.5rem;
            font-weight: 700;
            line-height: 1;
            margin: 0.5rem 0 0.5rem;
            color: var(--primary-blue-dark);
        }
        
        .card-label {
            font-size: 1rem;
            color: var(--text-muted);
            margin-top: 0.5rem;
        }
        
        .progress-bar-container {
            background-color: #E2E8F0;
            border-radius: 999px;
            height: 12px;
            margin-top: 2rem;
            overflow: hidden;
        }

        .progress-bar-fill {
            height: 100%;
            background: var(--accent-green);
            transition: width 1.5s ease-in-out;
            border-radius: 999px;
        }

        .progress-text {
            font-size: 0.95rem;
            font-weight: 500;
            color: var(--text-muted);
            margin-top: 1rem;
            display: block;
        }

        .critical-alert {
            background-color: #FEE2E2;
            color: var(--accent-red);
            padding: 1.5rem;
            border-radius: 10px;
            border: 1px solid #FCA5A5;
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-top: 2rem;
            font-weight: 500;
        }

        .critical-alert i {
            font-size: 1.8rem;
        }

        .card-link {
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            padding: 15px 30px;
            border-radius: 10px;
            color: white;
            background-color: var(--primary-blue-light);
            transition: all 0.3s ease;
            margin-top: 2rem;
        }

        .card-link:hover {
            background-color: #357ABD;
            box-shadow: 0 5px 15px rgba(74, 144, 226, 0.4);
        }

        .card-link i {
            transition: transform 0.3s ease;
        }
        .card-link:hover i {
            transform: translateX(5px);
        }
        
        @media (max-width: 992px) {
            .header {
                padding: 1.5rem 2rem;
            }
            .header-nav {
                display: none;
            }
            .dashboard-container {
                padding: 2.5rem 2rem;
            }
            .grid-container {
                gap: 2rem;
            }
        }
        @media (max-width: 576px) {
            .header {
                flex-direction: column;
                gap: 1.5rem;
                padding: 2rem;
            }
            .header-logo h1 {
                font-size: 1.6rem;
            }
            .grid-container {
                gap: 1.5rem;
            }
            .card {
                padding: 2.5rem;
            }
            .card-value {
                font-size: 4rem;
            }
            .card-icon {
                font-size: 3rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <a href="inicio.php" class="header-logo">
            <i class="ri-pulse-line"></i>
            <h1><?php echo htmlspecialchars($nombre_farmacia); ?></h1>
        </a>
        <nav>
            <ul class="header-nav">
                <li><a href="inicio.php" class="header-nav-link active">Panel</a></li>
                <li><a href="productos.php" class="header-nav-link">Productos</a></li>
                <li><a href="inventario_consulta.php" class="header-nav-link">Inventario</a></li>
                <li><a href="ventas.php" class="header-nav-link">Ventas</a></li>
                <li><a href="reporte_ventas.php" class="header-nav-link">Reportes</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <a href="cerrarSesion.php">Salir</a>
        </div>
    </header>

    <main class="dashboard-container">
        <div class="grid-container">
            <div class="card">
                <div class="card-header">
                    <h2 class="card-title">Inventario</h2>
                    <i class="ri-archive-line card-icon"></i>
                </div>
                <div class="card-value"><?php echo number_format($stock_total); ?></div>
                <div class="card-label">Total en Stock</div>
                
                <?php if ($productos_criticos > 0): ?>
                    <div class="critical-alert">
                        <i class="ri-error-warning-line"></i>
                        <span><?php echo $productos_criticos; ?> productos con stock crítico.</span>
                    </div>
                <?php endif; ?>
                <?php if ($productos_vencidos > 0): ?>
                    <div class="critical-alert">
                        <i class="ri-calendar-close-line"></i>
                        <span><?php echo $productos_vencidos; ?> productos vencidos.</span>
                    </div>
                <?php endif; ?>
            </div>

            <div class="card card-green">
                <div class="card-header">
                    <h2 class="card-title">Ventas</h2>
                    <i class="ri-line-chart-line card-icon green"></i>
                </div>
                <div class="card-value"><?php echo $ventas_hoy; ?></div>
                <div class="card-label">Ventas realizadas hoy</div>
                
                <div style="margin-top: 2rem;">
                    <div class="card-title" style="font-size: 1.1rem; margin-bottom: 1rem;">Meta del mes</div>
                    <div class="progress-bar-container">
                        <div class="progress-bar-fill" style="width: <?php echo round($porcentaje_ventas_mes); ?>%;"></div>
                    </div>
                    <span class="progress-text"><?php echo round($porcentaje_ventas_mes); ?>% completado (<?php echo $ventas_mes_actual; ?> de <?php echo $ventas_mes_meta; ?>)</span>
                </div>
            </div>

            <div class="card card-purple">
                <div class="card-header">
                    <h2 class="card-title">Productos</h2>
                    <i class="ri-box-3-line card-icon purple"></i>
                </div>
                <div class="card-value"><?php echo number_format($total_productos); ?></div>
                <div class="card-label">Total de productos registrados</div>

                <div style="margin-top: 2rem;">
                    <a href="productos.php" class="card-link">
                        Ver productos <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
            </div>
            
            <div class="card card-orange">
                <div class="card-header">
                    <h2 class="card-title">Reportes</h2>
                    <i class="ri-file-chart-line card-icon orange"></i>
                </div>
                <div class="card-value">...</div>
                <div class="card-label">Generar y analizar datos</div>

                <div style="margin-top: 2rem;">
                    <a href="reporte_ventas.php" class="card-link">
                        Ir a Reportes <i class="ri-arrow-right-line"></i>
                    </a>
                </div>
            </div>
        </div>
    </main>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const progressBar = document.querySelector('.progress-bar-fill');
            const percentage = <?php echo $porcentaje_ventas_mes; ?>;
            if (progressBar) {
                progressBar.style.width = `${percentage}%`;
            }
        });
    </script>
</body>
</html>