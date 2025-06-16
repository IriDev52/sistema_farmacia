<?php
// registrar_venta.php
header('Content-Type: application/json'); // ¡IMPORTANTE: DEBE SER LA PRIMERA SALIDA!
require_once("../conexion/conex.php"); // Incluye el archivo de conexión

$response = ['success' => false, 'message' => ''];
$conex->begin_transaction(); // Iniciar una transacción

try {
    $data = json_decode(file_get_contents('php://input'), true);

    if (json_last_error() !== JSON_ERROR_NONE) {
        throw new Exception("Error al decodificar JSON: " . json_last_error_msg());
    }

    $productos_venta = $data['productos'] ?? [];

    if (empty($productos_venta)) {
        throw new Exception("No se recibieron productos para la venta.");
    }

    $totalVentaCalculado = 0;
    foreach ($productos_venta as $item) {
        $totalVentaCalculado += ($item['cantidad'] * $item['precio_unitario']);
    }

    // 1. Insertar la venta principal en la tabla 'ventas'
    // Asegúrate de que tu tabla 'ventas' tiene 'id_venta', 'fecha_venta', 'total_venta'
    $query_insert_venta = "INSERT INTO ventas (fecha_venta, total_venta) VALUES (NOW(), ?)";
    $stmt_venta_principal = mysqli_prepare($conex, $query_insert_venta);
    
    if (!$stmt_venta_principal) {
        throw new Exception("Error al preparar inserción de venta principal: " . mysqli_error($conex));
    }
    
    mysqli_stmt_bind_param($stmt_venta_principal, "d", $totalVentaCalculado); // 'd' para decimal/double
    
    if (!mysqli_stmt_execute($stmt_venta_principal)) {
        throw new Exception("Error al registrar la venta principal: " . mysqli_error($conex));
    }
    
    $id_venta_generada = mysqli_insert_id($conex); // Obtener el ID de la venta recién insertada
    mysqli_stmt_close($stmt_venta_principal);

    // 2. Insertar cada producto en la tabla 'detalle_ventas' y actualizar el stock
    foreach ($productos_venta as $item) {
        $id_producto_en_carrito = $item['id'] ?? null;
        $cantidad_vendida = $item['cantidad'] ?? null;
        $precio_unitario_carrito = $item['precio_unitario'] ?? null;
        $subtotal_item = $cantidad_vendida * $precio_unitario_carrito;

        if (is_null($id_producto_en_carrito) || is_null($cantidad_vendida) || is_null($precio_unitario_carrito)) {
            throw new Exception("Datos incompletos para un producto en el carrito.");
        }

        // Insertar en detalle_ventas
        // Asegúrate de que tu tabla 'detalle_ventas' tiene 'id_venta', 'id_producto', 'cantidad', 'precio_unitario', 'subtotal'
        $query_insert_detalle = "INSERT INTO detalle_ventas (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)";
        $stmt_detalle = mysqli_prepare($conex, $query_insert_detalle);
        
        if (!$stmt_detalle) {
            throw new Exception("Error al preparar inserción de detalle de venta: " . mysqli_error($conex));
        }
        
        // 'i' para int, 'i' para int, 'd' para decimal, 'd' para decimal
        mysqli_stmt_bind_param($stmt_detalle, "iiidd", $id_venta_generada, $id_producto_en_carrito, $cantidad_vendida, $precio_unitario_carrito, $subtotal_item);
        
        if (!mysqli_stmt_execute($stmt_detalle)) {
            throw new Exception("Error al registrar detalle para producto ID " . $id_producto_en_carrito . ": " . mysqli_error($conex));
        }
        mysqli_stmt_close($stmt_detalle);

        // Actualizar stock_actual en la tabla 'productos'
        $query_update_stock = "UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?";
        $stmt_stock = mysqli_prepare($conex, $query_update_stock);
        
        if (!$stmt_stock) {
            throw new Exception("Error al preparar actualización de stock: " . mysqli_error($conex));
        }
        
        mysqli_stmt_bind_param($stmt_stock, "ii", $cantidad_vendida, $id_producto_en_carrito);
        
        if (!mysqli_stmt_execute($stmt_stock)) {
            throw new Exception("Error al actualizar stock para producto ID " . $id_producto_en_carrito . ": " . mysqli_error($conex));
        }
        mysqli_stmt_close($stmt_stock);
    }

    $conex->commit(); // Confirmar la transacción
    $response['success'] = true;
    $response['message'] = "Venta registrada exitosamente con ID: " . $id_venta_generada;

} catch (Exception $e) {
    $conex->rollback(); // Revertir la transacción si algo falla
    $response['message'] = "Error en la transacción: " . $e->getMessage();
    error_log("Error en registrar_venta.php: " . $e->getMessage()); // Registra el error para depuración
    http_response_code(500); // Internal Server Error
} finally {
    // if ($conex) mysqli_close($conex); // Puedes descomentar si quieres cerrar explícitamente
    echo json_encode($response);
    exit(); // Asegura que no se imprima nada más
}
?>