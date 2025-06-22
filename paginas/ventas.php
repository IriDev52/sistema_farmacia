<?php include("../recursos/header.php")?>

<div class="container mt-4">
    <header class="d-flex justify-content-between align-items-center p-3 ">
    <h2 class="text-black">Registrar Venta</h2>
    <a href="../paginas/inicio.php" class="btn btn-light "><i class="bi bi-arrow-left"></i> Regresar a Inicio</a>
</header>
    
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

<div id="invoiceContent" style="display: none; width: 800px; padding: 20px; box-sizing: border-box; background: white;">
    </div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jspdf/2.5.1/jspdf.umd.min.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const buscarProductoInput = document.getElementById('buscarProducto');
    const sugerenciasProductosDiv = document.getElementById('sugerenciasProductos');
    const productosVentaBody = document.getElementById('productosVenta');
    const totalVentaSpan = document.getElementById('totalVenta');
    const formVenta = document.getElementById('formVenta');

    let carrito = []; // Almacenará los productos en el carrito

    // Necesitas acceder a jsPDF y html2canvas desde el objeto window
    // Esto se asegura de que jspdf.umd.min.js haya cargado el objeto jsPDF en window
    const { jsPDF } = window.jspdf;


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
                            item.textContent = `${producto.nombre_producto} (Stock: ${producto.stock_actual})`;
                            item.addEventListener('click', function(e) {
                                e.preventDefault();
                                const existe = carrito.find(p => p.id === producto.id);
                                if (existe) {
                                    if (existe.cantidad + 1 > producto.stock_actual) {
                                        alert(`No hay suficiente stock para añadir más de ${producto.nombre_producto}.`);
                                        return;
                                    }
                                    existe.cantidad++;
                                } else {
                                    if (producto.stock_actual <= 0) {
                                        alert(`El producto ${producto.nombre_producto} no tiene stock disponible.`);
                                        return;
                                    }
                                    carrito.push({
                                        id: producto.id,
                                        nombre: producto.nombre_producto, // Asigna el valor de 'nombre_producto'
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

    // Evento para actualizar cantidad directamente en la tabla
    productosVentaBody.addEventListener('change', function(e) {
        if (e.target.classList.contains('cantidad-input')) {
            const index = parseInt(e.target.dataset.index);
            let nuevaCantidad = parseInt(e.target.value);

            if (isNaN(nuevaCantidad) || nuevaCantidad < 1) {
                alert('La cantidad debe ser un número positivo.');
                e.target.value = carrito[index].cantidad; // Revertir al valor anterior
                return;
            }

            if (nuevaCantidad > carrito[index].stock_disponible) {
                alert(`No hay suficiente stock para la cantidad solicitada de ${carrito[index].nombre}. Stock disponible: ${carrito[index].stock_disponible}`);
                e.target.value = carrito[index].cantidad; // Revertir al valor anterior
                return;
            }
            carrito[index].cantidad = nuevaCantidad;
            renderizarCarrito();
        }
    });

    // Evento para eliminar producto del carrito
    productosVentaBody.addEventListener('click', function(e) {
        if (e.target.closest('.eliminar-producto')) {
            const index = parseInt(e.target.closest('.eliminar-producto').dataset.index);
            carrito.splice(index, 1); // Eliminar del array
            renderizarCarrito(); // Volver a renderizar
        }
    });

    // --- FUNCION PARA GENERAR EL PDF (MOVIDA Y CONSOLIDADA) ---
    async function generateInvoicePdf(saleId, saleData, saleDetails) {
        const invoiceContentDiv = document.getElementById('invoiceContent');
        if (!invoiceContentDiv) {
            console.error("El div #invoiceContent no se encontró.");
            alert("Error interno al preparar la factura.");
            return;
        }

        // 1. Construir el HTML de la factura dinámicamente
        let invoiceHtml = `
            <style>
                body { font-family: sans-serif; margin: 0; padding: 0; font-size: 10px; color: #333; }
                .container { width: 100%; max-width: 780px; margin: 0 auto; padding: 20px; border: 1px solid #eee; }
                .header { text-align: center; margin-bottom: 20px; }
                .header h1 { margin: 0; font-size: 24px; color: #007bff; }
                .header p { margin: 2px 0; font-size: 11px; }
                .invoice-info { display: flex; justify-content: space-between; margin-bottom: 15px; }
                .invoice-info div { flex: 1; }
                .invoice-info strong { display: block; margin-bottom: 5px; color: #555; }
                .details-table { width: 100%; border-collapse: collapse; margin-top: 20px; }
                .details-table th, .details-table td { border: 1px solid #ddd; padding: 8px; text-align: left; }
                .details-table th { background-color: #f8f8f8; color: #666; }
                .total-section { text-align: right; margin-top: 20px; }
                .total-section h3 { margin: 0; font-size: 18px; color: #007bff; }
                .footer { text-align: center; margin-top: 30px; font-size: 9px; color: #888; }
                .text-right { text-align: right; }
            </style>
            <div class="container">
                <div class="header">
                    <h1>Factura de Venta</h1>
                    <p>Tu Farmacia - [Nombre de tu Empresa]</p>
                    <p>Dirección: Calle Falsa 123, Barinitas, Barinas, Venezuela</p>
                    <p>Teléfono: (123) 456-7890 | Email: contacto@tufarmacia.com</p>
                    <hr>
                </div>

                <div class="invoice-info">
                    <div>
                        <strong>FACTURA # ${saleData.id_venta}</strong>
                        <span>Fecha: ${new Date(saleData.fecha_venta).toLocaleDateString('es-ES', { day: '2-digit', month: '2-digit', year: 'numeric', hour: '2-digit', minute: '2-digit' })}</span>
                    </div>
                    <div class="text-right">
                        <strong>Cliente: Venta al Público</strong>
                        <span>(Cliente genérico)</span>
                    </div>
                </div>

                <table class="details-table">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Cantidad</th>
                            <th>P. Unitario</th>
                            <th>Subtotal</th>
                        </tr>
                    </thead>
                    <tbody>
        `;

        saleDetails.forEach(item => {
            invoiceHtml += `
                <tr>
                    <td>${item.nombre_producto}</td>
                    <td>${item.cantidad}</td>
                    <td>${parseFloat(item.precio_unitario).toFixed(2)}</td>
                    <td>${parseFloat(item.subtotal).toFixed(2)}</td>
                </tr>
            `;
        });

        invoiceHtml += `
                    </tbody>
                </table>

                <div class="total-section">
                    <h3>Total: ${parseFloat(saleData.total_venta).toFixed(2)}</h3>
                </div>

                <div class="footer">
                    <p>¡Gracias por su compra!</p>
                    <p>Este es un recibo generado automáticamente.</p>
                </div>
            </div>
        `;

        invoiceContentDiv.innerHTML = invoiceHtml; // Carga el HTML en el div oculto

        // 2. Usar html2canvas para renderizar el div como una imagen
        const canvas = await html2canvas(invoiceContentDiv, { scale: 2 });
        const imgData = canvas.toDataURL('image/png'); // Obtiene la imagen en base64

        // 3. Crear el PDF con jsPDF
        const pdf = new jsPDF('p', 'mm', 'a4'); // 'p' para portrait (vertical)

        const imgWidth = 190; // Ancho deseado en mm (aproximadamente, dejando márgenes)
        const pageHeight = pdf.internal.pageSize.getHeight();
        const imgHeight = canvas.height * imgWidth / canvas.width; // Mantiene la relación de aspecto

        let heightLeft = imgHeight;
        let position = 10; // Posición inicial desde el borde superior

        pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight); // Añade la imagen al PDF
        heightLeft -= pageHeight;

        while (heightLeft >= 0) {
            position = heightLeft - imgHeight + 10;
            pdf.addPage();
            pdf.addImage(imgData, 'PNG', 10, position, imgWidth, imgHeight);
            heightLeft -= pageHeight;
        }

        // 4. Abrir el PDF en una nueva pestaña (preview)
        window.open(pdf.output('bloburl'), '_blank');
        // Para descargar directamente: pdf.save(`factura_venta_${saleId}.pdf`);

        invoiceContentDiv.innerHTML = ''; // Limpiar el contenido del div después de generar el PDF
    }


    // --- UNICO EVENTO PARA ENVIAR LA VENTA ---
    formVenta.addEventListener('submit', function(e) {
        e.preventDefault();

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
           // ... (dentro de la parte .then(data => { ... }) de la primera solicitud a registrar_venta.php)

.then(data => {
    if (data.success) {
        alert(data.message); // Puedes mantener este alert si quieres
        carrito = [];
        renderizarCarrito();

        if (data.id_venta) {
            // Este es el bloque que modifica
            // Antes: fetch a obtener_detalles_venta.php y luego generateInvoicePdf
            // Ahora: Redirección
            window.location.href = `vista_previa_factura.php?id=${data.id_venta}`;
            // Puedes eliminar el alert de éxito si la redirección es inmediata
            // alert('Venta registrada. Redirigiendo a vista previa de factura.');

        } else {
            alert('Venta registrada, pero no se recibió ID de venta para generar factura.');
        }
    } else {
        alert('Error al registrar venta: ' + data.message);
        // Puedes agregar más manejo de errores o logging aquí
    }
})
.catch(error => {
    console.error('Error en el proceso de venta/facturación:', error);
    alert('Error en el proceso de venta/facturación: ' + error.message);
});
        }
    });

}); // Fin de DOMContentLoaded
</script>