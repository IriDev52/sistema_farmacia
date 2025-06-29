<?php
include("../recursos/header.php");
include("../conexion/conex.php");
session_start();

// --- Lógica PHP para cargar datos iniciales ---
$productos = [];
$query_productos = "SELECT id, nombre_producto, stock_actual, ubicacion FROM productos WHERE estado = 'Activo' ORDER BY nombre_producto";
$result_productos = mysqli_query($conn, $query_productos);
if ($result_productos) {
    while ($row = mysqli_fetch_assoc($result_productos)) {
        $productos[] = $row;
    }
} else {
    $_SESSION['message'] = "Error al cargar productos para el formulario: " . mysqli_error($conn);
    $_SESSION['message_type'] = "danger";
    $productos = [];
}

$ubicaciones_disponibles = [
    'Estante A1',
    'Estante A2',
    'Estante B1',
    'Estante B2',
    'Estante C1',
    'Estante C2'
];

// Lógica para las tarjetas de estadísticas
$total_productos = count($productos);

$total_stock_actual = 0;
$query_total_stock = "SELECT SUM(stock_actual) AS total_stock FROM productos WHERE estado = 'Activo'";
$result_total_stock = mysqli_query($conn, $query_total_stock);
if ($result_total_stock && mysqli_num_rows($result_total_stock) > 0) {
    $row_total_stock = mysqli_fetch_assoc($result_total_stock);
    $total_stock_actual = $row_total_stock['total_stock'] ?? 0;
}

$hoy = date('Y-m-d');
$fecha_90_dias = date('Y-m-d', strtotime('+90 days'));

$query_vencidos = "SELECT COUNT(*) AS total_vencidos FROM productos WHERE fecha_vencimiento < '{$hoy}' AND estado = 'Activo'";
$result_vencidos = mysqli_query($conn, $query_vencidos);
$total_vencidos = ($result_vencidos) ? mysqli_fetch_assoc($result_vencidos)['total_vencidos'] : 0;

$query_proximos = "SELECT COUNT(*) AS total_proximos FROM productos WHERE fecha_vencimiento >= '{$hoy}' AND fecha_vencimiento <= '{$fecha_90_dias}' AND estado = 'Activo'";
$result_proximos = mysqli_query($conn, $query_proximos);
$total_proximos = ($result_proximos) ? mysqli_fetch_assoc($result_proximos)['total_proximos'] : 0;

// --- HTML y Diseño ---
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestión de Inventario - Farmacia</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.2/css/responsive.bootstrap5.css">
    <style>
        /* Estilo general del cuerpo de la página */
        body {
            font-family: 'Roboto', sans-serif;
            background-color: #f0f2f5; /* Un gris claro, similar al de la imagen */
            color: #333;
            line-height: 1.6;
            padding: 0;
            margin: 0;
            min-height: 100vh;
        }
        .main-wrapper {
            max-width: 1400px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        /* Encabezado */
        .header-bar {
            background-color: #007bff; /* Azul primario */
            color: #fff;
            padding: 1.5rem 2rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .header-bar h2 {
            font-weight: 700;
            font-size: 2rem;
            margin: 0;
            display: flex;
            align-items: center;
        }
        .header-bar h2 i {
            font-size: 2.5rem;
            margin-right: 15px;
        }
        .btn-return {
            background-color: #fff;
            border: none;
            color: #007bff;
            font-weight: 500;
            padding: 0.7rem 1.5rem;
            border-radius: 5px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            font-size: 0.9rem;
        }
        .btn-return:hover {
            background-color: #e2e6ea;
            transform: translateY(-2px);
            box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
        }

        /* Contenedor principal de la página */
        .main-container {
            padding-top: 2rem;
        }

        /* Tarjetas de estadísticas */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin-bottom: 2.5rem;
        }
        .stat-card {
            color: #fff;
            border-radius: 8px;
            padding: 2rem;
            text-align: center;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15);
        }
        .stat-card.blue { background-color: #007bff; }
        .stat-card.green { background-color: #28a745; }
        .stat-card.yellow { background-color: #ffc107; color: #333; }
        .stat-card.red { background-color: #dc3545; }

        .stat-icon {
            font-size: 3.5rem;
            margin-bottom: 1rem;
            opacity: 0.8;
        }
        .stat-label {
            font-size: 1.1rem;
            font-weight: 500;
            margin-bottom: 0.5rem;
        }
        .stat-value {
            font-size: 4rem;
            font-weight: 700;
            line-height: 1;
        }

        /* Contenedor de la tabla */
        .table-container {
            background-color: #fff;
            border-radius: 8px;
            padding: 2.5rem;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
        }
        .action-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .action-bar h4 {
            font-weight: 600;
            font-size: 2rem;
            color: #333;
            display: flex;
            align-items: center;
        }
        .btn-action {
            background-color: #007bff;
            border: none;
            color: #fff;
            font-weight: 600;
            padding: 0.8rem 2rem;
            border-radius: 5px;
            transition: transform 0.3s ease, box-shadow 0.3s ease, background-color 0.3s ease;
            box-shadow: 0 2px 8px rgba(0, 123, 255, 0.3);
        }
        .btn-action:hover {
            transform: translateY(-2px);
            background-color: #0069d9;
            box-shadow: 0 4px 12px rgba(0, 123, 255, 0.4);
        }
        
        /* Estilos de DataTables */
        .dataTables_wrapper .dataTables_filter input {
            border: 1px solid #ccc;
            border-radius: 5px;
            padding: 0.6rem 1rem;
        }
        .dataTables_wrapper .dataTables_length select {
            border: 1px solid #ccc;
            border-radius: 5px;
        }
        .table thead th {
            background-color: #f8f9fa;
            border-bottom: 2px solid #ccc;
            color: #333;
            font-weight: 600;
            text-transform: uppercase;
        }
        .table tbody tr {
            background-color: #fff;
        }
        .table-striped > tbody > tr:nth-of-type(odd) > * {
            background-color: #f9f9f9;
        }
        .table-hover tbody tr:hover {
            background-color: #e9ecef !important;
        }
        .badge-styled {
            font-weight: 600;
            font-size: 0.8em;
            padding: 0.5em 0.8em;
            border-radius: 50px;
        }
        .badge-danger-styled { background-color: #dc3545; color: #fff; }
        .badge-warning-styled { background-color: #ffc107; color: #333; }
        .expired-row { background-color: rgba(220, 53, 69, 0.1) !important; color: #dc3545 !important; font-weight: 600; }
        .warning-row { background-color: rgba(255, 193, 7, 0.1) !important; color: #d39e00 !important; font-weight: 600; }

        /* Modal */
        .modal-content {
            border-radius: 10px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }
    </style>
</head>
<body>
<header class="header-bar">
    <h2><i class="bi bi-capsule-pill"></i> Gestión de Inventario</h2>
    <a href="../paginas/inicio.php" class="btn btn-return"><i class="bi bi-arrow-left-circle-fill me-2"></i>Regresar a Inicio</a>
</header>
<div class="main-wrapper">
    <main class="main-container container-fluid">
        <div id="ajax-message-container" class="mb-4">
            <?php
            if (isset($_SESSION['message'])) {
                echo '<div class="alert alert-' . $_SESSION['message_type'] . ' alert-dismissible fade show mb-4 py-2" role="alert">';
                echo $_SESSION['message'];
                echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>';
                echo '</div>';
                unset($_SESSION['message']);
                unset($_SESSION['message_type']);
            }
            ?>
        </div>
        <div class="stats-grid">
            <div class="stat-card blue">
                <i class="bi bi-boxes stat-icon"></i>
                <div class="stat-label">Productos Registrados</div>
                <div class="stat-value" id="total_productos"><?php echo $total_productos; ?></div>
            </div>
            <div class="stat-card green">
                <i class="bi bi-box-seam-fill stat-icon"></i>
                <div class="stat-label">Stock Total</div>
                <div class="stat-value" id="total_stock"><?php echo $total_stock_actual; ?></div>
            </div>
            <div class="stat-card yellow">
                <i class="bi bi-calendar-x stat-icon"></i>
                <div class="stat-label">Próximos a Vencer</div>
                <div class="stat-value" id="proximos_vencer"><?php echo $total_proximos; ?></div>
            </div>
            <div class="stat-card red">
                <i class="bi bi-calendar-x-fill stat-icon"></i>
                <div class="stat-label">Productos Vencidos</div>
                <div class="stat-value" id="productos_vencidos"><?php echo $total_vencidos; ?></div>
            </div>
        </div>
        <div class="table-container">
            <div class="action-bar">
                <h4><i class="bi bi-journal-check me-2"></i>Detalles de Inventario</h4>
                <button type="button" class="btn btn-action" data-bs-toggle="modal" data-bs-target="#entradaStockModal">
                    <i class="bi bi-plus-circle-fill me-2"></i>Registrar Entrada
                </button>
            </div>
            <div class="table-responsive">
                <table class="table table-striped table-hover" id="tablaInventario">
                    <thead>
                        <tr>
                            <th scope="col">ID</th>
                            <th scope="col">Nombre</th>
                            <th scope="col">Laboratorio</th>
                            <th scope="col">Stock</th>
                            <th scope="col">Estado</th>
                            <th scope="col">Vencimiento</th>
                            <th scope="col">Ubicación</th>
                            <th scope="col">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>
<div class="modal fade" id="entradaStockModal" tabindex="-1" aria-labelledby="entradaStockModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="entradaStockModalLabel">Registrar Entrada / Mover Stock</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="entradaStockForm">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label for="producto_id_entrada" class="form-label">Producto:</label>
                        <select class="form-select" id="producto_id_entrada" name="id" required>
                            <option value="">Seleccione un producto</option>
                            <?php foreach ($productos as $p) : ?>
                                <option value="<?php echo htmlspecialchars($p['id']); ?>">
                                    <?php echo htmlspecialchars($p['nombre_producto']); ?> (Stock: <?php echo htmlspecialchars($p['stock_actual']); ?>, Ubic: <?php echo htmlspecialchars($p['ubicacion']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="cantidad_entrada" class="form-label">Cantidad a Sumar:</label>
                        <input type="number" class="form-control" id="cantidad_entrada" name="cantidad" min="1" required>
                    </div>
                    <div class="mb-3">
                        <label for="fecha_vencimiento" class="form-label">Fecha de Vencimiento:</label>
                        <input type="date" class="form-control" id="fecha_vencimiento" name="fecha_vencimiento">
                        <small class="form-text text-muted">Opcional. Actualizar si es un nuevo lote.</small>
                    </div>
                    <div class="mb-4">
                        <label for="ubicacion_destino" class="form-label">Nueva Ubicación:</label>
                        <select class="form-select" id="ubicacion_destino" name="ubicacion_destino" required>
                            <option value="">Seleccione una ubicación</option>
                            <?php foreach ($ubicaciones_disponibles as $u_text) : ?>
                                <option value="<?php echo htmlspecialchars($u_text); ?>">
                                    <?php echo htmlspecialchars($u_text); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0 pb-3 pe-4">
                    <button type="button" class="btn btn-secondary me-2" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-action">Aplicar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="verDetallesModal" tabindex="-1" aria-labelledby="verDetallesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="verDetallesModalLabel">Detalles del Producto</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>ID:</strong> <span id="detalle_id"></span></p>
                <p><strong>Nombre:</strong> <span id="detalle_nombre"></span></p>
                <p><strong>Laboratorio:</strong> <span id="detalle_laboratorio"></span></p>
                <p><strong>Stock Actual:</strong> <span id="detalle_stock"></span></p>
                <p><strong>Estado:</strong> <span id="detalle_estado"></span></p>
                <p><strong>Fecha Vencimiento:</strong> <span id="detalle_vencimiento"></span></p>
                <p><strong>Ubicación:</strong> <span id="detalle_ubicacion"></span></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>

<div id="ajax-message-container" class="mt-4 px-4"></div>
<?php mysqli_close($conn); ?>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/dataTables.responsive.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.2/js/responsive.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var dataTable;
    $(document).ready(function() {
        dataTable = $('#tablaInventario').DataTable({
            language: {
                url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/es-ES.json',
            },
            paging: true,
            searching: true,
            ordering: true,
            info: true,
            responsive: true,
            ajax: {
                url: 'inventario_api.php?action=get_inventory_data',
                dataSrc: 'data'
            },
            columns: [
                { data: 'id' },
                { data: 'nombre_producto' },
                { data: 'laboratorio_fabrica' },
                { data: 'stock_actual' },
                { data: 'estado' },
                { 
                    data: 'fecha_vencimiento',
                    render: function(data, type, row) {
                        if (!data || data === '0000-00-00') return 'N/A';
                        var fechaVencimiento = new Date(data);
                        var fechaActual = new Date();
                        var diffDays = Math.ceil((fechaVencimiento - fechaActual) / (1000 * 60 * 60 * 24));
                        if (diffDays < 0) {
                            return data + ' <span class="badge badge-styled badge-danger-styled"><i class="bi bi-exclamation-triangle-fill"></i> Vencido</span>';
                        } else if (diffDays <= 90) {
                            return data + ' <span class="badge badge-styled badge-warning-styled"><i class="bi bi-exclamation-diamond-fill"></i> Prox. Vencer</span>';
                        }
                        return data;
                    }
                },
                { data: 'ubicacion' },
                {
                    data: null,
                    orderable: false,
                    render: function(data, type, row) {
                        return '<button class="btn btn-info btn-sm"><i class="bi bi-eye"></i></button>';
                    }
                }
            ],
            createdRow: function(row, data, dataIndex) {
                if (data.fecha_vencimiento && data.fecha_vencimiento !== '0000-00-00') {
                    var fechaVencimiento = new Date(data.fecha_vencimiento);
                    var fechaActual = new Date();
                    var diffDays = Math.ceil((fechaVencimiento - fechaActual) / (1000 * 60 * 60 * 24));
                    if (diffDays < 0) {
                        $(row).addClass('expired-row');
                    } else if (diffDays <= 90) {
                        $(row).addClass('warning-row');
                    }
                }
            }
        });
        
        // Lógica para el botón "Ver Detalles" (el ojo)
        $('#tablaInventario tbody').on('click', '.btn-info', function () {
            // Obtener la fila de la tabla a la que pertenece el botón
            var data = dataTable.row($(this).parents('tr')).data();
            
            // Llenar el modal con los datos de la fila
            $('#detalle_id').text(data.id);
            $('#detalle_nombre').text(data.nombre_producto);
            $('#detalle_laboratorio').text(data.laboratorio_fabrica);
            $('#detalle_stock').text(data.stock_actual);
            $('#detalle_estado').text(data.estado);
            $('#detalle_vencimiento').text(data.fecha_vencimiento);
            $('#detalle_ubicacion').text(data.ubicacion);

            // Mostrar el modal
            var verDetallesModal = new bootstrap.Modal(document.getElementById('verDetallesModal'));
            verDetallesModal.show();
        });

        $('#entradaStockForm').on('submit', function(e) {
            e.preventDefault();
            $('#ajax-message-container').empty();
            const loadingHtml = `
                <div class="alert alert-info d-flex align-items-center" role="alert">
                    <div class="spinner-border spinner-border-sm me-2" role="status">
                        <span class="visually-hidden">Procesando...</span>
                    </div>
                    <span>Procesando la solicitud, por favor espere...</span>
                </div>
            `;
            $('#ajax-message-container').html(loadingHtml);
            const formData = $(this).serialize() + '&action=register_entrada';
            $.ajax({
                type: 'POST',
                url: 'inventario_api.php',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    $('#entradaStockModal').modal('hide');
                    $('#ajax-message-container').empty();
                    let alertType = response.success ? 'success' : 'danger';
                    const alertHtml = `
                        <div class="alert alert-${alertType} alert-dismissible fade show" role="alert">
                            ${response.message}
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#ajax-message-container').html(alertHtml);
                    if (response.success) {
                        dataTable.ajax.reload(null, false);
                        updateStatsCards();
                    }
                },
                error: function(jqXHR, textStatus, errorThrown) {
                    $('#entradaStockModal').modal('hide');
                    $('#ajax-message-container').empty();
                    const errorHtml = `
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            Ocurrió un error al procesar la solicitud. Por favor, intente de nuevo.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    `;
                    $('#ajax-message-container').html(errorHtml);
                    console.error("Error AJAX:", textStatus, errorThrown, jqXHR.responseText);
                }
            });
        });
        $('#entradaStockModal').on('hidden.bs.modal', function () {
            $('#entradaStockForm')[0].reset();
            dataTable.ajax.reload(null, false);
            updateStatsCards();
        });
        $('#entradaStockModal').on('show.bs.modal', function () {
            $('#entradaStockForm')[0].reset();
        });
        function updateStatsCards() {
            $.ajax({
                url: 'inventario_api.php?action=get_stats',
                dataType: 'json',
                success: function(stats) {
                    $('#total_productos').text(stats.total_productos);
                    $('#total_stock').text(stats.total_stock_actual);
                    $('#proximos_vencer').text(stats.total_proximos);
                    $('#productos_vencidos').text(stats.total_vencidos);
                }
            });
        }
        updateStatsCards();
    });
</script>
</body>
</html>