<?php
session_start();
include("../recursos/header.php");

// Lógica para vaciar carrito completo
if (isset($_GET['vaciar'])) {
    unset($_SESSION['carrito']);
    header("Location: ver_carrito.php");
    exit();
}
?>

<link rel="stylesheet" href="../recursos/estilos_ecomerce.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<style>
    /* Estilos específicos para la tabla y resumen que complementan tu CSS global */
    .cart-wrapper { 
        background: var(--pc-white); 
        border-radius: 16px; 
    }
    .table thead th { 
        background-color: #f8f9fa; 
        color: var(--pc-dark); 
        text-transform: uppercase; 
        font-size: 0.75rem; 
        letter-spacing: 1px;
        border-bottom: 2px solid var(--pc-bg);
    }
    .btn-remove { 
        color: #dc3545; 
        background: #fff5f5;
        border-radius: 10px; 
        transition: 0.3s;
    }
    .btn-remove:hover { 
        background-color: #dc3545; 
        color: white; 
    }
    .summary-box { 
        background: var(--pc-white); 
        border-radius: 20px; 
        border: none; 
    }
    .total-highlight {
        background: rgba(78, 93, 248, 0.05);
        border: 1px dashed var(--pc-accent);
    }
</style>

<div class="container mb-5">
    <div class="row align-items-end mb-4">
        <div class="col-md-8">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb mb-2">
                    <li class="breadcrumb-item"><a href="ecomerce.php" class="text-decoration-none text-muted">Catálogo</a></li>
                    <li class="breadcrumb-item active fw-bold" style="color: var(--pc-accent);">Carrito</li>
                </ol>
            </nav>
            <h2 class="fw-bold mb-0 text-dark"><i class="bi bi-cart-check me-2"></i>Tu Carrito</h2>
        </div>
        <?php if(!empty($_SESSION['carrito'])): ?>
        <div class="col-md-4 text-md-end">
            <span class="badge px-3 py-2 rounded-pill" style="background-color: var(--pc-dark);">
                <?php echo count($_SESSION['carrito']); ?> Items seleccionados
            </span>
        </div>
        <?php endif; ?>
    </div>

    <?php if(!empty($_SESSION['carrito'])): ?>
        <div class="row g-4">
            <div class="col-lg-8">
                <div class="cart-wrapper shadow-sm overflow-hidden">
                    <div class="table-responsive">
                        <table class="table align-middle mb-0">
                            <thead>
                                <tr>
                                    <th class="ps-4 py-3">Medicamento</th>
                                    <th class="text-center py-3">Cantidad</th>
                                    <th class="py-3">Precio</th>
                                    <th class="py-3">Subtotal</th>
                                    <th class="text-center py-3">Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                $gran_total = 0;
                                foreach($_SESSION['carrito'] as $id => $item): 
                                    $subtotal = $item['precio'] * $item['cantidad'];
                                    $gran_total += $subtotal;
                                ?>
                                <tr>
                                    <td class="ps-4">
                                        <div class="d-flex align-items-center py-2">
                                            <div class="bg-light rounded p-2 me-3 d-none d-sm-block">
                                                <i class="bi bi-capsule text-primary fs-4"></i>
                                            </div>
                                            <div>
                                                <span class="d-block fw-bold text-dark"><?php echo htmlspecialchars($item['nombre']); ?></span>
                                                <small class="text-muted">ID: #<?php echo $id; ?></small>
                                            </div>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <span class="badge bg-light text-dark border px-3 py-2 fs-6"><?php echo $item['cantidad']; ?></span>
                                    </td>
                                    <td class="text-muted fw-semibold">$<?php echo number_format($item['precio'], 2); ?></td>
                                    <td class="fw-bold" style="color: var(--pc-accent);">$<?php echo number_format($subtotal, 2); ?></td>
                                    <td class="text-center">
                                        <a href="eliminar_item.php?id=<?php echo $id; ?>" class="btn btn-remove btn-sm px-3" title="Eliminar">
                                            <i class="bi bi-trash3"></i>
                                        </a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                    
                    <div class="p-4 border-top d-flex flex-column flex-md-row justify-content-between gap-3">
                        <a href="ecomerce.php" class="btn btn-light btn-rounded border text-dark">
                            <i class="bi bi-arrow-left me-2"></i>Seguir Comprando
                        </a>
                        <a href="ver_carrito.php?vaciar=true" class="btn btn-outline-danger btn-rounded" 
                           onclick="return confirm('¿Estás seguro de que quieres vaciar todo el carrito?')">
                            <i class="bi bi-trash-fill me-2"></i>Vaciar Carrito
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="summary-box p-4 shadow-sm position-sticky" style="top: 110px;">
                    <h5 class="fw-bold mb-4">Resumen de Compra</h5>
                    
                    <div class="d-flex justify-content-between mb-3 text-muted">
                        <span>Subtotal estimado:</span>
                        <span class="fw-bold">$<?php echo number_format($gran_total, 2); ?></span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-4">
                        <span>Envío (Express):</span>
                        <span class="text-success fw-bold">GRATIS</span>
                    </div>

                    <div class="total-highlight p-3 rounded-4 mb-4 text-center">
                        <span class="d-block text-muted small text-uppercase fw-bold mb-1">Total a Pagar</span>
                        <span class="h2 fw-bold mb-0" style="color: var(--pc-dark);">$<?php echo number_format($gran_total, 2); ?></span>
                    </div>
                    
                    <div class="d-grid">
                        <a href="finalizar_compra.php" class="btn btn-buy btn-lg py-3 shadow">
                            CONTINUAR AL PAGO <i class="bi bi-shield-lock-fill ms-2"></i>
                        </a>
                    </div>
                    
                    <div class="mt-4 p-3 rounded-3 bg-light border-0">
                        <div class="d-flex align-items-center">
                            <i class="bi bi-info-circle text-primary fs-4 me-3"></i>
                            <p class="small mb-0 text-muted">
                                Al procesar, confirmas que has verificado la dosis y presentación de los medicamentos.
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

    <?php else: ?>
        <div class="col-12 text-center py-5 cart-wrapper shadow-sm mt-4">
            <div class="mb-4">
                <i class="bi bi-cart-x display-1 text-muted opacity-25"></i>
            </div>
            <h3 class="fw-bold">Tu carrito está vacío</h3>
            <p class="text-muted mb-4 mx-auto" style="max-width: 400px;">
                Parece que aún no has añadido medicamentos. Explora nuestro catálogo y encuentra lo que necesitas.
            </p>
            <a href="ecomerce.php" class="btn btn-buy btn-rounded px-5 py-3 shadow">
                VOLVER AL CATÁLOGO
            </a>
        </div>
    <?php endif; ?>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>