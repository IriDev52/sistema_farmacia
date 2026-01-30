<?php
session_start();

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Verificamos si el producto existe en el carrito antes de borrar
    if (isset($_SESSION['carrito'][$id])) {
        unset($_SESSION['carrito'][$id]);
        // $_SESSION['mensaje_texto'] = "Producto eliminado correctamente.";
        // $_SESSION['mensaje_tipo'] = "warning";
    }
}

// Siempre regresamos al carrito
header("Location: ver_carrito.php");
exit();