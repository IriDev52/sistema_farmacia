<?php
include("../conexion/conex.php");
header('Content-Type: application/json');

$query = isset($_GET['query']) ? mysqli_real_escape_string($conn, $_GET['query']) : '';

if ($query !== '') {
    // Solo mostramos productos con estado 'activo' y stock > 0
    $sql = "SELECT id, nombre_producto, sustancia_activa, precio_venta, stock_actual 
            FROM productos 
            WHERE (nombre_producto LIKE '%$query%' 
            OR sustancia_activa LIKE '%$query%') 
            AND estado = 'activo' 
            AND stock_actual > 0 
            LIMIT 8";
            
    $res = mysqli_query($conn, $sql);
    $data = mysqli_fetch_all($res, MYSQLI_ASSOC);
    echo json_encode($data);
} else {
    echo json_encode([]);
}
?>