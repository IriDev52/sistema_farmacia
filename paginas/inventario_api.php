<?php
include("../conexion/conex.php");

header('Content-Type: application/json');

$action = isset($_GET['action']) ? $_GET['action'] : (isset($_POST['action']) ? $_POST['action'] : '');

switch ($action) {
    case 'get_inventory_data':
        $data = [];
        $query = "SELECT id, nombre_producto, laboratorio_fabrica, stock_actual, estado, fecha_vencimiento, ubicacion FROM productos WHERE estado = 'Activo' ORDER BY nombre_producto";
        $result = mysqli_query($conn, $query);
        if ($result) {
            while ($row = mysqli_fetch_assoc($result)) {
                $data[] = $row;
            }
        }
        echo json_encode(['data' => $data]);
        break;

    case 'get_stats':
        $hoy = date('Y-m-d');
        $fecha_90_dias = date('Y-m-d', strtotime('+90 days'));
        
        $total_productos = 0;
        $query_total_productos = "SELECT COUNT(*) AS total_productos FROM productos WHERE estado = 'Activo'";
        $result_total_productos = mysqli_query($conn, $query_total_productos);
        if ($result_total_productos) {
            $total_productos = mysqli_fetch_assoc($result_total_productos)['total_productos'];
        }

        $total_stock_actual = 0;
        $query_total_stock = "SELECT SUM(stock_actual) AS total_stock FROM productos WHERE estado = 'Activo'";
        $result_total_stock = mysqli_query($conn, $query_total_stock);
        if ($result_total_stock) {
            $total_stock_actual = mysqli_fetch_assoc($result_total_stock)['total_stock'];
        }

        $total_vencidos = 0;
        $query_vencidos = "SELECT COUNT(*) AS total_vencidos FROM productos WHERE fecha_vencimiento < '{$hoy}' AND estado = 'Activo'";
        $result_vencidos = mysqli_query($conn, $query_vencidos);
        if ($result_vencidos) {
            $total_vencidos = mysqli_fetch_assoc($result_vencidos)['total_vencidos'];
        }

        $total_proximos = 0;
        $query_proximos = "SELECT COUNT(*) AS total_proximos FROM productos WHERE fecha_vencimiento >= '{$hoy}' AND fecha_vencimiento <= '{$fecha_90_dias}' AND estado = 'Activo'";
        $result_proximos = mysqli_query($conn, $query_proximos);
        if ($result_proximos) {
            $total_proximos = mysqli_fetch_assoc($result_proximos)['total_proximos'];
        }

        echo json_encode([
            'total_productos' => $total_productos,
            'total_stock_actual' => $total_stock_actual,
            'total_vencidos' => $total_vencidos,
            'total_proximos' => $total_proximos
        ]);
        break;

    case 'register_entrada':
        $response = ['success' => false, 'message' => ''];
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $id_producto = $_POST['id'] ?? null;
            $cantidad = $_POST['cantidad'] ?? null;
            $ubicacion_destino = $_POST['ubicacion_destino'] ?? null;
            $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? null;

            
            $usuario_id = 1; 

            if ($id_producto && is_numeric($cantidad) && $cantidad > 0 && $ubicacion_destino) {
                mysqli_begin_transaction($conn);
                try {
                    
                    $query_get_stock = "SELECT stock_actual, nombre_producto, ubicacion FROM productos WHERE id = ?";
                    $stmt_get = mysqli_prepare($conn, $query_get_stock);
                    mysqli_stmt_bind_param($stmt_get, 'i', $id_producto);
                    mysqli_stmt_execute($stmt_get);
                    $result_get = mysqli_stmt_get_result($stmt_get);
                    $product = mysqli_fetch_assoc($result_get);

                    if ($product) {
                        $stock_anterior = $product['stock_actual'];
                        $ubicacion_origen = $product['ubicacion'];
                        $nombre_producto = $product['nombre_producto'];
                        $nuevo_stock = $stock_anterior + $cantidad;
                        
                       
                        $query_update_stock = "UPDATE productos SET stock_actual = ?, ubicacion = ? WHERE id = ?";
                        $stmt_update = mysqli_prepare($conn, $query_update_stock);
                        mysqli_stmt_bind_param($stmt_update, 'isi', $nuevo_stock, $ubicacion_destino, $id_producto);
                        $update_success = mysqli_stmt_execute($stmt_update);

                       
                        if ($fecha_vencimiento && !empty($fecha_vencimiento)) {
                            $query_update_vencimiento = "UPDATE productos SET fecha_vencimiento = ? WHERE id = ?";
                            $stmt_update_venc = mysqli_prepare($conn, $query_update_vencimiento);
                            mysqli_stmt_bind_param($stmt_update_venc, 'si', $fecha_vencimiento, $id_producto);
                            mysqli_stmt_execute($stmt_update_venc);
                        }

                      
                        $tipo_movimiento = 'Entrada';
                        $observaciones = "Entrada de stock. Se suman {$cantidad} unidades. Ubicación anterior: {$ubicacion_origen}. Ubicación actual: {$ubicacion_destino}.";
                        
                        
                        $query_insert_mov = "INSERT INTO movimientos_inventario (id_producto, tipo_movimiento, cantidad, stock_antes, stock_despues, ubicacion, observaciones, usuario_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
                        $stmt_insert = mysqli_prepare($conn, $query_insert_mov);
                        mysqli_stmt_bind_param($stmt_insert, 'isiiissi', $id_producto, $tipo_movimiento, $cantidad, $stock_anterior, $nuevo_stock, $ubicacion_destino, $observaciones, $usuario_id);
                        $log_success = mysqli_stmt_execute($stmt_insert);

                        if ($update_success && $log_success) {
                            mysqli_commit($conn);
                            $response['success'] = true;
                            $response['message'] = "Entrada de stock y ubicación registrada con éxito para el producto '{$nombre_producto}'. Nuevo stock: {$nuevo_stock}.";
                        } else {
                            mysqli_rollback($conn);
                            $response['message'] = "Error al registrar la entrada de stock o el movimiento: " . mysqli_error($conn);
                        }
                    } else {
                        $response['message'] = "Producto no encontrado.";
                    }
                } catch (Exception $e) {
                    mysqli_rollback($conn);
                    $response['message'] = "Error inesperado: " . $e->getMessage();
                }
            } else {
                $response['message'] = "Datos de entrada inválidos. Asegúrese de ingresar un producto, una cantidad válida y una ubicación.";
            }
        } else {
            $response['message'] = "Método de solicitud no válido.";
        }
        echo json_encode($response);
        break;

    default:
        echo json_encode(['data' => [], 'message' => 'Invalid action']);
        break;
}

mysqli_close($conn);
?>