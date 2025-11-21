<?php
include("../conexion/conex.php");
header('Content-Type: application/json');

if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo json_encode(['success' => false, 'message' => 'ID de venta no proporcionado.']);
    exit();
}

$id_venta = mysqli_real_escape_string($conn, $_GET['id']);


$query_venta = "SELECT * FROM ventas WHERE id_venta = '$id_venta'";
$result_venta = mysqli_query($conn, $query_venta);
$venta = mysqli_fetch_assoc($result_venta);

if (!$venta) {
    echo json_encode(['success' => false, 'message' => 'Venta no encontrada.']);
    exit();
}


$query_detalles = "SELECT dv.cantidad_vendida, dv.precio_venta, p.nombre_producto 
                   FROM detalles_venta dv
                   JOIN productos p ON dv.id_producto = p.id
                   WHERE dv.id_venta = '$id_venta'";
$result_detalles = mysqli_query($conn, $query_detalles);
$detalles = [];
while ($row = mysqli_fetch_assoc($result_detalles)) {
    $detalles[] = $row;
}


echo json_encode(['success' => true, 'venta' => [
    'id' => $venta['id_venta'],
    'fecha' => date('d/m/Y H:i:s', strtotime($venta['fecha_venta'])),
    'total' => number_format($venta['total_venta'], 2)
], 'detalles' => $detalles]);

mysqli_close($conn);
?>