<?php
// 1. Evitar que cualquier error previo ensucie la respuesta
ob_start();
error_reporting(0);
ini_set('display_errors', 0);

include("../conexion/conex.php");
header('Content-Type: application/json');

// 2. Leer los datos enviados por el carrito
$input = file_get_contents('php://input');
$data = json_decode($input, true);

if (!$data || !isset($data['productos'])) {
    ob_clean();
    echo json_encode(['success' => false, 'message' => 'No se recibieron productos.']);
    exit();
}

$productos = $data['productos'];
$tasa_bcv = floatval($data['tasa_bcv_usada']);
$id_venta_generada = 0;

// 3. Iniciar transacción
mysqli_begin_transaction($conn);

try {
    foreach ($productos as $p) {
        $id_prod = intval($p['id']);
        $cant = intval($p['cantidad']);
        $precio_u = floatval($p['precio_unitario']);
        $total_usd = $cant * $precio_u;
        $total_bs = $total_usd * $tasa_bcv;

        // INSERTAR con la columna id_producto que mencionaste
        $query = "INSERT INTO ventas (id_producto, cantidad, total_usd, tasa_bcv_usada, total_bs, fecha_venta) 
                  VALUES ('$id_prod', '$cant', '$total_usd', '$tasa_bcv', '$total_bs', NOW())";
        
        if (!mysqli_query($conn, $query)) {
            throw new Exception("Error al insertar producto $id_prod: " . mysqli_error($conn));
        }
        
        $id_venta_generada = mysqli_insert_id($conn);

        // Actualizar Stock
        $updateStock = "UPDATE productos SET stock_actual = stock_actual - $cant WHERE id = '$id_prod'";
        mysqli_query($conn, $updateStock);
    }

    mysqli_commit($conn);
    
    // 4. Limpiar buffer y enviar éxito
    ob_clean();
    echo json_encode([
        'success' => true, 
        'id_venta' => $id_venta_generada,
        'message' => 'Venta registrada con éxito'
    ]);

} catch (Exception $e) {
    mysqli_rollback($conn);
    ob_clean();
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}