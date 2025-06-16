<?php 
// eliminarProducto.php

include("../conexion/conex.php"); // Asegúrate de que esta ruta sea correcta y que define $conex
// No se incluye header.php aquí, ya que este script solo realiza una operación de eliminación y redirige.
// El alert de JS se mostrará antes de la redirección.

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Iniciar transacción - CORRECCIÓN: Usar $conex
    mysqli_begin_transaction($conex); 

    try {
        // 1. Eliminar los registros relacionados en producto_ubicacion
        $delete_pu_query = "DELETE FROM producto_ubicacion WHERE ID_Producto = ?";
        // CORRECCIÓN: Usar $conex
        $stmt_pu = mysqli_prepare($conex, $delete_pu_query); 
        
        if (!$stmt_pu) {
            // CORRECCIÓN: Usar $conex
            throw new Exception("Error al preparar la eliminación de producto_ubicacion: " . mysqli_error($conex)); 
        }
        
        mysqli_stmt_bind_param($stmt_pu, "i", $id);
        
        if (!mysqli_stmt_execute($stmt_pu)) {
            // CORRECCIÓN: Usar $conex
            throw new Exception("Error al eliminar registros en producto_ubicacion: " . mysqli_error($conex)); 
        }
        mysqli_stmt_close($stmt_pu);

        // 2. Ahora, eliminar el producto de la tabla productos
        // CORRECCIÓN: Cambié 'prductos' a 'productos' y usé 'id_producto' como ID. 
        // Si tu columna ID es realmente 'id', cámbialo de nuevo.
        $delete_p_query = "DELETE FROM productos WHERE id = ?"; 
        // CORRECCIÓN: Usar $conex
        $stmt_p = mysqli_prepare($conex, $delete_p_query); 
        
        if (!$stmt_p) {
            // CORRECCIÓN: Usar $conex
            throw new Exception("Error al preparar la eliminación de productos: " . mysqli_error($conex)); 
        }
        
        mysqli_stmt_bind_param($stmt_p, "i", $id);
        
        if (!mysqli_stmt_execute($stmt_p)) {
            // CORRECCIÓN: Usar $conex y la tabla corregida
            throw new Exception("Error al eliminar el producto de productos: " . mysqli_error($conex)); 
        }
        mysqli_stmt_close($stmt_p);

        // Confirmar ambas operaciones - CORRECCIÓN: Usar $conex
        mysqli_commit($conex); 

        echo "<script>
            alert('Producto y sus asignaciones de ubicación eliminados correctamente.');
            window.location.href = 'productos.php'; 
        </script>";
        exit();

    } catch (Exception $e) {
        // Revertir si algo falla - CORRECCIÓN: Usar $conex
        mysqli_rollback($conex); 
        echo "<script>
            alert('Error al eliminar el producto: " . addslashes($e->getMessage()) . "');
            window.location.href = 'productos.php'; // Volver a la lista de productos
        </script>";
        exit();
    }
} else {
    header('Location: productos.php');
    exit();
}