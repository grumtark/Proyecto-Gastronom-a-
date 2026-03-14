<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stock de Utensilios y Maquinarias</title>
  <link rel="stylesheet" href="css/stylo.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
</head>
<body>
  <header class="encabezado">
    <?php include 'menu.php'; ?>
    <img src="img/logo.png" class="logo">
    <h1 class="titulo">STOCK DE UTENSILIOS Y MAQUINARIAS</h1>
      <div class="alertas-icono-container">
    <div class="campana-alertas" id="campana-alertas">
        <img src="img/campana.png" class="icono-campana" alt="Notificaciones">
        <span class="contador-alertas" id="contador-alertas">0</span>
    </div>
    <div class="menu-alertas" id="menu-alertas">
      <div id="alertas-container"></div>
      </div>
    </div>
    
  </header>
  <button onclick="location.href='formularioUtensilioMaquinaria.php'" class="botonStock">Agregar Utensilio/Maquinaria</button>
  <div class="tablasProductos">
    <table id="herramientaTabla" class="display">
      <thead>
        <tr>
          <th>Utensilio/Maquinaria</th>
          <th>Tipo</th>
          <th>Cantidad</th>
          <th>Fecha de Compra</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>
  <!-- modificar utensilio -->
  <div id="modalModificar" style="display:none;">
    <div>
      <h2>Modificar Utensilio</h2>
      <form id="formModificarUtensilio">
        <input type="hidden" id="modificarId" name="id">
        <label for="modificarNombre">Nombre:</label>
        <input type="text" id="modificarNombre" name="nombre" required>
        <label for="modificarCantidad">Cantidad:</label>
        <input type="number" id="modificarCantidad" name="cantidad" required>
        <label for="modificarFechaCompra">Fecha de Compra:</label>
        <input type="date" id="modificarFechaCompra" name="fechaCompra" required>
        <label for="modificarTipo">Tipo:</label>
        <select id="modificarTipo" name="tipo" required>
          <option value="">Seleccionar...</option>
        </select>
        <button type="button" id="guardarModificacion">Guardar Cambios</button>
        <button type="button" id="cerrarModal">Cerrar</button>
      </form>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/script.js"></script>
  <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
  <script src="js/utensilios.js"></script>
  <script src="js/alertas.js"></script>
</body>
</html>