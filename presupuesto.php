<?php
require_once 'funcionalidades/gestorPresupuestos.php';
require_once 'funcionalidades/gestorRecetas.php';

$gestorPresupuestos = new GestorPresupuestos();
$gestorRecetas = new GestorRecetas();

// Obtener todas las recetas disponibles
$recetas = $gestorPresupuestos->obtenerTodasRecetas();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['guardar_presupuesto'])) {
        $datos = [
            'nombre' => $_POST['nombre'],
            'apellido' => $_POST['apellido'],
            'telefono' => $_POST['telefono'],
            'email' => $_POST['email'],
            'observaciones' => $_POST['observaciones'],
            'total' => $_POST['total'],
            // CORRECCIÓN: Cambiar 'productos' por 'recetas'
            'recetas' => json_decode($_POST['recetas'], true)
        ];
        
        try {
            $gestorPresupuestos->guardarPresupuesto($datos);
            header("Location: presupuesto.php?success=1");
            exit();
        } catch (Exception $e) {
            $error = "Error al guardar el presupuesto: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['cambiar_estado'])) {
        $gestorPresupuestos->cambiarEstadoPresupuesto($_POST['id'], $_POST['estado']);
        header("Location: presupuesto.php");
        exit();
    }
    
    // CORRECCIÓN: Agregar el caso para realizar_presupuesto
    if (isset($_POST['realizar_presupuesto'])) {
        try {
            $gestorPresupuestos->descontarStockPresupuesto($_POST['id']);
            header("Location: presupuesto.php?success=2");
            exit();
        } catch (Exception $e) {
            $error = "Error al realizar el presupuesto: " . $e->getMessage();
        }
    }
    
    if (isset($_POST['eliminar_presupuesto'])) {
        $gestorPresupuestos->eliminarPresupuesto($_POST['id']);
        header("Location: presupuesto.php");
        exit();
    }
}

$presupuestos = $gestorPresupuestos->obtenerPresupuestos();
?>

<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <title>Gastronomía - Presupuesto</title>
  <link rel="stylesheet" href="css/stylo.css">
  <link rel="stylesheet" href="css/presupuesto.css">
  <link href="https://fonts.googleapis.com/css2?family=Open+Sans&display=swap" rel="stylesheet">
  <link rel="stylesheet" type="text/css" href="https://cdn.datatables.net/1.13.6/css/jquery.dataTables.css">
</head>
<body>
<header class="encabezado">
  <?php include 'menu.php'; ?>
  <img src="img/logo.png" class="logo">
  <h1 class="titulo">PRESUPUESTOS</h1>
  <div class="alertas-icono-container">
    <div class="campana-alertas" id="campana-alertas">
      <img src="img/campana.png" class="icono-campana" alt="Notificaciones">
      <span class="contador-alertas" id="contador-alertas">0</span>
    </div>
    <div class="menu-alertas" id="menu-alertas"><div id="alertas-container"></div></div>
  </div>
  
</header>

<main>
  <button type="button" onclick="mostrarFormulario()">Agregar presupuesto</button>

  <div id="formularioPresupuesto">
    <form id="formPresupuesto" method="POST" action="presupuesto.php">
      <input type="hidden" name="guardar_presupuesto" value="1">
      <input type="hidden" name="recetas" id="inputRecetas">
      <input type="hidden" name="total" id="inputTotal">

      <label>Nombre: <input id="nombre" name="nombre" type="text" required></label>
      <label>Apellido: <input id="apellido" name="apellido" type="text" required></label>
      <label>Teléfono: <input id="telefono" name="telefono" type="text" required></label>
      <label>Email: <input id="email" name="email" type="email" required></label>
      <label>Observaciones: <textarea id="observaciones" name="observaciones" rows="4"></textarea></label>

      <button type="button" onclick="abrirModalRecetas()">Agregar receta</button>

      <table id="tablaRecetas">
        <thead>
          <tr><th>Receta</th><th>Cantidad</th><th>Precio Unitario</th><th>Total</th><th>Eliminar</th></tr>
        </thead>
        <tbody></tbody>
        <tfoot>
          <tr>
            <td colspan="3">Total Presupuesto:</td>
            <td colspan="2" id="totalPresupuesto">$0.00</td>
          </tr>
        </tfoot>
      </table>

      <button type="submit">Guardar presupuesto</button>
    </form>
  </div>

  <!-- Modal para seleccionar recetas -->
  <div id="modalRecetas" class="modal">
      <div class="modal-content">
          <span class="close" onclick="cerrarModalRecetas()">&times;</span>
          <h2 style="color: #0300af; margin-bottom: 20px;">Seleccionar Recetas</h2>
          
          <!-- Buscador -->
          <div style="margin-bottom: 20px;">
              <input type="text" id="buscadorRecetas" placeholder="Buscar receta..." 
                    style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px;">
          </div>
          
          <!-- Lista de recetas -->
          <div id="listaRecetas" style="max-height: 400px; overflow-y: auto; padding: 10px; border: 1px solid #eee; border-radius: 6px;">
              <?php foreach ($recetas as $receta): ?>
              <div class="receta-item" data-id="<?= $receta['id'] ?>" data-nombre="<?= htmlspecialchars($receta['titulo']) ?>">
                  <div style="display: flex; justify-content: space-between; align-items: center;">
                      <div style="flex: 1;">
                          <strong style="color: #0300af; font-size: 16px;"><?= htmlspecialchars($receta['titulo']) ?></strong>
                          <p style="margin: 8px 0 0 0; color: #666; line-height: 1.4;">
                              <?= htmlspecialchars(substr($receta['descripcion'], 0, 120)) ?><?= strlen($receta['descripcion']) > 120 ? '...' : '' ?>
                          </p>
                      </div>
                      <button type="button" onclick="seleccionarReceta(<?= $receta['id'] ?>, '<?= htmlspecialchars(addslashes($receta['titulo'])) ?>')" 
                              class="btn-seleccionar">
                          Seleccionar
                      </button>
                  </div>
              </div>
              <?php endforeach; ?>
          </div>
      </div>
  </div>

  <!-- Modal para ingresar cantidad y precio -->
  <div id="modalCantidadPrecio" class="modal">
      <div class="modal-content" style="max-width: 450px;">
          <span class="close" onclick="cerrarModalCantidadPrecio()">&times;</span>
          <h2 style="color: #c0392b; margin-bottom: 20px;">Configurar Receta</h2>
          
          <input type="hidden" id="recetaSeleccionadaId">
          <input type="hidden" id="recetaSeleccionadaNombre">
          
          <div style="margin-bottom: 20px; padding: 15px; background-color: #f8f9fa; border-radius: 6px;">
              <p style="margin: 0; font-size: 16px;">
                  <strong>Receta:</strong> 
                  <span id="nombreRecetaSeleccionada" style="color: #27ae60; font-weight: bold;"></span>
              </p>
          </div>
          
          <div style="margin-bottom: 20px;">
              <label for="cantidadReceta" style="display: block; margin-bottom: 8px; font-weight: bold;">Cantidad:</label>
              <input type="number" id="cantidadReceta" min="1" value="1" 
                    style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px;">
          </div>
          
          <div style="margin-bottom: 25px;">
              <label for="precioReceta" style="display: block; margin-bottom: 8px; font-weight: bold;">Precio Unitario:</label>
              <input type="number" id="precioReceta" min="0" step="0.01" value="" 
                    style="width: 100%; padding: 12px; border: 2px solid #ddd; border-radius: 6px; font-size: 16px;" 
                    placeholder="0.00" required>
          </div>
          
          <div style="display: flex; gap: 15px;">
              <button type="button" onclick="agregarRecetaAlPresupuesto()" class="btn-agregar">
                  Agregar
              </button>
              <button type="button" onclick="cerrarModalCantidadPrecio()" class="btn-cancelar">
                  Cancelar
              </button>
          </div>
      </div>
  </div>

  <div id="listaPresupuestos">
    <?php foreach ($presupuestos as $presupuesto):
      $recetasPresupuesto = $gestorPresupuestos->obtenerRecetasPresupuesto($presupuesto['id']);
      $estadoColor = '';
      switch ($presupuesto['estado']) {
          case 'Pendiente': $estadoColor = 'background-color: red; color: white;'; break;
          case 'Confirmado': $estadoColor = 'background-color: yellow; color: black;'; break;
          case 'Realizado': $estadoColor = 'background-color: green; color: white;'; break;
          case 'Rechazado': $estadoColor = 'background-color: black; color: white;'; break;
      }
    ?>
    <div class="presupuesto">
      <h3>Presupuesto #<?= $presupuesto['id'] ?></h3>
      <p><strong>Nombre:</strong> <?= htmlspecialchars($presupuesto['nombre']) ?></p>
      <p><strong>Apellido:</strong> <?= htmlspecialchars($presupuesto['apellido']) ?></p>
      <p><strong>Teléfono:</strong> <?= htmlspecialchars($presupuesto['telefono']) ?></p>
      <p><strong>Email:</strong> <?= htmlspecialchars($presupuesto['email']) ?></p>
      <p><strong>Observaciones:</strong> <?= htmlspecialchars($presupuesto['observaciones']) ?></p>
      <p><strong>Total:</strong> $<?= number_format($presupuesto['total'], 2) ?></p>
      <p><strong>Fecha:</strong> <?= $presupuesto['fecha_creacion'] ?></p>

      <button type="button" onclick="this.nextElementSibling.style.display = this.nextElementSibling.style.display === 'block' ? 'none' : 'block'">Ver Detalle</button>
      <div class="detallePresupuesto">
        <table>
          <thead>
            <tr><th>Receta</th><th>Cantidad</th><th>Precio</th><th>Total</th></tr>
          </thead>
          <tbody>
            <?php foreach ($recetasPresupuesto as $receta): ?>
              <tr>
                <td><?= htmlspecialchars($receta['nombre_receta']) ?></td>
                <td><?= $receta['cantidad'] ?></td>
                <td>$<?= number_format($receta['precio_unitario'], 2) ?></td>
                <td>$<?= number_format($receta['total'], 2) ?></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>

      <form method="POST" action="presupuesto.php" style="display: inline;">
        <input type="hidden" name="eliminar_presupuesto" value="1">
        <input type="hidden" name="id" value="<?= $presupuesto['id'] ?>">
        <button type="submit">Eliminar presupuesto</button>
      </form>

      <?php if ($presupuesto['estado'] === 'Pendiente'): ?>
      <form method="POST" action="presupuesto.php" style="display: inline;">
        <input type="hidden" name="cambiar_estado" value="1">
        <input type="hidden" name="id" value="<?= $presupuesto['id'] ?>">
        <input type="hidden" name="estado" value="Confirmado">
        <button type="submit" style="<?= $estadoColor ?>">Confirmar</button>
      </form>
      
      <form method="POST" action="presupuesto.php" style="display: inline;">
        <input type="hidden" name="cambiar_estado" value="1">
        <input type="hidden" name="id" value="<?= $presupuesto['id'] ?>">
        <input type="hidden" name="estado" value="Rechazado">
        <button type="submit">Rechazar</button>
      </form>
      <?php endif; ?>

      <?php if ($presupuesto['estado'] === 'Confirmado'): ?>
      <form method="POST" action="presupuesto.php" style="display: inline;">
        <input type="hidden" name="cambiar_estado" value="1">
        <input type="hidden" name="id" value="<?= $presupuesto['id'] ?>">
        <input type="hidden" name="estado" value="Pendiente">
        <button type="submit" style="<?= $estadoColor ?>">Volver a Pendiente</button>
      </form>

      <form method="POST" action="presupuesto.php" style="display: inline;" 
            onsubmit="return confirm('¿Estás seguro de marcar este presupuesto como realizado? Se descontará el stock de los ingredientes.');">
        <input type="hidden" name="realizar_presupuesto" value="1">
        <input type="hidden" name="id" value="<?= $presupuesto['id'] ?>">
        <button type="submit" style="background-color: green; color: white;">Realizar</button>
      </form>
      <?php endif; ?>

      <?php if ($presupuesto['estado'] === 'Realizado' || $presupuesto['estado'] === 'Rechazado'): ?>
      <span style="<?= $estadoColor ?>; padding: 5px 10px; border-radius: 3px;">
        <?= $presupuesto['estado'] ?>
      </span>
      <?php endif; ?>
    </div>
    <?php endforeach; ?>
  </div>

</main>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script type="text/javascript" charset="utf8" src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.js"></script>
<script src="js/script.js"></script>
<script src="js/inicio.js"></script>
<script src="js/utensilios.js"></script>
<script src="js/alertas.js"></script>
<script src="js/presupuesto.js"></script>
</body>
</html>