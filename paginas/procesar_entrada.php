<?php
include("../conexion/conex.php");
session_start();

header('Content-Type: application/json');

$response = [
    'success' => false,
    'message' => 'Error desconocido.'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_producto = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $cantidad_entrada = isset($_POST['cantidad']) ? (int)$_POST['cantidad'] : 0;
    $nueva_ubicacion = isset($_POST['ubicacion_destino']) ? mysqli_real_escape_string($conn, $_POST['ubicacion_destino']) : '';
    $observaciones = isset($_POST['observaciones']) ? mysqli_real_escape_string($conn, $_POST['observaciones']) : '';
    $fecha_vencimiento = isset($_POST['fecha_vencimiento']) && !empty($_POST['fecha_vencimiento']) ? $_POST['fecha_vencimiento'] : null;
    $numero_lote = isset($_POST['numero_lote']) ? mysqli_real_escape_string($conn, $_POST['numero_lote']) : null;

    if (empty($id_producto) || $cantidad_entrada <= 0 || empty($nueva_ubicacion)) {
        $response['message'] = "Por favor, complete los campos Producto, Cantidad y Ubicación. La cantidad debe ser un número positivo.";
        echo json_encode($response);
        exit();
    }

    mysqli_begin_transaction($conn);
    $success = true;
    $message = "";

    try {
        // 1. Obtener el stock actual y la fecha de vencimiento antes de la actualización
        $get_stock_query = "SELECT stock_actual, fecha_vencimiento FROM productos WHERE id = ?";
        $stmt_get_stock = mysqli_prepare($conn, $get_stock_query);
        if (!$stmt_get_stock) {
            throw new Exception("Error al preparar la consulta para obtener stock: " . mysqli_error($conn));
        }
        mysqli_stmt_bind_param($stmt_get_stock, "i", $id_producto);
        mysqli_stmt_execute($stmt_get_stock);
        mysqli_stmt_bind_result($stmt_get_stock, $current_stock_actual, $current_fecha_vencimiento);
        mysqli_stmt_fetch($stmt_get_stock);
        mysqli_stmt_close($stmt_get_stock);

        $new_stock_actual = $current_stock_actual + $cantidad_entrada;

        // 2. Preparar y ejecutar el UPDATE
        // Usamos una consulta diferente dependiendo de si hay una nueva fecha de vencimiento
        if ($fecha_vencimiento) {
            $update_query = "UPDATE productos SET stock_actual = ?, ubicacion = ?, fecha_vencimiento = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            if (!$stmt_update) {
                 throw new Exception("Error al preparar la consulta de actualización (con vencimiento): " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt_update, "issi", $new_stock_actual, $nueva_ubicacion, $fecha_vencimiento, $id_producto);
        } else {
            $update_query = "UPDATE productos SET stock_actual = ?, ubicacion = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($conn, $update_query);
            if (!$stmt_update) {
                throw new Exception("Error al preparar la consulta de actualización (sin vencimiento): " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt_update, "isi", $new_stock_actual, $nueva_ubicacion, $id_producto);
        }
        
        if (!mysqli_stmt_execute($stmt_update)) {
            $success = false;
            $message = "Error al ejecutar la actualización: " . mysqli_stmt_error($stmt_update);
        }
        mysqli_stmt_close($stmt_update);

        // 3. Registrar el movimiento en el historial
        if ($success) {
            $log_movement_query = "INSERT INTO movimientos_inventario (id_producto, tipo_movimiento, cantidad, stock_antes, stock_despues, ubicacion, observaciones) VALUES (?, 'Entrada', ?, ?, ?, ?, ?)";

            // Añadir información de lote y vencimiento a las observaciones si están disponibles
            $full_observaciones = $observaciones;
            if (!empty($numero_lote)) {
                $full_observaciones .= ($full_observaciones ? ' | ' : '') . "Lote: " . $numero_lote;
            }
            if ($fecha_vencimiento) {
                $full_observaciones .= ($full_observaciones ? ' | ' : '') . "Vencimiento: " . $fecha_vencimiento;
            }
            
            $stmt_log_movement = mysqli_prepare($conn, $log_movement_query);
            if (!$stmt_log_movement) {
                throw new Exception("Error al preparar la consulta de historial: " . mysqli_error($conn));
            }
            mysqli_stmt_bind_param($stmt_log_movement, "iddis", $id_producto, $cantidad_entrada, $current_stock_actual, $new_stock_actual, $nueva_ubicacion, $full_observaciones);
            
            if (!mysqli_stmt_execute($stmt_log_movement)) {
                $success = false;
                $message .= " Error al registrar el movimiento en el historial: " . mysqli_stmt_error($stmt_log_movement);
            }
            mysqli_stmt_close($stmt_log_movement);
        }

        // 4. Confirmar o revertir la transacción
        if ($success) {
            mysqli_commit($conn);
            $response['success'] = true;
            $response['message'] = "Entrada de stock registrada correctamente y ubicación actualizada.";
        } else {
            mysqli_rollback($conn);
            $response['message'] = $message . " Por favor, intente de nuevo.";
        }

    } catch (Exception $e) {
        mysqli_rollback($conn);
        $response['message'] = "Error inesperado: " . $e->getMessage();
    }
} else {
    $response['message'] = "Método de solicitud no válido.";
}

echo json_encode($response);
mysqli_close($conn);
?>