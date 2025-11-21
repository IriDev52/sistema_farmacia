<?php 
    include("../conexion/conex.php"); 
    include("../recursos/header.php");

    if (isset($_GET['id'])) {
        $id_ubicacion_eliminar = $_GET['id']; 

        mysqli_begin_transaction($conn);

        try {
           
            $delete_pu_query = "DELETE FROM producto_ubicacion WHERE ID_Ubicacion = ?";
            $stmt_pu = mysqli_prepare($conn, $delete_pu_query);
            
            if (!$stmt_pu) {
                throw new Exception("Error al preparar la eliminación de registros de producto_ubicacion: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt_pu, "i", $id_ubicacion_eliminar);
            
            if (!mysqli_stmt_execute($stmt_pu)) {
                throw new Exception("Error al eliminar registros en producto_ubicacion: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_pu);

            
            $delete_u_query = "DELETE FROM ubicacion WHERE ID_Ubicacion = ?"; 
            $stmt_u = mysqli_prepare($conn, $delete_u_query);
            
            if (!$stmt_u) {
                throw new Exception("Error al preparar la eliminación de la ubicación: " . mysqli_error($conn));
            }
            
            mysqli_stmt_bind_param($stmt_u, "i", $id_ubicacion_eliminar);
            
            if (!mysqli_stmt_execute($stmt_u)) {
                throw new Exception("Error al eliminar la ubicación de la tabla ubicacion: " . mysqli_error($conn));
            }
            mysqli_stmt_close($stmt_u);

            mysqli_commit($conn); 

            echo "<script>
                alert('Ubicación y sus asignaciones de productos eliminados correctamente.');
                window.location.href = 'ubicacion.php'; // Redirige a la página de ubicaciones
            </script>";
            exit();

        } catch (Exception $e) {
            mysqli_rollback($conn); 
            echo "<script>
                alert('Error al eliminar la ubicación: " . addslashes($e->getMessage()) . "');
                window.location.href = 'ubicacion.php'; // Volver a la lista de ubicaciones
            </script>";
            exit();
        }
    } else {
        
        header('Location: ubicacion.php');
        exit();
    }
?>