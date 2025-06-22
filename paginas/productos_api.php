<?php
// productos_api.php

// Incluimos tu archivo de conexión MySQLi
include("../conexion/conex.php"); // Asegúrate de que esta ruta sea correcta

header('Content-Type: application/json');

$query_param = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query_param)) {
    echo json_encode([]);
    exit();
}

$productos = [];

try {
    // Primero, intenta buscar por ID del producto
    // Esto es útil si el usuario introduce un número, asumiendo que es el ID del producto
    if (is_numeric($query_param)) {
        $sql_id = "SELECT id, nombre_producto, precio_venta, stock_actual FROM productos WHERE id = ? LIMIT 1";
        $stmt_id = mysqli_prepare($conn, $sql_id);
        if ($stmt_id) {
            mysqli_stmt_bind_param($stmt_id, "i", $query_param);
            mysqli_stmt_execute($stmt_id);
            $result_id = mysqli_stmt_get_result($stmt_id);
            if ($row = mysqli_fetch_assoc($result_id)) {
                $productos[] = $row; // Añade el producto encontrado por ID
            }
            mysqli_stmt_close($stmt_id);
        } else {
            throw new Exception('Error en la preparación de la consulta por ID: ' . mysqli_error($conn));
        }
    }

    // Si no se encontró por ID, o si la consulta original no era un número (es texto),
    // busca por el nombre del producto
    // Ojo: Si ya encontraste por ID, y la búsqueda por texto podría encontrar el mismo ID,
    // se maneja para evitar duplicados en el array $productos.
    if (empty($productos) || !is_numeric($query_param)) { // Añadimos esta condición para buscar por nombre si no se encontró por ID
        $search_term = "%" . $query_param . "%";
        // Buscamos por nombre_producto (ajusta si tu columna se llama 'nombre' o similar)
        $sql_text = "SELECT id, nombre_producto, precio_venta, stock_actual FROM productos WHERE nombre_producto LIKE ? LIMIT 10";
        $stmt_text = mysqli_prepare($conn, $sql_text);
        if ($stmt_text) {
            mysqli_stmt_bind_param($stmt_text, "s", $search_term);
            mysqli_stmt_execute($stmt_text);
            $results_text = mysqli_stmt_get_result($stmt_text);
            
            while ($row = mysqli_fetch_assoc($results_text)) {
                // Evita añadir duplicados si un producto ya se encontró por su ID
                $found = false;
                foreach ($productos as $p) {
                    if ($p['id'] == $row['id']) {
                        $found = true;
                        break;
                    }
                }
                if (!$found) {
                    $productos[] = $row;
                }
            }
            mysqli_stmt_close($stmt_text);
        } else {
            throw new Exception('Error en la preparación de la consulta de texto: ' . mysqli_error($conn));
        }
    }

    echo json_encode($productos);

} catch (Exception $e) {
    error_log("Error en productos_api.php: " . $e->getMessage());
    echo json_encode(['error' => 'Error en la operación: ' . $e->getMessage()]);
} finally {
    if (isset($conn) && $conn) {
        mysqli_close($conn);
    }
}
?>