<?php
include("../recursos/header.php");
include('../conexion/conex.php');
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === null) {
    header("Location: login.php");
    exit();
}

// =================================================================
// === SISTEMA DE CACHÉ PARA LA TASA DE CAMBIO DEL BCV ===
// =================================================================
// Nueva API para la tasa del BCV
$api_url = 'https://pydolarvenezuela-api.vercel.app/api/v1/dollar/bcv'; 
$cache_file = 'tasa_bcv_cache.txt'; // Nombre del archivo de caché
$cache_duration = 4 * 3600;      // Duración del caché en segundos (4 horas)
$fallback_rate = 38.5;           // Tasa de respaldo si todo lo demás falla (ajustada para el BCV)

$tasa_cambio_dolar_a_bs = $fallback_rate; // Se inicia con la tasa de respaldo

// 1. Verificar si el archivo de caché existe y no ha expirado
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
    // La caché es válida, lee la tasa del archivo
    $tasa_cacheada = file_get_contents($cache_file);
    if ($tasa_cacheada !== false && is_numeric($tasa_cacheada)) {
        $tasa_cambio_dolar_a_bs = (float)$tasa_cacheada;
    }
} else {
    // 2. La caché ha expirado o no existe, obtener la tasa de la API
    $json_data = @file_get_contents($api_url);

    if ($json_data !== false) {
        $data = json_decode($json_data, true);
        // Verifica si los datos esperados existen en la respuesta JSON de esta nueva API
        if (isset($data['price']) && is_numeric($data['price'])) {
            $tasa_obtenida = (float)$data['price'];
            $tasa_cambio_dolar_a_bs = $tasa_obtenida;
            // Guardar la nueva tasa en el archivo de caché
            file_put_contents($cache_file, $tasa_obtenida);
        }
    }
}
// Fin del sistema de caché
// =================================================================

$sql = "SELECT id, fecha_venta, total FROM ventas ORDER BY fecha_venta DESC";
$resultado = $conn->query($sql);

if ($resultado === false) {
    $error_message = "Error al cargar los datos del reporte: " . $conn->error;
    $resultado = [];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Ventas - Farmacia C.A.</title>
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&family=Montserrat:wght@500;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --header-bg: #2d3748; /* Darker blue-grey */
            --header-text: #ffffff;
            --primary-color: #4c6ef5; /* A new, vibrant blue */
            --secondary-color: #a0aec0;
            --text-color-dark: #212529;
            --bg-light: #f7fafc; /* Light grey background */
            --card-bg: #ffffff;
            --card-shadow: 0 4px 20px rgba(0, 0, 0, 0.05);
            --card-hover-shadow: 0 8px 30px rgba(0, 0, 0, 0.1);
        }
        body {
            font-family: 'Inter', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--bg-light);
            color: var(--text-color-dark);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }
        .header {
            background-color: var(--header-bg);
            color: var(--header-text);
            padding: 1rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
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
            font-size: 2rem;
            color: var(--primary-color);
        }
        .header-logo h2 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 1.5rem;
            margin: 0;
        }
        .header-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 2rem;
        }
        .header-nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.8rem 1rem;
            text-decoration: none;
            color: var(--header-text);
            font-weight: 600;
            font-size: 1rem;
            border-radius: 8px;
            transition: background-color 0.3s ease, color 0.3s ease;
        }
        .header-nav-link i {
            font-size: 1.2rem;
        }
        .header-nav-link:hover, .header-nav-link.active {
            background-color: var(--primary-color);
            color: var(--header-text);
        }
        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }
        .logout-btn {
            background-color: transparent;
            color: var(--secondary-color);
            border: 1px solid var(--secondary-color);
            padding: 0.8rem 1.8rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .logout-btn:hover {
            background-color: #dc3545;
            color: white;
            border-color: #dc3545;
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }
        .main-content {
            flex-grow: 1;
            padding: 3rem;
            max-width: 1200px;
            margin: 0 auto;
            width: 100%;
        }
        .report-header {
            margin-bottom: 2rem;
            text-align: center;
        }
        .report-header h1 {
            font-family: 'Montserrat', sans-serif;
            font-weight: 700;
            font-size: 2.8rem;
            margin: 0;
            color: var(--text-color-dark);
            position: relative;
            display: inline-block;
        }
        .report-header h1::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -10px;
            transform: translateX(-50%);
            width: 80px;
            height: 5px;
            background-color: var(--primary-color);
            border-radius: 5px;
        }
        .report-header p {
            font-size: 1.1rem;
            color: #6c757d;
            margin-top: 1.5rem;
        }
        .report-info {
            display: inline-block;
            background-color: #e2e8f0;
            color: #4a5568;
            padding: 5px 15px;
            border-radius: 20px;
            font-weight: 600;
            font-size: 0.9rem;
            margin-top: 10px;
        }
        .report-container {
            background-color: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--card-shadow);
            padding: 2.5rem;
            overflow-x: auto;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 1rem;
        }
        .data-table th, .data-table td {
            padding: 1.2rem;
            text-align: left;
            border-bottom: 1px solid #e2e8f0;
        }
        .data-table th {
            background-color: #edf2f7;
            color: #4a5568;
            font-weight: 700;
            font-size: 0.95rem;
            text-transform: uppercase;
        }
        .data-table .currency-col {
            text-align: right;
        }
        .data-table tbody tr:hover {
            background-color: #f0f3f6;
        }
        .data-table tbody tr:last-child td {
            border-bottom: none;
        }
        .alert-message {
            padding: 1.5rem;
            background-color: #fff3cd;
            color: #856404;
            border: 1px solid #ffeeba;
            border-radius: 0.75rem;
            text-align: center;
            font-weight: 600;
        }
        @media (max-width: 992px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
            }
            .header-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 1rem;
            }
            .main-content {
                padding: 2rem 1rem;
            }
            .report-header h1 {
                font-size: 2rem;
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
                <li><a href="inicio.php" class="header-nav-link"><i class="ri-dashboard-line"></i> Dashboard</a></li>
                <li><a href="productos.php" class="header-nav-link"><i class="ri-flask-line"></i> Productos</a></li>
                <li><a href="inventario_consulta.php" class="header-nav-link"><i class="ri-inbox-line"></i> Inventario</a></li>
                <li><a href="ventas.php" class="header-nav-link"><i class="ri-shopping-cart-2-line"></i> Ventas</a></li>
                <li><a href="reporte_ventas.php" class="header-nav-link active"><i class="ri-line-chart-line"></i> Reportes</a></li>
            </ul>
        </nav>
        <div class="user-actions">
            <a href="cerrarSesion.php" class="logout-btn">
                <i class="ri-logout-box-line"></i> Salir
            </a>
        </div>
    </header>
    <main class="main-content">
        <header class="report-header">
            <h1>Reporte de Ventas</h1>
            <p>Listado de todas las ventas registradas en el sistema.</p>
            <span class="report-info">Tasa Dólar: BCV</span>
        </header>
        <div class="report-container">
            <?php
            if (!empty($error_message)) {
                echo '<div class="alert-message">' . htmlspecialchars($error_message) . '</div>';
            } elseif ($resultado && $resultado->num_rows > 0) {
            ?>
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID Venta</th>
                            <th>Fecha</th>
                            <th class="currency-col">Total ($)</th>
                            <th class="currency-col">Total (Bs)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php 
                        while ($fila = $resultado->fetch_assoc()) { 
                            $total_bs = $fila['total'] * $tasa_cambio_dolar_a_bs;
                        ?>
                            <tr>
                                <td><?php echo htmlspecialchars($fila['id']); ?></td>
                                <td><?php echo date('d/m/Y', strtotime($fila['fecha_venta'])); ?></td>
                                <td class="currency-col"><?php echo '$ ' . number_format($fila['total'], 2, ',', '.'); ?></td>
                                <td class="currency-col"><?php echo 'Bs ' . number_format($total_bs, 2, ',', '.'); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            <?php } else { ?>
                <div class="alert-message">
                    No se encontraron ventas registradas en la base de datos.
                </div>
            <?php } ?>
        </div>
    </main>
</body>
</html>