<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Ingreso de Utensilio/Maquinaria</title>
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
    <h1>Gestión de Utensilios y Maquinarias</h1>
    <form id="formUtensilio">
      <label for="nombre">Nombre del Utensilio/Maquinaria:</label>
      <input type="text" id="nombre" name="nombre" required>

      <label for="cantidad">Cantidad:</label>
      <input type="number" id="cantidad" name="cantidad" required>

      <label for="fechaCompra">Fecha de Compra:</label>
      <input type="date" id="fechaCompra" name="fechaCompra" required>

      <label for="tipo">Tipo:</label>
      <select id="tipo" name="tipo" required>
        <option value="">Seleccionar...</option>
      </select>

      <div class="botones">
        <button type="button" id="guardarUtensilio" class="guardar">Guardar</button>
      </div>
    </form>
    <div id="mensaje"></div>
    
    <hr>
    <h2>Agregar nuevo tipo de Utensilio/Maquinaria</h2>
    <form id="formTipo">
      <label for="nuevo_tipo">Nombre del nuevo tipo:</label>
      <input type="text" id="nuevo_tipo" name="nuevo_tipo" required>
      <button type="button" id="agregarTipo">Agregar tipo</button>
    </form>
    <p id="mensajeTipo"></p>
  </div>
</div>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script src="js/script.js"></script> 
<script src="js/utensilios.js"></script> 
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="js/alertas.js"></script>
</body>
</html>