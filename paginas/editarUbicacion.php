<?php

error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);

include("../recursos/header.php"); 
include("../conexion/conex.php"); 

$id_ubicacion_editar = null; 
$ubicacion = null; 


if (isset($_POST['actualizar_ubicacion'])) {
    $id_ubicacion_actualizar = $_POST['id_ubicacion']; 
    $nueva_descripcion = $_POST['descripcion_ubicacion'];

   
    if (empty($id_ubicacion_actualizar) || empty($nueva_descripcion)) {
        echo '<script>alert("Por favor, complete todos los campos."); window.history.back();</script>';
        exit(); 
    }

   
    $query = "UPDATE ubicacion SET descripcion_ubicacion = ? WHERE id_ubicacion = ?";
    $stmt = mysqli_prepare($conn, $query);

    
    if ($stmt) {
       
        mysqli_stmt_bind_param($stmt, "si", $nueva_descripcion, $id_ubicacion_actualizar);

        
        if (mysqli_stmt_execute($stmt)) {
            
            echo '<script>alert("Ubicación actualizada correctamente."); window.location.href = "ubicacion.php";</script>';
            exit(); 
        } else {
           
            echo '<script>alert("Error al actualizar la ubicación: ' . addslashes(mysqli_error($conn)) . '"); window.history.back();</script>';
            exit();
        }
        mysqli_stmt_close($stmt); 
    } else {
      
        echo '<script>alert("Error al preparar la consulta de actualización: ' . addslashes(mysqli_error($conn)) . '"); window.history.back();</script>';
        exit();
    }
}


if (isset($_GET['id'])) {
    $id_ubicacion_editar = $_GET['id'];

    
    $query = "SELECT id_ubicacion, descripcion_ubicacion FROM ubicacion WHERE id_ubicacion = ?";
    $stmt = mysqli_prepare($conn, $query);

     
    if ($stmt) {
        
        mysqli_stmt_bind_param($stmt, "i", $id_ubicacion_editar);
        mysqli_stmt_execute($stmt);
        
        $result = mysqli_stmt_get_result($stmt);

       
        if (mysqli_num_rows($result) == 1) {
            $ubicacion = mysqli_fetch_assoc($result); 
        } else {
            
            echo '<script>alert("Ubicación no encontrada."); window.location.href = "ubicacion.php";</script>';
            exit();
        }
        mysqli_stmt_close($stmt); 
    } else {
        
        echo '<script>alert("Error al preparar la consulta de selección: ' . addslashes(mysqli_error($conn)) . '"); window.location.href = "ubicacion.php";</script>';
        exit();
    }
} else {
   
    echo '<script>alert("No se especificó una ubicación para editar."); window.location.href = "ubicacion.php";</script>';
    exit();
}


if (!$ubicacion) {
    echo '<script>alert("Error inesperado al cargar la ubicación."); window.location.href = "ubicacion.php";</script>';
    exit();
}
?>

<header class="d-flex justify-content-between align-items-center p-3 bg-purple text-white">
    <h2>Editar Ubicación</h2>
    <a href="ubicacion.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Volver a Ubicaciones</a>
</header>

<main class="container mt-4">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card p-4">
                <form action="editarUbicacion.php" method="POST">
                    <input type="hidden" name="id_ubicacion" value="<?php echo htmlspecialchars($ubicacion['id_ubicacion']); ?>">

                    <div class="mb-3">
                        <label for="descripcion_ubicacion" class="form-label">Descripción de la Ubicación:</label>
                        <input type="text" class="form-control" id="descripcion_ubicacion" name="descripcion_ubicacion" value="<?php echo htmlspecialchars($ubicacion['descripcion_ubicacion']); ?>" required>
                    </div>

                    <button type="submit" class="btn btn-info bg-purple" name="actualizar_ubicacion">Actualizar Ubicación</button>
                </form>
            </div>
        </div>
    </div>
</main>

