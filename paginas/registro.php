<?php 
include("../conexion/conex.php");
include("../recursos/header.php");

	if (isset($_POST['registrar_user'])) {
		$correo=$_POST['correo'];
		$clave=$_POST['clave'];
		$query="INSERT INTO usuarios(correo, clave) VALUES('$correo','$clave')";
		$result=mysqli_query($conn, $query);

		if (!$result) {
			die("query fallo");
		}else{
			 echo '<script>
              alert("usuario registrado exitosamente")
            </script>';
		}

		
	}

?>
<main style="height:100vh; " class="bg-dark p-2 ">

<div class="container  col-6 border border-secondary p-4 mt-4 rounded-2 bg-light ">
	<h1 class="text-center text-purple ">Sistema de Farmacia</h1>


	<form action="registro.php" method="POST" class="d-flex flex-column mb-2">
		<label for="" >Correo</label>
		<input required type="email" name="correo" id="" class="mb-2 p-1 rounded-2 border border-1 border-secondary">
		<label for=""  >Contraseña</label>
		<input required type="password" name="clave"  class="mb-2 p-1 rounded-2 border border-1 border-secondary" id="pass">
		<div class="text-muted mb-2"><input type="checkbox" name="" id="show"> Mostar contraseña</div>
		<input type="submit" value="Registrarse" class="btn btn-purple mb-2 text-white" name="registrar_user" >
		
	</form>
	<p>Ya tienes una cuenta? <a href="login.php" class="text-decoration-none text-purple "> Inicia Sesión aquí</a></p>
</div>
</main>
<?php include("../recursos/footer.php")?>