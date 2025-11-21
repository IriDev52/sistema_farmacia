<?php
include("../recursos/header.php");
include('../conexion/conex.php');
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario'] === null) {
    header("Location: ../index.php");
    exit();
}

$api_url = 'https://pydolarvenezuela-api.vercel.app/api/v1/dollar/bcv';
$cache_file = '../cache/tasa_bcv_cache.txt';
$cache_duration = 4 * 3600;
$fallback_rate = 110;

$tasa_cambio_dolar_a_bs = $fallback_rate;
if (file_exists($cache_file) && (time() - filemtime($cache_file)) < $cache_duration) {
    $tasa_cacheada = file_get_contents($cache_file);
    if ($tasa_cacheada !== false && is_numeric($tasa_cacheada)) {
        $tasa_cambio_dolar_a_bs = (float)$tasa_cacheada;
    }
} else {
    if (!is_dir('../cache')) {
        mkdir('../cache', 0755, true);
    }
    $json_data = @file_get_contents($api_url);

    if ($json_data !== false) {
        $data = json_decode($json_data, true);
        
        if (isset($data['price']) && is_numeric($data['price'])) {
            $tasa_obtenida = (float)$data['price'];
            $tasa_cambio_dolar_a_bs = $tasa_obtenida;
            file_put_contents($cache_file, $tasa_obtenida);
        }
    }
}

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
    <title>Reporte de Ventas - Farmacia Barrancas</title>
    <link rel="icon" href="../recursos/img/favicon-pharmacy.ico" type="image/x-icon">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@4.2.0/fonts/remixicon.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary-dark: #2c3e50;
            --primary-light: #34495e;
            --accent-green: #2ecc71;
            --accent-green-dark: #27ae60;
            --background-light: #f4f7f6;
            --card-bg: #ffffff;
            --text-color: #333333;
            --border-color: #e0e0e0;
            --shadow-light: 0 4px 15px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 8px 25px rgba(0, 0, 0, 0.1);
            --error-red: #e74c3c;
            --info-blue: #3498db;
            --return-button-bg: #6c757d; 
            --return-button-hover: #5a6268;
        }

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 0;
            background-color: var(--background-light);
            color: var(--text-color);
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .header {
            background-color: var(--primary-dark);
            color: var(--primary-light);
            padding: 1rem 3rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: var(--shadow-medium);
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
            font-size: 2.2rem;
            color: var(--accent-green);
        }

        .header-logo h2 {
            font-weight: 700;
            font-size: 1.6rem;
            margin: 0;
            color: var(--card-bg);
        }

        .header-nav {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            gap: 1.8rem;
        }

        .header-nav-link {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 0.7rem 1.2rem;
            text-decoration: none;
            color: var(--primary-light);
            font-weight: 500;
            font-size: 0.95rem;
            border-radius: 8px;
            transition: all 0.3s ease;
        }

        .header-nav-link i {
            font-size: 1.1rem;
        }

        .header-nav-link:hover, .header-nav-link.active {
            background-color: var(--accent-green);
            color: var(--card-bg);
            box-shadow: 0 4px 10px rgba(46, 204, 113, 0.3);
        }

        .user-actions {
            display: flex;
            align-items: center;
            gap: 1rem;
        }

        /* Estilo para el botón de regresar/volver */
        .return-btn {
            background-color: var(--return-button-bg);
            color: white;
            border: 1px solid var(--return-button-bg);
            padding: 0.7rem 1.5rem;
            border-radius: 50px;
            text-decoration: none;
            font-weight: 600;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .return-btn:hover {
            background-color: var(--return-button-hover);
            border-color: var(--return-button-hover);
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
        }

        .main-content {
            flex-grow: 1;
            padding: 2.5rem 2rem;
            max-width: 1300px;
            margin: 0 auto;
            width: 100%;
        }

        .report-header {
            margin-bottom: 2.5rem;
            text-align: center;
            position: relative;
        }

        .report-header h1 {
            font-weight: 700;
            font-size: 3rem;
            margin: 0;
            color: var(--primary-dark);
            position: relative;
            display: inline-block;
            letter-spacing: -0.05em;
        }

        .report-header h1::after {
            content: '';
            position: absolute;
            left: 50%;
            bottom: -15px;
            transform: translateX(-50%);
            width: 100px;
            height: 6px;
            background-color: var(--accent-green);
            border-radius: 5px;
        }

        .report-description {
            font-size: 1.1rem;
            color: #6c757d;
            margin-top: 2rem;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
        }

        .tasa-info-card {
            background-color: var(--info-blue);
            color: var(--card-bg);
            padding: 1rem 1.5rem;
            border-radius: 10px;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-top: 1.5rem;
            font-size: 1.1rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(52, 152, 219, 0.3);
        }

        .tasa-info-card i {
            font-size: 1.5rem;
        }

        .report-container {
            background-color: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow-light);
            padding: 2.5rem;
            overflow-x: auto;
            margin-top: 2.5rem;
        }

        .data-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            margin-top: 1.5rem;
            min-width: 600px;
        }

        .data-table th, .data-table td {
            padding: 1.2rem 1.5rem;
            text-align: left;
            border-bottom: 1px solid var(--border-color);
        }

        .data-table th {
            background-color: var(--background-light);
            color: var(--primary-dark);
            font-weight: 600;
            font-size: 0.9rem;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }

        .data-table th:first-child {
            border-top-left-radius: 0.75rem;
        }
        .data-table th:last-child {
            border-top-right-radius: 0.75rem;
        }

        .data-table .currency-col {
            text-align: right;
            font-weight: 500;
        }

        .data-table tbody tr:hover {
            background-color: #f0f4f7;
            transition: background-color 0.2s ease;
        }

        .data-table tbody tr:last-child td {
            border-bottom: none;
        }

        .data-table tbody tr:last-child td:first-child {
            border-bottom-left-radius: 0.75rem;
        }
        .data-table tbody tr:last-child td:last-child {
            border-bottom-right-radius: 0.75rem;
        }

        .alert-message {
            padding: 1.5rem;
            background-color: #fcebeb;
            color: var(--error-red);
            border: 1px solid #f5c6cb;
            border-radius: 0.75rem;
            text-align: center;
            font-weight: 600;
            margin-top: 1.5rem;
        }

        @media (max-width: 992px) {
            .header {
                flex-direction: column;
                gap: 1rem;
                padding: 1rem;
                text-align: center;
            }
            .header-nav {
                flex-wrap: wrap;
                justify-content: center;
                gap: 0.8rem;
            }
            .header-logo h2 {
                font-size: 1.4rem;
            }
            .header-nav-link {
                padding: 0.6rem 1rem;
                font-size: 0.9rem;
            }
            .user-actions {
                margin-top: 1rem;
                justify-content: center;
            }
            .return-btn {
                padding: 0.7rem 1.2rem;
                font-size: 0.9rem;
            }
            .main-content {
                padding: 2rem 1rem;
            }
            .report-header h1 {
                font-size: 2.2rem;
            }
            .report-description {
                font-size: 1rem;
            }
            .tasa-info-card {
                font-size: 1rem;
                padding: 0.8rem 1.2rem;
            }
            .report-container {
                padding: 1.5rem;
            }
            .data-table th, .data-table td {
                padding: 1rem;
                font-size: 0.9rem;
            }
        }

        @media (max-width: 576px) {
            .header {
                padding: 1rem 0.5rem;
            }
            .header-logo {
                flex-direction: column;
                gap: 5px;
            }
            .header-logo h2 {
                font-size: 1.2rem;
            }
            .header-nav {
                gap: 0.5rem;
            }
            .header-nav-link {
                font-size: 0.85rem;
                gap: 5px;
            }
            .report-header h1 {
                font-size: 1.8rem;
            }
            .report-header h1::after {
                bottom: -10px;
                width: 70px;
                height: 4px;
            }
        }
    </style>
</head>
<body>
    <header class="header">
        <div class="header-logo">
            <i class="ri-hospital-line"></i>
            <h2>Farmacia Barrancas</h2>
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
            <a href="inicio.php" class="return-btn">
                <i class="ri-arrow-left-line"></i> Regresar al inicio
            </a>
        </div>
    </header>
    <main class="main-content">
        <header class="report-header">
            <h1>Reporte de Ventas</h1>
            <p class="report-description">Aquí puedes visualizar un resumen detallado de todas las transacciones de ventas registradas en tu sistema.</p>
            <div class="tasa-info-card">
                <i class="ri-money-dollar-circle-line"></i>
                <span>Tasa BCV: $1 USD = Bs <?php echo number_format($tasa_cambio_dolar_a_bs, 2, ',', '.'); ?></span>
            </div>
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