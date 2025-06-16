<?php
// Habilitar la visualización de errores para depuración (QUÍTALA EN PRODUCCIÓN)
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

// Establece el encabezado para que el navegador sepa que la respuesta es JSON
header('Content-Type: application/json');

include("../conexion/conex.php"); // Incluye tu archivo de conexión a la BD, que debe definir $conex

$response = ['error' => ''];

if (isset($_GET['query'])) {
    $search_query = '%' . $_GET['query'] . '%';

    // Consulta para buscar productos por nombre o ID y obtener stock y precio
    // Asegúrate de que los nombres de las columnas coincidan con tu tabla 'productos'
    // Asumo que el ID es 'id' y el nombre es 'nombre_producto'
    $query = "SELECT id, nombre_producto, stock_actual, precio_venta FROM productos WHERE nombre_producto LIKE ? OR id LIKE ? LIMIT 10";
    $stmt = mysqli_prepare($conex, $query); // CORRECCIÓN: Usar $conex

    if ($stmt) {
        // "ss" porque hay dos parámetros string (aunque uno sea LIKE un número, se trata como string en LIKE)
        mysqli_stmt_bind_param($stmt, "ss", $search_query, $search_query);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        $productos = [];
        while ($row = mysqli_fetch_assoc($result)) {
            // Convertir valores numéricos a su tipo correcto para JSON
            $row['stock_actual'] = (int)$row['stock_actual'];
            $row['precio_venta'] = (float)$row['precio_venta'];
            $productos[] = $row;
        }
        mysqli_stmt_close($stmt);
        echo json_encode($productos);
        exit(); // Terminar el script después de enviar la respuesta JSON
    } else {
        $response['error'] = "Error al preparar la consulta de búsqueda: " . mysqli_error($conex); // CORRECCIÓN: Usar $conex
    }
} else {
    $response['error'] = "No se proporcionó una consulta de búsqueda.";
}

// Si hubo un error o no se proporcionó query, enviar la respuesta de error
echo json_encode($response);
?>