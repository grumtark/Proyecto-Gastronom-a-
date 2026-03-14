<?php
header('Content-Type: application/json');
include '../database.php';

$db = conectarDB();

if (!$db) {
    die(json_encode(['error' => 'Error al conectar con la base de datos']));
}

$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
$data = json_decode(file_get_contents('php://input'), true) ?? $_POST;

switch ($accion) {
    case 'agregarCategoria':
        agregarCategoria($db, $data);
        break;

    case 'obtenerCategorias':
        obtenerCategorias($db);
        break;

    case 'agregarProducto':
        agregarProducto($db, $data);
        break;

    case 'listarProductos':
        listarProductos($db);
        break;

    case 'obtenerLotesProducto':
        obtenerLotesProducto($db, $_GET['producto_id'] ?? null);
        break;

    case 'agregarLote':
        agregarLote($db, $data);
        break;

    case 'quitarLote':
        quitarLote($db, $data);
        break;

    case 'obtenerAlertas':
        header('Content-Type: application/json');
        try {
            $alertas = [
                'stock' => obtenerAlertasStock($db),
                'vencimiento' => obtenerAlertasVencimiento($db)
            ];
            echo json_encode($alertas);
        } catch (PDOException $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
        break;

    case 'eliminarCategoria':
        eliminarCategoria($db, $data);
        break;

    case 'eliminarProducto':
        eliminarProducto($db, $data);
        break;

    default:
        echo json_encode(['error' => 'Acción no reconocida']);
        break;
}

function agregarCategoria($db, $data) {
    if (empty($data['nombre'])) {
        echo json_encode(['error' => 'El nombre no puede estar vacío']);
        return;
    }

    $umbral = isset($data['umbral_stock']) ? $data['umbral_stock'] : 5;

    try {
        $stmt = $db->prepare("INSERT INTO categorias (nombre, umbral_stock) VALUES (:nombre, :umbral_stock)");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':umbral_stock', $umbral);
        $stmt->execute();
        echo json_encode(['success' => 'Categoría agregada', 'id' => $db->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al agregar categoría: ' . $e->getMessage()]);
    }
}

function obtenerCategorias($db) {
    try {
        $stmt = $db->query("SELECT id, nombre, umbral_stock FROM categorias WHERE estatus = 1 ORDER BY nombre");
        $categorias = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($categorias);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function agregarProducto($db, $data) {
    if (empty($data['nombre']) || empty($data['categoria_id'])) {
        echo json_encode(['error' => 'Faltan datos requeridos']);
        return;
    }

    try {
        // Verificar si el producto ya existe
        $stmt = $db->prepare("SELECT id FROM productos WHERE nombre = :nombre AND categoria_id = :categoria_id");
        $stmt->bindParam(':nombre', $data['nombre']);
        $stmt->bindParam(':categoria_id', $data['categoria_id']);
        $stmt->execute();
        
        if ($stmt->fetch()) {
            echo json_encode(['error' => 'El producto ya existe']);
        } else {
            $stmt = $db->prepare("INSERT INTO productos (nombre, categoria_id) VALUES (:nombre, :categoria_id)");
            $stmt->bindParam(':nombre', $data['nombre']);
            $stmt->bindParam(':categoria_id', $data['categoria_id']);
            $stmt->execute();
            echo json_encode(['success' => 'Producto agregado', 'id' => $db->lastInsertId()]);
        }
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al agregar producto: ' . $e->getMessage()]);
    }
}

function listarProductos($db) {
    try {
        $query = "SELECT 
                    p.id,
                    p.nombre as producto,
                    c.nombre as categoria,
                    IFNULL(SUM(l.cantidad), 0) as stock_total,
                    l.unidad_medida
                  FROM productos p
                  JOIN categorias c ON p.categoria_id = c.id
                  LEFT JOIN lotes l ON p.id = l.producto_id AND l.estatus = 1
                  WHERE p.estatus = 1
                  GROUP BY p.id, p.nombre, c.nombre, l.unidad_medida
                  ORDER BY p.nombre";
        
        $stmt = $db->prepare($query);
        $stmt->execute();
        $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($productos);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function obtenerLotesProducto($db, $producto_id) {
    if (!$producto_id) {
        echo json_encode(['error' => 'ID de producto no proporcionado']);
        return;
    }

    try {
        $stmt = $db->prepare("SELECT id, cantidad, fecha_vencimiento, numero_lote, unidad_medida 
                             FROM lotes 
                             WHERE producto_id = :producto_id AND estatus = 1 
                             ORDER BY fecha_vencimiento");
        $stmt->bindParam(':producto_id', $producto_id);
        $stmt->execute();
        $lotes = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($lotes);
    } catch (PDOException $e) {
        echo json_encode(['error' => $e->getMessage()]);
    }
}

function agregarLote($db, $data) {
    if (empty($data['producto_id']) || empty($data['cantidad']) || 
        empty($data['fecha_vencimiento']) || empty($data['numero_lote']) || 
        empty($data['unidad_medida'])) {
        echo json_encode(['error' => 'Faltan datos requeridos']);
        return;
    }

    try {
        $stmt = $db->prepare("INSERT INTO lotes (producto_id, cantidad, fecha_vencimiento, numero_lote, unidad_medida) 
                             VALUES (:producto_id, :cantidad, :fecha_vencimiento, :numero_lote, :unidad_medida)");
        $stmt->bindParam(':producto_id', $data['producto_id']);
        $stmt->bindParam(':cantidad', $data['cantidad']);
        $stmt->bindParam(':fecha_vencimiento', $data['fecha_vencimiento']);
        $stmt->bindParam(':numero_lote', $data['numero_lote']);
        $stmt->bindParam(':unidad_medida', $data['unidad_medida']);
        $stmt->execute();
        echo json_encode(['success' => 'Lote agregado', 'id' => $db->lastInsertId()]);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al agregar lote: ' . $e->getMessage()]);
    }
}

function quitarLote($db, $data) {
    if (empty($data['lote_id']) || empty($data['cantidad'])) {
        echo json_encode(['error' => 'Faltan datos requeridos']);
        return;
    }

    try {
        // Verificar cantidad disponible
        $stmt = $db->prepare("SELECT cantidad FROM lotes WHERE id = :lote_id AND estatus = 1");
        $stmt->bindParam(':lote_id', $data['lote_id']);
        $stmt->execute();
        $lote = $stmt->fetch();
        
        if (!$lote) {
            echo json_encode(['error' => 'Lote no encontrado']);
            return;
        }
        
        if ($lote['cantidad'] < $data['cantidad']) {
            echo json_encode(['error' => 'No hay suficiente stock en este lote']);
            return;
        }
        
        // Actualizar cantidad
        $nueva_cantidad = $lote['cantidad'] - $data['cantidad'];
        
        if ($nueva_cantidad > 0) {
            $stmt = $db->prepare("UPDATE lotes SET cantidad = :cantidad WHERE id = :lote_id");
            $stmt->bindParam(':cantidad', $nueva_cantidad);
            $stmt->bindParam(':lote_id', $data['lote_id']);
            $stmt->execute();
        } else {
            // Si la cantidad llega a 0, marcar como inactivo
            $stmt = $db->prepare("UPDATE lotes SET estatus = 0 WHERE id = :lote_id");
            $stmt->bindParam(':lote_id', $data['lote_id']);
            $stmt->execute();
        }
        
        echo json_encode(['success' => 'Stock actualizado correctamente']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al quitar del lote: ' . $e->getMessage()]);
    }
}

function obtenerAlertasStock($db) {
    $query = "SELECT 
                p.nombre as producto, 
                c.nombre as categoria, 
                SUM(l.cantidad) as stock_actual, 
                c.umbral_stock,
                l.unidad_medida
              FROM productos p
              JOIN categorias c ON p.categoria_id = c.id
              JOIN lotes l ON p.id = l.producto_id AND l.estatus = 1
              WHERE p.estatus = 1
              GROUP BY p.id, p.nombre, c.nombre, c.umbral_stock, l.unidad_medida
              HAVING stock_actual <= c.umbral_stock
              ORDER BY stock_actual ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function obtenerAlertasVencimiento($db) {
    $query = "SELECT 
                p.nombre as producto, 
                l.numero_lote, 
                l.fecha_vencimiento, 
                l.unidad_medida,
                CAST((julianday(l.fecha_vencimiento) - julianday('now')) AS INTEGER) as dias_restantes
              FROM lotes l
              JOIN productos p ON l.producto_id = p.id
              WHERE l.estatus = 1
              AND l.fecha_vencimiento BETWEEN DATE('now') AND DATE('now', '+1 month')
              ORDER BY l.fecha_vencimiento ASC";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

function eliminarCategoria($db, $data) {
    if (empty($data['id'])) {
        echo json_encode(['error' => 'ID de categoría no proporcionado']);
        return;
    }

    try {
        // Marcamos como inactivos los productos relacionados
        $stmt = $db->prepare("UPDATE productos SET estatus = 0 WHERE categoria_id = :categoria_id");
        $stmt->bindParam(':categoria_id', $data['id']);
        $stmt->execute();
        
        // Marcamos como inactivos sus lotes
        $stmt = $db->prepare("UPDATE lotes SET estatus = 0 
                             WHERE producto_id IN (SELECT id FROM productos WHERE categoria_id = :categoria_id)");
        $stmt->bindParam(':categoria_id', $data['id']);
        $stmt->execute();
        
        // La categoría
        $stmt = $db->prepare("UPDATE categorias SET estatus = 0 WHERE id = :id");
        $stmt->bindParam(':id', $data['id']);
        $stmt->execute();
        
        echo json_encode(['success' => 'Categoría eliminada']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al eliminar categoría: ' . $e->getMessage()]);
    }
}

function eliminarProducto($db, $data) {
    if (empty($data['id'])) {
        echo json_encode(['error' => 'ID de producto no proporcionado']);
        return;
    }

    try {
        // Marcamos como inactivo el producto
        $stmt = $db->prepare("UPDATE productos SET estatus = 0 WHERE id = :id");
        $stmt->bindParam(':id', $data['id']);
        $stmt->execute();
        
        // Marcamos como inactivos sus lotes
        $stmt = $db->prepare("UPDATE lotes SET estatus = 0 WHERE producto_id = :producto_id");
        $stmt->bindParam(':producto_id', $data['id']);
        $stmt->execute();
        
        echo json_encode(['success' => 'Producto eliminado']);
    } catch (PDOException $e) {
        echo json_encode(['error' => 'Error al eliminar producto: ' . $e->getMessage()]);
    }
}
?>