<?php
include("../conexion/conex.php");

header('Content-Type: application/json');

$query_param = isset($_GET['query']) ? $_GET['query'] : '';

if (empty($query_param)) {
    echo json_encode([]);
    exit();
}

$search_term = "%" . $query_param . "%";

$sql = "SELECT id_cliente, nombre_completo, cedula_rif FROM clientes WHERE nombre_completo LIKE ? OR cedula_rif LIKE ? LIMIT 10";
$stmt = mysqli_prepare($conn, $sql);

if ($stmt) {
    mysqli_stmt_bind_param($stmt, "ss", $search_term, $search_term);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);

    $clientes = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $clientes[] = $row;
    }
    echo json_encode($clientes);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['error' => 'Error en la preparación de la consulta de clientes: ' . mysqli_error($conn)]);
}

mysqli_close($conn);
?>