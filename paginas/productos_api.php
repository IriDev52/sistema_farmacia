<?php
// productos_api.php

// Incluimos tu archivo de conexión MySQLi
include("../conexion/conex.php"); // Asegúrate de que esta ruta sea correcta

// Buffer para evitar output prematuro
ob_start();
header('Content-Type: application/json');
ob_clean();

$query_param = isset($_GET['query']) ? trim($_GET['query']) : '';

if (empty($query_param)) {
    echo json_encode([]);
    exit();
}

$productos = [];

try {
    // Primero, intenta buscar por ID del producto
    if (is_numeric($query_param)) {
        $sql_id = "SELECT id, nombre_producto, precio_venta, stock_actual FROM productos WHERE id = ? LIMIT 1";
        $stmt_id = mysqli_prepare($conn, $sql_id);
        if ($stmt_id) {
            mysqli_stmt_bind_param($stmt_id, "i", $query_param);
            mysqli_stmt_execute($stmt_id);
            $result_id = mysqli_stmt_get_result($stmt_id);
            if ($row = mysqli_fetch_assoc($result_id)) {
                $productos[] = $row;
            }
            mysqli_stmt_close($stmt_id);
        } else {
            throw new Exception('Error en la preparación de la consulta por ID: ' . mysqli_error($conn));
        }
    }

    // Si no se encontró por ID, busca por nombre
    if (empty($productos) || !is_numeric($query_param)) {
        $search_term = "%" . $query_param . "%";
        $sql_text = "SELECT id, nombre_producto, precio_venta, stock_actual FROM productos WHERE nombre_producto LIKE ? LIMIT 10";
        $stmt_text = mysqli_prepare($conn, $sql_text);
        if ($stmt_text) {
            mysqli_stmt_bind_param($stmt_text, "s", $search_term);
            mysqli_stmt_execute($stmt_text);
            $results_text = mysqli_stmt_get_result($stmt_text);
            
            while ($row = mysqli_fetch_assoc($results_text)) {
                // Evita añadir duplicados
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
    ob_end_flush();
}
// NO CIERRES CON ?>