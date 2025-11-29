<?php
require_once "../Auth/auth.php"; // protege la página
include "../conexion.php";
include("../menu.php");
// Verificar que llega el ID
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["id"])) {
    $id = $_POST["id"];

    // 1. Obtener cliente_id del crédito (para redirigir después)
    $stmt = $pdo->prepare("SELECT cliente_id FROM creditos WHERE id=?");
    $stmt->execute([$id]);
    $credito = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($credito) {
        $cliente_id = $credito["cliente_id"];

        // 2. Eliminar primero las cuotas del crédito
        $stmt = $pdo->prepare("DELETE FROM cuotas WHERE credito_id=?");
        $stmt->execute([$id]);

        // 3. Eliminar el crédito
        $stmt = $pdo->prepare("DELETE FROM creditos WHERE id=?");
        $stmt->execute([$id]);

        // 4. Redirigir con alerta
        echo "<script>
                alert('✅ Crédito eliminado correctamente');
                window.location.href = 'credito_cliente.php?cliente_id={$cliente_id}';
              </script>";
        exit;
    } else {
        echo "<script>
                alert('⚠ Error: El crédito no existe');
                window.location.href = 'credito_cliente.php';
              </script>";
        exit;
    }
} else {
    echo "<script>
            alert('⚠ Acceso no válido');
            window.location.href = 'credito_cliente.php';
          </script>";
    exit;
}
?>
