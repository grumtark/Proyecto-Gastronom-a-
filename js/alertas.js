$(document).ready(function() {
    // Verificar alertas cada 10 segundos
    setInterval(verificarAlertas, 10000);
    verificarAlertas();
    
    // Manejar clic en la campana
    $('#campana-alertas').on('click', function(e) {
        e.stopPropagation(); // Evitar que el clic se propague
        $('#menu-alertas').toggleClass('show');
    });
    
    // Cerrar menú al hacer clic fuera
    $(document).on('click', function() {
        $('#menu-alertas').removeClass('show');
    });
});

function verificarAlertas() {
    $.ajax({
        url: 'funcionalidades/gestorStock.php?accion=obtenerAlertas',
        type: 'GET',
        dataType: 'json',
        success: function(data) {
            if (data.error) {
                console.error('Error del servidor:', data.error);
                // Mostrar mensaje genérico sin detalles técnicos al usuario
                mostrarMensajeError('No se pudieron cargar las alertas. Por favor intente más tarde.');
            } else {
                mostrarAlertas(data);
            }
        },
        error: function(xhr, status, error) {
            mostrarMensajeError('Error de conexión con el servidor');
        }
    });
}

function mostrarAlertas(alertas) {
    const $alertContainer = $('#alertas-container');
    $alertContainer.empty();
    
    if (!alertas || (typeof alertas !== 'object')) {
        mostrarMensajeError('Formato de alertas inválido');
        return;
    }
    
    let totalAlertas = 0;
    
    if (alertas.stock && alertas.stock.length > 0) {
        totalAlertas += alertas.stock.length;
        const $stockAlert = crearAlertaStock(alertas.stock);
        $alertContainer.append($stockAlert);
    }
    
    if (alertas.vencimiento && alertas.vencimiento.length > 0) {
        totalAlertas += alertas.vencimiento.length;
        const $vencAlert = crearAlertaVencimiento(alertas.vencimiento);
        $alertContainer.append($vencAlert);
    }
    
    const $contador = $('#contador-alertas');
    const $campana = $('#campana-alertas');
    
    if (totalAlertas > 0) {
        $contador.text(totalAlertas).show();
        $campana.addClass('has-alertas');
    } else {
        $contador.hide();
        $campana.removeClass('has-alertas');
        $alertContainer.append('<div class="alert alert-success">No hay alertas en este momento</div>');
    }
}

function mostrarMensajeError(mensaje) {
    const $alertContainer = $('#alertas-container');
    $alertContainer.empty();
    $alertContainer.append(`
        <div class="alert alert-danger">
            <h4>Error</h4>
            <p>${mensaje}</p>
            <small>Intente recargar la página</small>
        </div>
    `);
    $('#contador-alertas').text('!').show();
}

function crearAlertaStock(items) {
    const $alert = $(`
        <div class="alert alert-warning">
            <h4>Stock bajo <span class="badge">${items.length}</span></h4>
            <ul class="alert-items"></ul>
        </div>
    `);
    
    const $lista = $alert.find('.alert-items');
    
    items.forEach(item => {
        // Verificación segura de propiedades
        const producto = item.producto || 'Producto desconocido';
        const categoria = item.categoria || 'Sin categoría';
        const stock = item.stock_actual !== undefined ? item.stock_actual : 'N/A';
        const umbral = item.umbral_stock !== undefined ? item.umbral_stock : 'N/A';
        const unidad = item.unidad_medida || 'un';
        
        $lista.append(`
            <li>
                ${producto} (${categoria}) - 
                Stock: ${stock} ${unidad} (Mínimo: ${umbral})
            </li>
        `);
    });
    
    return $alert;
}

function crearAlertaVencimiento(items) {
    const $alert = $(`
        <div class="alert alert-danger">
            <h4>Productos por vencer <span class="badge">${items.length}</span></h4>
            <ul class="alert-items"></ul>
        </div>
    `);
    
    const $lista = $alert.find('.alert-items');
    
    items.forEach(item => {
        const dias = item.dias_restantes;
        $lista.append(`
            <li>
                ${item.producto} - Lote ${item.numero_lote} - 
                Vence: ${formatearFecha(item.fecha_vencimiento)} 
                (en ${dias} ${dias === 1 ? 'día' : 'días'})
            </li>
        `);
    });
    
    return $alert;
}

function formatearFecha(fechaString) {
    if (!fechaString) return 'Fecha no definida';
    const fecha = new Date(fechaString);
    return fecha.toLocaleDateString('es-ES', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });
}