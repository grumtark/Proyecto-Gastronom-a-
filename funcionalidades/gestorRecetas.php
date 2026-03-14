<?php
require_once __DIR__ . '/../database.php';

class GestorRecetas {
    private $db;

    public function __construct() {
        $this->db = conectarDB();
    }

    public function guardarReceta($datos) {
        try {
            $this->db->beginTransaction();

            // Insertar la receta principal
            $stmt = $this->db->prepare(
                "INSERT INTO recetas (titulo, descripcion, pasos) 
                 VALUES (:titulo, :descripcion, :pasos)"
            );
            $stmt->execute([
                ':titulo' => $datos['titulo'],
                ':descripcion' => $datos['descripcion'],
                ':pasos' => $datos['pasos']
            ]);
            $recetaId = $this->db->lastInsertId();

            // Insertar los ingredientes de la receta (ahora con producto_id)
            foreach ($datos['ingredientes'] as $ingrediente) {
                $stmt = $this->db->prepare(
                    "INSERT INTO receta_ingredientes (receta_id, producto_id, cantidad, unidad) 
                     VALUES (:receta_id, :producto_id, :cantidad, :unidad)"
                );
                $stmt->execute([
                    ':receta_id' => $recetaId,
                    ':producto_id' => $ingrediente['producto_id'],
                    ':cantidad' => $ingrediente['cantidad'],
                    ':unidad' => $ingrediente['unidad']
                ]);
            }

            $this->db->commit();
            return $recetaId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function obtenerRecetas() {
        $stmt = $this->db->query("SELECT * FROM recetas ORDER BY fecha_creacion DESC");
        return $stmt->fetchAll();
    }

    public function obtenerIngredientesReceta($recetaId) {
        $stmt = $this->db->prepare("
            SELECT ri.*, p.nombre as nombre_producto 
            FROM receta_ingredientes ri 
            LEFT JOIN productos p ON ri.producto_id = p.id 
            WHERE ri.receta_id = :receta_id
        ");
        $stmt->execute([':receta_id' => $recetaId]);
        return $stmt->fetchAll();
    }

    public function obtenerProductosDisponibles() {
        $stmt = $this->db->query("
            SELECT p.id, p.nombre, COALESCE(l.unidad_medida, 'unidades') as unidad 
            FROM productos p 
            LEFT JOIN lotes l ON p.id = l.producto_id 
            WHERE p.estatus = 1 
            GROUP BY p.id
            ORDER BY p.nombre
        ");
        return $stmt->fetchAll();
    }

    public function eliminarReceta($recetaId) {
        $stmt = $this->db->prepare("DELETE FROM recetas WHERE id = :id");
        return $stmt->execute([':id' => $recetaId]);
    }

    public function verificarStockReceta($recetaId) {
        $ingredientes = $this->obtenerIngredientesReceta($recetaId);
        $stockInsuficiente = [];
        
        foreach ($ingredientes as $ingrediente) {
            $stmt = $this->db->prepare("
                SELECT COALESCE(SUM(cantidad), 0) as stock_total 
                FROM lotes 
                WHERE producto_id = :producto_id AND estatus = 1
            ");
            $stmt->execute([':producto_id' => $ingrediente['producto_id']]);
            $stock = $stmt->fetch()['stock_total'];
            
            if ($stock < $ingrediente['cantidad']) {
                $stockInsuficiente[] = [
                    'producto' => $ingrediente['nombre_producto'],
                    'necesario' => $ingrediente['cantidad'],
                    'disponible' => $stock,
                    'unidad' => $ingrediente['unidad']
                ];
            }
        }
        
        return $stockInsuficiente;
    }
}
?>