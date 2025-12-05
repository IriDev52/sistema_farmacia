<?php
// buscador-p-ecomerce.php (Al mismo nivel que ecommerce.php)

/**
 * Función para obtener productos filtrados por un término de búsqueda.
 *
 * @param mysqli $conn Objeto de conexión a la base de datos.
 * @param string $termino_busqueda El texto a buscar.
 * @return mysqli_result|bool El resultado de la consulta o FALSE en caso de error.
 */
function buscarProductos($conn, $termino_busqueda) {
    $sql = "SELECT id, nombre_producto, descripcion, precio_venta FROM productos";
    $params = [];
    $types = '';

    // Si hay búsqueda, ajustamos la consulta para usar LIKE de forma segura
    if (!empty(trim($termino_busqueda))) {
        $sql .= " WHERE nombre_producto LIKE ? OR descripcion LIKE ?";
        $busqueda = '%' . $termino_busqueda . '%';

        $params[] = $busqueda;
        $params[] = $busqueda;
        $types = 'ss'; 
    }

    $stmt = $conn->prepare($sql);

    if ($stmt === FALSE) {
        // Usa error_log en producción, aquí mantenemos el echo para la depuración inmediata
        echo '<div class="alert alert-danger" role="alert">Error al preparar la consulta: ' . $conn->error . '</div>';
        return false;
    }

    if (!empty($params)) {
        // Vinculación segura de parámetros
        $stmt->bind_param($types, ...$params);
    }

    $stmt->execute();
    $resultado = $stmt->get_result();
    $stmt->close();
    
    return $resultado;
}
?>