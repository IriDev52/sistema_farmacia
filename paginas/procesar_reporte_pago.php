<?php
session_start();
include("../conexion/conex.php");

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Limpiamos los datos para evitar inyecciones (Ingeniería básica)
    $id_venta = mysqli_real_escape_string($conn, $_POST['id_venta']);
    $referencia = mysqli_real_escape_string($conn, $_POST['referencia']);
    $detalles_envio = mysqli_real_escape_string($conn, $_POST['detalles_envio']);

    // Actualizamos la venta: ahora el admin ya podrá ver la referencia y la dirección
    $sql = "UPDATE ventas SET 
            referencia_pago = '$referencia', 
            detalles_envio = '$detalles_envio' 
            WHERE id = '$id_venta'";

    if (mysqli_query($conn, $sql)) {
        echo "<script>
                alert('¡Pago reportado! Ahora el administrador verificará tu referencia.');
                window.location='mis_compras.php';
              </script>";
    } else {
        echo "Error al actualizar: " . mysqli_error($conn);
    }
}
?>