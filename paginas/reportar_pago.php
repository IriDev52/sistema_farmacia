<?php
session_start();
include("../conexion/conex.php");

// Verificamos sesión y que exista una venta para reportar
if (!isset($_SESSION['logeado']) || !isset($_GET['id_venta'])) {
    header("Location: ecomerce.php");
    exit();
}

$id_venta = $_GET['id_venta'];
$cedula_user = $_SESSION['usuario_cedula'];

include("../recursos/header.php"); 
?>

<style>
    body { background-color: #f0f2f5; }
    .card-pago { border-radius: 20px; border: none; overflow: hidden; }
    .metodo-pago { background: #e8f5e9; border: 2px dashed #2e7d32; border-radius: 15px; }
    .instrucciones { font-size: 0.9rem; color: #555; }
</style>

<div class="container mt-5 pt-5">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-5">
            <div class="card card-pago shadow-lg">
                <div class="card-header bg-success text-white text-center py-3">
                    <h4 class="mb-0"><i class="fas fa-receipt me-2"></i> Reportar Pago Móvil</h4>
                    <small>Venta Nro: #<?php echo $id_venta; ?></small>
                </div>
                
                <div class="card-body p-4">
                    <div class="metodo-pago p-3 mb-4 text-center">
                        <h6 class="fw-bold text-success mb-3"><i class="fas fa-university me-2"></i> DATOS DE TRANSFERENCIA</h6>
                        <div class="row g-2 text-start px-3">
                            <div class="col-6 text-muted">Banco:</div>
                            <div class="col-6 fw-bold">Banesco</div>
                            <div class="col-6 text-muted">Teléfono:</div>
                            <div class="col-6 fw-bold">0412-1234567</div>
                            <div class="col-6 text-muted">RIF/Cédula:</div>
                            <div class="col-6 fw-bold">J-123456789</div>
                        </div>
                    </div>

                    <div class="instrucciones mb-4 border-start border-4 border-warning ps-3">
                        <p class="mb-0"><strong>Nota:</strong> Realice el pago móvil por el monto total de su compra y coloque aquí los <strong>últimos 6 dígitos</strong> de la referencia bancaria.</p>
                    </div>

                    <form action="procesar_reporte_pago.php" method="POST">
                        <input type="hidden" name="id_venta" value="<?php echo $id_venta; ?>">
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-fingerprint me-1"></i> Número de Referencia</label>
                            <input type="text" name="referencia" class="form-control form-control-lg text-center fw-bold" 
                                   placeholder="Ej: 845213" pattern="[0-9]+" title="Solo números" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold"><i class="fas fa-map-marker-alt me-1"></i> Dirección de Entrega</label>
                            <textarea name="detalles_envio" class="form-control" rows="3" 
                                      placeholder="Punto de referencia, calle, número de casa..." required></textarea>
                        </div>

                        <hr>
                        
                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-success btn-lg fw-bold shadow-sm">
                                <i class="fas fa-check-circle me-2"></i> NOTIFICAR PAGO
                            </button>
                            <a href="ecomerce.php" class="btn btn-link text-muted btn-sm text-decoration-none">Pagar más tarde</a>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer bg-light text-center py-3">
                    <small class="text-muted">Su pedido será procesado una vez verifiquemos los fondos.</small>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include("../recursos/footer.php"); ?>