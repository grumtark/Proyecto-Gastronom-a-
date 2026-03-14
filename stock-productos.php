<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Stock de Productos</title>
  <link rel="stylesheet" href="css/stylo.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
</head>
<body>
  <header class="encabezado">
    <?php include 'menu.php'; ?>
    <img src="img/logo.png" class="logo">
    <h1 class="titulo">STOCK DE PRODUCTOS</h1>
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
  <button onclick="location.href='formularioStock.php'" class="botonStock">Agregar Categoría/Producto</button>
  
  <div class="tablasProductos">
    <table id="productoTabla" class="display">
      <thead>
        <tr>
          <th>Producto</th>
          <th>Categoría</th>
          <th>Stock Total</th>
          <th>Acciones</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>
  </div>

  <!-- Modal para agregar stock -->
  <div id="modalAgregar" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Agregar Stock</h2>
      <form id="formAgregarStock">
        <input type="hidden" id="productoId">
        
        <div class="form-group">
          <label for="agregarCategoria">Categoría:</label>
          <input type="text" id="agregarCategoria" readonly>
        </div>
        
        <div class="form-group">
          <label for="agregarNombre">Producto:</label>
          <input type="text" id="agregarNombre" readonly>
        </div>
        
        <div class="form-group">
          <label for="agregarCantidad">Cantidad:</label>
          <input type="number" id="agregarCantidad" min="1" required>
        </div>
        
        <div class="form-group">
          <label for="agregarUnidadMedida">Unidad de medida:</label>
          <select id="agregarUnidadMedida" required>
            <option value="">Seleccionar...</option>
            <option value="kg">Kilogramos (kg)</option>
            <option value="l">Litros (l)</option>
            <option value="unidades">Unidades</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="agregarFechaVencimiento">Fecha de vencimiento:</label>
          <input type="date" id="agregarFechaVencimiento" required>
        </div>
        
        <div class="form-group">
          <label for="agregarLote">Número de lote:</label>
          <input type="text" id="agregarLote" required>
        </div>
        
        <div class="botones">
          <button type="submit" class="guardar">Guardar</button>
          <button type="button" class="cancelar" onclick="$('#modalAgregar').hide()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

  <!-- Modal para quitar stock -->
  <div id="modalQuitar" class="modal" style="display:none;">
    <div class="modal-content">
      <span class="close">&times;</span>
      <h2>Quitar Stock</h2>
      <form id="formQuitarStock">
        <input type="hidden" id="quitarProductoId">
        
        <div class="form-group">
          <label for="quitarCategoria">Categoría:</label>
          <input type="text" id="quitarCategoria" readonly>
        </div>
        
        <div class="form-group">
          <label for="quitarNombre">Producto:</label>
          <input type="text" id="quitarNombre" readonly>
        </div>
        
        <div class="form-group">
          <label for="loteSeleccionado">Lote:</label>
          <select id="loteSeleccionado" required>
            <option value="">Seleccionar lote...</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="quitarCantidad">Cantidad a quitar:</label>
          <input type="number" id="quitarCantidad" min="1" required>
        </div>
        
        <div class="botones">
          <button type="submit" class="guardar">Confirmar</button>
          <button type="button" class="cancelar" onclick="$('#modalQuitar').hide()">Cancelar</button>
        </div>
      </form>
    </div>
  </div>

<!-- Carga jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Carga DataTables -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.min.css">

<!-- Carga scripts -->
<script src="js/stock.js"></script>
<script src="js/script.js"></script>
<script src="js/alertas.js"></script>
</body>
</html>