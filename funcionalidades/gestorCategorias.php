<?php
header('Content-Type: application/json');
$accion = $_POST['accion'] ?? '';
include '../database.php';

$db = conectarDB();

switch ($accion) {
    case 'agregarCategoria':
        $nombre = trim($_POST['nombre']);
        $stmt = $db->prepare("INSERT INTO categorias (nombre) VALUES (:nombre)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->execute();
        echo "Categoría agregada correctamente";
        break;
        
    case 'agregarSubcategoria':
        $nombre = trim($_POST['nombre']);
        $categoriaId = (int)$_POST['categoria_id'];
        $stmt = $db->prepare("INSERT INTO subcategorias (nombre, categoria_id) VALUES (:nombre, :categoria_id)");
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':categoria_id', $categoriaId);
        $stmt->execute();
        echo "Subcategoría agregada correctamente";
        break;
        
    default:
        echo "Acción no válida";
}