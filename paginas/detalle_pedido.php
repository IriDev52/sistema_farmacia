<?php
session_start();
include("../conexion/conex.php");

// 1. Verificación de seguridad
if (!isset($_SESSION['logeado']) || !isset($_GET['id'])) {
    header("Location: mis_pedidos.php");
    exit();
}

$id_venta = $_GET['id'];
$cedula = $_SESSION['usuario_cedula'];

// 2. Consulta a la base de datos para traer los datos reales del pedido
// Traemos la dirección que el usuario escribió al reportar el pago
$query = "SELECT v.*, u.nombre_completo 
          FROM ventas v 
          JOIN usuarios_client u ON v.cedula_cliente = u.cedula 
          WHERE v.id = '$id_venta' AND v.cedula_cliente = '$cedula'";

$resultado = mysqli_query($conn, $query);
$datos = mysqli_fetch_assoc($resultado);

// Si no existe el pedido o no pertenece al usuario, fuera.
if (!$datos) {
    header("Location: mis_pedidos.php");
    exit();
}

// Definimos la variable que causaba el error
$direccion_entrega = $datos['detalles_envio']; 
$estado = $datos['estado_pago'];

include("../recursos/header.php"); 
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
<link rel="stylesheet" href="../recursos/estilos_ecomerce.css">

<style>
    :root {
        --pc-accent: #4e5df8;
        --pc-success: #28a745;
        --pc-bg: #f4f7fe;
    }
    
    body { background-color: var(--pc-bg); }

    .invoice-card { 
        border: none; 
        border-radius: 24px; 
        background: white;
        box-shadow: 0 15px 35px rgba(26, 35, 58, 0.08);
    }

    /* Línea de tiempo mejorada */
    .status-timeline { 
        display: flex; 
        justify-content: space-between; 
        position: relative; 
        margin: 3rem 0;
        padding: 0 10px;
    }
    
    .status-timeline::before { 
        content: ''; 
        position: absolute; 
        top: 20px; 
        left: 5%; 
        right: 5%; 
        height: 3px; 
        background: #e9ecef; 
        z-index: 1; 
    }
    
    /* Progreso de la línea según el estado */
    .status-timeline.proceso-verificado::after {
        content: ''; position: absolute; top: 20px; left: 5%; width: 30%; height: 3px; 
        background: var(--pc-success); z-index: 1;
    }

    .step { 
        position: relative; 
        z-index: 2; 
        background: white; 
        width: 45px; 
        height: 45px; 
        border-radius: 50%; 
        border: 3px solid #e9ecef; 
        display: flex; 
        align-items: center; 
        justify-content: center;
        transition: all 0.4s ease;
    }

    .step.completed { border-color: var(--pc-success); background: var(--pc-success); color: white; }
    .step.active { border-color: var(--pc-accent); background: white; color: var(--pc-accent); box-shadow: 0 0 15px rgba(78, 93, 248, 0.3); }
    
    .label-step { font-size: 0.75rem; font-weight: 700; color: #adb5bd; margin-top: 10px; text-transform: uppercase; }
    .step.completed + .label-step { color: var(--pc-success); }
    .step.active + .label-step { color: var(--pc-accent); }

    .info-box {
        background: #f8faff;
        border: 1px solid #edf2f7;
        border-radius: 18px;
    }

    .btn-rounded { border-radius: 14px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

    @media print {
        .btn, .status-timeline, .no-print { display: none !important; }
        body { background: white; }
        .invoice-card { box-shadow: none; border: 1px solid #eee; }
    }
</style>

<div class="container mt-5 pt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card invoice-card p-4 p-md-5">
                
                <div class="text-center mb-5">
                    <?php if($estado == 'Verificado'): ?>
                        <div class="display-4 text-success mb-3"><i class="bi bi-patch-check-fill"></i></div>
                        <h2 class="fw-bold text-dark">¡Pago Verificado!</h2>
                        <p class="text-muted">Orden <span class="text-primary fw-bold">#<?php echo $id_venta; ?></span> • Hemos recibido tu reporte correctamente.</p>
                    <?php else: ?>
                        <div class="display-4 text-warning mb-3"><i class="bi bi-clock-history"></i></div>
                        <h2 class="fw-bold text-dark">Pago en Verificación</h2>
                        <p class="text-muted">Orden #<?php echo $id_venta; ?> • Estamos validando los fondos en nuestra cuenta.</p>
                    <?php endif; ?>
                </div>

                <div class="status-timeline <?php echo ($estado == 'Verificado') ? 'proceso-verificado' : ''; ?>">
                    <div class="text-center">
                        <div class="step completed"><i class="bi bi-credit-card"></i></div>
                        <div class="label-step">Pago</div>
                    </div>
                    <div class="text-center">
                        <div class="step <?php echo ($estado == 'Verificado') ? 'active' : ''; ?>">
                            <i class="bi bi-box-seam"></i>
                        </div>
                        <div class="label-step">Preparación</div>
                    </div>
                    <div class="text-center">
                        <div class="step"><i class="bi bi-truck"></i></div>
                        <div class="label-step">Despacho</div>
                    </div>
                    <div class="text-center">
                        <div class="step"><i class="bi bi-house-check"></i></div>
                        <div class="label-step">Entregado</div>
                    </div>
                </div>

                <div class="info-box p-4 mb-4">
                    <div class="row">
                        <div class="col-sm-7 border-end border-light">
                            <h6 class="text-muted small fw-bold text-uppercase mb-3"><i class="bi bi-geo-alt-fill me-1"></i> Dirección de Entrega</h6>
                            <p class="mb-0 fw-semibold text-dark">
                                <?php echo htmlspecialchars($direccion_entrega); ?>
                            </p>
                        </div>
                        <div class="col-sm-5 ps-md-4 mt-3 mt-sm-0 text-sm-end">
                            <h6 class="text-muted small fw-bold text-uppercase mb-3"><i class="bi bi-calendar-check me-1"></i> Entrega Estimada</h6>
                            <p class="mb-0 fw-bold text-success">
                                <?php echo ($estado == 'Verificado') ? 'Hoy antes de las 6:00 PM' : 'Pendiente por verificar'; ?>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 px-2">
                    <span class="text-muted small fw-bold text-uppercase">Total Pagado</span>
                    <span class="h4 mb-0 fw-bold text-dark">$<?php echo number_format($datos['total_usd'], 2); ?></span>
                </div>

                <div class="row g-3 no-print">
                    <div class="col-md-6">
                        <button onclick="window.print()" class="btn btn-outline-dark btn-rounded w-100 py-3">
                            <i class="bi bi-printer me-2"></i> Imprimir Recibo
                        </button>
                    </div>
                    <div class="col-md-6">
                        <a href="ecomerce.php" class="btn btn-buy btn-rounded w-100 py-3 shadow-sm">
                            Volver a la Tienda <i class="bi bi-arrow-right ms-2"></i>
                        </a>
                    </div>
                </div>
                
                <div class="text-center mt-4 no-print">
                    <p class="small text-muted">
                        ¿Tienes dudas? <a href="#" class="text-accent fw-bold text-decoration-none">Escríbenos por WhatsApp</a>
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>