<?php
// No es estrictamente necesario incluir conex.php aquí si no realizas operaciones directas de BD
// pero si lo tienes para alguna otra funcionalidad, no hay problema.
include("../conexion/conex.php"); 
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventario - Registrar Venta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-T3c6CoIi6uLrA9TneNEoa7RxnatzjcDSCmG1MXxSR1GAsXEV/Dwwykc2MPK8M2HN" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css"> 
    <link rel="stylesheet" href="../recursos/estilos/style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body>
    
<div class="container mt-4">
    <h2><i class="fas fa-cash-register"></i> Registrar Venta</h2>
    <hr>
    <form id="formVenta">
        <div class="mb-3">
            <label for="buscarProducto" class="form-label">Buscar Producto:</label>
            <input type="text" class="form-control" id="buscarProducto" placeholder="Escribe el nombre o código del producto">
            <div id="sugerenciasProductos" class="list-group"></div>
        </div>

        <div class="table-responsive">
            <table class="table table-bordered table-striped">
                <thead>
                    <tr>
                        <th>Producto</th>
                        <th>Precio Unitario</th>
                        <th>Cantidad</th>
                        <th>Subtotal</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody id="productosVenta">
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="3" class="text-end"><strong>Total:</strong></td>
                        <td id="totalVenta">0.00</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="d-grid gap-2 mt-4">
            <button type="submit" class="btn btn-success btn-lg"><i class="fas fa-check-circle"></i> Confirmar Venta</button>
        </div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.js" integrity="sha256-eKhayi8LEQwp4NKxN+CfCh+3qOVUtJn3QNZ0TciWLP4=" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.min.js" integrity="sha384-BBtl+eGJRgqQAUMxJ7pMwbEyER4l1g+O15P+16Ep7Q9Q+zqX6gSbd85u4mG4QzX+" crossorigin="anonymous"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarProductoInput = document.getElementById('buscarProducto');
    const sugerenciasProductosDiv = document.getElementById('sugerenciasProductos');
    const productosVentaBody = document.getElementById('productosVenta');
    const totalVentaSpan = document.getElementById('totalVenta');
    const formVenta = document.getElementById('formVenta');

    let carrito = [];

    function actualizarTotal() {
        let total = 0;
        carrito.forEach(producto => {
            total += producto.cantidad * producto.precio_unitario;
        });
        totalVentaSpan.textContent = total.toFixed(2);
    }

    function renderizarCarrito() {
        productosVentaBody.innerHTML = '';
        carrito.forEach((producto, index) => {
            const row = productosVentaBody.insertRow();
            row.innerHTML = `
                <td>${producto.nombre}</td>
                <td>${producto.precio_unitario.toFixed(2)}</td>
                <td>
                    <input type="number" class="form-control cantidad-input" min="1" value="${producto.cantidad}" data-index="${index}" style="width: 80px;">
                </td>
                <td>${(producto.cantidad * producto.precio_unitario).toFixed(2)}</td>
                <td>
                    <button type="button" class="btn btn-danger btn-sm eliminar-producto" data-index="${index}"><i class="fas fa-trash-alt"></i></button>
                </td>
            `;
        });
        actualizarTotal();
    }

    productosVentaBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            const index = parseInt(e.target.dataset.index);
            let nuevaCantidad = parseInt(e.target.value);

            if (isNaN(nuevaCantidad) || nuevaCantidad < 1) {
                nuevaCantidad = 1;
                e.target.value = 1;
            }

            const productoEnCarrito = carrito[index];
            if (productoEnCarrito) {
                if (nuevaCantidad > productoEnCarrito.stock_disponible) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Stock Insuficiente',
                        text: `No puedes añadir más de ${productoEnCarrito.nombre} de lo que hay en stock (${productoEnCarrito.stock_disponible} unidades).`,
                        confirmButtonText: 'Entendido'
                    });
                    e.target.value = productoEnCarrito.stock_disponible;
                    productoEnCarrito.cantidad = productoEnCarrito.stock_disponible;
                } else {
                    productoEnCarrito.cantidad = nuevaCantidad;
                }
                renderizarCarrito();
            }
        }
    });

    productosVentaBody.addEventListener('click', function(e) {
        if (e.target.classList.contains('eliminar-producto') || e.target.closest('.eliminar-producto')) {
            const button = e.target.closest('.eliminar-producto');
            const index = parseInt(button.dataset.index);
            carrito.splice(index, 1);
            renderizarCarrito();
        }
    });

    buscarProductoInput.addEventListener('input', function() {
        const query = this.value.trim();
        sugerenciasProductosDiv.innerHTML = '';

        if (query.length > 2) {
            fetch(`productos_api.php?query=${encodeURIComponent(query)}`)
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Error HTTP en productos_api.php:', text);
                            throw new Error(`La API de búsqueda de productos devolvió un error ${response.status}. Respuesta: ${text.substring(0, 200)}...`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        Swal.fire('Error', 'Error del servidor al buscar productos: ' + data.error, 'error');
                        return;
                    }
                    if (data.length > 0) {
                        data.forEach(producto => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.classList.add('list-group-item', 'list-group-item-action');
                            item.textContent = `${producto.nombre_producto} (Stock: ${producto.stock_actual})`;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                const existe = carrito.find(p => p.id === producto.id);
                                if (existe) {
                                    if (existe.cantidad + 1 > producto.stock_actual) {
                                        Swal.fire({
                                            icon: 'warning',
                                            title: 'Stock Insuficiente',
                                            text: `No hay suficiente stock para añadir más de ${producto.nombre_producto}. Stock disponible: ${producto.stock_actual}`,
                                            confirmButtonText: 'Entendido'
                                        });
                                        return;
                                    }
                                    existe.cantidad++;
                                } else {
                                    if (producto.stock_actual <= 0) {
                                        Swal.fire({
                                            icon: 'error',
                                            title: 'Sin Stock',
                                            text: `El producto ${producto.nombre_producto} no tiene stock disponible.`,
                                            confirmButtonText: 'Entendido'
                                        });
                                        return;
                                    }
                                    carrito.push({
                                        id: producto.id,
                                        nombre: producto.nombre_producto,
                                        precio_unitario: parseFloat(producto.precio_venta),
                                        cantidad: 1,
                                        stock_disponible: producto.stock_actual
                                    });
                                }
                                renderizarCarrito();
                                buscarProductoInput.value = '';
                                sugerenciasProductosDiv.innerHTML = '';
                            });
                            sugerenciasProductosDiv.appendChild(item);
                        });
                    }
                })
                .catch(error => {
                    console.error('Error en la petición de búsqueda de productos:', error);
                    Swal.fire('Error', 'Ocurrió un error al buscar productos. Consulta la consola para más detalles.', 'error');
                });
        }
    });

    formVenta.addEventListener('submit', function(e) {
        e.preventDefault();

        if (carrito.length === 0) {
            Swal.fire({
                icon: 'info',
                title: 'Carrito Vacío',
                text: '¡No hay productos en la venta! 😕',
                confirmButtonText: 'Ok'
            });
            return;
        }

        Swal.fire({
            title: 'Confirmar Venta',
            text: '¿Está seguro de que desea registrar esta venta?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Sí, registrar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('registrar_venta.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ productos: carrito })
                })
                .then(response => {
                    if (!response.ok) {
                        return response.text().then(text => {
                            console.error('Error HTTP en registrar_venta.php (backend):', text);
                            throw new Error(`La API de registro de venta devolvió un error ${response.status}. Respuesta: ${text.substring(0, 200)}...`);
                        });
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        Swal.fire(
                            '¡Venta Exitosa!',
                            data.message,
                            'success'
                        );
                        carrito = [];
                        renderizarCarrito();
                    } else {
                        Swal.fire(
                            'Error al Registrar',
                            data.message,
                            'error'
                        );
                    }
                })
                .catch(error => {
                    console.error('Error en la petición de registro de venta:', error);
                    Swal.fire(
                        'Error',
                        'Ocurrió un error al registrar la venta. Consulta la consola para más detalles.',
                        'error'
                    );
                });
            }
        });
    });
});
</script>