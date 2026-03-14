$(document).ready(function() {
  // Menú toggle para dispositivos móviles
  $(".menu-toggle").on('click', function() {
    $(this).toggleClass("on");
    $('.menu-section').toggleClass("on");
    $("nav ul").toggleClass('hidden');
  });
});

// Función para mostrar notificaciones
function mostrarNotificacion(mensaje, tipo = 'success') {
  const notificacion = $(`
    <div class="notificacion ${tipo}">
      ${mensaje}
      <span class="cerrar-notificacion">&times;</span>
    </div>
  `);
  
  $('body').append(notificacion);
  
  // Auto-eliminar después de 5 segundos
  setTimeout(() => {
    notificacion.fadeOut(() => notificacion.remove());
  }, 5000);
  
  // Cerrar manualmente
  notificacion.find('.cerrar-notificacion').on('click', function() {
    notificacion.remove();
  });
}

// Manejo de errores AJAX global
$(document).ajaxError(function(event, jqxhr, settings, thrownError) {
  mostrarNotificacion('Error en la solicitud: ' + thrownError, 'error');
});

// Función para confirmar acciones importantes
function confirmarAccion(mensaje, callback) {
  if (confirm(mensaje)) {
    if (typeof callback === 'function') {
      callback();
    }
    return true;
  }
  return false;
}

// Función para formatear fechas
function formatearFecha(fecha) {
  if (!fecha) return '';
  const date = new Date(fecha);
  return date.toLocaleDateString('es-ES', {
    day: '2-digit',
    month: '2-digit',
    year: 'numeric'
  });
}

// Función para validar campos requeridos
function validarCamposRequeridos(campos) {
  let valido = true;
  campos.forEach(campo => {
    const valor = $(campo).val().trim();
    if (!valor) {
      $(campo).addClass('error');
      valido = false;
    } else {
      $(campo).removeClass('error');
    }
  });
  return valido;
}

// Remover clase error al enfocar un campo
$(document).on('focus', 'input, select, textarea', function() {
  $(this).removeClass('error');
});

// Función para cargar select con datos AJAX
function cargarSelect(url, selectId, valorDefault = '') {
  $.get(url, function(data) {
    const select = $(`#${selectId}`);
    select.empty().append(`<option value="">${valorDefault}</option>`);
    
    if (Array.isArray(data)) {
      data.forEach(item => {
        select.append($('<option>', {
          value: item.id,
          text: item.nombre || item.texto || item.descripcion
        }));
      });
    }
  }).fail(function() {
    mostrarNotificacion('Error al cargar opciones', 'error');
  });
}

// Función para resetear formularios
function resetearFormulario(formId) {
  $(`#${formId}`)[0].reset();
  $(`#${formId} input, #${formId} select`).removeClass('error');
}

// Función para manejar el evento de enter en inputs
$(document).on('keypress', 'input', function(e) {
  if (e.which === 13) { // Tecla Enter
    e.preventDefault();
    $(this).blur();
    const form = $(this).closest('form');
    if (form.length) {
      form.submit();
    }
  }
});