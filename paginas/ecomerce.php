<?php
session_start(); 

// --- LÓGICA DE CIERRE DE SESIÓN ---
if (isset($_GET['logout'])) {
    session_unset();    // Elimina las variables de sesión
    session_destroy();  // Destruye la sesión
    header("Location: ecomerce.php"); // Recarga para limpiar la URL y actualizar la UI
    exit();
}
// ----------------------------------
include("../conexion/conex.php");
include("buscador-p-ecomerce.php");

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Lógica de búsqueda
$termino_busqueda = isset($_GET['buscar']) ? $_GET['buscar'] : '';
$resultado = buscarProductos($conn, $termino_busqueda);

include("../recursos/header.php");
?>

<style>
    body {
        padding-top: 70px;
        background-color: #f8f9fa;
    }

    .bg-primary-custom {
        background-color: #007bff !important;
    }

    .card {
        transition: transform 0.2s;
        border-radius: 10px;
    }

    .card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 20px rgba(0, 0, 0, 0.1);
    }

    .contenedor-img-ecom {
        height: 180px;
        display: flex;
        align-items: center;
        justify-content: center;
        padding: 10px;
    }

    .img-producto-ajuste {
        max-height: 100%;
        max-width: 100%;
        object-fit: contain;
    }
</style>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom fixed-top shadow-sm">
    <div class="container">
        <a class="navbar-brand" href="ecomerce.php">
            <i class="fas fa-hand-holding-medical text-warning"></i>
            Farmacia <span class="fw-bold">Ecomerce</span>
        </a>

        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbarNav">
            <form class="d-flex mx-auto col-lg-5" method="GET" action="ecomerce.php">
                <div class="input-group">
                    <input class="form-control" type="search" placeholder="Buscar medicamentos..." name="buscar" value="<?php echo htmlspecialchars($termino_busqueda); ?>">
                    <button class="btn btn-warning" type="submit"><i class="fas fa-search"></i></button>
                </div>
            </form>

            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['logeado']) && $_SESSION['logeado'] === true): ?>
                    <li class="nav-item me-3">
                        <a class="btn btn-warning position-relative" href="ver_carrito.php">
                            <i class="fas fa-shopping-cart"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php echo isset($_SESSION['carrito']) ? count($_SESSION['carrito']) : '0'; ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" data-bs-toggle="dropdown">
                            <i class="fas fa-user-circle"></i> ID: <?php echo $_SESSION['usuario_cedula']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="mis_compras.php"><i class="fas fa-list"></i> Mis Pedidos</a></li>
                            <li>
                                <hr class="dropdown-divider">
                            </li>
                            <li>
                                <a class="dropdown-item text-danger" href="ecomerce.php?logout=true">
                                    <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <button class="btn btn-outline-light me-2" data-bs-toggle="modal" data-bs-target="#modalLogin">Iniciar Sesión</button>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-warning text-dark fw-bold" data-bs-toggle="modal" data-bs-target="#modalRegistro">Registrarse</button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-3">
    <?php if (isset($_SESSION['mensaje_texto'])): ?>
        <div class="alert alert-<?php echo $_SESSION['mensaje_tipo'] == 'success' ? 'success' : 'danger'; ?> alert-dismissible fade show">
            <?php echo $_SESSION['mensaje_texto'];
            unset($_SESSION['mensaje_texto']);
            unset($_SESSION['mensaje_tipo']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>
</div>

<div class="container mt-4">
    <header class="text-center mb-5 p-4 bg-white rounded shadow-sm border-start border-success border-5">
        <h1 class="h2 text-success"><i class="fas fa-pills me-2"></i> Nuestro Catálogo Farmacéutico</h1>
    </header>

    <div class="row row-cols-1 row-cols-sm-2 row-cols-lg-4 g-4">
        <?php
        if ($resultado && $resultado->num_rows > 0) {
            while ($fila = $resultado->fetch_assoc()) {
                $nombre_foto = !empty($fila['imagen']) ? $fila['imagen'] : 'descarga.png';
                $ruta_final = "../img/" . $nombre_foto;
                if (!file_exists($ruta_final)) {
                    $ruta_final = "../img/descarga.png";
                }
        ?>
                <div class="col">
                    <div class="card h-100 shadow-sm border-0">
                        <div class="contenedor-img-ecom">
                            <img src="<?php echo $ruta_final; ?>" class="img-producto-ajuste" alt="Producto">
                        </div>
                        <div class="card-body d-flex flex-column text-center">
                            <h6 class="card-title fw-bold text-primary"><?php echo htmlspecialchars($fila['nombre_producto']); ?></h6>
                            <p class="small text-muted mb-3"><?php echo htmlspecialchars(substr($fila['descripcion'], 0, 50)) . '...'; ?></p>
                            <p class="h5 text-danger mt-auto">$<?php echo number_format($fila['precio_venta'], 2); ?></p>

                            <button type="button" class="btn btn-success w-100 mt-2 rounded-pill"
                                data-bs-toggle="modal" data-bs-target="#carritoModal"
                                data-nombre="<?php echo htmlspecialchars($fila['nombre_producto']); ?>"
                                data-precio="<?php echo htmlspecialchars($fila['precio_venta']); ?>"
                                data-id="<?php echo htmlspecialchars($fila['id']); ?>">
                                <i class="fas fa-plus-circle me-1"></i> Comprar
                            </button>
                        </div>
                    </div>
                </div>
        <?php
            }
        } else {
            echo '<div class="col-12 text-center py-5"><h3>No hay productos disponibles.</h3></div>';
        }
        ?>
    </div>
</div>

<div class="modal fade" id="modalLogin" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title"><i class="fas fa-lock me-2"></i> Iniciar Sesión</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_login.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Cédula</label>
                        <input type="text" name="cedula" class="form-control" placeholder="V-00000000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Contraseña</label>
                        <input type="password" name="clave" class="form-control" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary w-100 fw-bold">ENTRAR</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRegistro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title"><i class="fas fa-user-plus me-2"></i> Registro de Cliente</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_registro.php" method="POST">
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nombre Completo</label>
                            <input type="text" name="nombre_completo" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Cédula</label>
                            <input type="text" name="cedula" class="form-control" placeholder="V-XXXXXXXX" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Teléfono</label>
                            <input type="text" name="telefono" class="form-control" required>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Dirección de Entrega</label>
                            <textarea name="direccion" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-md-12 mb-3">
                            <label class="form-label fw-bold">Contraseña</label>
                            <input type="password" name="clave" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer text-center">
                    <button type="submit" class="btn btn-success w-100 fw-bold">CREAR CUENTA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="carritoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold">Añadir al Carrito</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_carrito.php" method="POST">
                <div class="modal-body text-center p-4">
                    <?php if (!isset($_SESSION['logeado'])): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-triangle"></i> Debe iniciar sesión para comprar.
                        </div>
                        <button type="button" class="btn btn-primary w-100" data-bs-toggle="modal" data-bs-target="#modalLogin">Ir al Login</button>
                    <?php else: ?>
                        <h4 id="nombreProductoModal" class="text-primary"></h4>
                        <p class="h3 text-danger fw-bold" id="precioProductoModal"></p>
                        <input type="hidden" name="id_producto" id="idProductoInput">
                        <div class="mt-3 mx-auto" style="max-width: 150px;">
                            <label class="form-label">Cantidad:</label>
                            <input type="number" class="form-control text-center" name="cantidad" value="1" min="1" required>
                        </div>
                        <button type="submit" class="btn btn-warning w-100 mt-4 fw-bold shadow-sm">CONFIRMAR</button>
                    <?php endif; ?>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
    var carritoModal = document.getElementById('carritoModal');
    carritoModal.addEventListener('show.bs.modal', function(event) {
        var button = event.relatedTarget;
        document.getElementById('nombreProductoModal').textContent = button.getAttribute('data-nombre');
        document.getElementById('precioProductoModal').textContent = '$' + button.getAttribute('data-precio');
        document.getElementById('idProductoInput').value = button.getAttribute('data-id');
    });
</script>
</body>

</html>