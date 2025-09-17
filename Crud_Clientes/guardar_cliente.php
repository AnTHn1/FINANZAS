<?php
include "../conexion.php";

// ==================== PROCESO DE GUARDADO ====================
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre   = $_POST["nombre"];
    $dni      = $_POST["dni"];
    $ingresos = $_POST["ingresos"];
    $telefono = $_POST["telefono"];
    $correo   = $_POST["correo"];

    try {
        // 🔹 Insertar en clientes (sin usuarios)
        $stmt = $pdo->prepare("INSERT INTO clientes (nombre, dni, ingresos, telefono, correo) 
                               VALUES (:nombre, :dni, :ingresos, :telefono, :correo)");
        $stmt->execute([
            ":nombre"   => $nombre,
            ":dni"      => $dni,
            ":ingresos" => $ingresos,
            ":telefono" => $telefono,
            ":correo"   => $correo
        ]);

        // Redirigir de vuelta al index
        header("Location: ../index.php");
        exit;

    } catch (PDOException $e) {
        echo "❌ Error: " . $e->getMessage();
    }
}
?>

<!-- ==================== FORMULARIO HTML ==================== -->
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        h2 { color: #2c3e50; }
        form { background: #fff; padding: 20px; border-radius: 8px; max-width: 400px; }
        label { display: block; margin-top: 10px; }
        input { width: 100%; padding: 8px; margin-top: 5px; }
        button { margin-top: 15px; padding: 10px; background: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; }
        button:hover { opacity: 0.9; }
    </style>
</head>
<body>
    <h2>➕ Agregar Cliente</h2>
    <form method="POST">
        <label>Nombre:</label>
        <input type="text" name="nombre" required>

        <label>DNI:</label>
        <input type="text" name="dni" required>

        <label>Ingresos:</label>
        <input type="number" name="ingresos" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" required>

        <label>Correo:</label>
        <input type="email" name="correo" required>

        <button type="submit">Guardar Cliente</button>
        
    </form>
    <br>
<a href="../index.php">⬅️ Volver</a>
</body>
</html>
