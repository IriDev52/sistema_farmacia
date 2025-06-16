<?php 
// eliminarUbicacion.php

include("../conexion/conex.php"); // Asegúrate de que esta ruta sea correcta y que define $conex
include("../recursos/header.php"); // Agregué el header para que se muestren los alerts correctamente

if (isset($_GET['id'])) {
    $id_ubicacion_eliminar = $_GET['id']; // Renombré la variable para mayor claridad

    mysqli_begin_transaction($conex); // Iniciar transacción - CORRECCIÓN: Usar $conex

    try {
        // 1. Eliminar los registros relacionados en producto_ubicacion que hacen referencia a esta ubicación
        $delete_pu_query = "DELETE FROM producto_ubicacion WHERE ID_Ubicacion = ?";
        $stmt_pu = mysqli_prepare($conex, $delete_pu_query); // CORRECCIÓN: Usar $conex
        
        if (!$stmt_pu) {
            throw new Exception("Error al preparar la eliminación de registros de producto_ubicacion: " . mysqli_error($conex)); // CORRECCIÓN: Usar $conex
        }
        
        mysqli_stmt_bind_param($stmt_pu, "i", $id_ubicacion_eliminar);
        
        if (!mysqli_stmt_execute($stmt_pu)) {
            throw new Exception("Error al eliminar registros en producto_ubicacion: " . mysqli_error($conex)); // CORRECCIÓN: Usar $conex
        }
        mysqli_stmt_close($stmt_pu);

        // 2. Ahora, eliminar la ubicación de la tabla ubicacion
        $delete_u_query = "DELETE FROM ubicacion WHERE ID_Ubicacion = ?"; // Corregido: ID_Ubicacion
        $stmt_u = mysqli_prepare($conex, $delete_u_query); // Renombré la variable para claridad y CORRECCIÓN: Usar $conex
        
        if (!$stmt_u) {
            throw new Exception("Error al preparar la eliminación de la ubicación: " . mysqli_error($conex)); // CORRECCIÓN: Usar $conex
        }
        
        mysqli_stmt_bind_param($stmt_u, "i", $id_ubicacion_eliminar);
        
        if (!mysqli_stmt_execute($stmt_u)) {
            throw new Exception("Error al eliminar la ubicación de la tabla ubicacion: " . mysqli_error($conex)); // CORRECCIÓN: Usar $conex
        }
        mysqli_stmt_close($stmt_u);

        mysqli_commit($conex); // Confirmar ambas operaciones - CORRECCIÓN: Usar $conex

        echo "<script>
            alert('Ubicación y sus asignaciones de productos eliminados correctamente.');
            window.location.href = 'ubicacion.php'; // Redirige a la página de ubicaciones
        </script>";
        exit();

    } catch (Exception $e) {
        mysqli_rollback($conex); // Revertir si algo falla - CORRECCIÓN: Usar $conex
        echo "<script>
            alert('Error al eliminar la ubicación: " . addslashes($e->getMessage()) . "');
            window.location.href = 'ubicacion.php'; // Volver a la lista de ubicaciones
        </script>";
        exit();
    }
} else {
    // Si no se proporcionó un ID de ubicación, redirigir
    header('Location: ubicacion.php');
    exit();
}
?>