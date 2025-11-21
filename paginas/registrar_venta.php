<?php
// registrar_venta.php - VERSIÓN ULTRA SIMPLIFICADA

// Buffer MUY temprano
ob_start();

// Incluir conexión
include("../conexion/conex.php");

// Headers JSON
header('Content-Type: application/json; charset=utf-8');

// Limpiar buffer COMPLETAMENTE
ob_clean();

// Obtener datos
$input = file_get_contents('php://input');
$data = json_decode($input, true);

// Validación
if (!$data || !isset($data['productos'])) {
    die(json_encode(['success' => false, 'message' => 'Datos inválidos']));
}

$tasa_bcv_usada = isset($data['tasa_bcv_usada']) ? (float)$data['tasa_bcv_usada'] : 1.0; 
if ($tasa_bcv_usada <= 0) {
     $tasa_bcv_usada = 1.0; 
}

// Cálculo de Totales
$totalVentaUsd = 0;
foreach ($data['productos'] as $producto) {
    $totalVentaUsd += $producto['cantidad'] * $producto['precio_unitario'];
}
$totalVentaBs = $totalVentaUsd * $tasa_bcv_usada;

// Iniciar transacción
mysqli_autocommit($conn, FALSE);
$idVenta = null;

try {
    // 1. Insertar Venta
    $stmtVenta = mysqli_prepare($conn, "INSERT INTO ventas (fecha_venta, total_usd, tasa_bcv_usada, total_bs) VALUES (NOW(), ?, ?, ?)");
    
    if (!$stmtVenta) {
        throw new Exception('Error preparando venta: ' . mysqli_error($conn));
    }

    mysqli_stmt_bind_param($stmtVenta, "ddd", $totalVentaUsd, $tasa_bcv_usada, $totalVentaBs);
    
    if (!mysqli_stmt_execute($stmtVenta)) {
        throw new Exception('Error ejecutando venta: ' . mysqli_stmt_error($stmtVenta));
    }
    
    $idVenta = mysqli_insert_id($conn); 

    // 2. Preparar statements para detalle y stock
    $stmtDetalle = mysqli_prepare($conn, "INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
    if (!$stmtDetalle) {
        throw new Exception('Error preparando detalle: ' . mysqli_error($conn));
    }
    
    $stmtUpdateStock = mysqli_prepare($conn, "UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ?");
    if (!$stmtUpdateStock) {
        throw new Exception('Error preparando stock: ' . mysqli_error($conn));
    }

    // 3. Procesar productos
    foreach ($data['productos'] as $producto) {
        $precioUnitarioUsd = (float)$producto['precio_unitario']; 
        $subtotalProductoUsd = $producto['cantidad'] * $precioUnitarioUsd;

        // Insertar Detalle
        mysqli_stmt_bind_param($stmtDetalle, "iidds", $idVenta, $producto['id'], $producto['cantidad'], $precioUnitarioUsd, $subtotalProductoUsd);
        if (!mysqli_stmt_execute($stmtDetalle)) {
            throw new Exception('Error en detalle producto ' . $producto['id'] . ': ' . mysqli_stmt_error($stmtDetalle));
        }

        // Actualizar Stock
        mysqli_stmt_bind_param($stmtUpdateStock, "ii", $producto['cantidad'], $producto['id']);
        if (!mysqli_stmt_execute($stmtUpdateStock)) {
            throw new Exception('Error actualizando stock producto ' . $producto['id'] . ': ' . mysqli_stmt_error($stmtUpdateStock));
        }
    }

    // Commit
    mysqli_commit($conn);
    
    // NO CERRAR LOS STATEMENTS - PHP los cierra automáticamente
    // SOLO enviar JSON
    echo json_encode([
        'success' => true, 
        'message' => 'Venta registrada exitosamente.', 
        'id_venta' => $idVenta
    ]);

} catch (Exception $e) {
    // Rollback
    mysqli_rollback($conn);
    error_log("Error al registrar venta: " . $e->getMessage());
    
    echo json_encode([
        'success' => false, 
        'message' => 'Error al registrar la venta: ' . $e->getMessage()
    ]);

} finally {
    // Solo restaurar autocommit
    mysqli_autocommit($conn, TRUE);
    
    // NO cerrar statements ni conexión explícitamente
    // PHP los manejará automáticamente al final del script
}

// Terminar script inmediatamente
exit();