<?php
session_start();
include("../conexion/conex.php");

if (!isset($_SESSION['logeado'])) {
    header("Location: ecomerce.php");
    exit();
}

$cedula = $_SESSION['usuario_cedula'];

// Consulta optimizada
$res = mysqli_query($conn, "SELECT id, total_usd, estado_pago, fecha, referencia_pago 
                            FROM ventas 
                            WHERE cedula_cliente = '$cedula' 
                            ORDER BY id DESC");

include("../recursos/header.php"); 
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --pc-accent: #4e5df8;
        --pc-bg: #f4f7fe;
    }
    body { background-color: var(--pc-bg); }
    
    .card-compras { 
        border-radius: 20px; 
        border: none; 
        box-shadow: 0 10px 30px rgba(0,0,0,0.05);
        background: white;
    }
    
    .table thead { background-color: #1a233a; color: white; }
    .table thead th { border: none; padding: 15px; font-size: 0.8rem; text-transform: uppercase; }
    
    .badge-estado { 
        padding: 6px 14px; 
        border-radius: 10px; 
        font-weight: 600; 
        font-size: 0.75rem;
        letter-spacing: 0.5px;
    }

    .btn-detalle {
        border-radius: 10px;
        font-weight: 600;
        transition: 0.3s;
        border: 1px solid #e0e6ed;
        background: white;
        color: var(--pc-accent);
    }

    .btn-detalle:hover {
        background: var(--pc-accent);
        color: white;
        transform: translateY(-2px);
    }

    .empty-state { padding: 80px 0; text-align: center; }
</style>

<div class="container mt-5 pt-4">
    <div class="row justify-content-center">
        <div class="col-lg-11">
            
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold text-dark mb-1">Mis Pedidos</h2>
                    <p class="text-muted small">Rastrea tus compras y estados de pago</p>
                </div>
                <a href="ecomerce.php" class="btn btn-primary px-4 rounded-pill shadow-sm" style="background-color: var(--pc-accent); border: none;">
                    <i class="bi bi-cart-plus me-2"></i>Nueva Compra
                </a>
            </div>

            <div class="card card-compras overflow-hidden">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID Pedido</th>
                                    <th>Fecha de Compra</th>
                                    <th>Referencia</th>
                                    <th>Monto Total</th>
                                    <th>Estado Actual</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($res) > 0): ?>
                                    <?php while($v = mysqli_fetch_assoc($res)): 
                                        // Definición de estilos por estado
                                        switch($v['estado_pago']) {
                                            case 'Verificado':
                                                $bg = 'rgba(40, 167, 69, 0.1)';
                                                $color = '#28a745';
                                                $icon = 'bi-check-circle-fill';
                                                break;
                                            case 'Rechazado':
                                                $bg = 'rgba(220, 53, 69, 0.1)';
                                                $color = '#dc3545';
                                                $icon = 'bi-x-circle-fill';
                                                break;
                                            default: // Pendiente
                                                $bg = 'rgba(255, 193, 7, 0.1)';
                                                $color = '#856404';
                                                $icon = 'bi-hourglass-split';
                                                break;
                                        }
                                    ?>
                                    <tr>
                                        <td class="ps-4">
                                            <span class="fw-bold text-dark">#ORD-<?php echo $v['id']; ?></span>
                                        </td>
                                        <td>
                                            <span class="text-muted small">
                                                <i class="bi bi-calendar3 me-1"></i><?php echo date('d/m/Y', strtotime($v['fecha'])); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <code class="text-primary fw-bold"><?php echo ($v['referencia_pago']) ? $v['referencia_pago'] : '---'; ?></code>
                                        </td>
                                        <td>
                                            <span class="fw-bold text-dark">$<?php echo number_format($v['total_usd'], 2); ?></span>
                                        </td>
                                        <td>
                                            <span class="badge-estado" style="background: <?php echo $bg; ?>; color: <?php echo $color; ?>;">
                                                <i class="bi <?php echo $icon; ?> me-1"></i>
                                                <?php echo strtoupper($v['estado_pago']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <a href="detalle_pedido.php?id=<?php echo $v['id']; ?>" 
                                               class="btn btn-sm btn-detalle px-3">
                                                <i class="bi bi-receipt me-1"></i> Ver Detalle
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="empty-state">
                                            <i class="bi bi-bag-x text-muted opacity-25" style="font-size: 4rem;"></i>
                                            <h5 class="mt-3 text-muted">Aún no tienes historial de pedidos</h5>
                                            <p class="small text-muted">Tus compras aparecerán aquí una vez las realices.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-white rounded-4 shadow-sm border-start border-4 border-info d-flex align-items-center">
                <i class="bi bi-info-circle-fill text-info fs-4 me-3"></i>
                <p class="mb-0 small text-muted">
                    <strong>¿Qué sigue?</strong> Si tu pedido aparece como <b>VERIFICADO</b>, puedes hacer clic en "Ver Detalle" para descargar tu factura digital y ver el tiempo estimado de entrega.
                </p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>