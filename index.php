<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Stock Gastronomia</title>
    <link rel="stylesheet" href="css/stylo.css">
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
</head>
<body>
<!-- Menu -->
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
<!-- Tablas -->
<div class="contenedor-principal">
    <h2>Resumen de Stock</h2>
    
    <div class="tablas-container">
        <!-- Tabla de Productos -->
        <div class="tabla-seccion">
            <h3>Productos</h3>
            <div class="tablasProductos">
                <table id="tablaProductosInicio" class="display">
                    <thead>
                        <tr>
                            <th>Producto</th>
                            <th>Categoría</th>
                            <th>Stock Total</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
        
        <!-- Tabla de Utensilios/Maquinarias -->
        <div class="tabla-seccion">
            <h3>Utensilios y Maquinarias</h3>
            <div class="tablasProductos">
                <table id="herramientaTabla" class="display">
                    <thead>
                        <tr>
                            <th>Utensilio/Maquinaria</th>
                            <th>Cantidad</th>
                            <th>Fecha de Compra</th>
                        </tr>
                    </thead>
                    <tbody>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="js/script.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="js/inicio.js"></script>
<script src="js/utensilios.js"></script>
<script src="js/alertas.js"></script>
</body>
</html>