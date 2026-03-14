$.fn.dataTable.ext.errMode = 'none';

$(document).ready(function() {
    // Inicializar DataTable para productos
    $('#tablaProductosInicio').DataTable({
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
            { 
                data: 'producto',
                className: 'nombre-producto' 
            },
            { 
                data: 'categoria',
                className: 'categoria-producto' 
            },
            { 
                data: 'stock_total',
                className: 'stock-producto',
                render: function(data, type, row) {
                    // Mostrar stock con unidad de medida
                    return data + ' ' + (row.unidad_medida || 'un');
                }
            }
        ],
        order: [[2, 'desc']], // Ordenar por stock total (3ra columna)
        pageLength: 5, // Mostrar solo 5 registros
        dom: '<"top"f>rt<"bottom"lip><"clear">', // Configuración compacta
        responsive: true,
        initComplete: function() {
            console.log('Tabla de productos cargada correctamente');
        },
        error: function(xhr, error, thrown) {
            console.error('Error al cargar datos de productos:', error);
        }
    });

    // Inicializar DataTable para utensilios
    $('#herramientaTabla').DataTable({
        language: {
            url: "js/es-ES.json"
        },
        ajax: {
            url: 'funcionalidades/listarUtensilios.php',
            type: 'GET',
            dataType: 'json',
            dataSrc: ''
        },
        columns: [
            { data: 'nombre' },
            { data: 'cantidad' },
            { 
                data: 'fecha_compra',
                render: function(data) {
                    return new Date(data).toLocaleDateString('es-ES');
                }
            }
        ],
        order: [[1, 'desc']]
    });
});