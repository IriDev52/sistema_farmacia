<?php  include("../recursos/header.php"); ?>
<?php  include("../conexion/conex.php");
if (isset($_POST['registrar_producto'])) {
    $nombre=$_POST['nombre'];
    $descripcion=$_POST['descripcion'];
    $laboratorio=$_POST['laboratorio'];
    $cantidad=$_POST['cantidad'];
    $fecha_vencimiento=$_POST['fecha_vencimiento'];
    $requiere_refrigeracion=$_POST['requiere_refrigeracion'];
    $precio_venta=$_POST[' $precio_venta'];
   


    $query="INSERT INTO productos(nombre_producto,descripcion,laboratorio_fabrica,stock_actual,fecha_vencimiento, requiere_refrigeracion,precio_venta) VALUES('$nombre','$descripcion','$laboratorio','$cantidad','$fecha_vencimiento','$requiere_refrigeracion','$precio_venta')";
    $result=mysqli_query($conn, $query);

    if ($result) {
    	echo '<script>
         alert("Producto registrado");
           </script> ';
    
    }else{
         die("Query falló: " . mysqli_error($conn));
  }

 }

?>


<header class="d-flex justify-content-between align-items-center p-3 bg-purple">
    <h2 class="text-white">Productos</h2>
    <a href="../paginas/inicio.php" class="btn btn-light "><i class="bi bi-arrow-left"></i> Regresar a Inicio</a>
</header>


<main class="d-flex justify-content-center align-items-center">
  <div class="p-2 d-flex flex-column col-5  border border-1 border-secondary mx-4 mt-4 mb-4 rounded-2 " >
  <form action="productos.php" method="POST" class="d-flex flex-column p-2">
    <label class="fw-semibold" for="">Nombre del producto</label>
    <input class="mb-2" type="text" name="nombre">
    <label class="fw-semibold" for="">Descripción </label>
    <input class="mb-2" type="text" name="descripcion">
     <label class="fw-semibold" for="">Laboratorio/fabrica</label>
    <input class="mb-2" type="text" name="laboratorio">
       <label class="fw-semibold" for="">Cantidad</label>
    <input class="mb-2" type="text" name="cantidad">
       <label class="fw-semibold" for="">Fecha de vencimiento</label>
    <input class="mb-2" type="date" name="fecha_vencimiento">
     <label class="fw-semibold" for="">Requiere refrigeracion</label>
    <input class="mb-2" type="text" name="requiere_refrigeracion" placeholder="Si o no?">
    <label class="fw-semibold" for="">Precio de venta</label>
    <input class="mb-2" type="text" name="precio_venta" placeholder="Si o no?">

    <button type="submit" class="btn btn-secondary bg-purple" name="registrar_producto">Registar producto</button>
  </form>
</div>
</main>

<div class="mx-4">
  <table class="table col-10" id="productos">
              <thead>
                <tr>
                 
                  <th scope="col">Nombre del producto</th>
                  <th scope="col">Descripcion</th>
                  <th scope="col">Laboratorio/fabrica</th>
                  <th scope="col">Cantidad</th>
                  <th scope="col">Fecha de vencimiento</th>
                  <th scope="col">Requiere refrigeración</th>
                  <th scope="col">Presio de venta</th>
                  <th scope="col">Acciones</th>
                </tr>
              </thead>
               <tbody>
                <?php 

            $query="SELECT * FROM productos";
            $resultado_productos=mysqli_query($conn,$query);
            while ($row=mysqli_fetch_array($resultado_productos)) { ?>
              <tr>

                <td ><?php echo $row['nombre_producto'] ?></td>
                <td ><?php echo $row['descripcion'] ?></td>
                <td ><?php echo $row['laboratorio_fabrica'] ?></td>
                <td ><?php echo $row['stock_actual'] ?></td>
                <td ><?php echo $row['fecha_vencimiento'] ?></td>
                <td ><?php echo $row['requiere_refrigeracion'] ?></td>
                 <td ><?php echo $row['precio_venta'] ?></td>


               

                <td class="d-flex ">
                
                <a href="editarProducto.php?id=<?php echo $row['id']?>" class=" d-flex m-1 text-decoration-none btn btn-secondary"><i class="bi bi-pencil-fill"></i></a>
                  <a href="eliminarProducto.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger " onclick="return confirm('¿Está seguro de que desea eliminar este producto y todas las asignaciones de productos en ella?');">

    <i class="bi bi-trash"></i> 
                  </td>

            <?php } ?>
        </tbody>
             
 </table>
  
<?php  include("../recursos/footer.php") ?>