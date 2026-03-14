<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Agregar Producto</title>
  <link rel="stylesheet" href="css/stylo.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
</head>
<body>
  <header class="encabezado">
    <?php include 'menu.php'; ?>
    <img src="img/logo.png" class="logo">
    <h1 class="titulo">GASTRONOMIA</h1>
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
  <div class="cuerpo">
    <div class="contenedor">
      <h1>Agregar Nuevo Producto</h1>
      
      <div id="mensaje"></div>
      
      <form id="formProducto">
        <div class="form-group">
          <label for="nombre">Nombre del producto:</label>
          <input type="text" id="nombre" name="nombre" required>
        </div>
        
        <div class="form-group">
          <label for="categoria">Categoría:</label>
          <select id="categoria" name="categoria" required>
            <option value="">Seleccionar...</option>
          </select>
        </div>
        
        <div class="form-group">
          <label for="unidad_medida">Unidad de medida:</label>
          <select id="unidad_medida" name="unidad_medida" required>
            <option value="">Seleccionar...</option>
            <option value="kg">Kilogramos (kg)</option>
            <option value="l">Litros (L)</option>
            <option value="unidades">Unidades</option>
          </select>
        </div>
        
        <div class="botones">
          <button type="submit" class="guardar">Guardar Producto</button>
          <button type="button" class="cancelar" onclick="location.href='stock-productos.php'">Cancelar</button>
        </div>
      </form>
      
      <hr>
      
      <div class="gestion-categorias">
        <h2>Gestionar Categorías</h2>
        
        <div class="nueva-categoria">
          <h3>Agregar Nueva Categoría</h3>
          <input type="text" id="nuevaCategoria" placeholder="Nombre de la categoría">
          <input type="number" id="umbralStock" placeholder="Mínimo stock" min="1">
          <button type="button" id="btnAgregarCategoria">Agregar</button>
        </div>
        
        <div class="eliminar-categoria">
          <h3>Eliminar Categoría</h3>
          <select id="categoriaAEliminar">
            <option value="">Seleccionar categoría...</option>
          </select>
          <button type="button" id="btnEliminarCategoria">Eliminar</button>
        </div>
      </div>
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