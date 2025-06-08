
<?php  include("../recursos/header.php")?>

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
<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarProductoInput = document.getElementById('buscarProducto');
    const sugerenciasProductosDiv = document.getElementById('sugerenciasProductos');
    const productosVentaBody = document.getElementById('productosVenta');
    const totalVentaSpan = document.getElementById('totalVenta');
    const formVenta = document.getElementById('formVenta');

    let carrito = []; // Almacenará los productos en el carrito

    // Función para actualizar el total de la venta
    function actualizarTotal() {
        let total = 0;
        carrito.forEach(producto => {
            total += producto.cantidad * producto.precio_unitario;
        });
        totalVentaSpan.textContent = total.toFixed(2);
    }

    // Función para renderizar la tabla de productos en la venta
    function renderizarCarrito() {
        productosVentaBody.innerHTML = ''; // Limpiar tabla
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

    // Evento para buscar productos (autocompletado)
    buscarProductoInput.addEventListener('input', function() {
        const query = this.value.trim();
        sugerenciasProductosDiv.innerHTML = '';

        if (query.length > 2) { // Mínimo 3 caracteres para buscar
            fetch(`productos_api.php?query=${encodeURIComponent(query)}`)
                .then(response => {
                    const contentType = response.headers.get("content-type");
                    if (contentType && contentType.includes("application/json")) {
                        return response.json();
                    } else {
                        return response.text().then(text => {
                            console.error('La respuesta no es JSON (o content-type incorrecto):', text);
                            throw new Error('La respuesta del servidor no es JSON o tiene un Content-Type inesperado. Revisa el archivo productos_api.php');
                        });
                    }
                })
                .then(data => {
                    if (data.error) {
                        alert('Error del servidor: ' + data.error);
                        return;
                    }

                    if (data.length > 0) {
                        data.forEach(producto => {
                            const item = document.createElement('a');
                            item.href = '#';
                            item.classList.add('list-group-item', 'list-group-item-action');
                            // AQUI: Usas 'nombre_producto' para mostrarlo en las sugerencias, ¡esto está bien!
                            item.textContent = `${producto.nombre_producto} (Stock: ${producto.stock_actual})`;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                // Añadir producto al carrito
                                const existe = carrito.find(p => p.id === producto.id);
                                if (existe) {
                                    // Asegúrate de que la cantidad no exceda el stock disponible si lo validas en frontend
                                    // if (existe.cantidad + 1 > producto.stock_actual) {
                                    //     alert(`No hay suficiente stock para añadir más de ${producto.nombre_producto}.`);
                                    //     return;
                                    // }
                                    existe.cantidad++;
                                } else {
                                    // Validar si el stock es 0 antes de añadir
                                    if (producto.stock_actual <= 0) {
                                        alert(`El producto ${producto.nombre_producto} no tiene stock disponible.`);
                                        return;
                                    }
                                    carrito.push({
                                        id: producto.id,
                                        // ****** LA CLAVE ESTÁ AQUÍ ******
                                        // Asigna el valor de 'nombre_producto' que viene del servidor
                                        // a la propiedad 'nombre' de tu objeto local en el carrito.
                                        nombre: producto.nombre_producto,
                                        // *******************************
                                        precio_unitario: parseFloat(producto.precio_venta),
                                        cantidad: 1,
                                        stock_disponible: producto.stock_actual
                                    });
                                }
                                renderizarCarrito();
                                buscarProductoInput.value = ''; // Limpiar input
                                sugerenciasProductosDiv.innerHTML = ''; // Limpiar sugerencias
                            });
                            sugerenciasProductosDiv.appendChild(item);
                        });
                    }
                })
                .catch(error => console.error('Error al obtener productos:', error));
        }
    });
    // Evento para enviar la venta
    formVenta.addEventListener('submit', function(e) {
        e.preventDefault(); // <-- ¡ESTA LÍNEA ES CRUCIAL!

        if (carrito.length === 0) {
            alert('¡No hay productos en la venta! 😕');
            return;
        }

        if (confirm('¿Está seguro de que desea registrar esta venta?')) {
            fetch('registrar_venta.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ productos: carrito })
            })
            .then(response => {
                // Asegúrate de que la respuesta sea JSON o text para depurar
                const contentType = response.headers.get("content-type");
                if (contentType && contentType.includes("application/json")) {
                    return response.json();
                } else {
                    return response.text().then(text => {
                        console.error('La respuesta de registrar_venta.php no es JSON o tiene un Content-Type inesperado:', text);
                        throw new Error('Error en la respuesta del servidor al registrar la venta. Revisa registrar_venta.php');
                    });
                }
            })
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    carrito = []; // Limpiar carrito
                    renderizarCarrito(); // Limpiar tabla
                    // Opcional: Redirigir a una página de confirmación o de listado de ventas
                } else {
                    alert('Error al registrar venta: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error al registrar la venta (catch):', error);
                alert('Ocurrió un error al registrar la venta. Consulta la consola para más detalles.');
            });
        }
    });

    // ... (El resto del código JavaScript sigue igual) ...
    // Aquí es donde ya usas producto.nombre, que ahora sí tendrá el valor correcto
    // renderizarCarrito() {
    //     row.innerHTML = `<td>${producto.nombre}</td>...`
    // }
    // ...
});
</script>
<?php  include("../recursos/footer.php")?>