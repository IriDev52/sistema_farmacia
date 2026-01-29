<?php
session_start();
include("../conexion/conex.php");

if (!isset($_SESSION['logeado'])) {
    header("Location: ecomerce.php?error=sesion");
    exit();
}

if(!empty($_SESSION['carrito'])){
    $cedula = $_SESSION['usuario_cedula'];
    $total_usd = 0;
    foreach($_SESSION['carrito'] as $i) { $total_usd += ($i['precio'] * $i['cantidad']); }

    // Insertamos la cabecera
    $sql_venta = "INSERT INTO ventas (cedula_cliente, total_usd, estado_pago) 
                  VALUES ('$cedula', '$total_usd', 'Pendiente')";
    
    if(mysqli_query($conn, $sql_venta)){
        $id_venta = mysqli_insert_id($conn);

        // Insertamos detalles
        foreach($_SESSION['carrito'] as $item){
            $id_p = $item['id'];
            $cant = $item['cantidad'];
            $precio = $item['precio'];
            $subtotal = $cant * $precio;
            mysqli_query($conn, "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal) 
                                 VALUES ($id_venta, $id_p, $cant, $precio, $subtotal)");
        }

        unset($_SESSION['carrito']); 
        // REDIRECCIÓN CLAVE: Enviamos al formulario de pago con el ID de venta
        header("Location: reportar_pago.php?id_venta=$id_venta");
        exit();
    }
}
?>