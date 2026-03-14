<?php
require_once 'funcionalidades/gestorRecetas.php';
$gestorRecetas = new GestorRecetas();

// Obtener productos desde la base de datos
$productos = $gestorRecetas->obtenerProductosDisponibles();

// Procesar acciones
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_receta'])) {
        $datos = [
            'titulo' => $_POST['titulo'],
            'descripcion' => $_POST['descripcion'],
            'pasos' => $_POST['pasos'],
            'ingredientes' => json_decode($_POST['ingredientes'], true)
        ];
        
        try {
            $gestorRecetas->guardarReceta($datos);
            header("Location: recetas.php?success=1");
            exit();
        } catch (Exception $e) {
            $error = "Error al guardar la receta: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['eliminar_receta'])) {
        $gestorRecetas->eliminarReceta($_POST['id']);
        header("Location: recetas.php");
        exit();
    }
}

$recetas = $gestorRecetas->obtenerRecetas();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Gastronomía - Recetas</title>
  <link rel="stylesheet" href="css/stylo.css">
  <link rel="stylesheet" href="css/recetas.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
</head>
<body>
  <header class="encabezado">
    <?php include 'menu.php'; ?>
    <img src="img/logo.png" class="logo">
    <h1 class="titulo">RECETAS</h1>
    <div class="alertas-icono-container">
      <div class="campana-alertas" id="campana-alertas">
          <img src="img/campana.png" class="icono-campana" alt="Notificaciones">
          <span class="contador-alertas" id="contador-alertas">0</span>
      </div>
      <div class="menu-alertas" id="menu-alertas">
        <div id="alertas-container"></div>
      </div>
    
  </header>

  <main>
    <button onclick="mostrarFormulario()">Crear receta</button>

    <div id="formularioReceta">
      <form id="formReceta" method="POST" action="recetas.php">
        <input type="hidden" name="guardar_receta" value="1">
        <input type="hidden" name="ingredientes" id="inputIngredientes">
        
        <div class="form-group">
          <label for="titulo">Título de la receta:</label>
          <input type="text" id="titulo" name="titulo" required>
        </div>

        <div class="form-group">
          <label for="descripcion">Descripción:</label>
          <textarea id="descripcion" name="descripcion" rows="5" required></textarea>
        </div>

        <div class="form-group">
          <label for="pasos">Pasos de la receta:</label>
          <textarea id="pasos" name="pasos" rows="5" required></textarea>
        </div>

        
        <button type="button" onclick="abrirModalProductos()">Agregar producto</button>

        <table id="tablaIngredientes">
          <thead>
            <tr>
              <th>Ingrediente</th>
              <th>Cantidad</th>
              <th>Unidad</th>
              <th>Eliminar</th>
            </tr>
          </thead>
          <tbody></tbody>
        </table>

        <button type="submit">Guardar receta</button>
      </form>
    </div>

    <div id="recetasGuardadasContainer">
      <?php foreach ($recetas as $receta): 
        $ingredientes = $gestorRecetas->obtenerIngredientesReceta($receta['id']);
        $ingredientesHTML = '';
        foreach ($ingredientes as $ingrediente) {
            // Usa 'nombre_producto' en lugar de 'ingrediente'
            $nombreIngrediente = isset($ingrediente['nombre_producto']) ? 
                                $ingrediente['nombre_producto'] : 
                                (isset($ingrediente['ingrediente']) ? $ingrediente['ingrediente'] : 'Sin nombre');
            
            $ingredientesHTML .= '<li>' . htmlspecialchars($nombreIngrediente) . ': ' . 
                              $ingrediente['cantidad'] . ' ' . 
                              htmlspecialchars($ingrediente['unidad']) . '</li>';
        }
      ?>
      <div class="receta-card">
        <h3><strong>Título:</strong> <?= htmlspecialchars($receta['titulo']) ?></h3>
        <p><strong>Descripción:</strong> <?= htmlspecialchars($receta['descripcion']) ?></p>
        <div><strong>Ingredientes:</strong><ul><?= $ingredientesHTML ?></ul></div>
        
        <button onclick='verReceta(
            <?= json_encode($receta['titulo']) ?>,
            <?= json_encode($receta['descripcion']) ?>, 
            <?= json_encode($receta['pasos']) ?>, 
            <?= json_encode($ingredientesHTML) ?>
        )'>Ver</button>
        
        <form method="POST" action="recetas.php" style="display: inline;">
          <input type="hidden" name="eliminar_receta" value="1">
          <input type="hidden" name="id" value="<?= $receta['id'] ?>">
          <button type="submit">Eliminar</button>
        </form>
      </div>
      <?php endforeach; ?>
    </div>
  </main>

  <!-- Modal ver receta -->
  <div id="modalVer" class="modal">
    <div class="modal-content">
      <span class="close" onclick="cerrarModal()">&times;</span>
      <h2>Vista de Receta</h2>
      <p><strong>Título:</strong> <span id="vTitulo"></span></p>
      <p><strong>Descripción:</strong></p>
      <p id="vDescripcion"></p>
      <h4>Ingredientes:</h4>
      <ul id="vIngredientes"></ul>
      <h4>Pasos de la receta:</h4>
      <p id="vPasos"></p>
    </div>
  </div>

  <!-- Modal para seleccionar productos -->
  <div id="modalProductos" class="modal">
      <div class="modal-content" style="max-width: 800px;">
          <span class="close" onclick="cerrarModalProductos()">&times;</span>
          <h3>Seleccionar productos</h3>
          
          <!-- Buscador -->
          <div class="form-group">
              <input type="text" id="buscadorProductos" placeholder="Buscar producto..." 
                    style="width: 100%; padding: 8px; margin-bottom: 15px;" 
                    onkeyup="filtrarProductos()">
          </div>
          
          <form id="formSeleccionProductos">
              <table id="tablaProductosModal">
                  <thead>
                      <tr>
                          <th>Sel.</th>
                          <th>Producto</th>
                          <th>Cantidad</th>
                          <th>Unidad</th>
                      </tr>
                  </thead>
                  <tbody>
                      <?php foreach($productos as $p): ?>
                      <tr class="fila-producto">
                          <td>
                              <input type="checkbox" 
                                    data-id="<?= $p['id'] ?>" 
                                    data-nombre="<?= htmlspecialchars($p['nombre']) ?>" 
                                    data-unidad="<?= $p['unidad'] ?>"
                                    class="check-producto">
                          </td>
                          <td><?= htmlspecialchars($p['nombre']) ?></td>
                          <td>
                              <input type="number" min="0" step="0.01" 
                                    style="width:70px;" 
                                    class="cantidad"
                                    oninput="marcarSiTieneCantidad(this)">
                          </td>
                          <td><?= $p['unidad'] ?></td>
                      </tr>
                      <?php endforeach; ?>
                  </tbody>
              </table>
              <div style="margin-top: 15px;">
                  <button type="button" onclick="aceptarProductos()">Aceptar</button>
                  <button type="button" onclick="cerrarModalProductos()" style="margin-left: 10px;">Cancelar</button>
              </div>
          </form>
      </div>
  </div>

  <script src="js/recetas.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="js/script.js"></script>
  <script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
  <script src="js/inicio.js"></script>
  <script src="js/utensilios.js"></script>
  <script src="js/alertas.js"></script>
</body>
</html>
