<?php
ob_start(); 
error_reporting(0);
include("../conexion/conex.php");

header("Access-Control-Allow-Origin: *");
header('Content-Type: application/json; charset=utf-8');

if (ob_get_length()) ob_clean(); 

$action = $_GET['action'] ?? $_POST['action'] ?? '';

switch ($action) {
    case 'get_inventory_data':
        $data = [];
        $query = "SELECT id, nombre_producto, laboratorio_fabrica, stock_actual, estado, fecha_vencimiento, ubicacion FROM productos WHERE estado = 'Activo'";
        $result = mysqli_query($conn, $query);
        while ($row = mysqli_fetch_assoc($result)) {
            $data[] = $row;
        }
        echo json_encode(['data' => $data]);
        break;

    case 'get_stats':
        $hoy = date('Y-m-d');
        $f90 = date('Y-m-d', strtotime('+90 days'));
        $res_p = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM productos WHERE estado = 'Activo'"));
        $res_s = mysqli_fetch_assoc(mysqli_query($conn, "SELECT SUM(stock_actual) as t FROM productos WHERE estado = 'Activo'"));
        $res_v = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM productos WHERE fecha_vencimiento < '$hoy' AND estado = 'Activo'"));
        $res_x = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as t FROM productos WHERE fecha_vencimiento >= '$hoy' AND fecha_vencimiento <= '$f90' AND estado = 'Activo'"));

        echo json_encode([
            'total_productos' => $res_p['t'] ?? 0,
            'total_stock_actual' => $res_s['t'] ?? 0,
            'total_vencidos' => $res_v['t'] ?? 0,
            'total_proximos' => $res_x['t'] ?? 0
        ]);
        break;
}
mysqli_close($conn);
ob_end_flush();