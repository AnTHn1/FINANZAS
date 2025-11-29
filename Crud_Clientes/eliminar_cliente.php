<?php
// eliminar_cliente.php
require_once "../Auth/auth.php"; // protege la página
include "../conexion.php";
include("../menu.php");

// ==================== Verificar método ====================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: listar_cliente.php");
    exit;
}

// ==================== Leer y validar ID ====================
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    header("Location: listar_cliente.php?error=invalid_id");
    exit;
}

try {
    // ==================== Comprobar existencia del cliente ====================
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        header("Location: listar_cliente.php?error=not_found");
        exit;
    }

    // ==================== Borrar el cliente ====================
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Redirigir de vuelta al listado con flag de éxito
    header("Location: listar_cliente.php?msg=deleted");
    exit;

} catch (PDOException $e) {
    echo "❌ Error al eliminar cliente: " . htmlspecialchars($e->getMessage());
    exit;
}
?>
