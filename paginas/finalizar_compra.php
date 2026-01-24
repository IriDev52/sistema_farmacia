<?php
session_start();
include("../conexion/conex.php");

if(!empty($_SESSION['carrito'])){
    foreach($_SESSION['carrito'] as $item){
        $id = $item['id'];
        $cant = $item['cantidad'];
        
        // El UPDATE que descuenta el stock
        mysqli_query($conn, "UPDATE productos SET stock_actual = stock_actual - $cant WHERE id = $id");
    }
    unset($_SESSION['carrito']); // Limpiar carrito
    echo "<script>alert('Venta realizada con éxito'); window.location='ecomerce.php';</script>";
}
?>