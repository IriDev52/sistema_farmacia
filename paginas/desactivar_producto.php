<?php
include("../conexion/conex.php");
session_start();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    $sql = "UPDATE productos SET estado = 'Inactivo' WHERE id = $id";
    
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "Producto desactivado correctamente.";
        $_SESSION['message_type'] = "warning";
    } else {
        $_SESSION['message'] = "Error al desactivar.";
        $_SESSION['message_type'] = "danger";
    }
}

header("Location: productos.php");
exit();
?>