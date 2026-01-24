<?php
include("../conexion/conex.php");
session_start();

if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];

    $res = mysqli_query($conn, "SELECT imagen FROM productos WHERE id = $id");
    $p = mysqli_fetch_assoc($res);

    if ($p) {
        if (!empty($p['imagen'])) {
            $ruta = "../img/" . $p['imagen'];
            if (file_exists($ruta)) unlink($ruta);
        }

        if (mysqli_query($conn, "DELETE FROM productos WHERE id = $id")) {
            $_SESSION['message'] = "Producto eliminado correctamente.";
            $_SESSION['message_type'] = "success";
        } else {
            $_SESSION['message'] = "Error de base de datos.";
            $_SESSION['message_type'] = "danger";
        }
    }
}

header("Location: productos.php");
exit();
?>