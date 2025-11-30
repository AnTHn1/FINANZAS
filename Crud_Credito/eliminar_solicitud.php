<?php
require_once "../Auth/auth.php";
require_once "../conexion.php";

// Verificar si se recibe un ID válido
if (!isset($_GET['id']) || empty($_GET['id'])) {
    echo "⚠ No se recibió un ID válido.";
    exit;
}

$id = $_GET['id'];

try {
    // Eliminar la solicitud del bono
    $stmt = $pdo->prepare("DELETE FROM solicitud_bono WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Redirigir al listado
    header("Location: listar_solicitudes.php");
    exit;
} catch (PDOException $e) {
    echo "❌ Error al eliminar la solicitud: " . $e->getMessage();
}
?>
