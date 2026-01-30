<?php
session_start();
include("../recursos/header.php"); 

// Verificamos sesión y que exista una venta para reportar
if (!isset($_SESSION['logeado']) || !isset($_GET['id_venta'])) {
    header("Location: ecomerce.php");
    exit();
}

$id_venta = $_GET['id_venta'];
$cedula_user = $_SESSION['usuario_cedula'];
?>

<link rel="stylesheet" href="../recursos/estilos_ecomerce.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    body { background-color: var(--pc-bg); }
    
    .card-pago { 
        border-radius: 24px; 
        border: none; 
        overflow: hidden; 
        background: var(--pc-white);
    }
    
    .metodo-pago-box { 
        background: rgba(40, 167, 69, 0.04); 
        border: 2px solid rgba(40, 167, 69, 0.1); 
        border-radius: 18px; 
    }

    .dato-bancario {
        background: white;
        padding: 10px 15px;
        border-radius: 12px;
        margin-bottom: 8px;
        border: 1px solid #edf2f7;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .label-dato {
        font-size: 0.75rem;
        text-transform: uppercase;
        color: #718096;
        font-weight: 700;
        letter-spacing: 0.5px;
    }

    .valor-dato {
        color: var(--pc-dark);
        font-weight: 600;
    }

    .stepper-pago {
        display: flex;
        justify-content: space-around;
        margin-bottom: 2rem;
    }

    .step-item {
        text-align: center;
        opacity: 0.4;
    }

    .step-item.active {
        opacity: 1;
        color: var(--pc-accent);
        font-weight: bold;
    }
</style>

<div class="container mt-5 pt-4 mb-5">
    <div class="row justify-content-center">
        <div class="col-md-8 col-lg-5">
            
            <div class="stepper-pago">
                <div class="step-item"><i class="bi bi-cart-check fs-4"></i><div class="small">Carrito</div></div>
                <div class="step-item active"><i class="bi bi-credit-card-2-front fs-4"></i><div class="small">Pago</div></div>
                <div class="step-item"><i class="bi bi-truck fs-4"></i><div class="small">Envío</div></div>
            </div>

            <div class="card card-pago shadow-lg">
                <div class="card-header border-0 text-white text-center py-4" style="background-color: var(--pc-dark);">
                    <h4 class="mb-0 fw-bold text-info">Confirmar Pago</h4>
                    <span class="badge bg-info text-dark mt-2">Orden #<?php echo $id_venta; ?></span>
                </div>
                
                <div class="card-body p-4 p-md-5">
                    
                    <h6 class="fw-bold mb-3 text-center"><i class="bi bi-bank me-2"></i>Datos para Pago Móvil</h6>
                    
                    <div class="metodo-pago-box p-3 mb-4">
                        <div class="dato-bancario">
                            <span class="label-dato">Banco</span>
                            <span class="valor-dato">Banesco (0134)</span>
                        </div>
                        <div class="dato-bancario">
                            <span class="label-dato">Teléfono</span>
                            <span class="valor-dato">0412-1234567</span>
                        </div>
                        <div class="dato-bancario">
                            <span class="label-dato">RIF</span>
                            <span class="valor-dato">J-123456789</span>
                        </div>
                    </div>

                    <form action="procesar_reporte_pago.php" method="POST">
                        <input type="hidden" name="id_venta" value="<?php echo $id_venta; ?>">
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">NÚMERO DE REFERENCIA (Últimos 6 dígitos)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-hash"></i></span>
                                <input type="text" name="referencia" class="form-control form-control-lg border-start-0 ps-0 fw-bold" 
                                       placeholder="Ej: 845213" pattern="[0-9]+" maxlength="8" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold small text-muted">CONFIRMAR DIRECCIÓN DE ENTREGA</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0"><i class="bi bi-geo-alt"></i></span>
                                <textarea name="detalles_envio" class="form-control border-start-0 ps-0" rows="3" 
                                          placeholder="Ej: Edificio Farma, Piso 2, Apto 4. Frente a la plaza." required></textarea>
                            </div>
                        </div>

                        <div class="d-grid gap-3 mt-4">
                            <button type="submit" class="btn btn-buy btn-lg py-3 shadow-sm text-uppercase">
                                Finalizar Pedido <i class="bi bi-check2-circle ms-2"></i>
                            </button>
                            <a href="ecomerce.php" class="btn btn-link text-muted btn-sm text-decoration-none">
                                <i class="bi bi-arrow-left me-1"></i> Regresar al catálogo
                            </a>
                        </div>
                    </form>
                </div>
                
                <div class="card-footer bg-light border-0 text-center py-3">
                    <div class="d-flex align-items-center justify-content-center text-muted small">
                        <i class="bi bi-shield-lock-fill text-success me-2"></i>
                        Transacción protegida por PharmaCore SSL
                    </div>
                </div>
            </div>

            <div class="text-center mt-4">
                <p class="text-muted small">¿Problemas con el pago? <a href="#" class="text-accent fw-bold text-decoration-none">Contactar a soporte</a></p>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>