<?php
session_start();
include("../conexion/conex.php");
include("buscador-p-ecomerce.php");

if (isset($_GET['logout'])) {
    session_unset();
    session_destroy();
    header("Location: ecomerce.php");
    exit();
}

$resultado = buscarProductos($conn, '');
include("../recursos/header.php"); // Asegúrate que aquí NO se cargue otro bootstrap.js antiguo
?>

<link rel="stylesheet" href="../recursos/estilos_ecomerce.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

<nav class="navbar navbar-expand-lg navbar-dark bg-primary-custom fixed-top shadow">
    <div class="container">
        <a class="navbar-brand d-flex align-items-center" href="ecomerce.php">
            <i class="bi bi-capsule-pill text-info me-2 fs-3"></i>
            <span class="fw-bold">PHARMA</span><span class="text-info">CORE</span>
        </a>
        <div class="collapse navbar-collapse" id="navbarNav">
            <div class="mx-auto" style="width: 40%;">
                <div class="input-group">
                    <span class="input-group-text bg-white border-0" style="border-radius: 50px 0 0 50px;">
                        <i class="bi bi-search text-muted"></i>
                    </span>
                    <input class="form-control border-0" type="text" id="inputBuscador" placeholder="Buscar medicamento..." style="border-radius: 0 50px 50px 0 !important; box-shadow: none;">
                </div>
            </div>
            <ul class="navbar-nav ms-auto align-items-center">
                <?php if (isset($_SESSION['logeado'])): ?>
                    <li class="nav-item me-3">
                        <a class="btn btn-info position-relative rounded-pill text-white" href="ver_carrito.php">
                            <i class="bi bi-cart3"></i>
                            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                                <?php 
                                $t = 0;
                                if (isset($_SESSION['carrito'])) foreach ($_SESSION['carrito'] as $i) $t += $i['cantidad'];
                                echo $t; 
                                ?>
                            </span>
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-white" href="#" id="userDrop" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> <?= $_SESSION['usuario_cedula']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end shadow border-0">
                            <li><a class="dropdown-item" href="mis_pedidos.php"><i class="bi bi-bag-check me-2"></i>Mis Pedidos</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="ecomerce.php?logout=true"><i class="bi bi-power me-2"></i>Salir</a></li>
                        </ul>
                    </li>
                <?php else: ?>
                    <li class="nav-item">
                        <button class="btn btn-outline-light me-2 border-0" data-bs-toggle="modal" data-bs-target="#modalLogin">Entrar</button>
                    </li>
                    <li class="nav-item">
                        <button class="btn btn-info text-white fw-bold rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#modalRegistro">Unirse</button>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-5">
    <div class="row row-cols-1 row-cols-md-4 g-4" id="gridProductos">
        <?php while ($fila = $resultado->fetch_assoc()):
            $img = !empty($fila['imagen']) && file_exists("../img/" . $fila['imagen']) ? "../img/" . $fila['imagen'] : "../img/descarga.png";
        ?>
            <div class="col producto-item" data-nombre="<?= strtolower($fila['nombre_producto']); ?>" data-lab="<?= strtolower($fila['laboratorio_fabrica'] ?? ''); ?>">
                <div class="card h-100 card-producto shadow-sm">
                    <div class="contenedor-img-ecom p-3" style="height:180px; display:flex; align-items:center; justify-content:center;">
                        <img src="<?= $img ?>" class="img-fluid" style="max-height:100%; object-fit:contain;">
                    </div>
                    <div class="card-body d-flex flex-column text-center">
                        <h6 class="fw-bold"><?= htmlspecialchars($fila['nombre_producto']); ?></h6>
                        <p class="text-primary fw-bold fs-5 mt-auto">$<?= number_format($fila['precio_venta'], 2); ?></p>
                        <button class="btn btn-buy" 
                                data-bs-toggle="modal" 
                                data-bs-target="#carritoModal"
                                data-nombre="<?= $fila['nombre_producto'] ?>" 
                                data-precio="<?= $fila['precio_venta'] ?>" 
                                data-id="<?= $fila['id'] ?>">
                            <i class="bi bi-plus-circle me-1"></i> Añadir
                        </button>
                    </div>
                </div>
            </div>
        <?php endwhile; ?>
    </div>
</div>

<div class="modal fade" id="modalLogin" tabindex="-1" aria-labelledby="modalLoginLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-shield-lock me-2"></i>Ingreso de Clientes</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="procesar_login.php" method="POST">
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">CÉDULA DE IDENTIDAD (Campo: cedula)</label>
                        <input type="text" name="cedula" class="form-control form-control-lg" placeholder="V-00000000" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-muted">CONTRASEÑA</label>
                        <input type="password" name="clave" class="form-control form-control-lg" required>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">INICIAR SESIÓN</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="modalRegistro" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title fw-bold"><i class="bi bi-person-plus me-2"></i>Registro de Nuevo Usuario</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_registro.php" method="POST">
                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">NOMBRE COMPLETO</label>
                            <input type="text" name="nombre_completo" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">CÉDULA (ID)</label>
                            <input type="text" name="cedula" class="form-control" placeholder="V-XXXXXXXX" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">EMAIL</label>
                            <input type="email" name="email" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-bold small">TELÉFONO</label>
                            <input type="text" name="telefono" class="form-control" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">DIRECCIÓN DE DESPACHO</label>
                            <textarea name="direccion" class="form-control" rows="2" required></textarea>
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-bold small">CREAR CONTRASEÑA</label>
                            <input type="password" name="clave" class="form-control" required>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-success w-100 py-3 text-uppercase fw-bold">Crear mi cuenta</button>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="modal fade" id="carritoModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <div class="modal-header bg-warning">
                <h5 class="modal-title fw-bold text-dark"><i class="bi bi-cart-plus me-2"></i>Confirmar pedido</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="procesar_carrito.php" method="POST">
                <div class="modal-body text-center p-4">
                    <?php if (!isset($_SESSION['logeado'])): ?>
                        <div class="py-4">
                            <i class="bi bi-lock text-danger display-1"></i>
                            <h5 class="mt-3 fw-bold">Acceso Restringido</h5>
                            <p class="text-muted">Inicia sesión para poder agregar productos al carrito.</p>
                            <button type="button" class="btn btn-primary px-5 py-2 fw-bold" 
                                    data-bs-dismiss="modal" 
                                    data-bs-toggle="modal" 
                                    data-bs-target="#modalLogin">
                                Ir al Login
                            </button>
                        </div>
                    <?php else: ?>
                        <h4 id="nombreProductoModal" class="fw-bold"></h4>
                        <p class="h2 text-primary fw-bold" id="precioProductoModal"></p>
                        <input type="hidden" name="id_producto" id="idProductoInput">
                        <div class="mt-4 mx-auto" style="max-width: 150px;">
                            <label class="form-label fw-bold small">CANTIDAD</label>
                            <input type="number" class="form-control form-control-lg text-center fw-bold" name="cantidad" value="1" min="1" required>
                        </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="submit" class="btn btn-warning w-100 py-3 fw-bold shadow-sm">AGREGAR AL CARRITO</button>
                </div>
                    <?php endif; ?>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
    // 1. Lógica del buscador
    document.getElementById('inputBuscador').addEventListener('input', function(e) {
        const q = e.target.value.toLowerCase();
        document.querySelectorAll('.producto-item').forEach(item => {
            const nombre = item.getAttribute('data-nombre');
            const lab = item.getAttribute('data-lab');
            item.style.display = (nombre.includes(q) || lab.includes(q)) ? 'block' : 'none';
        });
    });

    // 2. Pasar datos al modal de carrito
    const modalCarrito = document.getElementById('carritoModal');
    if(modalCarrito) {
        modalCarrito.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            // Solo si el usuario está logeado llenamos estos campos
            const nombreElem = document.getElementById('nombreProductoModal');
            if(nombreElem) {
                nombreElem.textContent = button.getAttribute('data-nombre');
                document.getElementById('precioProductoModal').textContent = '$' + button.getAttribute('data-precio');
                document.getElementById('idProductoInput').value = button.getAttribute('data-id');
            }
        });
    }

    // 3. FIX: Limpieza de Backdrops (por si acaso)
    document.querySelectorAll('.modal').forEach(m => {
        m.addEventListener('hidden.bs.modal', () => {
            document.querySelectorAll('.modal-backdrop').forEach(b => b.remove());
            document.body.style.overflow = 'auto';
        });
    });
</script>