<?php
session_start();
if (!isset($_SESSION['logeado']) || $_SESSION['logeado'] !== true) {
    exit("Acceso denegado");
}

include("../conexion/conex.php");
include("tasa_bcv.php");

// Configuración de cabeceras para descargar Excel
header("Content-Type: application/vnd.ms-excel; charset=utf-8");
header("Content-Disposition: attachment; filename=Reporte_Ventas_Farmacia_" . date('d-m-Y') . ".xls");
header("Pragma: no-cache");
header("Expires: 0");

// Obtener tasa
$tasa_cambio = 36.50; 
$tasa_api = obtenerTasaBCV_API_Anidada(); 
if (is_float($tasa_api) && $tasa_api > 0) { $tasa_cambio = $tasa_api; }

// Consulta de datos
$sql = "SELECT v.id, v.fecha_venta, v.total_usd, v.cantidad, p.nombre_producto 
        FROM ventas v 
        INNER JOIN productos p ON v.id_producto = p.id 
        ORDER BY v.fecha_venta DESC";
$resultado = $conn->query($sql);
?>

<table borde="1">
    <thead>
        <tr style="background-color: #3b82f6; color: white; font-weight: bold;">
            <th>ID Venta</th>
            <th>Fecha</th>
            <th>Hora</th>
            <th>Producto</th>
            <th>Cantidad</th>
            <th>Precio (USD)</th>
            <th>Total (Bs)</th>
        </tr>
    </thead>
    <tbody>
        <?php while($f = $resultado->fetch_assoc()): 
            $ts = strtotime($f['fecha_venta']);
        ?>
        <tr>
            <td><?php echo $f['id']; ?></td>
            <td><?php echo date('d/m/Y', $ts); ?></td>
            <td><?php echo date('h:i A', $ts); ?></td>
            <td><?php echo utf8_decode($f['nombre_producto']); ?></td>
            <td><?php echo $f['cantidad']; ?></td>
            <td><?php echo number_format($f['total_usd'], 2); ?></td>
            <td><?php echo number_format($f['total_usd'] * $tasa_cambio, 2, ',', '.'); ?></td>
        </tr>
        <?php endwhile; ?>
    </tbody>
</table>