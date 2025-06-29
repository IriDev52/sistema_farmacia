<?php 
session_start();
include('../conexion/conex.php');

if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === null) {
    header("Location: login.php");
    exit();
}

$total_productos = 1250;
$ventas_hoy = 12;
$productos_bajos_stock = 45;
$clientes_registrados = 321;
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Control - Farmacia C.A.</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <style>
        :root {
            --bg-dark: #1f2937;
            --bg-light: #f3f4f6;
            --card-bg: #ffffff;
            --text-primary: #374151;
            --text-light: #f9fafb;
            --accent-blue: #0ea5e9;
            --accent-green: #22c55e;
            --accent-orange: #f59e0b;
            --accent-purple: #8b5cf6;
            --shadow-subtle: 0 4px 12px rgba(0, 0, 0, 0.08);
            --shadow-strong: 0 10px 25px rgba(0, 0, 0, 0.15);
        }
        body {
            font-family: 'Inter', sans-serif;
            background-color: var(--bg-light);
            margin: 0;
            padding: 0;
            display: flex;
            flex-direction: column;
            min-height: 100vh;
            color: var(--text-primary);
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        .header {
            background-color: var(--bg-dark);
            color: var(--text-light);
            padding: 1.5rem 4rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-strong);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-logo {
            display: flex;
            align-items: center;
            gap: 15px;
        }
        .header-logo i {
            font-size: 2.5rem;
            color: var(--accent-blue);
        }
        .header-logo h2 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 1.8rem;
            margin: 0;
        }
        .header-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 3rem;
        }
        .header-nav-link {
            display: flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
            color: rgba(255, 255, 255, 0.7);
            font-weight: 500;
            font-size: 1rem;
            padding: 0.5rem 0.8rem;
            border-radius: 10px;
            transition: color 0.3s ease, background-color 0.3s ease, transform 0.2s ease;
        }
        .header-nav-link i {
            font-size: 1.4rem;
        }
        .header-nav-link:hover, .header-nav-link.active {
            color: var(--text-light);
            background-color: rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }
        .user-actions {
            display: flex;
            align-items: center;
            gap: 2rem;
        }
        .user-info span {
            font-family: 'Poppins', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            color: var(--text-light);
        }
        .logout-btn {
            background-color: transparent;
            border: 2px solid rgba(255, 255, 255, 0.5);
            color: rgba(255, 255, 255, 0.8);
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover {
            background-color: #ef4444;
            border-color: #ef4444;
            color: white;
            transform: translateY(-3px) scale(1.05);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.3);
        }
        .dashboard-container {
            flex-grow: 1;
            padding: 4rem;
            max-width: 1440px;
            margin: 0 auto;
            width: 100%;
        }
        .welcome-banner {
            background: linear-gradient(135deg, #2c3e50, #203140);
            color: white;
            padding: 4rem;
            border-radius: 25px;
            margin-bottom: 3rem;
            box-shadow: var(--shadow-strong);
            text-align: center;
        }
        .welcome-banner h1 {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 3.8rem;
            margin: 0 0 1rem 0;
            line-height: 1.2;
        }
        .welcome-banner p {
            font-size: 1.3rem;
            color: rgba(255, 255, 255, 0.8);
            margin: 0 auto;
            max-width: 800px;
            line-height: 1.6;
        }
        .section-title {
            font-family: 'Poppins', sans-serif;
            font-size: 2.2rem;
            font-weight: 600;
            color: var(--text-primary);
            margin-bottom: 2.5rem;
            border-left: 5px solid var(--accent-blue);
            padding-left: 20px;
        }
        .stats-grid, .menu-grid {
            display: grid;
            gap: 2.5rem;
        }
        .stats-grid {
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            margin-bottom: 4rem;
        }
        .stats-card {
            background-color: var(--card-bg);
            border-radius: 18px;
            padding: 2.5rem;
            box-shadow: var(--shadow-subtle);
            display: flex;
            align-items: center;
            gap: 2rem;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #e5e7eb;
        }
        .stats-card:hover {
            transform: translateY(-8px);
            box-shadow: var(--shadow-strong);
        }
        .stats-icon-wrapper {
            width: 65px;
            height: 65px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 2.5rem;
            color: white;
            flex-shrink: 0;
        }
        .stats-info h4 {
            font-family: 'Poppins', sans-serif;
            font-size: 1.2rem;
            font-weight: 600;
            color: #6b7280;
            margin: 0;
        }
        .stats-info p {
            font-family: 'Poppins', sans-serif;
            font-size: 2.5rem;
            font-weight: 700;
            margin: 0;
            line-height: 1.2;
            color: var(--text-primary);
        }
        .stats-card.green .stats-icon-wrapper { background-color: var(--accent-green); }
        .stats-card.blue .stats-icon-wrapper { background-color: var(--accent-blue); }
        .stats-card.orange .stats-icon-wrapper { background-color: var(--accent-orange); }
        .stats-card.purple .stats-icon-wrapper { background-color: var(--accent-purple); }

        .menu-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 2.5rem;
        }
        .menu-card {
            background-color: var(--card-bg);
            border-radius: 20px;
            padding: 3rem;
            box-shadow: var(--shadow-subtle);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            text-decoration: none;
            color: var(--text-primary);
            text-align: center;
            border: 1px solid #e5e7eb;
        }
        .menu-card:hover {
            transform: translateY(-12px);
            box-shadow: var(--shadow-strong);
        }
        .menu-card .icon-wrapper {
            width: 100px;
            height: 100px;
            color: white;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 2rem auto;
            transition: all 0.3s ease;
            overflow: hidden;
            border: 6px solid rgba(255,255,255,0.5);
        }
        .menu-card .icon-wrapper svg {
            width: 60px;
            height: 60px;
        }
        .menu-card .icon-wrapper i {
            font-size: 3.5rem;
            transition: transform 0.3s ease;
        }
        .menu-card:hover .icon-wrapper {
            transform: scale(1.1) rotate(5deg);
        }
        .menu-card.products-card .icon-wrapper { background-color: #2ecc71; }
        .menu-card.inventory-card .icon-wrapper { background-color: #e67e22; }
        .menu-card.sales-card .icon-wrapper { background-color: #3498db; }
        .menu-card.reports-card .icon-wrapper { background-color: #9b59b6; }
        .menu-card.clients-card .icon-wrapper { background-color: #f1c40f; }

        .card-title {
            font-family: 'Poppins', sans-serif;
            font-weight: 700;
            font-size: 2rem;
            margin: 0 0 0.8rem 0;
            line-height: 1.2;
        }
        .card-description {
            font-size: 1.1rem;
            color: #9ca3af;
            line-height: 1.6;
        }

        @media (max-width: 1200px) {
            .header, .dashboard-container {
                padding: 2.5rem;
            }
            .welcome-banner {
                padding: 3rem;
            }
        }
        @media (max-width: 992px) {
            .header {
                flex-direction: column;
                gap: 1.5rem;
                padding: 2rem;
            }
            .header-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1.5rem;
            }
            .welcome-banner h1 {
                font-size: 3rem;
            }
            .section-title {
                font-size: 1.8rem;
            }
        }
        @media (max-width: 768px) {
            .dashboard-container {
                padding: 2rem;
            }
            .stats-card, .menu-card {
                padding: 2rem;
            }
        }
        @media (max-width: 576px) {
            .header {
                padding: 1.5rem 1rem;
            }
            .header-nav {
                flex-direction: column;
                align-items: center;
                gap: 1rem;
            }
            .user-actions {
                flex-direction: column;
                gap: 1rem;
            }
            .dashboard-container {
                padding: 1.5rem;
            }
            .welcome-banner {
                padding: 2rem;
            }
            .welcome-banner h1 {
                font-size: 2.5rem;
            }
            .section-title {
                font-size: 1.5rem;
                margin-bottom: 2rem;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-logo">
            <i class="ri-hospital-line"></i>
            <h2>Farmacia C.A.</h2>
        </div>
        <nav>
            <ul class="header-nav">
                <li><a href="inicio.php" class="header-nav-link active"><i class="ri-dashboard-fill"></i> Dashboard</a></li>
                <li><a href="productos.php" class="header-nav-link"><i class="ri-capsule-fill"></i> Productos</a></li>
                <li><a href="inventario_consulta.php" class="header-nav-link"><i class="ri-inbox-fill"></i> Inventario</a></li>
                <li><a href="tabla_cliente.php" class="header-nav-link"><i class="ri-group-fill"></i> Clientes</a></li>
                <li><a href="ventas.php" class="header-nav-link"><i class="ri-shopping-cart-fill"></i> Ventas</a></li>
                <li><a href="reporte_ventas.php" class="header-nav-link"><i class="ri-line-chart-fill"></i> Reportes</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <span class="user-info">
                <span>Hola, <?php echo htmlspecialchars($_SESSION['usuario']); ?></span>
            </span>
            <a href="cerrarSesion.php" class="logout-btn">
                <i class="ri-logout-box-line"></i> Salir
            </a>
        </div>
    </header>
    <main class="dashboard-container">
        <section class="welcome-banner">
            <h1>Bienvenido, <?php echo htmlspecialchars($_SESSION['usuario']); ?></h1>
            <p>
                Tu panel de control para una gestión eficiente y exitosa.
            </p>
        </section>

        <h3 class="section-title">Estadísticas Rápidas</h3>
        <section class="stats-grid">
            <div class="stats-card green">
                <div class="stats-icon-wrapper"><i class="ri-flask-line"></i></div>
                <div class="stats-info">
                    <h4>Total de Productos</h4>
                    <p><?php echo number_format($total_productos); ?></p>
                </div>
            </div>
            <div class="stats-card blue">
                <div class="stats-icon-wrapper"><i class="ri-shopping-bag-2-line"></i></div>
                <div class="stats-info">
                    <h4>Ventas de Hoy</h4>
                    <p><?php echo number_format($ventas_hoy); ?></p>
                </div>
            </div>
            <div class="stats-card orange">
                <div class="stats-icon-wrapper"><i class="ri-alert-line"></i></div>
                <div class="stats-info">
                    <h4>Productos con Bajo Stock</h4>
                    <p><?php echo number_format($productos_bajos_stock); ?></p>
                </div>
            </div>
            <div class="stats-card purple">
                <div class="stats-icon-wrapper"><i class="ri-group-line"></i></div>
                <div class="stats-info">
                    <h4>Clientes Registrados</h4>
                    <p><?php echo number_format($clientes_registrados); ?></p>
                </div>
            </div>
        </section>

        <h3 class="section-title">Módulos de Gestión</h3>
        <section class="menu-grid">
            <a href="productos.php" class="menu-card products-card">
                <div class="icon-wrapper">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="white">
                        <path d="M14.5 2H9.5C5.91 2 3 4.91 3 8.5v7C3 19.09 5.91 22 9.5 22h5c3.59 0 6.5-2.91 6.5-6.5v-7C21 4.91 18.09 2 14.5 2zM12 14a2 2 0 1 1 0-4 2 2 0 0 1 0 4z"/>
                    </svg>
                </div>
                <h3 class="card-title">Productos</h3>
                <p class="card-description">
                    Administra el catálogo de medicamentos, precios y existencias.
                </p>
            </a>
            <a href="inventario_consulta.php" class="menu-card inventory-card">
                <div class="icon-wrapper">
                    <i class="ri-inbox-fill"></i>
                </div>
                <h3 class="card-title">Inventario</h3>
                <p class="card-description">
                    Controla el stock, entradas y salidas de todos los productos.
                </p>
            </a>
            <a href="tabla_cliente.php" class="menu-card clients-card">
                <div class="icon-wrapper">
                    <i class="ri-group-fill"></i>
                </div>
                <h3 class="card-title">Clientes</h3>
                <p class="card-description">
                    Gestiona la información y el historial de tus clientes.
                </p>
            </a>
            <a href="ventas.php" class="menu-card sales-card">
                <div class="icon-wrapper">
                    <i class="ri-shopping-cart-fill"></i>
                </div>
                <h3 class="card-title">Ventas</h3>
                <p class="card-description">
                    Registra nuevas transacciones y consulta el historial de ventas.
                </p>
            </a>
            <a href="reporte_ventas.php" class="menu-card reports-card">
                <div class="icon-wrapper">
                    <i class="ri-line-chart-fill"></i>
                </div>
                <h3 class="card-title">Reportes</h3>
                <p class="card-description">
                    Genera reportes y analiza el rendimiento de tu negocio.
                </p>
            </a>
        </section>
    </main>
</body>
</html>