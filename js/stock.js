$(document).ready(function() {
    function scrollAlModal(modalId) {
        const modal = $('#' + modalId);
        $('html, body').animate({
            scrollTop: modal.offset().top - 20 // -20 para dejar un margen arriba
        }, 500); // 500 ms de animación
    }   
    // Inicializar DataTable
    const tablaProductos = $('#productoTabla').DataTable({
        language: {
            url: "js/es-ES.json"
        },
        ajax: {
            url: 'funcionalidades/gestorStock.php?accion=listarProductos',
            type: 'GET',
            dataType: 'json',
            dataSrc: ''
        },
        columns: [
            { data: 'producto' },
            { data: 'categoria' },
            { 
                data: 'stock_total',
                render: function(data, type, row) {
                    return data + ' ' + (row.unidad_medida || 'kg');
                }
            },
            {
                data: null,
                render: function(data, type, row) {
                    return `
                        <button class="btn-agregar" data-id="${row.id}" 
                                data-nombre="${row.producto}" 
                                data-categoria="${row.categoria}">
                            Agregar
                        </button>
                        <button class="btn-quitar" data-id="${row.id}" 
                                data-nombre="${row.producto}" 
                                data-categoria="${row.categoria}">
                            Quitar
                        </button>
                        <button class="btn-eliminar" data-id="${row.id}" 
                                data-nombre="${row.producto}">
                            Eliminar
                        </button>
                    `;
                }
            }
        ]
    });

    // Cargar categorias
    cargarCategorias();
    // Cargar categorías para eliminar
    cargarCategoriasParaEliminar();

    // Agregar producto
    $('#formProducto').on('submit', function(e) {
        e.preventDefault();
        const nombre = $('#nombre').val().trim();
        const categoriaId = $('#categoria').val();
        const unidadMedida = $('#unidad_medida').val();
        
        if (nombre && categoriaId && unidadMedida) {
            agregarProducto(nombre, categoriaId, unidadMedida);
        } else {
            mostrarError('Por favor complete todos los campos');
        }
    });

    // Agregar categoria
    $('#btnAgregarCategoria').on('click', function() {
        const nombre = $('#nuevaCategoria').val().trim();
        const umbral = $('#umbralStock').val() || 5;
        
        if (nombre) {
            agregarCategoria(nombre, umbral);
        } else {
            mostrarError('Ingrese un nombre para la categoria');
        }
    });

    // Boton agregar en la tabla
    $(document).on('click', '.btn-agregar', function() {
        const productoId = $(this).data('id');
        const nombre = $(this).data('nombre');
        const categoria = $(this).data('categoria');
        
        $('#productoId').val(productoId);
        $('#agregarNombre').val(nombre);
        $('#agregarCategoria').val(categoria);
        
        $('#modalAgregar').show();
        scrollAlModal('modalAgregar');
    });

    // Boton quitar en la tabla
    $(document).on('click', '.btn-quitar', function() {
        const productoId = $(this).data('id');
        const nombre = $(this).data('nombre');
        const categoria = $(this).data('categoria');
        
        $('#quitarProductoId').val(productoId);
        $('#quitarNombre').val(nombre);
        $('#quitarCategoria').val(categoria);
        
        // Cargar lotes disponibles
        $.ajax({
            url: 'funcionalidades/gestorStock.php?accion=obtenerLotesProducto&producto_id=' + productoId,
            type: 'GET',
            dataType: 'json',
            success: function(lotes) {
                const $select = $('#loteSeleccionado');
                $select.empty().append('<option value="">Seleccionar lote...</option>');
                
                if (Array.isArray(lotes) && lotes.length > 0) {
                    lotes.forEach(function(lote) {
                        $select.append($('<option>', {
                            value: lote.id,
                            text: `Lote ${lote.numero_lote} (${lote.cantidad} ${lote.unidad_medida} - Vence: ${lote.fecha_vencimiento})`
                        }));
                    });
                    $('#modalQuitar').show();
                    scrollAlModal('modalQuitar');
                } else {
                    mostrarError('No hay lotes disponibles para este producto');
                }
            },
            error: function(xhr, status, error) {
                mostrarError('Error al cargar los lotes');
                console.error('Error:', error);
            }
        });
    });

    // Guardar Stock
    $('#formAgregarStock').on('submit', function(e) {
        e.preventDefault();
        const productoId = $('#productoId').val();
        const cantidad = $('#agregarCantidad').val();
        const fechaVencimiento = $('#agregarFechaVencimiento').val();
        const numeroLote = $('#agregarLote').val();
        const unidadMedida = $('#agregarUnidadMedida').val();
        
        if (productoId && cantidad && fechaVencimiento && numeroLote && unidadMedida) {
            agregarStock(productoId, cantidad, fechaVencimiento, numeroLote, unidadMedida);
        } else {
            mostrarError('Por favor complete todos los campos');
        }
    });

    // Quitar stock
    $('#formQuitarStock').on('submit', function(e) {
        e.preventDefault();
        const loteId = $('#loteSeleccionado').val();
        const cantidad = $('#quitarCantidad').val();
        
        if (loteId && cantidad) {
            quitarStock(loteId, cantidad);
        } else {
            mostrarError('Por favor complete todos los campos');
        }
    });

    // Cerrar modales
    $('.close, .modal').on('click', function(e) {
        if (e.target === this || $(e.target).hasClass('close')) {
            $(this).closest('.modal').hide();
        }
    });

    $('.modal-content').on('click', function(e) {
        e.stopPropagation();
    });
});

function cargarCategorias() {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=obtenerCategorias',
        type: 'GET',
        dataType: 'json',
        success: function(categorias) {
            if (categorias && !categorias.error) {  // Verifica que no haya error
                const $selectCategoria = $('#categoria');
                $selectCategoria.empty().append('<option value="">Seleccionar...</option>');
                
                categorias.forEach(function(categoria) {
                    $selectCategoria.append($('<option>', {
                        value: categoria.id,
                        text: categoria.nombre
                    }));
                });
            } else {
                console.error('Error al cargar categorías:', categorias.error);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar categorias:', error);
        }
    });
}

function cargarCategoriasParaEliminar() {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=obtenerCategorias',
        type: 'GET',
        dataType: 'json',
        success: function(categorias) {
            const $select = $('#categoriaAEliminar');
            $select.empty().append('<option value="">Seleccionar categoría...</option>');
            
            if (Array.isArray(categorias)) {
                categorias.forEach(function(categoria) {
                    $select.append($('<option>', {
                        value: categoria.id,
                        text: categoria.nombre
                    }));
                });
            }
        }
    });
}

function agregarProducto(nombre, categoriaId, unidadMedida) {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=agregarProducto',
        type: 'POST',
        data: { 
            nombre: nombre,
            categoria_id: categoriaId,
            unidad_medida: unidadMedida
        },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                mostrarError(response.error);
            } else {
                mostrarExito('Producto agregado correctamente');
                $('#formProducto')[0].reset();
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error al agregar producto');
            console.error('Error:', error);
        }
    });
}

function agregarCategoria(nombre, umbral) {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=agregarCategoria',
        type: 'POST',
        data: { 
            nombre: nombre,
            umbral_stock: umbral || 5
        },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                mostrarError(response.error);
            } else {
                mostrarExito('Categoría agregada correctamente');
                $('#nuevaCategoria').val('');
                $('#umbralStock').val('5');
                cargarCategorias();
                cargarCategoriasParaEliminar();
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error al agregar categoría');
            console.error('Error:', error);
        }
    });
}

function agregarStock(productoId, cantidad, fechaVencimiento, numeroLote, unidadMedida) {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=agregarLote',
        type: 'POST',
        data: { 
            producto_id: productoId,
            cantidad: cantidad,
            fecha_vencimiento: fechaVencimiento,
            numero_lote: numeroLote,
            unidad_medida: unidadMedida
        },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                mostrarError(response.error);
            } else {
                mostrarExito('Stock agregado correctamente');
                $('#modalAgregar').hide();
                $('#formAgregarStock')[0].reset();
                $('#productoTabla').DataTable().ajax.reload();
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error al agregar stock');
            console.error('Error:', error);
        }
    });
}

function quitarStock(loteId, cantidad) {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=quitarLote',
        type: 'POST',
        data: { 
            lote_id: loteId,
            cantidad: cantidad
        },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                mostrarError(response.error);
            } else {
                mostrarExito('Stock actualizado correctamente');
                $('#modalQuitar').hide();
                $('#formQuitarStock')[0].reset();
                $('#productoTabla').DataTable().ajax.reload();
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error al quitar stock');
            console.error('Error:', error);
        }
    });
}

function mostrarError(mensaje) {
    $('#mensaje').html(`<div class="error-message">${mensaje}</div>`);
}

function mostrarExito(mensaje) {
    $('#mensaje').html(`<div class="success-message">${mensaje}</div>`);
}

$('#btnEliminarCategoria').on('click', function() {
    const categoriaId = $('#categoriaAEliminar').val();
    if (!categoriaId) {
        mostrarError('Seleccione una categoría para eliminar');
        return;
    }
    
    if (confirm('¿Está seguro que desea eliminar esta categoría? Esto eliminará también todos los productos asociados.')) {
        eliminarCategoria(categoriaId);
    }
});

$(document).on('click', '.btn-eliminar', function() {
    const productoId = $(this).data('id');
    const nombre = $(this).data('nombre');
    
    if (confirm(`¿Está seguro que desea eliminar el producto "${nombre}"?`)) {
        eliminarProducto(productoId);
    }
});

function eliminarCategoria(categoriaId) {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=eliminarCategoria',
        type: 'POST',
        data: { id: categoriaId },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                mostrarError(response.error);
            } else {
                mostrarExito('Categoría eliminada correctamente');
                cargarCategorias();
                cargarCategoriasParaEliminar();
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error al eliminar categoría');
            console.error('Error:', error);
        }
    });
}

function eliminarProducto(productoId) {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=eliminarProducto',
        type: 'POST',
        data: { id: productoId },
        dataType: 'json',
        success: function(response) {
            if (response.error) {
                mostrarError(response.error);
            } else {
                mostrarExito('Producto eliminado correctamente');
                $('#productoTabla').DataTable().ajax.reload();
            }
        },
        error: function(xhr, status, error) {
            mostrarError('Error al eliminar producto');
            console.error('Error:', error);
        }
    });
}