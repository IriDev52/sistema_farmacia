<?php
$host = "localhost";
$user = "root"; // Tu usuario de la base de datos
$password = ""; // Tu contraseña de la base de datos (vacía si no tienes)
$db = "bd_farmacia"; // ¡Cambiado a bd_farmacia!

$conex = mysqli_connect($host, $user, $password, $db);

if (!$conex) {
    die("Error al conectar con la base de datos: " . mysqli_connect_error());
}
?>