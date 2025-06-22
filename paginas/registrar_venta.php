<?php
// C:\xampp\htdocs\sistema_farmacia\paginas\registrar_venta.php

// 1. Incluir el archivo de conexión. ES CRUCIAL QUE ESTE ARCHIVO NO IMPRIMA NADA.
//    Asegúrate de que 'conex.php' esté limpio y solo contenga código PHP.
//    Debe comenzar con <?php y terminar preferiblemente sin el ? > de cierre
//    para evitar espacios en blanco accidentales.
include '../conexion/conex.php'; // Asegúrate de que esta ruta es correcta y el archivo está limpio.

// 2. Establecer la cabecera Content-Type como lo PRIMERO que se envía al navegador,
//    después de cualquier posible error de PHP o include con salida.
//    Si hay algún error antes de esto, ya es demasiado tarde.
header('Content-Type: application/json');

// Descomentar para depuración exhaustiva si persisten los problemas.
// Una vez resuelto, vuelve a comentar o quita estas líneas.
// ini_set('display_errors', 1);
// error_reporting(E_ALL);

$response = ['success' => false, 'message' => ''];

// Verificar que la conexión a la base de datos sea válida
if (!isset($conn) || $conn->connect_error) {
    $response['message'] = 'Error en la conexión a la base de datos: ' . (isset($conn) ? $conn->connect_error : 'Variable $conn no definida.');
    echo json_encode($response);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);

    // Es crucial validar que 'productos' exista y sea un array
    if (json_last_error() !== JSON_ERROR_NONE) {
        $response['message'] = 'Error al decodificar JSON: ' . json_last_error_msg() . '. Datos recibidos: ' . $input;
        echo json_encode($response);
        exit();
    }

    if (empty($data) || !isset($data['productos']) || !is_array($data['productos'])) {
        $response['message'] = 'Datos de productos no válidos o vacíos.';
        echo json_encode($response);
        exit();
    }

    $totalVenta = 0;
    foreach ($data['productos'] as $producto) {
        // Asegúrate de que los datos recibidos tienen las claves correctas y son numéricos
        if (!isset($producto['cantidad']) || !is_numeric($producto['cantidad']) ||
            !isset($producto['precio_unitario']) || !is_numeric($producto['precio_unitario'])) {
            $response['message'] = 'Datos incompletos o inválidos (cantidad/precio) para un producto en el carrito.';
            echo json_encode($response);
            exit();
        }
        $totalVenta += $producto['cantidad'] * $producto['precio_unitario'];
    }

    // --- Inicio de la Transacción con MySQLi ---
    $conn->begin_transaction();

    try {
        // 1. Insertar la venta principal
        // Revisa el nombre de la columna en tu tabla 'ventas'. Si es 'total', úsalo.
        $stmtVenta = $conn->prepare("INSERT INTO ventas (total) VALUES (?)");
        if ($stmtVenta === false) {
            throw new Exception("Error al preparar la inserción de venta: " . $conn->error);
        }
        $stmtVenta->bind_param("d", $totalVenta); // 'd' para decimal/double
        if (!$stmtVenta->execute()) {
            throw new Exception("Error al ejecutar la inserción de venta: " . $stmtVenta->error);
        }
        $idVenta = $conn->insert_id; // Para MySQLi, usa $conn->insert_id
        $stmtVenta->close();

        // 2. Insertar los detalles de la venta y actualizar el stock
        $stmtDetalle = $conn->prepare("INSERT INTO detalle_venta (id_venta, id_producto, cantidad, precio_unitario, subtotal) VALUES (?, ?, ?, ?, ?)");
        $stmtUpdateStock = $conn->prepare("UPDATE productos SET stock_actual = stock_actual - ? WHERE id = ? AND stock_actual >= ?");

        if ($stmtDetalle === false) {
            throw new Exception("Error al preparar la inserción de detalle: " . $conn->error);
        }
        if ($stmtUpdateStock === false) {
            throw new Exception("Error al preparar la actualización de stock: " . $conn->error);
        }

        foreach ($data['productos'] as $producto) {
            $idProducto = $producto['id'];
            $cantidad = $producto['cantidad'];
            $precioUnitario = $producto['precio_unitario'];
            $subtotal = $cantidad * $precioUnitario;
            $stockDisponible = $producto['stock_disponible']; // Recuperar el stock disponible que enviaste desde el frontend

            // Validar stock antes de intentar actualizar
            if ($cantidad > $stockDisponible) {
                throw new Exception("Stock insuficiente para el producto ID " . $idProducto . ". Cantidad solicitada: " . $cantidad . ", Disponible: " . $stockDisponible);
            }

            // Insertar detalle de venta
            $stmtDetalle->bind_param("iiidd", $idVenta, $idProducto, $cantidad, $precioUnitario, $subtotal);
            if (!$stmtDetalle->execute()) {
                throw new Exception("Error al insertar detalle para producto ID " . $idProducto . ": " . $stmtDetalle->error);
            }

            // Actualizar stock. Asegurarse de que no baje de cero para evitar stock negativo.
            // La condición 'stock_actual >= ?' es crucial para evitar ventas con stock insuficiente
            // si múltiples usuarios intentan comprar el mismo artículo al mismo tiempo.
            $stmtUpdateStock->bind_param("iii", $cantidad, $idProducto, $cantidad);
            if (!$stmtUpdateStock->execute()) {
                throw new Exception("Error al actualizar stock para producto ID " . $idProducto . ": " . $stmtUpdateStock->error);
            }
            // Verificar si la actualización afectó alguna fila. Si no afectó, es porque el stock era insuficiente.
            if ($conn->affected_rows === 0) {
                 throw new Exception("No se pudo actualizar el stock para el producto ID " . $idProducto . ". Posiblemente stock insuficiente.");
            }
        }

        // Si todo va bien, confirmar la transacción
        $conn->commit();
        $response['success'] = true;
        $response['message'] = 'Venta registrada exitosamente. ID de Venta: ' . $idVenta;

    } catch (Exception $e) {
        // En caso de error, revertir la transacción
        $conn->rollback();
        $response['message'] = 'Error en la transacción: ' . $e->getMessage();
        // Log el error en el servidor para depuración
        error_log('Error en registrar_venta.php: ' . $e->getMessage());
    } finally {
        // Cerrar statements si se abrieron
        if (isset($stmtVenta) && $stmtVenta) $stmtVenta->close();
        if (isset($stmtDetalle) && $stmtDetalle) $stmtDetalle->close();
        if (isset($stmtUpdateStock) && $stmtUpdateStock) $stmtUpdateStock->close();
        // Opcional: Cerrar la conexión a la base de datos aquí si no se usará más en el script.
        // $conn->close();
    }
} else {
    $response['message'] = 'Método de solicitud no permitido.';
}

echo json_encode($response);
exit(); // Importante para asegurar que no se imprima nada más.
?>