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
            $mensaje = "Venta #$id_venta aprobada e inventario actualizado.";
            $tipo_mensaje = "success";
            $icono_msj = "bi-check-all";
        } catch (Exception $e) {
            $conn->rollback();
            $mensaje = "Error en la transacción: " . $e->getMessage();
            $tipo_mensaje = "danger";
            $icono_msj = "bi-exclamation-triangle";
        }
    } 
    
    if ($_POST['accion'] == 'rechazar') {
        $conn->query("UPDATE ventas SET estado_pago = 'Rechazado' WHERE id = $id_venta");
        $mensaje = "Venta #$id_venta marcada como rechazada.";
        $tipo_mensaje = "warning";
        $icono_msj = "bi-info-circle";
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

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    :root {
        --pc-dark: #1a233a;
        --pc-accent: #4e5df8;
        --pc-bg: #f4f7fe;
    }

    body { 
        background-color: var(--pc-bg); 
        font-family: 'Inter', system-ui, sans-serif;
    }

    .admin-title {
        border-left: 5px solid var(--pc-accent);
        padding-left: 15px;
    }

    .card-admin { 
        border: none; 
        border-radius: 20px; 
        box-shadow: 0 10px 30px rgba(26, 35, 58, 0.05); 
        overflow: hidden;
    }

    .table thead { 
        background: var(--pc-dark); 
        color: white; 
    }

    .table thead th {
        font-weight: 500;
        text-transform: uppercase;
        font-size: 0.75rem;
        letter-spacing: 1px;
        padding: 15px;
        border: none;
    }

    .table tbody td {
        padding: 18px 15px;
        vertical-align: middle;
        border-bottom: 1px solid #edf2f7;
    }

    .ref-badge {
        background: rgba(78, 93, 248, 0.1);
        color: var(--pc-accent);
        font-family: 'Monaco', monospace;
        font-weight: bold;
        padding: 5px 12px;
        border-radius: 8px;
    }

    .btn-action { 
        border-radius: 10px; 
        padding: 8px 16px; 
        font-weight: 600; 
        font-size: 0.85rem;
        transition: 0.2s;
    }

    .btn-approve {
        background-color: #28a745;
        border: none;
        color: white;
    }

    .btn-approve:hover {
        background-color: #218838;
        transform: translateY(-2px);
    }

    .alert-custom {
        border: none;
        border-radius: 15px;
        display: flex;
        align-items: center;
    }
</style>

<div class="container mt-5 pt-4">
    <div class="row mb-4 align-items-center">
        <div class="col-md-7">
            <h2 class="fw-bold text-dark admin-title">Panel de Verificación</h2>
            <p class="text-muted mb-0">Revisión de comprobantes y liberación de inventario</p>
        </div>
        <div class="col-md-5 text-md-end">
            <div class="bg-white d-inline-block p-2 px-3 rounded-pill shadow-sm">
                <span class="text-muted small">Ventas por procesar:</span>
                <span class="badge bg-danger rounded-pill ms-2"><?php echo $resultado->num_rows; ?></span>
            </div>
        </div>
    </div>

    <?php if(isset($mensaje)): ?>
        <div class="alert alert-<?php echo $tipo_mensaje; ?> alert-dismissible fade show alert-custom shadow-sm mb-4" role="alert">
            <i class="bi <?php echo $icono_msj; ?> fs-4 me-3"></i>
            <div><?php echo $mensaje; ?></div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card card-admin">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead>
                        <tr>
                            <th class="ps-4">ID & Fecha</th>
                            <th>Datos del Cliente</th>
                            <th>Referencia Pago Móvil</th>
                            <th>Total a Validar</th>
                            <th class="text-center">Acciones de Verificación</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if($resultado->num_rows > 0): ?>
                            <?php while($v = $resultado->fetch_assoc()): ?>
                            <tr>
                                <td class="ps-4">
                                    <span class="d-block fw-bold text-dark">#ORD-<?php echo $v['id']; ?></span>
                                    <span class="text-muted small"><i class="bi bi-calendar3 me-1"></i><?php echo date("d/m/Y", strtotime($v['fecha'])); ?></span>
                                </td>
                                <td>
                                    <div class="fw-bold text-dark"><?php echo $v['nombre_completo']; ?></div>
                                    <div class="small text-muted">
                                        <i class="bi bi-person-badge me-1"></i>V-<?php echo $v['cedula_cliente']; ?> | 
                                        <i class="bi bi-whatsapp me-1 text-success"></i><?php echo $v['telefono']; ?>
                                    </div>
                                </td>
                                <td>
                                    <span class="ref-badge">
                                        <i class="bi bi-upc-scan me-1"></i><?php echo $v['referencia_pago']; ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="h6 fw-bold text-success mb-0">$<?php echo number_format($v['total_usd'], 2); ?></span>
                                </td>
                                <td class="text-center">
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="id_venta" value="<?php echo $v['id']; ?>">
                                        
                                        <button type="submit" name="accion" value="aprobar" 
                                                class="btn btn-approve btn-action me-2"
                                                onclick="return confirm('¿Confirmas la recepción del pago? Esto descontará el stock.')">
                                            <i class="bi bi-check-lg"></i> Aprobar
                                        </button>

                                        <button type="submit" name="accion" value="rechazar" 
                                                class="btn btn-outline-danger btn-action"
                                                onclick="return confirm('¿Rechazar este pago por datos incorrectos?')">
                                            <i class="bi bi-x-lg"></i> Rechazar
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-5">
                                    <div class="opacity-25 mb-3">
                                        <i class="bi bi-inbox-fill" style="font-size: 5rem;"></i>
                                    </div>
                                    <h5 class="text-muted fw-light">No hay solicitudes pendientes de verificación</h5>
                                    <a href="dashboard_admin.php" class="btn btn-link text-decoration-none small">Volver al Dashboard</a>
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
</body>
</html>