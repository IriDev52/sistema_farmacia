<?php 
    include("../conexion/conex.php"); // Asegúrate de que esta ruta sea correcta

    if (isset($_GET['id'])) {
        $id = $_GET['id'];

        mysqli_begin_transaction($conn); // Iniciar transacción

        try {
            // 1. Eliminar los registros relacionados en producto_ubicacion
            $delete_pu_query = "DELETE FROM producto_ubicacion WHERE ID_Producto = ?";
            $stmt_pu = mysqli_prepare($conn, $delete_pu_query);
            
            if (!$stmt_pu) {
                throw new Exception("Error al preparar la eliminación de producto_ubicacion: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt_pu, "i", $id);
            
            if (!mysqli_stmt_execute($stmt_pu)) {
                throw new Exception("Error al eliminar registros en producto_ubicacion: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_pu);

            // 2. Ahora, eliminar el producto de la tabla prductos
            $delete_p_query = "DELETE FROM productos WHERE id = ?";
            $stmt_p = mysqli_prepare($conn, $delete_p_query);
            
            if (!$stmt_p) {
                throw new Exception("Error al preparar la eliminación de productos: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt_p, "i", $id);
            
            if (!mysqli_stmt_execute($stmt_p)) {
                throw new Exception("Error al eliminar el producto de prductos: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_p);

            mysqli_commit($conn); // Confirmar ambas operaciones

            echo "<script>
                alert('Producto y sus asignaciones de ubicación eliminados correctamente.');
                window.location.href = 'productos.php'; 
            </script>";
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conn); // Revertir si algo falla
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
?>