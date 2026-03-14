<?php
header('Content-Type: application/json');
require_once '../database.php';

try {
    $db = conectarDB();
    
    $query = "SELECT 
                p.id as producto_id,
                p.nombre as producto,
                c.nombre as categoria,
                sc.nombre as subcategoria,
                SUM(l.cantidad) as stock_total
              FROM productos p
              JOIN categorias c ON p.categoria_id = c.id
              JOIN subcategorias sc ON p.subcategoria_id = sc.id
              JOIN lotes l ON p.id = l.producto_id
              WHERE p.estatus = 1 AND l.estatus = 1
              GROUP BY p.id, p.nombre, c.nombre, sc.nombre
              ORDER BY p.nombre";
    
    $stmt = $db->prepare($query);
    $stmt->execute();
    $productos = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    echo json_encode($productos);
} catch (PDOException $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>