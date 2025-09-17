<?php
// eliminar_cliente.php
// Eliminar SOLO la fila en la tabla "clientes". No se toca la tabla "usuarios".

include "../conexion.php";

// ==================== Verificar método ====================
if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    // Si se accede sin POST, redirigimos al index.
    header("Location: ../index.php");
    exit;
}

// ==================== Leer y validar ID ====================
$id = isset($_POST['id']) ? intval($_POST['id']) : 0;
if ($id <= 0) {
    // ID inválido
    header("Location: ../index.php?error=invalid_id");
    exit;
}

try {
    // ==================== Comprobar existencia del cliente ====================
    $stmt = $pdo->prepare("SELECT id FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $exists = $stmt->fetchColumn();

    if (!$exists) {
        // No existe ese cliente
        header("Location: ../index.php?error=not_found");
        exit;
    }

    // ==================== Borrar el cliente ====================
    $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
    $stmt->execute([':id' => $id]);

    // Redirigir de vuelta al index con flag de éxito
    header("Location: ../index.php?msg=deleted");
    exit;

} catch (PDOException $e) {
    // En caso de error SQL, mostramos el mensaje (en desarrollo).
    // En producción convendría registrar el error y mostrar un mensaje genérico.
    echo "❌ Error al eliminar cliente: " . htmlspecialchars($e->getMessage());
    exit;
}

/* ================= Nota sobre eliminar usuario relacionado =================
Si en el futuro quieres eliminar también el usuario asociado (campo usuario_id en clientes),
descomenta y usa el bloque siguiente *solo si* estás seguro de que quieres esa lógica:
(asegúrate primero de que la columna usuario_id existe y contiene el id correcto)

    // // obtener usuario_id
    // $stmt = $pdo->prepare("SELECT usuario_id FROM clientes WHERE id = :id");
    // $stmt->execute([':id' => $id]);
    // $usuario_id = $stmt->fetchColumn();
    // if ($usuario_id) {
    //     // eliminar cliente
    //     $stmt = $pdo->prepare("DELETE FROM clientes WHERE id = :id");
    //     $stmt->execute([':id' => $id]);
    //     // eliminar usuario
    //     $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id = :usuario_id");
    //     $stmt->execute([':usuario_id' => $usuario_id]);
    // }

============================================================================= */
?>
