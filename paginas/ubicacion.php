<?php  include("../recursos/header.php"); ?>
<?php  include("../conexion/conex.php");
if (isset($_POST['registrar_ubicacion'])) {
    $nombre=$_POST['nombre'];

    $query="INSERT INTO ubicacion(descripcion_ubicacion) VALUES('$nombre') ";
    $result=mysqli_query($conn, $query);

   if ($result) {
    echo '<script> alert("Ubicacion registrada"); </script> ';
} else {
    // Agrega esta línea para ver el error específico de la base de datos
    die("Query falló: " . mysqli_error($conn));
}

 }

?>
<header class="d-flex justify-content-between align-items-center p-3 bg-purple">
    <h2 class="text-white">Estantes</h2>
    <a href="../paginas/inicio.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Regresar a Inicio</a>
</header>



<main class="d-flex justify-content-center align-items-center">
  <div class="p-2 d-flex flex-column col-5  border border-1 border-secondary mx-4 mt-4 mb-4 rounded-2 " >
  <form action="ubicacion.php" method="POST" class="d-flex flex-column p-2">
    <label class="fw-semibold" for="">Nombre de ubicacion</label>
    <input class="mb-2" type="text" name="nombre">
    <button type="submit" class="btn btn-secondary bg-purple" name="registrar_ubicacion">Registar ubicacion </button>
  </form>
</div>
</main>

<div class="mx-4">
  <table class="table col-10" id="productos">
              <thead>
                <tr>
                 
                  <th scope="col">Id</th>
                  <th scope="col">Nombre</th>
                  <th scope="col">Acciones</th>
                </tr>
              </thead>
               <tbody>
                <?php 

            $query="SELECT * FROM ubicacion";
            $resultado_productos=mysqli_query($conn,$query);
            while ($row=mysqli_fetch_array($resultado_productos)) { ?>
              <tr>

                <td ><?php echo $row['id_ubicacion'] ?></td>
                <td ><?php echo $row['descripcion_ubicacion'] ?></td>
               

                <td class="d-flex ">
                    <a href="editarUbicacion.php?id=<?php echo htmlspecialchars($row['id_ubicacion']); ?>" class="btn btn-secondary ">
                                        <i class="bi bi-pencil-fill"></i> 
                                    </a>
                
            
                <a href="eliminarUbicacion.php?id=<?php echo htmlspecialchars($row['id_ubicacion']); ?>" class="btn btn-danger " onclick="return confirm('¿Está seguro de que desea eliminar esta ubicación y todas las asignaciones de productos en ella?');">
    <i class="bi bi-trash"></i> 
              </tr>


            <?php } ?>
        </tbody>
             
 </table>
  
<?php  include("../recursos/footer.php") ?>