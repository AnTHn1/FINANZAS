<?php
require_once "../Auth/auth.php"; // protege la página
include "../conexion.php";
include("../menu.php");

// ✅ Conexión PDO centralizada
$pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// ✅ Verificar si llega un ID
$id = $_GET['id'] ?? null;
if (!$id) {
    echo "❌ ID de cliente no proporcionado.";
    exit;
}

// ✅ Procesar actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre   = $_POST['nombre']   ?? '';
    $dni      = $_POST['dni']      ?? '';
    $ingresos = $_POST['ingresos'] ?? 0;
    $telefono = $_POST['telefono'] ?? '';
    $correo   = $_POST['correo']   ?? '';

    try {
        $stmt = $pdo->prepare("UPDATE clientes 
                               SET nombre = :nombre, dni = :dni, ingresos = :ingresos, 
                                   telefono = :telefono, correo = :correo
                               WHERE id = :id");
        $stmt->execute([
            ":nombre"   => $nombre,
            ":dni"      => $dni,
            ":ingresos" => $ingresos,
            ":telefono" => $telefono,
            ":correo"   => $correo,
            ":id"       => $id
        ]);

        // ✅ Redirigir a listar_cliente.php después de actualizar
        header("Location: listar_cliente.php");
        exit;
    } catch (PDOException $e) {
        echo "❌ Error al actualizar: " . $e->getMessage();
    }
}

// ✅ Obtener datos actuales del cliente
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE id = :id");
$stmt->execute([":id" => $id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    echo "❌ Cliente no encontrado.";
    exit;
}
?>

<!-- ==============================
 ✏️ FORMULARIO DE EDICIÓN CLIENTE
============================== -->
<h2>✏️ Editar Cliente</h2>
<form method="post">
    <label>Nombre:</label><br>
    <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre']) ?>" required><br><br>

    <label>DNI:</label><br>
    <input type="text" name="dni" value="<?= htmlspecialchars($cliente['dni']) ?>" required><br><br>

    <label>Ingresos:</label><br>
    <input type="number" step="0.01" name="ingresos" value="<?= htmlspecialchars($cliente['ingresos']) ?>" required><br><br>

    <label>Teléfono:</label><br>
    <input type="text" name="telefono" value="<?= htmlspecialchars($cliente['telefono']) ?>"><br><br>

    <label>Correo:</label><br>
    <input type="email" name="correo" value="<?= htmlspecialchars($cliente['correo']) ?>"><br><br>

    <button type="submit">✅ Actualizar</button>
</form>

<br>
<a href="listar_cliente.php">⬅️ Volver</a>
