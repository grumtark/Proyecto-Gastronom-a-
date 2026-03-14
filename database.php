<?php
function conectarDB() {
    $ruta_db = __DIR__ . '/../ProyecGastro/gestion_gastronomia.db';

    try {
        // Crear conexión
        $db = new PDO("sqlite:$ruta_db");
        
        // Configurar atributos
        $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        
        return $db;
    } catch(PDOException $e) {
        die("Error de conexión: " . $e->getMessage() . 
            "<br>Ruta usada: " . realpath($ruta_db) ?: $ruta_db);
    }
}
?>