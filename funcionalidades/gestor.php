<?php
header('Content-Type: text/html; charset=UTF-8');
$accion = $_GET['accion'] ?? $_POST['accion'] ?? '';
include '../database.php';

$db = conectarDB();

if (!$db) {
    die("Error al conectar con la base de datos");
}

switch ($accion) {
    case 'agregarUtensilio':
        agregarUtensilio($db);
        break;
        
    case 'agregarTipoUtensilio':
        agregarTipoUtensilio($db);
        break;

    case 'obtenerTiposUtensilios':
        obtenerTiposUtensilios($db);
        break;

    case 'obtenerUtensilio':
        obtenerUtensilio($db);
        break;

    case 'modificarUtensilio':
        modificarUtensilio($db);
        break;

    case 'borrarUtensilio':
        borrarUtensilio($db);
        break;

    default:
        echo "Acción no reconocida.";
        break;
}
function agregarTipoUtensilio($db) {
    if (isset($_POST['nuevo_tipo'])) {
        $nuevoTipo = trim($_POST['nuevo_tipo']);
        if ($nuevoTipo !== '') {
            $stmt = $db->prepare("INSERT OR IGNORE INTO tipos_utensilios (nombre) VALUES (:nombre)");
            $stmt->bindParam(':nombre', $nuevoTipo);
            $stmt->execute();
            echo "Tipo '$nuevoTipo' agregado correctamente.";
        } else {
            echo "El nombre del tipo no puede estar vacío.";
        }
    } else {
        echo "No se proporcionó un nuevo tipo.";
    }
}
function agregarUtensilio($db) {
    if (isset($_POST['nombre'], $_POST['cantidad'], $_POST['fechaCompra'], $_POST['tipo'])) {
        $nombre = trim($_POST['nombre']);
        $cantidad = (int) $_POST['cantidad'];
        $fechaCompra = $_POST['fechaCompra'];
        $tipo = (int) $_POST['tipo'];

        if ($nombre !== '' && $cantidad > 0 && $tipo > 0) {
            $stmt = $db->prepare("INSERT INTO utensilios (nombre, cantidad, fecha_compra, tipo_id, estatus) VALUES (:nombre, :cantidad, :fecha, :tipo, 1)");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':fecha', $fechaCompra);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->execute();
            echo "Utensilio '$nombre' guardado correctamente.";
        } else {
            echo "Datos inválidos para agregar el utensilio.";
        }
    } else {
        echo "Faltan datos para agregar el utensilio.";
    }
}

function obtenerTiposUtensilios($db) {
    $stmt = $db->query("SELECT id, nombre FROM tipos_utensilios");
    $tipos = $stmt->fetchAll();
    echo json_encode($tipos);
}

function obtenerUtensilio($db) {
    if (isset($_GET['id'])) {
        $id = (int) $_GET['id'];
        $stmt = $db->prepare("SELECT * FROM utensilios WHERE id = :id AND estatus = 1");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        $utensilio = $stmt->fetch();
        if ($utensilio) {
            echo json_encode($utensilio);
        } else {
            echo json_encode(['error' => 'Utensilio no encontrado']);
        }
    } else {
        echo json_encode(['error' => 'ID no proporcionado']);
    }
}

function modificarUtensilio($db) {
    if (isset($_POST['id'], $_POST['nombre'], $_POST['cantidad'], $_POST['fechaCompra'], $_POST['tipo'])) {
        $id = (int) $_POST['id'];
        $nombre = trim($_POST['nombre']);
        $cantidad = (int) $_POST['cantidad'];
        $fechaCompra = $_POST['fechaCompra'];
        $tipo = (int) $_POST['tipo'];

        if ($nombre !== '' && $cantidad > 0 && $tipo > 0) {
            $stmt = $db->prepare("UPDATE utensilios SET nombre = :nombre, cantidad = :cantidad, fecha_compra = :fecha, tipo_id = :tipo WHERE id = :id AND estatus = 1");
            $stmt->bindParam(':nombre', $nombre);
            $stmt->bindParam(':cantidad', $cantidad);
            $stmt->bindParam(':fecha', $fechaCompra);
            $stmt->bindParam(':tipo', $tipo);
            $stmt->bindParam(':id', $id);
            $stmt->execute();
            if ($stmt->rowCount() > 0) {
                echo "Utensilio modificado correctamente.";
            } else {
                echo "No se encontró el utensilio o no se realizó ningún cambio.";
            }
        } else {
            echo "Datos inválidos para modificar el utensilio.";
        }
    } else {
        echo "Faltan datos para modificar el utensilio.";
    }
}

function borrarUtensilio($db) {
    if (isset($_POST['id'])) {
        $id = (int) $_POST['id'];
        $stmt = $db->prepare("UPDATE utensilios SET estatus = 0 WHERE id = :id");
        $stmt->bindParam(':id', $id);
        $stmt->execute();
        if ($stmt->rowCount() > 0) {
            echo "Utensilio borrado correctamente.";
        } else {
            echo "No se encontró el utensilio.";
        }
    } else {
        echo "ID no proporcionado.";
    }
}
?>