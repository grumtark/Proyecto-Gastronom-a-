<?php
header('Content-Type: application/json');
require_once '../database.php';

try {
    $db = conectarDB();

    $query = "
        SELECT 
            u.id,          
            u.nombre, 
            u.cantidad, 
            u.fecha_compra,
            t.nombre as tipo_nombre
        FROM utensilios u
        JOIN tipos_utensilios t ON u.tipo_id = t.id
        WHERE u.estatus = 1
        ORDER BY u.nombre
    ";

    $stmt = $db->prepare($query);
    $stmt->execute();
    $utensilios = $stmt->fetchAll();
    echo json_encode($utensilios);
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}