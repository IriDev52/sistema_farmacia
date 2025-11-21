<?php
include("../recursos/header.php");
include("../conexion/conex.php");

/**
 * Obtiene la tasa USD del BCV usando el endpoint de DolarVzla y maneja la estructura JSON anidada.
 * Utiliza cURL para una conexión más robusta.
 * @return float La tasa USD. Retorna una tasa de respaldo si hay algún error.
 */
function obtenerTasaBCV_API_Anidada() {
    $url = 'https://api.dolarvzla.com/public/exchange-rate';
    $default_rate = 36.5; // Tasa de respaldo (DEFAULT_RATE)
    
    // --- 1. CONFIGURACIÓN E INICIO DE CÁPSULA (Implementación con cURL) ---
    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

    $response_json = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curl_error = curl_error($ch);
    curl_close($ch);
    // --- FIN CÁPSULA cURL ---

    // Manejo de Errores de Conexión/HTTP
    if ($response_json === FALSE || $curl_error || $http_code !== 200) {
        error_log("Error de conexión/HTTP ({$http_code}): {$curl_error}. Usando tasa predeterminada de {$default_rate}.");
        return $default_rate;
    }

    // --- 2. PROCESAMIENTO JSON ---
    $data = json_decode($response_json, true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        error_log("Error JSON: Respuesta de la API no válida. Usando tasa predeterminada.");
        return $default_rate;
    }

    if (isset($data['current']) && is_array($data['current']) && isset($data['current']['usd'])) {
        $rate_float = $data['current']['usd'];
        
        if (is_numeric($rate_float) && $rate_float > 0) {
            $rate = (float)$rate_float;
            error_log("Tasa de DolarVzla obtenida con éxito: {$rate}"); 
            return $rate;
        } else {
            error_log("La API devolvió un campo 'usd' no numérico o inválido. Usando tasa predeterminada.");
            return $default_rate;
        }
    } else {
        error_log("La API no devolvió la estructura anidada esperada (current.usd). Usando tasa predeterminada.");
        return $default_rate;
    }
}

// 1. OBTENER LA TASA DE CAMBIO
$tasa_bcv_usada = obtenerTasaBCV_API_Anidada(); 


// Consulta para verificar inventario
$query_check_products = "SELECT COUNT(*) AS total_products FROM productos WHERE stock_actual > 0";
$result_check_products = mysqli_query($conn, $query_check_products);
$row = mysqli_fetch_assoc($result_check_products);
$total_products_available = $row['total_products'];
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Registar Venta</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&display=swap" rel="stylesheet">
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    
    <style>
        :root {
            --bg-color: #f0f4f8;
            --card-bg: #ffffff;
            --primary-color: #007bff;
            --secondary-color: #6c757d;
            --text-dark: #212529;
            --text-muted: #6c757d;
            --border-color: #e0e6ec;
            --success-color: #28a745;
            --danger-color: #dc3545;
            --warning-color: #ffc107;
            --shadow-light: 0 4px 15px rgba(0, 0, 0, 0.05);
            --shadow-medium: 0 10px 30px rgba(0, 0, 0, 0.08);
        }

        body {
            background-color: var(--bg-color);
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            padding: 2rem 0;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        .container-fluid {
            max-width: 1200px;
        }

        .header-section {
            background-color: var(--card-bg);
            border-radius: 1.5rem;
            padding: 2rem;
            box-shadow: var(--shadow-medium);
            margin-bottom: 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1.5rem;
            border: 1px solid var(--border-color);
            animation: fadeInDown 0.8s ease-out;
        }
        
        @keyframes fadeInDown {
            from { opacity: 0; transform: translateY(-30px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .header-section h2 {
            font-weight: 800;
            color: var(--primary-color);
            margin: 0;
            font-size: 2.5rem;
        }
        
        .btn-elegant {
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 2rem;
            padding: 0.8rem 2rem;
            font-weight: 600;
            box-shadow: 0 4px 15px rgba(0, 123, 255, 0.3);
            transition: all 0.3s ease;
        }
        .btn-elegant:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0, 123, 255, 0.4);
            background-color: #0069d9;
        }
        .btn-elegant:active {
            transform: translateY(1px);
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }

        .main-content {
            display: grid;
            grid-template-columns: 2fr 1fr;
            gap: 2rem;
            animation: fadeInUp 1s ease-out;
        }

        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(30px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .section-card {
            background-color: var(--card-bg);
            border-radius: 1.5rem;
            box-shadow: var(--shadow-light);
            padding: 2rem;
            border: 1px solid var(--border-color);
        }
        
        .section-title {
            font-weight: 700;
            color: var(--text-dark);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1rem;
            font-size: 1.5rem;
        }
        
        .form-control-modern {
            border: 2px solid var(--border-color);
            border-radius: 1rem;
            padding: 1rem 1.5rem;
            font-size: 1rem;
            transition: all 0.3s ease;
            background-color: var(--bg-color);
            color: var(--text-dark);
        }
        .form-control-modern:focus {
            border-color: var(--primary-color);
            box-shadow: 0 0 0 0.25rem rgba(0, 123, 255, 0.25);
            background-color: #fff;
        }
        
        #sugerenciasProductos {
            position: absolute;
            z-index: 1000;
            width: calc(100% - 4rem);
            max-height: 300px;
            overflow-y: auto;
            background-color: var(--card-bg);
            border-radius: 1rem;
            box-shadow: var(--shadow-medium);
            margin-top: 0.5rem;
            border: 1px solid var(--border-color);
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            padding: 1rem;
        }
        
        .product-suggestion-card {
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
            border-radius: 0.8rem;
            padding: 1rem;
            cursor: pointer;
            transition: all 0.3s ease;
            width: calc(50% - 0.5rem);
            box-shadow: 0 2px 10px rgba(0,0,0,0.05);
        }
        .product-suggestion-card:hover {
            transform: translateY(-5px) scale(1.02);
            box-shadow: 0 8px 20px rgba(0,0,0,0.1);
            background-color: #eaf1f8;
        }
        .product-suggestion-card strong {
            font-weight: 700;
            color: var(--primary-color);
            font-size: 1.1rem;
        }
        .product-suggestion-card small {
            display: block;
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        .product-suggestion-card .stock-badge {
            font-size: 0.8rem;
            font-weight: 600;
            padding: 0.3rem 0.6rem;
            border-radius: 1rem;
        }

        .table-rounded {
            border-radius: 1rem;
            overflow: hidden;
            border: 1px solid var(--border-color);
        }
        .table-rounded thead th {
            background-color: var(--bg-color);
            border: none;
            color: var(--secondary-color);
            font-weight: 700;
            padding: 1rem;
            text-transform: uppercase;
            font-size: 0.8rem;
        }
        .table-rounded tbody td {
            padding: 1rem;
            vertical-align: middle;
            border-color: #f0f0f0;
            font-size: 0.9rem;
        }
        .cantidad-input {
            width: 80px;
            padding: 0.5rem;
            border-radius: 0.5rem;
            background-color: var(--bg-color);
            border: 1px solid var(--border-color);
        }
        
        /* Estilos específicos para la tasa de cambio */
        .tasa-bcv-info {
            font-size: 0.9rem;
            font-weight: 600;
            color: #ffc107; /* Color de advertencia o dorado */
            margin-bottom: 1.5rem;
            padding: 0.75rem 1.5rem;
            border-radius: 0.75rem;
            background-color: rgba(255, 193, 7, 0.1);
            border: 1px solid rgba(255, 193, 7, 0.3);
            display: inline-block;
        }

        .summary-card {
            background: linear-gradient(135deg, #1f2a40, #2c3e50);
            color: white;
            border-radius: 1.5rem;
            padding: 2.5rem;
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.25);
            text-align: center;
            position: sticky;
            top: 2rem;
            height: fit-content;
            animation: scaleIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes scaleIn {
            from { transform: scale(0.9); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }

        .summary-card .total-label {
            font-size: 1.5rem;
            font-weight: 600;
            color: rgba(255, 255, 255, 0.8);
            margin-bottom: 1rem;
        }
        .summary-card .total-amount {
            font-size: 4rem;
            font-weight: 900;
            color: #28a745;
            text-shadow: 0 0 15px rgba(40, 167, 69, 0.5);
            margin-bottom: 0.5rem; /* Reducido para meter el USD */
        }
        .summary-card .total-usd {
            font-size: 1.8rem;
            font-weight: 700;
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 2rem;
        }
        .btn-confirm-venta {
            width: 100%;
            background: linear-gradient(45deg, #28a745, #218838);
            border: none;
            border-radius: 2.5rem;
            padding: 1.2rem;
            font-size: 1.3rem;
            font-weight: 800;
            color: white;
            box-shadow: 0 10px 25px rgba(40, 167, 69, 0.4);
            transition: all 0.3s ease;
            text-transform: uppercase;
        }
        .btn-confirm-venta:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(40, 167, 69, 0.5);
            background: linear-gradient(45deg, #218838, #1e7e34);
        }
        .btn-confirm-venta:active {
            transform: translateY(1px);
            box-shadow: 0 5px 15px rgba(40, 167, 69, 0.4);
        }

        .modal-elegant .modal-content {
            border-radius: 1.5rem;
            background-color: var(--card-bg);
            box-shadow: var(--shadow-medium);
            border: none;
            padding: 1.5rem;
            animation: bounceIn 0.8s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        @keyframes bounceIn {
            0% { transform: scale(0.3); opacity: 0; }
            50% { transform: scale(1.1); opacity: 1; }
            70% { transform: scale(0.9); }
            100% { transform: scale(1); }
        }

        .modal-icon-container {
            width: 100px;
            height: 100px;
            margin: 0 auto 1.5rem;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            font-size: 3.5rem;
            box-shadow: inset 0 0 10px rgba(0,0,0,0.1);
        }

        .modal-icon-container.success { background-color: #d4edda; color: #155724; }
        .modal-icon-container.warning { background-color: #fff3cd; color: #856404; }
        .modal-icon-container.danger { background-color: #f8d7da; color: #721c24; }
        
        .inventory-empty-card {
            background-color: var(--card-bg);
            border-radius: 1.5rem;
            padding: 4rem 2rem;
            box-shadow: var(--shadow-medium);
            border: 1px solid var(--border-color);
            animation: fadeIn 1s cubic-bezier(0.25, 0.46, 0.45, 0.94) forwards;
            animation-delay: 0.6s;
            opacity: 0;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }
    </style>
</head>
<body>

<div class="container-fluid py-5 px-lg-5">
    <header class="header-section">
        <h2 class="text-uppercase"><i class="bi bi-shop-window me-3"></i>Punto de Venta</h2>
        <a href="../paginas/inicio.php" class="btn btn-elegant">
            <i class="bi bi-house-door-fill me-2"></i>Regresar a Inicio
        </a>
    </header>

    <?php if ($total_products_available <= 0): ?>
        <div class="row justify-content-center mt-5">
            <div class="col-lg-8 col-md-10">
                <div class="inventory-empty-card text-center">
                    <div class="card-body">
                        <i class="bi bi-box-seam-fill text-muted mb-4" style="font-size: 6rem;"></i>
                        <h3 class="card-title fw-bold text-dark mb-3">¡Inventario Agotado para Venta!</h3>
                        <p class="card-text fs-6 text-muted px-4">
                            En este momento, no hay productos disponibles en el inventario para ser vendidos.
                            Por favor, registre nuevas entradas de stock o verifique el inventario actual.
                        </p>
                        <a href="inventario_consulta.php" class="btn btn-primary btn-lg mt-4 rounded-pill px-5 py-3 fw-bold shadow-sm">
                            <i class="bi bi-box-arrow-in-right me-2"></i>Ir a Gestionar Inventario
                        </a>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div class="main-content">
            <div class="left-column">
                <div class="section-card mb-4">
                    <h4 class="section-title"><i class="bi bi-search"></i>Buscar y Agregar Producto</h4>
                    <div class="mb-4 position-relative">
                        <label for="buscarProducto" class="form-label text-muted fw-bold">Escribe el nombre o código</label>
                        <input type="text" class="form-control form-control-modern" id="buscarProducto" placeholder="Ej: Acetaminofén, Código 123...">
                        <div id="sugerenciasProductos"></div>
                    </div>
                </div>

                <div class="section-card">
                    <h4 class="section-title"><i class="bi bi-cart-fill"></i>Detalles del Carrito</h4>
                    <div class="tasa-bcv-info">
                        <i class="bi bi-currency-exchange me-2"></i>
                        Tasa BCV utilizada: <strong>Bs. <?php echo number_format($tasa_bcv_usada, 4, ',', '.'); ?></strong> por $1.00 USD
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover table-striped align-middle table-rounded">
                            <thead>
                                <tr>
                                    <th scope="col" class="text-start">Producto</th>
                                    <th scope="col" class="text-center">Precio (USD/Bs)</th>
                                    <th scope="col" class="text-center" style="width: 120px;">Cantidad</th>
                                    <th scope="col" class="text-center">Subtotal (USD/Bs)</th>
                                    <th scope="col" class="text-center" style="width: 70px;"></th>
                                </tr>
                            </thead>
                            <tbody id="productosVenta">
                                <tr>
                                    <td colspan="5" class="text-center text-muted fst-italic py-4">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Utiliza el buscador para añadir productos.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <input type="hidden" id="tasaBcvInput" value="<?php echo $tasa_bcv_usada; ?>">
                </div>
            </div>

            <div class="right-column">
                <div class="summary-card">
                    <form id="formVenta">
                        <input type="hidden" name="tasa_bcv_usada" value="<?php echo $tasa_bcv_usada; ?>"> 

                        <h4 class="total-label text-uppercase">Total a Pagar (Bs.)</h4>
                        <div class="total-amount" id="totalVentaBs">Bs. 0.00</div>
                        <h4 class="total-label text-uppercase mt-4">Total a Pagar (USD)</h4>
                        <div class="total-usd" id="totalVentaUsd">$ 0.00</div>
                        <button type="submit" class="btn btn-confirm-venta">
                            <i class="bi bi-cash-coin me-2"></i> Confirmar Venta
                        </button>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<div class="modal fade" id="confirmationModal" tabindex="-1" aria-labelledby="confirmationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-elegant">
    <div class="modal-content">
      <div class="modal-header justify-content-center border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body text-center p-4">
            <div class="modal-icon-container success">
                <i class="bi bi-check-circle-fill"></i>
            </div>
            <h4 class="text-success fw-bold mt-3 mb-2" style="font-size: 1.8rem;">¡Venta Exitosa!</h4>
            <p class="text-muted fs-6 px-3">
                La transacción ha sido registrada y el inventario actualizado.
            </p>
        </div>
        <div class="modal-footer justify-content-center gap-2 border-0 pt-0">
            <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
                <i class="bi bi-x-circle me-2"></i>Cerrar
            </button>
            <a href="#" id="viewInvoiceBtn" class="btn btn-primary rounded-pill px-4 fw-bold">
                <i class="bi bi-file-earmark-pdf me-2"></i>Ver Factura
            </a>
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="stockWarningModal" tabindex="-1" aria-labelledby="stockWarningModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-elegant">
    <div class="modal-content">
      <div class="modal-header justify-content-center border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <div class="modal-icon-container warning">
            <i class="bi bi-exclamation-triangle-fill"></i>
        </div>
        <h4 class="text-warning fw-bold mt-3 mb-2" style="font-size: 1.8rem;">Stock Insuficiente</h4>
        <p class="text-muted fs-6 px-3" id="stockWarningMessage"></p>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <button type="button" class="btn btn-warning rounded-pill px-4" data-bs-dismiss="modal">
            <i class="bi bi-check-circle me-2"></i>Entendido
        </button>
        </div>
    </div>
  </div>
</div>

<div class="modal fade" id="emptyCartModal" tabindex="-1" aria-labelledby="emptyCartModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered modal-elegant">
    <div class="modal-content">
      <div class="modal-header justify-content-center border-0 pb-0">
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center p-4">
        <div class="modal-icon-container danger">
            <i class="bi bi-emoji-frown-fill"></i>
        </div>
        <h4 class="text-danger fw-bold mt-3 mb-2" style="font-size: 1.8rem;">¡Carrito de Venta Vacío!</h4>
        <p class="text-muted fs-6 px-3">
            Para confirmar la venta, por favor, agregue al menos un producto al carrito.
        </p>
      </div>
      <div class="modal-footer justify-content-center border-0 pt-0">
        <button type="button" class="btn btn-secondary rounded-pill px-4" data-bs-dismiss="modal">
            <i class="bi bi-x-circle me-2"></i>Cerrar
        </button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarProductoInput = document.getElementById('buscarProducto');
    const sugerenciasProductosDiv = document.getElementById('sugerenciasProductos');
    const productosVentaBody = document.getElementById('productosVenta');
    const totalVentaBsSpan = document.getElementById('totalVentaBs'); // Nuevo ID
    const totalVentaUsdSpan = document.getElementById('totalVentaUsd'); // Nuevo ID
    const tasaBcvInput = document.getElementById('tasaBcvInput');

    const formVenta = document.getElementById('formVenta');
    const confirmationModal = new bootstrap.Modal(document.getElementById('confirmationModal'));
    const stockWarningModal = new bootstrap.Modal(document.getElementById('stockWarningModal'));
    const emptyCartModal = new bootstrap.Modal(document.getElementById('emptyCartModal'));
    const stockWarningMessage = document.getElementById('stockWarningMessage');
    const viewInvoiceBtn = document.getElementById('viewInvoiceBtn');
    
    // Obtener la tasa de cambio desde el campo PHP
    const TASA_BCV = parseFloat(tasaBcvInput.value) || 1.0; 

    let carrito = [];

   function actualizarTotal() {
    let totalBs = 0;
    let totalUsd = 0;

    carrito.forEach(producto => {
        const subtotalBs = producto.cantidad * producto.precio_unitario * TASA_BCV;
        const subtotalUsd = producto.cantidad * producto.precio_unitario;

        totalBs += subtotalBs;
        totalUsd += subtotalUsd;
    });

    totalVentaBsSpan.textContent = `Bs. ${totalBs.toFixed(2).replace('.', ',')}`;
    totalVentaUsdSpan.textContent = `$ ${totalUsd.toFixed(2).replace('.', ',')}`;
}


    function renderizarCarrito() {
        productosVentaBody.innerHTML = '';
        if (carrito.length === 0) {
            productosVentaBody.innerHTML = `
                <tr>
                    <td colspan="5" class="text-center text-muted fst-italic py-4">
                        <i class="bi bi-info-circle me-1"></i>
                        Utiliza el buscador para añadir productos.
                    </td>
                </tr>
            `;
        } else {
            carrito.forEach((producto, index) => {
    const precioUsd = producto.precio_unitario;
    const precioBs = producto.precio_unitario * TASA_BCV;
    const subtotalUsd = producto.cantidad * producto.precio_unitario;
    const subtotalBs = producto.cantidad * producto.precio_unitario * TASA_BCV;

    const row = productosVentaBody.insertRow();
    row.innerHTML = `
        <td>${producto.nombre}</td>
        <td class="text-center">
            <small class="text-muted d-block">$ ${precioUsd.toFixed(2)}</small>
            <span class="fw-bold">Bs. ${precioBs.toFixed(2).replace('.', ',')}</span>
        </td>
        <td class="text-center">
            <input type="number" class="form-control text-center cantidad-input" min="1" value="${producto.cantidad}" data-index="${index}">
        </td>
        <td class="fw-bold text-center">
            <small class="text-muted d-block">$ ${subtotalUsd.toFixed(2).replace('.', ',')}</small>
            <span class="text-dark">Bs. ${subtotalBs.toFixed(2).replace('.', ',')}</span>
        </td>
        <td class="text-center">
            <button type="button" class="btn btn-danger btn-sm eliminar-producto" data-index="${index}" title="Eliminar producto">
                <i class="bi bi-trash"></i>
            </button>
        </td>
    `;
});

        }
        actualizarTotal();
    }

   buscarProductoInput.addEventListener('input', function() {
    const query = this.value.trim();
    sugerenciasProductosDiv.innerHTML = '';

    if (query.length > 2) {
        console.log('Buscando productos con query:', query);
        
        fetch(`productos_api.php?query=${encodeURIComponent(query)}`)
            .then(response => {
                console.log('Status búsqueda:', response.status);
                
                // Primero obtener como texto para debug
                return response.text().then(text => {
                    console.log('Respuesta búsqueda:', text);
                    
                    try {
                        const jsonData = JSON.parse(text);
                        return jsonData;
                    } catch (e) {
                        console.error('Error parseando JSON de búsqueda:', e);
                        console.error('Respuesta cruda:', text);
                        throw new Error('Respuesta de búsqueda no es JSON válido');
                    }
                });
            })
            .then(data => {
                if (data.error) {
                    console.error('Error en datos de búsqueda:', data.error);
                    return;
                }
                
                console.log('Productos encontrados:', data.length);
                
                if (data.length > 0) {
                    data.forEach(producto => {
                        const item = document.createElement('div');
                        item.classList.add('product-suggestion-card');
                        const precioUsd = (parseFloat(producto.precio_venta) / TASA_BCV).toFixed(2);
                        item.innerHTML = `
                            <strong>${producto.nombre_producto}</strong>
                            <small>${producto.laboratorio_fabrica || 'Sin laboratorio'}</small>
                            <span class="badge text-bg-info mt-2 stock-badge me-2">Precio: $ ${precioUsd}</span>
                            <span class="badge text-bg-secondary mt-2 stock-badge">Stock: ${producto.stock_actual}</span>
                        `;
                        
                        item.addEventListener('click', function() {
                            const existe = carrito.find(p => p.id === producto.id);
                            if (existe) {
                                if (parseInt(existe.cantidad) + 1 > parseInt(producto.stock_actual)) {
                                    stockWarningMessage.innerHTML = `No hay suficiente stock para añadir más de <strong>${producto.nombre_producto}</strong>. <br> Stock disponible: <strong>${producto.stock_actual}</strong>`;
                                    stockWarningModal.show();
                                    return;
                                }
                                existe.cantidad++;
                            } else {
                                if (parseInt(producto.stock_actual) <= 0) {
                                    stockWarningMessage.innerHTML = `El producto <strong>${producto.nombre_producto}</strong> no tiene stock disponible.`;
                                    stockWarningModal.show();
                                    return;
                                }
                                carrito.push({
                                    id: producto.id,
                                    nombre: producto.nombre_producto,
                                    precio_unitario: parseFloat(producto.precio_venta),
                                    cantidad: 1,
                                    stock_disponible: parseInt(producto.stock_actual)
                                });
                            }
                            renderizarCarrito();
                            buscarProductoInput.value = '';
                            sugerenciasProductosDiv.innerHTML = '';
                        });
                        sugerenciasProductosDiv.appendChild(item);
                    });
                } else {
                    const noResults = document.createElement('div');
                    noResults.classList.add('text-muted', 'py-3', 'text-center');
                    noResults.textContent = 'No se encontraron productos que coincidan con la búsqueda.';
                    sugerenciasProductosDiv.appendChild(noResults);
                }
            })
            .catch(error => {
                console.error('Error completo en búsqueda:', error);
                const errorDiv = document.createElement('div');
                errorDiv.classList.add('text-danger', 'py-2', 'text-center');
                errorDiv.textContent = 'Error al cargar productos';
                sugerenciasProductosDiv.appendChild(errorDiv);
            });
    } else {
        sugerenciasProductosDiv.innerHTML = '';
    }
});

    productosVentaBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            const index = parseInt(e.target.dataset.index);
            let nuevaCantidad = parseInt(e.target.value);

            if (isNaN(nuevaCantidad) || nuevaCantidad < 1) {
                stockWarningMessage.innerHTML = 'La cantidad debe ser un número entero positivo.';
                stockWarningModal.show();
                e.target.value = carrito[index].cantidad;
                return;
            }

            if (nuevaCantidad > carrito[index].stock_disponible) {
                stockWarningMessage.innerHTML = `No hay suficiente stock para la cantidad solicitada de <strong>${carrito[index].nombre}</strong>. <br> Stock disponible: <strong>${carrito[index].stock_disponible}</strong>`;
                stockWarningModal.show();
                e.target.value = carrito[index].cantidad;
                return;
            }
            carrito[index].cantidad = nuevaCantidad;
            renderizarCarrito();
        }
    });

    productosVentaBody.addEventListener('click', function(e) {
        if (e.target.closest('.eliminar-producto')) {
            const index = parseInt(e.target.closest('.eliminar-producto').dataset.index);
            carrito.splice(index, 1);
            renderizarCarrito();
        }
    });

    formVenta.addEventListener('submit', function(e) {
    e.preventDefault();

    if (carrito.length === 0) {
        emptyCartModal.show();
        return;
    }
    
    const dataToSend = {
        productos: carrito,
        tasa_bcv_usada: TASA_BCV
    };

    console.log('Datos enviados a registrar_venta.php:', dataToSend);

    fetch('registrar_venta.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify(dataToSend)
    })  
    .then(response => {
        console.log('Status HTTP:', response.status);
        console.log('Content-Type:', response.headers.get('content-type'));
        
        // PRIMERO obtener como texto para ver la respuesta CRUDA
        return response.text().then(text => {
            console.log('=== RESPUESTA CRUDA DEL SERVIDOR ===');
            console.log('Respuesta completa:', text);
            console.log('Longitud total:', text.length);
            
            // Mostrar caracteres problemáticos
            if (text.length > 70) {
                console.log('Caracteres alrededor de la posición 73:');
                console.log('Posición 60-70:', JSON.stringify(text.substring(60, 70)));
                console.log('Posición 70-80:', JSON.stringify(text.substring(70, 80)));
                console.log('Posición 80-90:', JSON.stringify(text.substring(80, 90)));
            }
            
            // Mostrar primeros 100 caracteres
            console.log('Primeros 100 caracteres:', text.substring(0, 100));
            
            // Mostrar últimos 50 caracteres (por si hay algo al final)
            if (text.length > 50) {
                console.log('Últimos 50 caracteres:', text.substring(text.length - 50));
            }
            
            // Ahora intentar parsear como JSON
            try {
                const jsonData = JSON.parse(text);
                console.log('JSON parseado exitosamente:', jsonData);
                return jsonData;
            } catch (parseError) {
                console.error('=== ERROR AL PARSEAR JSON ===');
                console.error('Mensaje de error:', parseError.message);
                
                // Mostrar caracteres invisibles
                console.log('Caracteres invisibles detectados:');
                for (let i = 0; i < Math.min(text.length, 100); i++) {
                    const char = text[i];
                    const charCode = char.charCodeAt(0);
                    if (charCode < 32 || charCode > 126) {
                        console.log(`Posición ${i}: código ${charCode} (caracter no imprimible)`);
                    }
                }
                
                throw new Error(`JSON inválido: ${parseError.message}. Respuesta cruda: "${text.substring(0, 100)}..."`);
            }
        });
    })
    .then(data => {
        if (data.success) {
            console.log('Venta registrada exitosamente. ID:', data.id_venta);
            confirmationModal.show();
            
            if (data.id_venta) {
                viewInvoiceBtn.href = `vista_previa_factura.php?id=${data.id_venta}`;
                viewInvoiceBtn.style.display = 'inline-block';
            } else {
                viewInvoiceBtn.style.display = 'none';
            }

            carrito = [];
            renderizarCarrito();

        } else {
            console.error('Error del servidor:', data.message);
            alert('Error al registrar venta: ' + data.message);
        }
    })
    .catch(error => {
        console.error('=== ERROR COMPLETO EN EL PROCESO ===');
        console.error('Error:', error);
        console.error('Stack:', error.stack);
        
        // Mensaje más informativo para el usuario
        let errorMessage = 'Error en el proceso de venta: ';
        
        if (error.message.includes('JSON inválido')) {
            errorMessage += 'El servidor devolvió una respuesta no válida. ';
            errorMessage += 'Por favor, verifica la consola para más detalles.';
        } else if (error.message.includes('Failed to fetch')) {
            errorMessage += 'No se pudo conectar con el servidor. ';
            errorMessage += 'Verifica tu conexión a internet.';
        } else {
            errorMessage += error.message;
        }
        
        alert(errorMessage);
    });
});
    
    renderizarCarrito();
});
</script>
</body>
</html>
