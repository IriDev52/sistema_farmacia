<?php
include("../conexion/conex.php");

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("ID de venta no proporcionado o inválido.");
}
$id_venta = $_GET['id'];

$venta_data = null;
$detalles_venta = [];

global $conn;

if (!$conn) {
    die("Error de conexión a la base de datos. Por favor, revisa conex.php");
}

$sql_venta = "SELECT id, fecha_venta, total FROM ventas WHERE id = ?";
if ($stmt_venta = $conn->prepare($sql_venta)) {
    $stmt_venta->bind_param("i", $id_venta);
    $stmt_venta->execute();
    $result_venta = $stmt_venta->get_result();
    $venta_data = $result_venta->fetch_assoc();
    $stmt_venta->close();

    if (!$venta_data) {
        die("Venta con ID " . htmlspecialchars($id_venta) . " no encontrada.");
    }
} else {
    die("Error en la preparación de la consulta de venta: " . $conn->error);
}

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

$empresa_nombre = "Farmacia Barrancas, C.A.";
$empresa_direccion = "Av. Principal, Barinas, Local 1,Barrancas , Venezuela";
$empresa_telefono = "+58 (212) 555-4321";
$empresa_email = "contacto@farmaciabarrancas.com";
$cliente_nombre = "Cliente General";
$cliente_tipo_venta = "Venta a Consumidor Final";
$cliente_direccion = "Dirección del Cliente, Ciudad, País";
$cliente_rif_ci = "V-";
$cliente_telefono = "+58";

$iva_rate = 0.16;
$total_con_iva = $venta_data['total'];
$base_imponible = $total_con_iva / (1 + $iva_rate);
$iva_monto = $total_con_iva - $base_imponible;

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Factura #<?php echo htmlspecialchars($id_venta); ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;500;700;900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
    <style>
        :root {
            --primary-color: #00796b; 
            --secondary-color: #607d8b;
            --text-dark: #263238;
            --text-muted: #78909c;
            --bg-light: #e0f2f1; 
            --border-color: #b2dfdb;
            --shadow-subtle: 0 4px 12px rgba(0, 0, 0, 0.05);
        }
        body {
            font-family: 'Roboto', sans-serif;
            background-color: var(--bg-light);
            padding: 3rem 1rem;
            color: var(--text-dark);
            line-height: 1.6;
        }
        .invoice-container {
            max-width: 900px;
            margin: auto;
            background: #fff;
            padding: 3.5rem 4.5rem;
            border-radius: 0.5rem;
            box-shadow: var(--shadow-subtle);
            border: 1px solid var(--border-color);
        }
        .invoice-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 3rem;
            padding-bottom: 1.5rem;
            border-bottom: 2px solid var(--primary-color);
        }
        .invoice-logo-container {
            font-size: 3.5rem;
            color: var(--primary-color);
        }
        .invoice-header-info {
            text-align: right;
        }
        .invoice-header-info h1 {
            font-size: 2.8rem;
            font-weight: 900;
            margin: 0;
            color: var(--primary-color);
            text-transform: uppercase;
        }
        .invoice-header-info p {
            margin: 0;
            font-size: 1rem;
            color: var(--text-muted);
        }
        .invoice-meta-info {
            margin-top: 1rem;
            font-size: 0.95rem;
            font-weight: 500;
        }
        .invoice-meta-info strong {
            color: var(--text-dark);
            font-weight: 700;
        }
        .section-divider {
            margin: 2.5rem 0;
            border-top: 1px dashed var(--border-color);
        }
        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 1rem;
        }
        .info-block {
            padding: 0.5rem 0;
        }
        .info-block p {
            margin: 0;
            font-size: 0.95rem;
            line-height: 1.5;
            color: var(--text-dark);
        }
        .info-block strong {
            font-weight: 700;
            color: var(--primary-color);
        }
        .table-invoice {
            width: 100%;
            border-collapse: collapse;
            margin-top: 2.5rem;
        }
        .table-invoice thead th {
            background-color: var(--primary-color);
            color: #fff;
            padding: 1rem 1.5rem;
            text-align: left;
            font-weight: 700;
            text-transform: uppercase;
            font-size: 0.85rem;
        }
        .table-invoice tbody td {
            padding: 1rem 1.5rem;
            border-bottom: 1px solid var(--border-color);
            font-size: 0.95rem;
        }
        .table-invoice tbody tr:last-child td {
            border-bottom: none;
        }
        .table-invoice .text-right {
            text-align: right;
        }
        .total-summary {
            display: flex;
            justify-content: flex-end;
            margin-top: 3rem;
        }
        .total-box {
            width: 100%;
            max-width: 320px;
            padding-top: 1.5rem;
            border-top: 2px solid var(--primary-color);
        }
        .total-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 0.5rem;
        }
        .total-label {
            font-weight: 500;
            color: var(--secondary-color);
            font-size: 1rem;
        }
        .total-amount {
            font-weight: 700;
            font-size: 1.1rem;
            color: var(--text-dark);
        }
        .grand-total-row {
            margin-top: 1.5rem;
            padding-top: 1rem;
            border-top: 1px solid var(--border-color);
        }
        .grand-total-label {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--primary-color);
        }
        .grand-total-amount {
            font-size: 2.2rem;
            font-weight: 900;
            color: var(--primary-color);
        }
        .action-buttons {
            text-align: center;
            margin-top: 3rem;
            display: flex;
            justify-content: center;
            gap: 1.5rem;
        }
        .btn-classic {
            font-size: 1rem;
            font-weight: 600;
            padding: 0.8rem 2rem;
            border-radius: 0.3rem;
            transition: all 0.2s ease;
            text-transform: uppercase;
            letter-spacing: 0.02em;
        }
        .btn-primary-classic {
            background-color: var(--primary-color);
            color: #fff;
            border: 1px solid var(--primary-color);
        }
        .btn-primary-classic:hover {
            background-color: #fff;
            color: var(--primary-color);
            box-shadow: 0 4px 10px rgba(0, 121, 107, 0.2);
        }
        .btn-secondary-classic {
            background-color: transparent;
            color: var(--secondary-color);
            border: 1px solid var(--secondary-color);
        }
        .btn-secondary-classic:hover {
            background-color: var(--secondary-color);
            color: #fff;
            box-shadow: 0 4px 10px rgba(96, 125, 139, 0.2);
        }
    </style>
</head>
<body>

<div class="invoice-wrapper">
    <div id="invoiceContent" class="invoice-container">
        
        <div class="invoice-header">
            <div class="invoice-logo-container">
                <i class="bi bi-plus-square-fill"></i>
            </div>
            <div class="invoice-header-info">
                <h1>FACTURA</h1>
                <p><strong><?php echo htmlspecialchars($empresa_nombre); ?></strong></p>
                <p><?php echo htmlspecialchars($empresa_direccion); ?></p>
                <div class="invoice-meta-info">
                    No. de Factura: <strong>#<?php echo htmlspecialchars($venta_data['id']); ?></strong>
                    <br>Fecha: <span><?php echo date('d/m/Y', strtotime(htmlspecialchars($venta_data['fecha_venta']))); ?></span>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-md-6">
                <div class="section-title">Información del Cliente</div>
                <div class="info-block">
                    <p><strong>Nombre:</strong> <?php echo htmlspecialchars($cliente_nombre); ?></p>
                    <p><strong>RIF/C.I.:</strong> <?php echo htmlspecialchars($cliente_rif_ci); ?></p>
                    <p><strong>Dirección:</strong> <?php echo htmlspecialchars($cliente_direccion); ?></p>
                    <p><strong>Teléfono:</strong> <?php echo htmlspecialchars($cliente_telefono); ?></p>
                </div>
            </div>
            <div class="col-md-6">
                <div class="section-title">Detalles de la Transacción</div>
                <div class="info-block">
                    <p><strong>Tipo de Venta:</strong> <?php echo htmlspecialchars($cliente_tipo_venta); ?></p>
                    <p><strong>Método de Pago:</strong> Transferencia</p>
                    <p><strong>IVA Aplicado:</strong> <?php echo $iva_rate * 100; ?>%</p>
                    <p><strong>Vencimiento:</strong> N/A</p>
                </div>
            </div>
        </div>

        <div class="section-divider"></div>

        <table class="table-invoice">
            <thead>
                <tr>
                    <th>Descripción</th>
                    <th class="text-right">Cantidad</th>
                    <th class="text-right">Precio Unitario (Bs.)</th>
                    <th class="text-right">Subtotal (Bs.)</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($detalles_venta as $detalle): ?>
                <tr>
                    <td><?php echo htmlspecialchars($detalle['nombre_producto']); ?></td>
                    <td class="text-right"><?php echo htmlspecialchars($detalle['cantidad']); ?></td>
                    <td class="text-right"><?php echo number_format(htmlspecialchars($detalle['precio_unitario']), 2, ',', '.'); ?></td>
                    <td class="text-right"><?php echo number_format(htmlspecialchars($detalle['subtotal']), 2, ',', '.'); ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="total-summary">
            <div class="total-box">
                <div class="total-row">
                    <span class="total-label">Subtotal</span>
                    <span class="total-amount">Bs. <?php echo number_format($base_imponible, 2, ',', '.'); ?></span>
                </div>
                <div class="total-row">
                    <span class="total-label">IVA (<?php echo $iva_rate * 100; ?>%)</span>
                    <span class="total-amount">Bs. <?php echo number_format($iva_monto, 2, ',', '.'); ?></span>
                </div>
                <div class="total-row grand-total-row">
                    <span class="grand-total-label">Total a Pagar</span>
                    <span class="grand-total-amount">Bs. <?php echo number_format(htmlspecialchars($venta_data['total']), 2, ',', '.'); ?></span>
                </div>
            </div>
        </div>
        
        <div class="text-center mt-5" style="color: var(--text-muted); font-size: 0.9rem;">
            <p>Gracias por su confianza en nuestros productos y servicios. ¡Esperamos su pronta recuperación!</p>
            <p>Para emergencias, contacte a su médico.</p>
        </div>

    </div>

    <div class="action-buttons">
        <button id="downloadPdfBtn" class="btn btn-classic btn-primary-classic">
            <i class="bi bi-file-earmark-arrow-down me-2"></i> Descargar Factura
        </button>
        <a href="ventas.php" class="btn btn-classic btn-secondary-classic">
            <i class="bi bi-arrow-left-circle me-2"></i> Regresar a Ventas
        </a>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const invoiceContent = document.getElementById('invoiceContent');
    const idVenta = <?php echo json_encode($id_venta); ?>;
    const downloadPdfBtn = document.getElementById('downloadPdfBtn');
    
    function generateAndDownloadPdf() {
        downloadPdfBtn.disabled = true;
        downloadPdfBtn.innerHTML = '<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Generando...';
        
        html2canvas(invoiceContent, {
            scale: 2,
            useCORS: true,
            logging: false,
            allowTaint: true
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
                position = heightLeft - pageHeight;
                pdf.addPage();
                pdf.addImage(imgData, 'PNG', 0, position, imgWidth, imgHeight);
                heightLeft -= pageHeight;
            }
            
            pdf.save(`factura_${idVenta}.pdf`);
            
        }).catch(error => {
            console.error('Error al generar el PDF:', error);
            alert('Error al generar el PDF: ' + error.message);
        }).finally(() => {
            downloadPdfBtn.disabled = false;
            downloadPdfBtn.innerHTML = '<i class="bi bi-file-earmark-arrow-down me-2"></i> Descargar Factura';
        });
    }

    downloadPdfBtn.addEventListener('click', generateAndDownloadPdf);
});
</script>

</body>
</html>