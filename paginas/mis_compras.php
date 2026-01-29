<?php
session_start();
include("../conexion/conex.php");

if (!isset($_SESSION['logeado'])) {
    header("Location: ecomerce.php");
    exit();
}

$cedula = $_SESSION['usuario_cedula'];

// Consulta mejorada para obtener también la fecha y referencia
$res = mysqli_query($conn, "SELECT id, total_usd, estado_pago, fecha, referencia_pago 
                            FROM ventas 
                            WHERE cedula_cliente = '$cedula' 
                            ORDER BY id DESC");

include("../recursos/header.php"); 
?>

<style>
    body { background-color: #f8f9fa; padding-top: 80px; }
    .card-compras { border-radius: 15px; border: none; }
    .table thead { background-color: #007bff; color: white; }
    .badge-estado { padding: 8px 12px; border-radius: 50px; font-size: 0.85rem; }
</style>

<div class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-lg-10">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h2 class="fw-bold text-dark"><i class="fas fa-shopping-bag text-success me-2"></i> Mis Pedidos</h2>
                <a href="ecomerce.php" class="btn btn-outline-primary shadow-sm">
                    <i class="fas fa-plus me-1"></i> Nueva Compra
                </a>
            </div>

            <div class="card card-compras shadow-sm">
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4">ID Pedido</th>
                                    <th>Fecha</th>
                                    <th>Referencia</th>
                                    <th>Total Pagado</th>
                                    <th>Estado de Verificación</th>
                                    <th class="text-center">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(mysqli_num_rows($res) > 0): ?>
                                    <?php while($v = mysqli_fetch_assoc($res)): 
                                        // Lógica de colores según tu Paso 6
                                        if($v['estado_pago'] == 'Verificado'){
                                            $clase = 'bg-success';
                                            $icono = 'fa-check-circle';
                                        } elseif($v['estado_pago'] == 'Rechazado'){
                                            $clase = 'bg-danger';
                                            $icono = 'fa-times-circle';
                                        } else {
                                            $clase = 'bg-warning text-dark';
                                            $icono = 'fa-clock';
                                        }
                                    ?>
                                    <tr>
                                        <td class="ps-4 fw-bold text-primary">#<?php echo $v['id']; ?></td>
                                        <td><?php echo date('d/m/Y', strtotime($v['fecha'])); ?></td>
                                        <td>
                                            <small class="text-muted">
                                                <?php echo ($v['referencia_pago']) ? $v['referencia_pago'] : '---'; ?>
                                            </small>
                                        </td>
                                        <td class="fw-bold text-success">$<?php echo number_format($v['total_usd'], 2); ?></td>
                                        <td>
                                            <span class="badge badge-estado <?php echo $clase; ?>">
                                                <i class="fas <?php echo $icono; ?> me-1"></i>
                                                <?php echo strtoupper($v['estado_pago']); ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <button class="btn btn-sm btn-info text-white rounded-pill px-3" 
                                                    onclick="verDetalle(<?php echo $v['id']; ?>)">
                                                <i class="fas fa-eye me-1"></i> Detalle
                                            </button>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center py-5">
                                            <img src="../img/empty-cart.png" alt="vacío" style="width: 80px; opacity: 0.5;">
                                            <p class="mt-3 text-muted">Aún no has realizado compras.</p>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <div class="mt-4 p-3 bg-white rounded shadow-sm border-start border-4 border-info">
                <p class="mb-0 small text-muted">
                    <i class="fas fa-info-circle text-info me-2"></i>