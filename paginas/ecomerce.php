<?php
// ecomerce.php (O ecommerce.php)

// 0. Iniciar la sesión para poder verificar si el usuario está logeado.
session_start(); 

// 1. Incluir la conexión a la base de datos (contiene $conn)
// Asegúrate de que el path sea correcto: ejemplo: ../conexion/conex.php
include("../conexion/conex.php"); 

// Incluir el archivo de lógica del buscador. Están en el mismo nivel.
// El archivo debe llamarse exactamente 'buscador-p-ecomerce.php'
include("buscador-p-ecomerce.php"); 

// Verificar si la conexión es válida y evitar errores fatales
if ($conn->connect_error) {
    die("Error de conexión a la base de datos: " . $conn->connect_error);
}

// 2. Obtener el término de búsqueda de la URL
$termino_busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';

// 3. Obtener los productos usando la función modular
$resultado = buscarProductos($conn, $termino_busqueda);

// 4. Incluir el encabezado HTML (Contiene <!DOCTYPE>, <head>, y la apertura de <body>)
// Asegúrate de que este archivo contiene las etiquetas HTML de inicio.
include("../recursos/header.php"); 
?>

<style>
    /* Estilos personalizados */
    body { padding-top: 65px; background-color: #f8f9fa; } 
    .navbar-brand i { color: #28a745; margin-right: 5px; } 
    .card { transition: transform 0.2s, box-shadow 0.2s; border-radius: 10px; }
    .card:hover { transform: translateY(-3px); box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important; }
    .bg-primary-custom { background-color: #007bff !important; }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom fixed-top shadow-sm">
    <div class="container-fluid container">
        <a class="navbar-brand" href="ecomerce.php">
            <i class="fas fa-hand-holding-medical"></i> 
            Farmacia Barrancas<span class="fw-bold"> Ecomerce</span>
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            
            <form class="d-flex me-auto ms-lg-3 my-2 my-lg-0" method="GET" action="ecomerce.php"> 
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Buscar productos..." aria-label="Search" name="buscar" 
                           value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                    <button class="btn btn-outline-light" type="submit">
                        <i class="fas fa-search"></i>
                    </button>
                </div>
            </form>
            
            <ul class="navbar-nav">
                <li class="nav-item me-2">
                    <button class="btn btn-outline-light" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fas fa-sign-in-alt"></i> Iniciar Sesión
                    </button>
                </li>
                <li class="nav-item">
                    <button class="btn btn-warning" data-bs-toggle="modal" data-bs-target="#registroModal">
                        <i class="fas fa-user-plus"></i> Registrarse
                    </button>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-4"> 
    <header class="text-center mb-5 p-3 bg-white rounded shadow-sm">
        <h1 class="display-5 text-success"><i class="fas fa-pills me-2"></i> Productos Disponibles</h1>
        <?php if (!empty(trim($termino_busqueda))): ?>
            <p class="lead text-muted">Mostrando resultados para: <strong><?php echo htmlspecialchars($termino_busqueda); ?></strong></p>
        <?php else: ?>
            <p class="lead text-muted">Encuentra los medicamentos y artículos esenciales que necesitas.</p>
        <?php endif; ?>
    </header>
    
    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
        <?php
        // 5. Mostrar los productos con la lógica de seguridad
        if ($resultado && $resultado->num_rows > 0) {
            while($fila = $resultado->fetch_assoc()) {
                ?>
                <div class="col">
                    <div class="card h-100 shadow-sm">
                        <div class="text-center p-3 bg-light" style="height: 150px; display: flex; align-items: center; justify-content: center;">
                            <i class="fas fa-capsules fa-4x text-info"></i> 
                        </div>
                        
                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title text-truncate fw-bold text-primary"><?php echo htmlspecialchars($fila['nombre_producto']); ?></h5>
                            <p class="card-text text-muted small flex-grow-1 mb-3">
                                <?php echo htmlspecialchars(substr($fila['descripcion'], 0, 80)) . '...'; ?>
                            </p>
                            
                            <div class="mt-auto pt-2 border-top">
                                <p class="h5 text-danger mb-3">
                                    <i class="fas fa-tag me-1"></i> 
                                    <strong>$<?php echo number_format($fila['precio_venta'], 2); ?></strong>
                                </p>
                                
                                <?php if (isset($_SESSION['usuario_id'])): ?>
                                    <button type="button" 
                                            class="btn btn-success w-100 fw-bold" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#carritoModal"
                                            data-nombre="<?php echo htmlspecialchars($fila['nombre_producto']); ?>"
                                            data-precio="<?php echo htmlspecialchars($fila['precio_venta']); ?>"
                                            data-id="<?php echo htmlspecialchars($fila['id']); ?>">
                                        <i class="fas fa-cart-plus me-1"></i> Añadir
                                    </button>
                                <?php else: ?>
                                    <button type="button" 
                                            class="btn btn-secondary w-100 fw-bold" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#alertaLoginModal"> 
                                        <i class="fas fa-lock me-1"></i> Iniciar Sesión
                                    </button>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
                <?php
            }
        } else {
            // Mensaje de no resultados
            $mensaje = !empty(trim($termino_busqueda)) 
                        ? "No se encontraron resultados para '" . htmlspecialchars($termino_busqueda) . "'." 
                        : "No hay productos disponibles en este momento.";
            echo '<div class="col-12"><p class="alert alert-warning text-center">'. $mensaje . '</p></div>';
        }
        
        // 6. Cerrar la conexión
        $conn->close();
        ?>
    </div>
</div>

<div class="modal fade" id="carritoModal" tabindex="-1" aria-labelledby="carritoModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-success text-white"> 	
                <h5 class="modal-title" id="carritoModalLabel"><i class="fas fa-shopping-cart me-2"></i> Agregar al Carrito</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAgregarCarrito" action="procesar_carrito.php" method="POST">
                <div class="modal-body">
                    <h4 id="nombreProductoModal" class="text-primary">Nombre del Producto</h4>
                    <p class="lead">Precio Unitario: <strong id="precioProductoModal" class="text-danger"></strong></p>
                    <hr>
                    <input type="hidden" name="id_producto" id="idProductoInput">
                    <div class="mb-3">
                        <label for="cantidadInput" class="form-label fw-bold"><i class="fas fa-sort-amount-up me-1"></i> Cantidad:</label>
                        <input type="number" class="form-control" id="cantidadInput" name="cantidad" value="1" min="1" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-success"><i class="fas fa-check me-1"></i> Confirmar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="alertaLoginModal" tabindex="-1" aria-labelledby="alertaLoginModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm">
    <div class="modal-content">
      <div class="modal-header bg-warning text-dark">
        <h5 class="modal-title" id="alertaLoginModalLabel"><i class="fas fa-exclamation-triangle me-2"></i> Acceso Restringido</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
        <p class="mb-3">Para añadir productos, por favor **inicia sesión**.</p>
        <button type="button" class="btn btn-primary w-100 mb-2" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fas fa-sign-in-alt me-1"></i> Iniciar Sesión</button>
        <button type="button" class="btn btn-warning w-100" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registroModal"><i class="fas fa-user-plus me-1"></i> Registrarse</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="loginModalLabel"><i class="fas fa-sign-in-alt me-2"></i> Iniciar Sesión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesar_login.php" method="POST">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="cedulaLogin" class="form-label">Cédula:</label>
                        <input type="text" class="form-control" id="cedulaLogin" name="cedula" required>
                    </div>
                    <div class="mb-3">
                        <label for="claveLogin" class="form-label">Contraseña:</label>
                        <input type="password" class="form-control" id="claveLogin" name="clave" required>
                    </div>
                    <p class="text-center mt-3"><a href="#" data-bs-dismiss="modal" data-bs-toggle="modal" data-bs-target="#registroModal">¿No tienes cuenta? Regístrate aquí.</a></p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-lock-open me-1"></i> Entrar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="registroModal" tabindex="-1" aria-labelledby="registroModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title" id="registroModalLabel"><i class="fas fa-user-plus me-2"></i> Crear Cuenta Nueva</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesar_registro.php" method="POST">
                <div class="modal-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="nombreRegistro" class="form-label">Nombre Completo:</label>
                            <input type="text" class="form-control" id="nombreRegistro" name="nombre_completo" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cedulaRegistro" class="form-label fw-bold text-primary">Cédula:</label>
                            <input type="text" class="form-control" id="cedulaRegistro" name="cedula" required>
                            <small class="form-text text-muted">Usaremos este número para identificarte.</small>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="emailRegistro" class="form-label">Email:</label>
                            <input type="email" class="form-control" id="emailRegistro" name="email" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="telefonoRegistro" class="form-label">Teléfono:</label>
                            <input type="text" class="form-control" id="telefonoRegistro" name="telefono">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label for="claveRegistro" class="form-label">Contraseña:</label>
                        <input type="password" class="form-control" id="claveRegistro" name="clave" required minlength="6">
                    </div>
                    <div class="mb-3">
                        <label for="direccionRegistro" class="form-label">Dirección (Opcional):</label>
                        <textarea class="form-control" id="direccionRegistro" name="direccion" rows="2"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-warning fw-bold"><i class="fas fa-save me-1"></i> Registrarme</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // Lógica para llenar el modal del carrito (se mantiene igual)
    var carritoModal = document.getElementById('carritoModal');
    carritoModal.addEventListener('show.bs.modal', function (event) {
        var button = event.relatedTarget; 
        var id = button.getAttribute('data-id');
        var nombre = button.getAttribute('data-nombre');
        var precio = button.getAttribute('data-precio');

        var modalTitle = carritoModal.querySelector('#nombreProductoModal');
        var modalPrice = carritoModal.querySelector('#precioProductoModal');
        var modalIdInput = carritoModal.querySelector('#idProductoInput');
        var modalCantidadInput = carritoModal.querySelector('#cantidadInput');

        modalTitle.textContent = nombre;
        modalPrice.textContent = `$${parseFloat(precio).toFixed(2)}`;
        modalIdInput.value = id;
        modalCantidadInput.value = 1; 
    });
</script>

</body>
</html>