<?php
// vista_previa_factura.php
include("../conexion/conex.php"); // Asegúrate de que esta ruta sea correcta

// Activar reporte de errores solo para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Obtener el ID de venta de la URL
if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de venta no proporcionado o inválido.");
}
$id_venta = $_GET['id'];

// Obtener los datos de la venta y sus detalles
$response = ['success' => false, 'message' => ''];
$venta_data = null;
$detalles_venta = [];

// Usar la conexión existente si conex.php ya la establece en una variable global,
// o llamar a la función conectar() si así está definida.
// Si tu conex.php simplemente hace include "tu_db_conn.php" que define $conn, entonces puedes usar $conn directamente.
// Si tienes la función conectar(), descomenta la línea de abajo:
// $conn = conectar();
global $conn; // Si $conn es una variable global en conex.php

if (!$conn) {
    die("Error de conexión a la base de datos.");
}

// 1. Obtener los datos principales de la venta
$sql_venta = "SELECT id, fecha_venta, total FROM ventas WHERE id = ?"; // Asegúrate que 'id' es el nombre correcto
if ($stmt_venta = $conn->prepare($sql_venta)) {
    $stmt_venta->bind_param("i", $id_venta);
    $stmt_venta->execute();
    $result_venta = $stmt_venta->get_result();
    $venta_data = $result_venta->fetch_assoc();
    $stmt_venta->close();

    if (!$venta_data) {
        die("Venta no encontrada.");
    }
} else {
    die("Error en la preparación de la consulta de venta: " . $conn->error);
}

// 2. Obtener los detalles de los productos de la venta (¡con la corrección de id_venta!)
$sql_detalles = "
    SELECT
        dv.cantidad,
        dv.precio_unitario,
        dv.subtotal,
        p.nombre_producto,
        p.id as producto_id
    FROM detalle_venta dv
    JOIN productos p ON dv.id_producto = p.id
    WHERE dv.id_venta = ?
";

if ($stmt_detalles = $conn->prepare($sql_detalles)) {
    $stmt_detalles->bind_param("i", $id_venta);
    $stmt_detalles->execute();
    $result_detalles = $stmt_detalles->get_result();
    while ($row = $result_detalles->fetch_assoc()) {
        $detalles_venta[] = $row;
    }
    $stmt_detalles->close();
} else {
    die("Error en la preparación de la consulta de detalles de venta: " . $conn->error);
}

$conn->close();

// Ahora, los datos de la venta y los detalles están en $venta_data y $detalles_venta
// Vamos a imprimir el HTML de la factura para que html2canvas lo capture
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo htmlspecialchars($id_venta); ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
   
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .invoice-box {
            max-width: 800px;
            margin: auto;
            padding: 30px;
            border: 1px solid #eee;
            box-shadow: 0 0 10px rgba(0, 0, 0, .15);
            font-size: 16px;
            line-height: 24px;
            color: #555;
        }
        .invoice-box table { width: 100%; line-height: inherit; text-align: left; }
        .invoice-box table td { padding: 5px; vertical-align: top; }
        .invoice-box table tr td:nth-child(2) { text-align: right; }
        .invoice-box table tr.top table td { padding-bottom: 20px; }
        .invoice-box table tr.top table td.title { font-size: 45px; line-height: 45px; color: #333; }
        .invoice-box table tr.information table td { padding-bottom: 30px; }
        .invoice-box table tr.heading td { background: #eee; border-bottom: 1px solid #ddd; font-weight: bold; }
        .invoice-box table tr.details td { padding-bottom: 20px; }
        .invoice-box table tr.item td { border-bottom: 1px solid #eee; }
        .invoice-box table tr.item.last td { border-bottom: none; }
        .invoice-box table tr.total td:nth-child(2) { border-top: 2px solid #eee; font-weight: bold; }
        .rtl { direction: rtl; }
        .rtl table { text-align: right; }
        .rtl table tr td:nth-child(2) { text-align: left; }

        /* Estilo para el logo */
        .logo-invoice {
            max-width: 150px; /* Ajusta esto según el tamaño de tu logo */
            height: auto;
        }
    </style>
</head>
<body>

<div id="invoiceContent" class="invoice-box">
    <table>
        <tr class="top">
            <td colspan="4">
                <table>
                    <tr>
                        <td class="title">

                            <img src="" style="width:100%; max-width:150px;" class="logo-invoice">
                            </td>
                        <td>
                            Factura #: <?php echo htmlspecialchars($venta_data['id']); ?><br>
                            Fecha: <?php echo date('d/m/Y H:i:s', strtotime(htmlspecialchars($venta_data['fecha_venta']))); ?><br>
                            </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="information">
            <td colspan="4">
                <table>
                    <tr>
                        <td>
                            Tu Empresa, S.A.<br>
                            Dirección de la empresa<br>
                            Teléfono, Email
                        </td>
                        <td>
                            Cliente General<br>
                            Venta Minorista<br>
                            </td>
                    </tr>
                </table>
            </td>
        </tr>

        <tr class="heading">
            <td>Producto</td>
            <td style="text-align: right;">Cantidad</td>
            <td style="text-align: right;">Precio Unitario</td>
            <td style="text-align: right;">Subtotal</td>
        </tr>

        <?php foreach ($detalles_venta as $detalle): ?>
        <tr class="item">
            <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
            <td style="text-align: right;"><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
            <td style="text-align: right;"><?php echo number_format(htmlspecialchars($detalle['precio_unitario']), 2); ?></td>
            <td style="text-align: right;"><?php echo number_format(htmlspecialchars($detalle['subtotal']), 2); ?></td>
        </tr>
        <?php endforeach; ?>

        <tr class="total">
            <td></td>
            <td colspan="2"></td>
            <td style="text-align: right;">Total: <?php echo number_format(htmlspecialchars($venta_data['total']), 2); ?></td>
        </tr>
    </table>
</div>
<div style="text-align: center; margin-top: 20px;">
    <button id="downloadPdfBtn" class="btn btn-primary">Descargar Factura</button>
    <a href="ventas.php" class="btn btn-secondary">Regresar</a>
</div>s

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script> <script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const invoiceContent = document.getElementById('invoiceContent');
        const idVenta = <?php echo json_encode($id_venta); ?>;
        const downloadPdfBtn = document.getElementById('downloadPdfBtn');

        // Función para generar y descargar el PDF
        function generateAndDownloadPdf() {
            // Puedes agregar aquí un spinner o deshabilitar el botón para evitar clics múltiples
            downloadPdfBtn.disabled = true;
            downloadPdfBtn.textContent = 'Generando...';

            html2canvas(invoiceContent, {
                scale: 2,
                useCORS: true
            }).then(canvas => {
                const imgData = canvas.toDataURL('image/png');
                const { jsPDF } = window.jspdf;
                const pdf = new jsPDF('p', 'mm', 'a4');

                const imgWidth = 210;
                const pageHeight = 297;
                const imgHeight = canvas.height * imgWidth / canvas.width;
                let heightLeft = imgHeight;
                let position = 0;

                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;

                while (heightLeft >= 0) {
                    position = heightLeft - imgHeight;
                    pdf.addPage();
                    pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                    heightLeft -= pageHeight;
                }

                pdf.save(`factura_${idVenta}.pdf`);

            }).catch(error => {
                console.error('Error al generar el PDF:', error);
                alert('Error al generar el PDF: ' + error.message);
            }).finally(() => {
                // Restablecer el botón
                downloadPdfBtn.disabled = false;
                downloadPdfBtn.textContent = 'Descargar Factura';
            });
        }

        // Asignar el evento click al botón
        downloadPdfBtn.addEventListener('click', generateAndDownloadPdf);

        // Opcional: Si quieres que la ventana se abra y el PDF se genere solo cuando el usuario quiera,
        // no llames a generateAndDownloadPdf aquí. Si quieres que se genere al cargar la página
        // y luego se ofrezca el botón para descargar de nuevo, podrías llamarla aquí:
        // generateAndDownloadPdf(); // Si quieres que se genere al cargar la página y luego el botón
    });
</script>


</body>
</html>