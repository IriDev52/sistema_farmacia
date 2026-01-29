<?php
session_start();
include("../conexion/conex.php");

// --- LÓGICA DE PROCESAMIENTO (Pasos 5, 6 y 7) ---
if (isset($_POST['accion'])) {
    $id_venta = $_POST['id_venta'];
    
    if ($_POST['accion'] == 'aprobar') {
        $conn->begin_transaction();
        try {
            // 1. Paso 6: Marcar como Verificado
            $conn->query("UPDATE ventas SET estado_pago = 'Verificado' WHERE id = $id_venta");

            // 2. Paso 7: Descontar Inventario
            $productos = $conn->query("SELECT id_producto, cantidad FROM detalle_venta WHERE id_venta = $id_venta");
            while ($item = $productos->fetch_assoc()) {
                $id_p = $item['id_producto'];
                $cant = $item['cantidad'];
                $conn->query("UPDATE productos SET stock_actual = stock_actual - $cant WHERE id = $id_p");
            }

            $conn->commit();
            $mensaje = "✅ Venta #$id_venta aprobada e inventario actualizado.";
            $tipo_mensaje = "success";
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "❌ Error: " . $e->getMessage();
            $tipo_mensaje = "danger";
        }
    } 
    
    if ($_POST['accion'] == 'rechazar') {
        // Paso 6 (Bifurcación): Marcar como Rechazado (No descuenta stock)
        $conn->query("UPDATE ventas SET estado_pago = 'Rechazado' WHERE id = $id_venta");
        $mensaje = "🚫 Venta #$id_venta rechazada correctamente.";
        $tipo_mensaje = "warning";
    }
}

// Consulta de ventas pendientes
$sql = "SELECT v.*, u.nombre_completo, u.telefono 
        FROM ventas v 
        JOIN usuarios_client u ON v.cedula_cliente = u.cedula 
        WHERE v.estado_pago = 'Pendiente' 
        ORDER BY v.id DESC";
$resultado = $conn->query($sql);

include("../recursos/header.php");
?>

<style>
    body { background-color: #f4f7f6; }
    .card-admin { border: none; border-radius: 15px; box-shadow: 0 4px 20px rgba(0,0,0,0.08); }
    .table thead { background: #2c3e50; color: white; }
    .status-badge { font-size: 0.85rem; padding: 0.5em 1em; border-radius: 50px; }
    .btn-action { transition: all 0.3s; border-radius: 8px; font-weight: 600; }
    .btn-action:hover { transform: scale(1.05); }
</style>

<div class="container mt-5">
    <div class="row mb-4">
        <div class="col-md-8">
            <h2 class="fw-bold text-dark"><i class="fas fa-clipboard-check text-primary me-2"></i> Verificación de Pagos</h2>
            <p class="text-muted">Gestiona las solicitudes de compra del Ecommerce (Pasos 5, 6 y 7)</p>
        </div>
    </div>

    <?php if(isset($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show shadow-sm" role="alert">
            <?php echo $mensaje; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID / Fecha</th>
                            <th>Cliente</th>
                            <th>Referencia Pago Móvil</th>
                            <th>Monto Total</th>
                            <th class="text-center">Acciones de Control</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($resultado->num_rows > 0): ?>
                            <?php while($v = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="fw-bold text-primary">#<?php echo $v['id']; ?></span><br>
                                    <small class="text-muted"><?php echo $v['fecha']; ?></small>
                                </td>
                                <td>
                                    <div class="fw-bold"><?php echo $v['nombre_completo']; ?></div>
                                    <div class="small text-muted"><i class="fas fa-id-card me-1"></i><?php echo $v['cedula_cliente']; ?></div>
                                    <div class="small text-muted"><i class="fas fa-phone me-1"></i><?php echo $v['telefono']; ?></div>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border p-2">
                                        <i class="fas fa- university me-1 text-primary"></i> 
                                        Ref: <strong><?php echo $v['referencia_pago']; ?></strong>
                                    </span>
                                </td>
                                <td>
                                    <h5 class="mb-0 text-success fw-bold">$<?php echo number_format($v['total_usd'], 2); ?></h5>
                                </td>
                                <td class="text-center">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_venta" value="<?php echo $v['id']; ?>">
                                        
                                        <button type="submit" name="accion" value="aprobar" 
                                                class="btn btn-success btn-action me-2"
                                                onclick="return confirm('¿Confirmas que el dinero está en cuenta? El stock se descontará automáticamente.')">
                                            <i class="fas fa-check-circle me-1"></i> Aceptar
                                        </button>

                                        <button type="submit" name="accion" value="rechazar" 
                                                class="btn btn-outline-danger btn-action"
                                                onclick="return confirm('¿Rechazar este pago? El cliente deberá contactar soporte.')">
                                            <i class="fas fa-ban me-1"></i> Rechazar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i><br>
                                    <h5>No hay pagos pendientes por verificar</h5>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>