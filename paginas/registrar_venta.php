<?php


include("../conexion/conex.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['productos']) || !is_array($data['productos']) || empty($data['productos'])) {
    echo json_encode(['success' => false, 'message' => 'No se recibieron productos para la venta.']);
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
    exit;
}

mysqli_autocommit($conn, FALSE);
$transaction_successful = TRUE;
$idVenta = null; 

try {
    $stmtVenta = mysqli_prepare($conn, "INSERT INTO ventas (fecha_venta, total) VALUES (NOW(), ?)");
    
    if (!$stmtVenta) {
        throw new Exception('Error en la preparación de la consulta de venta: ' . mysqli_error($conn));
    }

    $totalVenta = 0;
    foreach ($data['productos'] as $producto) {
        $totalVenta += $producto['cantidad'] * $producto['precio_unitario'];
    }
    
    mysqli_stmt_bind_param($stmtVenta, "d", $totalVenta);
    if (!mysqli_stmt_execute($stmtVenta)) {
        throw new Exception('Error al ejecutar la inserción de venta: ' . mysqli_stmt_error($stmtVenta));
    }
    $idVenta = mysqli_insert_id($conn); 
    mysqli_stmt_close($stmtVenta);

    $stmtDetalle = mysqli_prepare($conn, "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    if (!$stmtDetalle) {
        throw new Exception('Error en la preparación de la consulta de detalle de venta: ' . mysqli_error($conn));
    }
    $stmtUpdateStock = mysqli_prepare($conn, "UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
    if (!$stmtUpdateStock) {
        throw new Exception('Error en la preparación de la consulta de actualización de stock: ' . mysqli_error($conn));
    }

    foreach ($data['productos'] as $producto) {
        $subtotalProducto = $producto['cantidad'] * $producto['precio_unitario'];
        mysqli_stmt_bind_param($stmtDetalle, "iidds", $idVenta, $producto['id'], $producto['cantidad'], $producto['precio_unitario'], $subtotalProducto);
        if (!mysqli_stmt_execute($stmtDetalle)) {
            throw new Exception('Error al ejecutar la inserción de detalle de venta para producto ' . $producto['id'] . ': ' . mysqli_stmt_error($stmtDetalle));
        }
        mysqli_stmt_bind_param($stmtUpdateStock, "ii", $producto['cantidad'], $producto['id']);
        if (!mysqli_stmt_execute($stmtUpdateStock)) {
            throw new Exception('Error al actualizar el stock para producto ' . $producto['id'] . ': ' . mysqli_stmt_error($stmtUpdateStock));
        }
    }

    mysqli_commit($conn);
    $transaction_successful = TRUE;

    
    echo json_encode(['success' => true, 'message' => 'Venta registrada exitosamente.', 'id_venta' => $idVenta]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    $transaction_successful = FALSE;
    error_log("Error al registrar venta: " . $e->getMessage());
    echo json_encode(['success' => false, 'message' => 'Error al registrar la venta: ' . $e->getMessage()]);
} finally {
    mysqli_autocommit($conn, TRUE);
    if (isset($stmtDetalle) && $stmtDetalle) {
        mysqli_stmt_close($stmtDetalle);
    }
    if (isset($stmtUpdateStock) && $stmtUpdateStock) {
        mysqli_stmt_close($stmtUpdateStock);
    }
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>