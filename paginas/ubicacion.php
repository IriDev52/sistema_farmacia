<?php
include("../recursos/header.php");
include("../conexion/conex.php");

if (isset($_POST['registrar_ubicacion'])) {
    $nombre = $_POST['nombre'];

    $query_insert = "INSERT INTO ubicacion(descripcion_ubicacion) VALUES(?)";
    
    if ($stmt = mysqli_prepare($conn, $query_insert)) {
        mysqli_stmt_bind_param($stmt, "s", $nombre);

        if (mysqli_stmt_execute($stmt)) {
            echo '<script> alert("Ubicacion registrada"); </script> ';
        } else {
            echo '<script> alert("Error al registrar ubicación: ' . htmlspecialchars(mysqli_stmt_error($stmt)) . '"); </script>';
        }

        mysqli_stmt_close($stmt);
    } else {
        die("Error al preparar la consulta: " . mysqli_error($conn));
    }
}
?>

<header class="d-flex justify-content-between align-items-center p-3 bg-purple">
    <h2 class="text-white">Estantes</h2>
    <a href="../paginas/inicio.php" class="btn btn-light"><i class="bi bi-arrow-left"></i> Regresar a Inicio</a>
</header>

<main class="d-flex justify-content-center align-items-center">
    <div class="p-2 d-flex flex-column col-5 border border-1 border-secondary mx-4 mt-4 mb-4 rounded-2">
        <form action="ubicacion.php" method="POST" class="d-flex flex-column p-2">
            <label class="fw-semibold" for="nombre_ubicacion">Nombre de ubicación</label>
            <input class="mb-2" type="text" name="nombre" id="nombre_ubicacion" required>
            <button type="submit" class="btn btn-secondary bg-purple" name="registrar_ubicacion">Registrar ubicación</button>
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
            $query_select = "SELECT * FROM ubicacion";
            $resultado_productos = mysqli_query($conn, $query_select);
            
            if ($resultado_productos) {
                while ($row = mysqli_fetch_assoc($resultado_productos)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['id_ubicacion']); ?></td>
                        <td><?php echo htmlspecialchars($row['descripcion_ubicacion']); ?></td>
                        <td class="d-flex">
                            <a href="editarUbicacion.php?id=<?php echo htmlspecialchars($row['id_ubicacion']); ?>" class="btn btn-secondary">
                                <i class="bi bi-pencil-fill"></i>
                            </a>
                            <a href="eliminarUbicacion.php?id=<?php echo htmlspecialchars($row['id_ubicacion']); ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar esta ubicación y todas las asignaciones de productos en ella?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
            <?php 
                }
            } else {
                echo "<tr><td colspan='3'>Error al cargar ubicaciones: " . htmlspecialchars(mysqli_error($conn)) . "</td></tr>";
            }
            ?>
        </tbody>
    </table>
</div>

<?php 
if (isset($conn) && $conn) {
    mysqli_close($conn);
}
?>
  
