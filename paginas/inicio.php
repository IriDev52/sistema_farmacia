<?php 
include("../recursos/header.php");
 ?>
 <?php 
include('../conexion/conex.php');
session_start();
if (isset($_SESSION['usuario'])!="usuario") {
	header("Location: login.php");
}
?>
<header class=" p-2 bg-purple text-white text-center" >
	
<h2>Sistema de inventario farmacia </h2>
<a href="cerrarSesion.php" class="col-2 text-decoration-none text-white"><i class="bi bi-box-arrow-left h4"></i> Salir  </a>
</header>

<div class="container d-flex flex-column justify-content-center align-items-center mt-4">
	<div class="mb-2 col-5 text-center p-4 rounded-2 bg-purple"><a href="productos.php" class="text-decoration-none text-white"> <i class="bi bi-bandaid"></i> Productos</a></div>
	<div class="mb-2 col-5 text-center p-4 rounded-2 bg-purple"><a href="inventario_consulta.php" class="text-decoration-none text-white"> <i class="bi bi-shop-window"></i> Gestionar Inventario</a></div>
	<div class="mb-2 col-5 text-center p-4 rounded-2 bg-success"><a href="ventas.php" class="text-decoration-none text-white"> <i class="bi bi-cart"></i> Ventas</a></div>

</div>

