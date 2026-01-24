<?php
include("../conexion/conex.php");

// Manejo de errores para desarrollo
ini_set('display_errors', 1);
error_reporting(E_ALL);

header('Content-Type: application/json');

$response = ['success' => false, 'message' => ''];

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    $response['message'] = 'ID de venta no proporcionado o inválido.';
    echo json_encode($response);
    exit();
}

$id_venta = $_GET['id'];

// --- 1. PROCESO: Consulta de la Venta y el Producto (JOIN) ---
// Usamos JOIN para traer el nombre del producto de una vez
$sql = "SELECT 
            v.id, 
            v.fecha_venta, 
            v.cantidad, 
            v.total_usd, 
            v.tasa_bcv_usada, 
            v.total_bs,
            p.nombre_producto 
        FROM ventas v
        JOIN productos p ON v.id_producto = p.id
        WHERE v.id = ?";

if ($stmt = $conn->prepare($sql)) {
    $stmt->bind_param("i", $id_venta);
    $stmt->execute();
    $result = $stmt->get_result();
    $venta_data = $result->fetch_assoc();
    $stmt->close();

    if (!$venta_data) {
        $response['message'] = 'Registro de venta no encontrado.';
        echo json_encode($response);
        exit();
    }
} else {
    $response['message'] = 'Error en el proceso de consulta: ' . $conn->error;
    echo json_encode($response);
    exit();
}

$conn->close();

// --- 2. REGISTRO: Respuesta estructurada para el frontend ---
$response['success'] = true;
$response['message'] = 'Detalles obtenidos con éxito.';
$response['venta'] = [
    'id' => $venta_data['id'],
    'fecha' => $venta_data['fecha_venta'],
    'producto' => $venta_data['nombre_producto'],
    'cantidad' => $venta_data['cantidad'],
    'tasa' => $venta_data['tasa_bcv_usada'],
    'total_usd' => $venta_data['total_usd'],
    'total_bs' => $venta_data['total_bs']
];

echo json_encode($response);
?>