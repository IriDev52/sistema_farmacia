<?php
// Asegúrate de que esta línea esté al principio, antes de cualquier HTML, espacio en blanco, o "echo"
header('Content-Type: application/json'); // ¡Añade esta línea!

include '../conexion/conex.php';

if (isset($_GET['query'])) {
    $query = $_GET['query'];

    // Si tu columna es 'nombre_producto' en la DB, asegúrate de usarla aquí
    $stmt = $conn->prepare("SELECT id, nombre_producto, precio_venta, stock_actual FROM productos WHERE nombre_producto LIKE ? LIMIT 10");

    if ($stmt === false) {
        // Enviar un JSON de error si la preparación falla
        echo json_encode(['error' => 'Error al preparar la consulta: ' . $conn->error]);
        exit();
    }

    $param = "%" . $query . "%";
    $stmt->bind_param("s", $param);

    $stmt->execute();
    $result = $stmt->get_result();

    if ($result === false) {
        // Enviar un JSON de error si la ejecución o obtención de resultados falla
        echo json_encode(['error' => 'Error al obtener resultados: ' . $stmt->error]);
        exit();
    }

    $productos = [];
    while ($row = $result->fetch_assoc()) {
        $productos[] = $row;
    }

    $stmt->close();
    echo json_encode($productos); // Esto ya imprime el JSON
} else {
    // Si no hay 'query', también es una buena práctica devolver JSON
    echo json_encode([]); // Devolver un array vacío
}
?>