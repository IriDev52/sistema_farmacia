<?php
// C:\xampp\htdocs\sistema_farmacia\paginas\registrar_venta.php

// Asegúrate de que este include define $conn como tu objeto de conexión MySQLi
include '../conexion/conex.php';

// Es crucial enviar esta cabecera para que el frontend espere JSON.
// DESPUÉS de depurar, descomenta esta línea.
header('Content-Type: application/json');

// Descomenta solo para depurar si el problema persiste y quieres ver errores HTML
// ini_set('display_errors', 1);
// error_reporting(E_ALL); // Añade esta línea para ver todas las advertencias y errores


$response = ['success' => false, 'message' => ''];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents('php://input'), true);

    // Es importante validar que 'productos' exista y sea un array
    if (empty($data) || !isset($data['productos']) || !is_array($data['productos'])) {
        $response['message'] = 'Datos de productos no válidos o vacíos.';
        echo json_encode($response);
        exit();
    }

    $totalVenta = 0;
    foreach ($data['productos'] as $producto) {
        // Asegúrate de que los datos recibidos tienen las claves correctas
        if (!isset($producto['cantidad']) || !isset($producto['precio_unitario'])) {
            $response['message'] = 'Datos incompletos para un producto en el carrito.';
            echo json_encode($response);
            exit();
        }
        $totalVenta += $producto['cantidad'] * $producto['precio_unitario'];
    }

    // --- Inicio de la Transacción con MySQLi ---
    // Usa $conn en lugar de $pdo
    // Esta es la línea que estaba causando el error en la línea 24 (si no cambiaste el código)
    $conn->begin_transaction();

    try {
        // 1. Insertar la venta principal
        // Asegúrate de que tu tabla 'ventas' tiene una columna 'total_venta' y no solo 'total'
        // Si tu columna es 'total', cámbialo aquí.
        $stmtVenta = $conn->prepare("INSERT INTO ventas (total) VALUES (?)"); // Asumo 'total_venta'
        if ($stmtVenta === false) {
             throw new Exception("Error al preparar la inserción de venta: " . $conn->error);
        }
        $stmtVenta->bind_param("d", $totalVenta); // 'd' para decimal/double
        $stmtVenta->execute();
        $idVenta = $conn->insert_id; // Para MySQLi, usa $conn->insert_id
        $stmtVenta->close();

        // 2. Insertar los detalles de la venta y actualizar el stock
        $stmtDetalle = $conn->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        if ($stmtDetalle === false) {
             throw new Exception("Error al preparar la inserción de detalle: " . $conn->error);
        }

        // Importante: verificar stock y actualizar
        // Usamos stock_actual como en tu tabla 'productos'
        $stmtUpdateStock = $conn->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ? AND stock_actual >= ?");
        if ($stmtUpdateStock === false) {
             throw new Exception("Error al preparar la actualización de stock: " . $conn->error);
        }


        foreach ($data['productos'] as $producto) {
            // Verificar stock antes de vender
            // Asumo que tu tabla 'productos' tiene una columna 'stock_actual'
            $stmtCheckStock = $conn->prepare("SELECT stock_actual FROM productos WHERE id = ?");
            if ($stmtCheckStock === false) {
                 throw new Exception("Error al preparar la verificación de stock: " . $conn->error);
            }
            $stmtCheckStock->bind_param("i", $producto['id']);
            $stmtCheckStock->execute();
            $resultCheckStock = $stmtCheckStock->get_result();
            $currentStock = $resultCheckStock->fetch_assoc()['stock_actual'] ?? 0; // Usar fetch_assoc() para MySQLi
            $stmtCheckStock->close();

            if ($currentStock < $producto['cantidad']) {
                // Si hay stock insuficiente, hacemos rollback y enviamos mensaje
                $conn->rollback();
                $response['message'] = 'Stock insuficiente para el producto: ' . htmlspecialchars($producto['nombre']) . '. Stock disponible: ' . $currentStock;
                echo json_encode($response);
                exit(); // Salir para que no continúe el bucle
            }

            $subtotal = $producto['cantidad'] * $producto['precio_unitario'];

            // Insertar detalle de la venta
            $stmtDetalle->bind_param("iiidd", $idVenta, $producto['id'], $producto['cantidad'], $producto['precio_unitario'], $subtotal); // iiidd: int, int, int, double, double
            $stmtDetalle->execute();

            // Descontar el stock
            $stmtUpdateStock->bind_param("iii", $producto['cantidad'], $producto['id'], $producto['cantidad']); // int, int, int
            $stmtUpdateStock->execute();
        }

        // Si todo salió bien, confirmar la transacción
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Venta registrada con éxito. ¡Stock actualizado! 👍';

    } catch (Exception $e) { // Usamos Exception general para capturar errores de MySQLi
        // Si algo falla, deshacer la transacción
        $conn->rollback();
        $response['message'] = 'Error al registrar la venta: ' . $e->getMessage();
        // Puedes loggear el error para depuración
        // error_log('Error en registrar_venta.php: ' . $e->getMessage());
    } finally {
        // Asegúrate de cerrar las sentencias preparadas
        if (isset($stmtVenta) && $stmtVenta !== false) $stmtVenta->close();
        if (isset($stmtDetalle) && $stmtDetalle !== false) $stmtDetalle->close();
        if (isset($stmtUpdateStock) && $stmtUpdateStock !== false) $stmtUpdateStock->close();
        if (isset($stmtCheckStock) && $stmtCheckStock !== false) $stmtCheckStock->close();
    }

} else {
    $response['message'] = 'Método no permitido.';
}

echo json_encode($response);
?>