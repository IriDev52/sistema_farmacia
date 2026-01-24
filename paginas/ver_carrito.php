<?php
session_start();
include("../recursos/header.php");
$total = 0;
?>

<div class="container mt-5 pt-5">
    <div class="card shadow border-0">
        <div class="card-body">
            <h3><i class="fas fa-shopping-cart me-2"></i> Revisar Pedido</h3>
            <hr>
            <?php if(empty($_SESSION['carrito'])): ?>
                <p>El carrito está vacío.</p>
            <?php else: ?>
                <table class="table align-middle">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Precio</th>
                            <th>Cantidad</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($_SESSION['carrito'] as $item): 
                            $subtotal = $item['precio'] * $item['cantidad'];
                            $total += $subtotal;
                        ?>
                        <tr>
                            <td><?php echo $item['nombre']; ?></td>
                            <td>$<?php echo number_format($item['precio'], 2); ?></td>
                            <td><?php echo $item['cantidad']; ?></td>
                            <td class="fw-bold">$<?php echo number_format($subtotal, 2); ?></td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <div class="text-end">
                    <h4>Total: <span class="text-success">$<?php echo number_format($total, 2); ?></span></h4>
                    <a href="finalizar_compra.php" class="btn btn-primary btn-lg px-5 rounded-pill">Confirmar Pago</a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>