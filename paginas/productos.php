<?php
// productos.php

// Incluye el encabezado y la conexión a la base de datos
include("../recursos/header.php");
// Asegúrate de que conex.php establece la variable de conexión como $conex
include("../conexion/conex.php"); 

// Procesa el formulario de registro de producto
if (isset($_POST['registrar_producto'])) {
    $nombre = $_POST['nombre'] ?? '';
    $descripcion = $_POST['descripcion'] ?? '';
    $laboratorio = $_POST['laboratorio'] ?? '';
    $cantidad = (int)($_POST['cantidad'] ?? 0); // Convertir a entero
    $fecha_vencimiento = $_POST['fecha_vencimiento'] ?? '';
    $precio_venta = (float)($_POST['precio_venta'] ?? 0.0); // Convertir a float

    // CORRECCIÓN CLAVE: Convertir 'Si' a 1 y 'No' a 0 para la base de datos
    $requiere_refrigeracion = ($_POST['requiere_refrigeracion'] ?? 'No') === 'Si' ? 1 : 0;

    // IMPORTANTE: USAR SENTENCIAS PREPARADAS PARA PREVENIR INYECCIÓN SQL
    // CORRECCIÓN: Cambiar $conn a $conex aquí
    $query = "INSERT INTO productos(nombre_producto, descripcion, laboratorio_fabrica, stock_actual, fecha_vencimiento, requiere_refrigeracion, precio_venta) VALUES(?, ?, ?, ?, ?, ?, ?)";
    $stmt = mysqli_prepare($conex, $query); // Usar $conex

    if ($stmt) {
        // "sssisid" -> s: string, i: integer, d: double.
        // Asume que requiere_refrigeracion es un TINYINT(1) o BOOLEAN en tu DB.
        mysqli_stmt_bind_param($stmt, "sssisid", $nombre, $descripcion, $laboratorio, $cantidad, $fecha_vencimiento, $requiere_refrigeracion, $precio_venta);
        $result = mysqli_stmt_execute($stmt);

        if ($result) {
            echo '<script>alert("Producto registrado correctamente"); window.location.href = "productos.php";</script>';
        } else {
            // Muestra el error específico de MySQLi si la ejecución falla
            // CORRECCIÓN: Cambiar $conn a $conex aquí
            echo '<script>alert("Error al registrar producto: ' . mysqli_error($conex) . '");</script>'; // Usar $conex
        }
        mysqli_stmt_close($stmt);
    } else {
        // Muestra el error específico de MySQLi si la preparación falla
        // CORRECCIÓN: Cambiar $conn a $conex aquí
        die("Error en la preparación de la consulta: " . mysqli_error($conex)); // Usar $conex
    }
}
?>

<header class="d-flex justify-content-between align-items-center p-3 bg-purple">
    <h2 class="text-white">Productos</h2>
    <a href="../paginas/inicio.php" class="btn btn-light "><i class="bi bi-arrow-left"></i> Regresar a Inicio</a>
</header>

<main class="d-flex justify-content-center align-items-center">
    <div class="p-2 d-flex flex-column col-5 border border-1 border-secondary mx-4 mt-4 mb-4 rounded-2">
        <form action="productos.php" method="POST" class="d-flex flex-column p-2">
            <label class="fw-semibold" for="nombre">Nombre del producto</label>
            <input class="mb-2" type="text" name="nombre" id="nombre" required>

            <label class="fw-semibold" for="descripcion">Descripción</label>
            <input class="mb-2" type="text" name="descripcion" id="descripcion">

            <label class="fw-semibold" for="laboratorio">Laboratorio/fábrica</label>
            <input class="mb-2" type="text" name="laboratorio" id="laboratorio">

            <label class="fw-semibold" for="cantidad">Cantidad</label>
            <input class="mb-2" type="number" name="cantidad" id="cantidad" min="0" required>

            <label class="fw-semibold" for="fecha_vencimiento">Fecha de vencimiento</label>
            <input class="mb-2" type="date" name="fecha_vencimiento" id="fecha_vencimiento">

            <label class="fw-semibold" for="requiere_refrigeracion">Requiere refrigeración</label>
            <select class="form-select mb-2" name="requiere_refrigeracion" id="requiere_refrigeracion">
                <option value="No">No</option>
                <option value="Si">Sí</option>
            </select>

            <label class="fw-semibold" for="precio_venta">Precio de venta</label>
            <input class="mb-2" type="number" name="precio_venta" id="precio_venta" step="0.01" min="0" required>

            <button type="submit" class="btn btn-secondary bg-purple" name="registrar_producto">Registrar producto</button>
        </form>
    </div>
</main>

<div class="mx-4">
    <table class="table col-10" id="productos">
        <thead>
            <tr>
                <th scope="col">Nombre del producto</th>
                <th scope="col">Descripción</th>
                <th scope="col">Laboratorio/fábrica</th>
                <th scope="col">Cantidad</th>
                <th scope="col">Fecha de vencimiento</th>
                <th scope="col">Requiere refrigeración</th>
                <th scope="col">Precio de venta</th>
                <th scope="col">Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php
            // CORRECCIÓN: Cambiar $conn a $conex aquí
            $query_select_products = "SELECT * FROM productos";
            $resultado_productos = mysqli_query($conex, $query_select_products); // Usar $conex
            if ($resultado_productos) {
                while ($row = mysqli_fetch_array($resultado_productos)) { ?>
                    <tr>
                        <td><?php echo htmlspecialchars($row['nombre_producto']); ?></td>
                        <td><?php echo htmlspecialchars($row['descripcion']); ?></td>
                        <td><?php echo htmlspecialchars($row['laboratorio_fabrica']); ?></td>
                        <td><?php echo htmlspecialchars($row['stock_actual']); ?></td>
                        <td><?php echo htmlspecialchars($row['fecha_vencimiento']); ?></td>
                        <td>
                            <?php
                            // CORRECCIÓN CLAVE: Mostrar 'Sí' o 'No' según el valor 0 o 1
                            echo ($row['requiere_refrigeracion'] == 1) ? 'Sí' : 'No';
                            ?>
                        </td>
                        <td><?php echo htmlspecialchars($row['precio_venta']); ?></td>
                        <td class="d-flex">
                            <a href="editarProducto.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="d-flex m-1 text-decoration-none btn btn-secondary"><i class="bi bi-pencil-fill"></i></a>
                            <a href="eliminarProducto.php?id=<?php echo htmlspecialchars($row['id']); ?>" class="btn btn-danger" onclick="return confirm('¿Está seguro de que desea eliminar este producto?');">
                                <i class="bi bi-trash"></i>
                            </a>
                        </td>
                    </tr>
                <?php }
            } else {
                // CORRECCIÓN: Cambiar $conn a $conex aquí
                echo "<tr><td colspan='8'>Error al cargar productos: " . mysqli_error($conex) . "</td></tr>"; // Usar $conex
            }
            ?>
        </tbody>
    </table>
</div>

<?php include("../recursos/footer.php") ?>