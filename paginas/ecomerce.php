<?php
// ecomerce.php
session_start(); 
include("../conexion/conex.php"); 
include("buscador-p-ecomerce.php"); 

if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

$termino_busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$resultado = buscarProductos($conn, $termino_busqueda);

include("../recursos/header.php"); 
?>

<style>
    body { padding-top: 65px; background-color: #f8f9fa; } 
    .navbar-brand i { color: #28a745; margin-right: 5px; } 
    .card { transition: transform 0.2s, box-shadow 0.2s; border-radius: 10px; overflow: hidden; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; }
    .bg-primary-custom { background-color: #007bff !important; }
    
    /* Contenedor fijo para que todas las imágenes se vean alineadas */
    .contenedor-img-ecom {
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        background-color: #fff;
        padding: 10px;
        border-bottom: 1px solid #eee;
    }
    .img-producto-ajuste {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom fixed-top shadow-sm">
    <div class="container-fluid container">
        <a class="navbar-brand" href="ecomerce.php">
            <i class="fas fa-hand-holding-medical"></i> 
            Farmacia Barrancas<span class="fw-bold"> Ecomerce</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="d-flex me-auto ms-lg-3" method="GET" action="ecomerce.php"> 
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Buscar productos..." name="buscar" value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                    <button class="btn btn-outline-light" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-4"> 
    <header class="text-center mb-5 p-3 bg-white rounded shadow-sm">
        <h1 class="display-5 text-success"><i class="fas fa-pills me-2"></i> Productos Disponibles</h1>
    </header>
    
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
        <?php
        if ($resultado && $resultado->num_rows > 0) {
            while($fila = $resultado->fetch_assoc()) {
                // --- LÓGICA DE RUTA PARA TU CARPETA IMG ---
                // Según tu captura, las fotos están directo en 'img'
                $nombre_foto = !empty($fila['imagen']) ? $fila['imagen'] : 'descarga.png';
                $ruta_final = "../img/" . $nombre_foto;

                // Verificamos si el archivo realmente existe en la carpeta
                if (!file_exists($ruta_final)) {
                    $ruta_final = "../img/descarga.png"; // Imagen de respaldo
                }
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="contenedor-img-ecom">
                            <img src="<?php echo $ruta_final; ?>" 
                                 class="img-producto-ajuste" 
                                 alt="<?php echo htmlspecialchars($fila['nombre_producto']); ?>">
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate fw-bold text-primary"><?php echo htmlspecialchars($fila['nombre_producto']); ?></h5>
                            <p class="card-text text-muted small flex-grow-1">
                                <?php echo htmlspecialchars(substr($fila['descripcion'], 0, 70)) . '...'; ?>
                            </p>
                            
                            <div class="mt-auto pt-2 border-top text-center">
                                <p class="h5 text-danger mb-3"><strong>$<?php echo number_format($fila['precio_venta'], 2); ?></strong></p>
                                
                                <button type="button" class="btn btn-success w-100 fw-bold" 
                                        data-bs-toggle="modal" data-bs-target="#carritoModal"
                                        data-nombre="<?php echo htmlspecialchars($fila['nombre_producto']); ?>"
                                        data-precio="<?php echo htmlspecialchars($fila['precio_venta']); ?>"
                                        data-id="<?php echo htmlspecialchars($fila['id']); ?>">
                                    <i class="fas fa-cart-plus me-1"></i> Añadir
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            echo '<div class="col-12"><p class="alert alert-warning text-center">No se encontraron productos.</p></div>';
        }
        $conn->close();
        ?>
    </div>
</div>

<div class="modal fade" id="carritoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"> 	
                <h5 class="modal-title">Agregar al Carrito</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_carrito.php" method="POST">
                <div class="modal-body">
                    <h4 id="nombreProductoModal" class="text-primary"></h4>
                    <p class="lead">Precio: <strong id="precioProductoModal" class="text-danger"></strong></p>
                    <input type="hidden" name="id_producto" id="idProductoInput">
                    <div class="mb-3">
                        <label class="form-label">Cantidad:</label>
                        <input type="number" class="form-control" name="cantidad" value="1" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-success">Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var carritoModal = document.getElementById('carritoModal');
    carritoModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; 
        document.getElementById('nombreProductoModal').textContent = button.getAttribute('data-nombre');
        document.getElementById('precioProductoModal').textContent = '$' + button.getAttribute('data-precio');
        document.getElementById('idProductoInput').value = button.getAttribute('data-id');
    });
</script>
</body>
</html>