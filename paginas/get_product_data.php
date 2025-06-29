<?php
include("../conexion/conex.php");
header('Content-Type: application/json');

$product_id = isset($_GET['product_id']) ? (int)$_GET['product_id'] : 0;

if ($product_id === 0) {
    echo json_encode(['error' => 'ID de producto no válido.']);
    exit();
}

$product_data = [];
$ubicaciones_con_stock = [];

// Obtener datos generales del producto
$query_product = "SELECT id, nombre_producto, stock_actual FROM productos WHERE id = ?";
$stmt_product = mysqli_prepare($conn, $query_product);
mysqli_stmt_bind_param($stmt_product, "i", $product_id);
mysqli_stmt_execute($stmt_product);
$result_product = mysqli_stmt_get_result($stmt_product);
$product_data = mysqli_fetch_assoc($result_product);
mysqli_stmt_close($stmt_product);

if (!$product_data) {
    echo json_encode(['error' => 'Producto no encontrado.']);
    exit();
}

// Obtener ubicaciones y stock para este producto
$query_ubicaciones = "
    SELECT
        pu.ID_Ubicacion,
        u.descripcion_ubicacion,
        pu.cantidad
    FROM
        producto_ubicacion pu
    JOIN
        ubicacion u ON pu.ID_Ubicacion = u.id_ubicacion
    WHERE
        pu.ID_Producto = ? AND pu.cantidad > 0
    ORDER BY
        u.descripcion_ubicacion;
";
$stmt_ubicaciones = mysqli_prepare($conn, $query_ubicaciones);
mysqli_stmt_bind_param($stmt_ubicaciones, "i", $product_id);
mysqli_stmt_execute($stmt_ubicaciones);
$result_ubicaciones = mysqli_stmt_get_result($stmt_ubicaciones);

while ($row = mysqli_fetch_assoc($result_ubicaciones)) {
    $ubicaciones_con_stock[] = $row;
}
mysqli_stmt_close($stmt_ubicaciones);

$product_data['ubicaciones_con_stock'] = $ubicaciones_con_stock;

echo json_encode($product_data);

mysqli_close($conn);
?>