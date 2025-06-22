<?php include("../recursos/header.php");
 ?>

<?php
include("../conexion/conex.php");
session_start();

if (!empty($_POST['comprobar'])) {
    $usuario = $_POST['correo'];
    $clave = $_POST['clave'];

    $sql = $conn->query("SELECT * FROM usuarios WHERE correo='$usuario' AND clave='$clave'");

    if ($datos = $sql->fetch_object()) {
        $_SESSION['usuario'] = $usuario;
        // Redirect to dashboard on successful login
        header("Location:inicio.php");
        exit(); // Always exit after a header redirect
    } else {
        // Set a session variable to indicate login error
        $_SESSION['login_error'] = true;
        // Redirect back to login.php to display the error
        header("Location: login.php");
        exit(); // Always exit after a header redirect
    }
}
?>

<main style="height:100vh; " class="bg-dark p-2 ">

<div class="container  col-6 border border-secondary p-4 mt-4 rounded-2 bg-light ">
    <h1 class="text-center text-purple ">Sistema de Farmacia</h1>
    <h3  class="text-center">Tu sistema de Inventario</h3>


    <form action="login.php" method="POST" class="d-flex flex-column mb-2">
        <label for="" >Usuario</label>
        <input required type="email" name="correo" id="" class="mb-2 p-1 rounded-2 border border-1 border-secondary">
        <label for=""  >Contraseña</label>
        <input required type="password" name="clave"  class="mb-2 p-1 rounded-2 border border-1 border-secondary" id="pass">
        <div class="text-muted mb-2"><input type="checkbox" name="" id="show"> Mostar contraseña</div>
        <input  type="submit" value="Iniciar sesión" class="btn btn-purple mb-2 text-white" name="comprobar" >
        
    </form>
    <p>No tienes una cuenta? <a href="registro.php" class="text-decoration-none text-purple "> Registrate aquí</a></p>
</div>
</main>

<?php
// Check for login error and display SweetAlert2
if (isset($_SESSION['login_error']) && $_SESSION['login_error']) {
    echo '<script>
            document.addEventListener("DOMContentLoaded", function() {
                alert("Error de inicio de sesión, usuario o contraseña incorrecta")
               
            });
          </script>';
    unset($_SESSION['login_error']); // Clear the session variable after displaying the message
}
?>

