<?php
// Incluir el archivo de conexión a la base de datos
include("../conexion/conex.php");
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $response['message'] = 'ID de venta no proporcionado o inválido.';
    echo json_encode($response);
    exit();
}

$id_venta = $_GET['id'];

if ($conn->connect_error) {
    $response['message'] = 'Error de conexión a la base de datos: ' . $conn->connect_error;
    echo json_encode($response);
    exit();
}

// 1. Obtener los datos principales de la venta
$sql_venta = "SELECT id, fecha_venta, total FROM ventas WHERE id = ?";
if ($stmt_venta = $conn->prepare($sql_venta)) {
    $stmt_venta->bind_param("i", $id_venta);
    $stmt_venta->execute();
    $result_venta = $stmt_venta->get_result();
    $venta_data = $result_venta->fetch_assoc();
    $stmt_venta->close();

    if (!$venta_data) {
        $response['message'] = 'Venta no encontrada.';
        echo json_encode($response);
        exit();
    }
} else {
    $response['message'] = 'Error en la preparación de la consulta de venta: ' . $conn->error;
    echo json_encode($response);
    exit();
}

// 2. Obtener los detalles de los productos de la venta
$sql_detalles = "
    SELECT 
        dv.cantidad, 
        dv.precio_unitario, 
        dv.subtotal, 
        p.nombre_producto, 
        p.id as producto_id
    FROM detalle_venta dv
    JOIN productos p ON dv.id_producto = p.id
    WHERE dv.id_venta = ?
";

$detalles_venta = [];
if ($stmt_detalles = $conn->prepare($sql_detalles)) {
    $stmt_detalles->bind_param("i", $id_venta);
    $stmt_detalles->execute();
    $result_detalles = $stmt_detalles->get_result();
    while ($row = $result_detalles->fetch_assoc()) {
        $detalles_venta[] = $row;
    }
    $stmt_detalles->close();
} else {
    $response['message'] = 'Error en la preparación de la consulta de detalles de venta: ' . $conn->error;
    echo json_encode($response);
    exit();
}

$conn->close();

$response['success'] = true;
$response['message'] = 'Detalles de venta obtenidos con éxito.';
$response['venta'] = $venta_data;
$response['detalles_venta'] = $detalles_venta;

echo json_encode($response);

?>