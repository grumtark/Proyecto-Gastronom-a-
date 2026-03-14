$(document).ready(function () {
  function scrollAlModal(modalId) {
    const modal = $('#' + modalId);
    $('html, body').animate({
      scrollTop: modal.offset().top - 20 // -20 deja un pequeño margen arriba
    }, 500); // 500 ms de animación
  }
  // Inicializar DataTable
  if ($('#herramientaTabla').length) {
    const tabla = $('#herramientaTabla').DataTable({
      language: { url: "https://cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json" },
      ajax: {
        url: 'funcionalidades/listarUtensilios.php',
        type: 'GET',
        dataType: 'json',
        dataSrc: '',
        error: function (xhr, error, thrown) {
          console.log('Error en DataTable:', xhr, error, thrown);
          alert('Error al cargar la tabla: ' + error);
        }
      },
      columns: [
        { data: 'nombre' },
        { data: 'tipo_nombre' },
        { data: 'cantidad' },
        {
          data: 'fecha_compra',
          render: function (fecha) {
            return new Date(fecha).toLocaleDateString('es-ES');
          }
        },
        {
          data: null,
          render: function (data, type, row) {
            return `
      <button class="modificar" data-id="${row.id}">Modificar</button>
      <button class="borrar" data-id="${row.id}">Eliminar</button>
    `;
          }
        }

      ]
    });

    // Actualizar tabla cada 30 segundos
    setInterval(() => tabla.ajax.reload(null, false), 30000);
  }

  // Cargar tipos de utensilios al cargar la página
  cargarTiposUtensilios();


  $('#guardarUtensilio').on('click', function () {
    var nombre = $('#nombre').val();
    var cantidad = $('#cantidad').val();
    var fechaCompra = $('#fechaCompra').val();
    var tipo = $('#tipo').val();

    if (nombre === '' || cantidad === '' || fechaCompra === '' || tipo === '') {
      alert('Por favor, complete todos los campos.');
      return;
    }

    $.ajax({
      url: 'funcionalidades/gestor.php',
      type: 'POST',
      data: {
        accion: 'agregarUtensilio',
        nombre: nombre,
        cantidad: cantidad,
        fechaCompra: fechaCompra,
        tipo: tipo
      },
      success: function (response) {
        $('#mensaje').html(response);
        $('#formUtensilio')[0].reset();
      },
      error: function () {
        alert('Error al enviar los datos.');
      }
    });
  });

  // "Agregar tipo"
  $('#agregarTipo').on('click', function () {
    var nuevoTipo = $('#nuevo_tipo').val();
    if (nuevoTipo === '') {
      alert('Por favor, ingrese un nombre para el nuevo tipo.');
      return;
    }
    $.ajax({
      url: 'funcionalidades/gestor.php',
      type: 'POST',
      data: {
        accion: 'agregarTipoUtensilio',
        nuevo_tipo: nuevoTipo
      },
      success: function (response) {
        $('#mensajeTipo').html(response);
        cargarTiposUtensilios();
        $('#nuevo_tipo').val('');
      },
      error: function () {
        alert('Error al agregar el tipo.');
      }
    });
  });

  // Función para cargar los tipos de utensilios
  function cargarTiposUtensilios() {
    $.ajax({
      url: 'funcionalidades/gestor.php',
      type: 'GET',
      data: { accion: 'obtenerTiposUtensilios' },
      success: function (response) {
        var tipos = JSON.parse(response);
        var select = $('#tipo');
        var modificarSelect = $('#modificarTipo');
        select.empty();
        modificarSelect.empty();
        select.append('<option value="">Seleccionar...</option>');
        modificarSelect.append('<option value="">Seleccionar...</option>');
        tipos.forEach(function (tipo) {
          select.append('<option value="' + tipo.id + '">' + tipo.nombre + '</option>');
          modificarSelect.append('<option value="' + tipo.id + '">' + tipo.nombre + '</option>');
        });
      },
      error: function () {
        alert('Error al cargar los tipos de utensilios.');
      }
    });
  }

  // Manejar clic en modificar
  $(document).on('click', '.modificar', function () {
    var id = $(this).data('id');
    console.log('Intentando modificar utensilio con ID:', id);
    $.ajax({
      url: 'funcionalidades/gestor.php',
      type: 'GET',
      data: { accion: 'obtenerUtensilio', id: id },
      success: function (response) {
        var utensilio = JSON.parse(response);
        if (utensilio.error) {
          alert(utensilio.error);
        } else {
          $('#modificarId').val(utensilio.id);
          $('#modificarNombre').val(utensilio.nombre);
          $('#modificarCantidad').val(utensilio.cantidad);
          $('#modificarFechaCompra').val(utensilio.fecha_compra);
          $('#modificarTipo').val(utensilio.tipo_id);
          $('#modalModificar').show();
          scrollAlModal('modalModificar');
        }
      },
      error: function () {
        alert('Error al obtener los datos del utensilio.');
      }
    });
  });

  //guardar modificación
  $('#guardarModificacion').on('click', function () {
    var id = $('#modificarId').val();
    var nombre = $('#modificarNombre').val();
    var cantidad = $('#modificarCantidad').val();
    var fechaCompra = $('#modificarFechaCompra').val();
    var tipo = $('#modificarTipo').val();

    if (nombre === '' || cantidad === '' || fechaCompra === '' || tipo === '') {
      alert('Por favor, complete todos los campos.');
      return;
    }

    $.ajax({
      url: 'funcionalidades/gestor.php',
      type: 'POST',
      data: {
        accion: 'modificarUtensilio',
        id: id,
        nombre: nombre,
        cantidad: cantidad,
        fechaCompra: fechaCompra,
        tipo: tipo
      },
      success: function (response) {
        alert(response);
        $('#modalModificar').hide();
        $('#herramientaTabla').DataTable().ajax.reload();
      },
      error: function () {
        alert('Error al modificar el utensilio.');
      }
    });
  });


  $('#cerrarModal').on('click', function () {
    $('#modalModificar').hide();
  });

  // borrar 
  $(document).on('click', '.borrar', function () {
    var id = $(this).data('id');
    if (confirm('¿Está seguro de que desea borrar este utensilio?')) {
      $.ajax({
        url: 'funcionalidades/gestor.php',
        type: 'POST',
        data: { accion: 'borrarUtensilio', id: id },
        success: function (response) {
          alert(response);
          $('#modalModificar').hide();
          $('#herramientaTabla').DataTable().ajax.reload();
        },
        error: function () {
          alert('Error al borrar el utensilio.');
        }
      });
    }
  });
});