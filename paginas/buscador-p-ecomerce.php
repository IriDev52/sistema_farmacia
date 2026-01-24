<?php
function buscarProductos($conn, $termino = '') {
    $termino = mysqli_real_escape_string($conn, $termino);
    
    // Seleccionamos explícitamente las columnas para evitar errores de "columna desconocida"
    $sql = "SELECT id, nombre_producto, descripcion, laboratorio_fabrica, stock_actual, precio_venta, precio_bs, ubicacion, imagen, estado 
            FROM productos 
            WHERE estado = 'Activo'";

    if (!empty($termino)) {
        $sql .= " AND (nombre_producto LIKE '%$termino%' 
                  OR laboratorio_fabrica LIKE '%$termino%' 
                  OR ubicacion LIKE '%$termino%')";
    }

    $sql .= " ORDER BY nombre_producto ASC";
    
    $resultado = mysqli_query($conn, $sql);
    return $resultado;
}
?>