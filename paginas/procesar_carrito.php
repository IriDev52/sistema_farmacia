<?php
session_start();
include("../conexion/conex.php");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id = $_POST['id_producto'];
    $cantidad = (int)$_POST['cantidad'];

    // Consultar datos reales para evitar que alteren el precio desde el navegador
    $res = mysqli_query($conn, "SELECT nombre_producto, precio_venta, stock_actual FROM productos WHERE id = $id");
    $prod = mysqli_fetch_assoc($res);

    if ($prod) {
        // Verificar si hay stock suficiente antes de agregar
        if ($prod['stock_actual'] >= $cantidad) {
            $_SESSION['carrito'][$id] = [
                'id' => $id,
                'nombre' => $prod['nombre_producto'],
                'precio' => $prod['precio_venta'],
                'cantidad' => $cantidad
            ];
            // Redirigir a la vista del carrito
            header("Location: ver_carrito.php");
        } else {
            echo "<script>alert('No hay stock suficiente. Disponible: ".$prod['stock_actual']."'); window.history.back();</script>";
        }
    }
}
exit();
?>