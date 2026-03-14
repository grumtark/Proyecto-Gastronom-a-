<?php
require_once __DIR__ . '/../database.php';

class GestorPresupuestos {
    private $db;

    public function __construct() {
        $this->db = conectarDB();
    }

    public function guardarPresupuesto($datos) {
        try {
            $this->db->beginTransaction();

            // Insertar el presupuesto principal
            $stmt = $this->db->prepare(
                "INSERT INTO presupuestos (nombre, apellido, telefono, email, observaciones, total) 
                 VALUES (:nombre, :apellido, :telefono, :email, :observaciones, :total)"
            );
            $stmt->execute([
                ':nombre' => $datos['nombre'],
                ':apellido' => $datos['apellido'],
                ':telefono' => $datos['telefono'],
                ':email' => $datos['email'],
                ':observaciones' => $datos['observaciones'],
                ':total' => $datos['total']
            ]);
            $presupuestoId = $this->db->lastInsertId();

            // Insertar las recetas del presupuesto
            foreach ($datos['recetas'] as $receta) {
                $stmt = $this->db->prepare(
                    "INSERT INTO presupuesto_recetas (presupuesto_id, receta_id, cantidad, precio_unitario, total) 
                     VALUES (:presupuesto_id, :receta_id, :cantidad, :precio_unitario, :total)"
                );
                $stmt->execute([
                    ':presupuesto_id' => $presupuestoId,
                    ':receta_id' => $receta['receta_id'],
                    ':cantidad' => $receta['cantidad'],
                    ':precio_unitario' => $receta['precio_unitario'],
                    ':total' => $receta['total']
                ]);
            }

            $this->db->commit();
            return $presupuestoId;
        } catch (PDOException $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function obtenerPresupuestos() {
        $stmt = $this->db->query("SELECT * FROM presupuestos ORDER BY fecha_creacion DESC");
        return $stmt->fetchAll();
    }

    public function obtenerRecetasPresupuesto($presupuestoId) {
        $stmt = $this->db->prepare("
            SELECT pr.*, r.titulo as nombre_receta 
            FROM presupuesto_recetas pr 
            JOIN recetas r ON pr.receta_id = r.id 
            WHERE pr.presupuesto_id = :presupuesto_id
        ");
        $stmt->execute([':presupuesto_id' => $presupuestoId]);
        return $stmt->fetchAll();
    }

    public function cambiarEstadoPresupuesto($presupuestoId, $estado) {
        $stmt = $this->db->prepare("UPDATE presupuestos SET estado = :estado WHERE id = :id");
        return $stmt->execute([':estado' => $estado, ':id' => $presupuestoId]);
    }

    public function eliminarPresupuesto($presupuestoId) {
        // Primero eliminar las recetas asociadas
        $stmt = $this->db->prepare("DELETE FROM presupuesto_recetas WHERE presupuesto_id = :id");
        $stmt->execute([':id' => $presupuestoId]);
        
        // Luego eliminar el presupuesto
        $stmt = $this->db->prepare("DELETE FROM presupuestos WHERE id = :id");
        return $stmt->execute([':id' => $presupuestoId]);
    }

    public function obtenerTodasRecetas() {
        try {
            $stmt = $this->db->query("
                SELECT 
                    id, 
                    titulo, 
                    descripcion
                FROM recetas 
                ORDER BY titulo
            ");
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("Error obteniendo recetas: " . $e->getMessage());
            return [];
        }
    }

    public function descontarStockPresupuesto($presupuestoId) {
        try {
            $this->db->beginTransaction();
            
            // Obtener todas las recetas del presupuesto
            $recetasPresupuesto = $this->obtenerRecetasPresupuesto($presupuestoId);
            
            foreach ($recetasPresupuesto as $receta) {
                // Obtener ingredientes de la receta
                $stmt = $this->db->prepare("
                    SELECT ri.producto_id, ri.cantidad, ri.unidad
                    FROM receta_ingredientes ri
                    WHERE ri.receta_id = :receta_id
                ");
                $stmt->execute([':receta_id' => $receta['receta_id']]); // CORRECCIÓN: sin comilla extra
                $ingredientes = $stmt->fetchAll();
                
                // Descontar cada ingrediente multiplicado por la cantidad de la receta
                foreach ($ingredientes as $ingrediente) {
                    $cantidadTotal = $ingrediente['cantidad'] * $receta['cantidad'];
                    
                    // Descontar de los lotes (método FIFO)
                    $stmt = $this->db->prepare("
                        SELECT id, cantidad 
                        FROM lotes 
                        WHERE producto_id = :producto_id 
                        AND estatus = 1 
                        AND cantidad > 0
                        ORDER BY fecha_ingreso ASC
                    ");
                    $stmt->execute([':producto_id' => $ingrediente['producto_id']]);
                    $lotes = $stmt->fetchAll();
                    
                    $cantidadRestante = $cantidadTotal;
                    
                    foreach ($lotes as $lote) {
                        if ($cantidadRestante <= 0) break;
                        
                        $cantidadADescontar = min($cantidadRestante, $lote['cantidad']);
                        
                        $stmtUpdate = $this->db->prepare("
                            UPDATE lotes 
                            SET cantidad = cantidad - :cantidad
                            WHERE id = :lote_id
                        ");
                        $stmtUpdate->execute([
                            ':cantidad' => $cantidadADescontar,
                            ':lote_id' => $lote['id']
                        ]);
                        
                        $cantidadRestante -= $cantidadADescontar;
                    }
                    
                    if ($cantidadRestante > 0) {
                        throw new Exception("Stock insuficiente para el producto ID: " . $ingrediente['producto_id']);
                    }
                }
            }
            
            // Marcar presupuesto como realizado
            $this->cambiarEstadoPresupuesto($presupuestoId, 'Realizado');
            
            $this->db->commit();
            return true;
            
        } catch (Exception $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    // 🆕 MÉTODO FALTANTE - Para obtener productos de presupuestos antiguos (compatibilidad)
    public function obtenerProductosPresupuesto($presupuestoId) {
        try {
            $stmt = $this->db->prepare("
                SELECT * FROM presupuesto_productos 
                WHERE presupuesto_id = :presupuesto_id
            ");
            $stmt->execute([':presupuesto_id' => $presupuestoId]);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            // Si la tabla no existe, retornar array vacío
            return [];
        }
    }
}
?>