<?php
// ubicacion.php

// Incluye el encabezado y la conexión a la base de datos
include("../recursos/header.php");
// Asegúrate de que conex.php establece la variable de conexión como $conex
include("../conexion/conex.php"); 

// Procesa el formulario de registro de ubicación
if (isset($_POST['registrar_ubicacion'])) {
    $nombre = $_POST['nombre'] ?? ''; // Usar el operador null coalescing para evitar "Undefined index"

    // IMPORTANTE: USAR SENTENCIAS PREPARADAS PARA PREVENIR INYECCIÓN SQL
    $query = "INSERT INTO ubicacion(descripcion_ubicacion) VALUES(?)";
    $stmt = mysqli_prepare($conex, $query); // CORRECCIÓN: Usar $conex y preparar la consulta

    if ($stmt) {
        mysqli_stmt_bind_param($stmt, "s", $nombre); // "s" indica que $nombre es un string
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script> alert("Ubicacion registrada"); window.location.href = "ubicacion.php";</script> '; // Redirigir para evitar reenvío de formulario
        } else {
            // Agrega esta línea para ver el error específico de la base de datos
            die("Error al registrar ubicación: " . mysqli_error($conex)); // CORRECCIÓN: Usar $conex
        }
        mysqli_stmt_close($stmt);
    } else {
        die("Error en la preparación de la consulta: " . mysqli_error($conex)); // CORRECCIÓN: Usar $conex
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
            <label class="fw-semibold" for="nombre_ubicacion">Nombre de ubicacion</label>
            <input class="mb-2" type="text" name="nombre" id="nombre_ubicacion" required>
            <button type="submit" class="btn btn-secondary bg-purple" name="registrar_ubicacion">Registrar ubicacion</button>
        </form>
    </div>
</main>

<div class="mx-4">
    <table class="table col-10" id="ubicaciones"> <thead>
            <tr>
                <th scope="col">Id</th>
                <th scope="col">Nombre</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $query_select_ubicaciones = "SELECT * FROM ubicacion";
            $resultado_ubicaciones = mysqli_query($conex, $query_select_ubicaciones); // CORRECCIÓN: Usar $conex
            
            if ($resultado_ubicaciones) { // Añadir verificación si la consulta fue exitosa
                while ($row = mysqli_fetch_array($resultado_ubicaciones)) { ?>
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
                <?php }
            } else {
                echo "<tr><td colspan='3'>Error al cargar ubicaciones: " . mysqli_error($conex) . "</td></tr>"; // Mensaje de error si la consulta falla
            }
            ?>
        </tbody>
    </table>
</div>

<?php include("../recursos/footer.php") ?>