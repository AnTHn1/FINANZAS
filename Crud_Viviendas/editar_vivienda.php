<?php
require_once "../Auth/auth.php"; // protege la página
include("../conexion.php");
include("../menu.php");

// Verificar si llega el ID
if (!isset($_GET["id"])) {
    die("⚠ No se recibió un ID válido.");
}

$id = $_GET["id"];

// Obtener datos actuales
try {
    $stmt = $pdo->prepare("SELECT * FROM viviendas WHERE id = :id");
    $stmt->execute([":id" => $id]);
    $vivienda = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$vivienda) {
        die("❌ Vivienda no encontrada.");
    }
} catch (PDOException $e) {
    die("❌ Error al buscar la vivienda: " . $e->getMessage());
}

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $direccion = $_POST["direccion"];
    $provincia = $_POST["provincia"];
    $distrito = $_POST["distrito"];
    $moneda = $_POST["moneda"];
    $precio = $_POST["precio"];
    $area = $_POST["area"];
    $tipo = $_POST["tipo"];
    $ruta_imagen = $vivienda["imagen"]; // Por defecto mantiene la actual

    // Si se sube una nueva imagen
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
        $carpeta = "img/";
        if (!is_dir($carpeta)) mkdir($carpeta, 0777, true);

        $nombreTemp = $_FILES["imagen"]["tmp_name"];
        $nombreArchivo = "vivienda_" . uniqid() . "." . pathinfo($_FILES["imagen"]["name"], PATHINFO_EXTENSION);
        $rutaDestino = $carpeta . $nombreArchivo;

        // Validar tipo permitido
        $permitidos = ["jpg", "jpeg", "png", "gif", "webp"];
        $ext = strtolower(pathinfo($rutaDestino, PATHINFO_EXTENSION));

        if (in_array($ext, $permitidos)) {
            if (move_uploaded_file($nombreTemp, $rutaDestino)) {
                // Elimina imagen anterior si existe
                if (!empty($vivienda["imagen"]) && file_exists($vivienda["imagen"])) {
                    unlink($vivienda["imagen"]);
                }
                $ruta_imagen = $rutaDestino;
            }
        } else {
            echo "<p style='color:red'>⚠ Formato de imagen no permitido.</p>";
        }
    }

    try {
        $stmt = $pdo->prepare("UPDATE viviendas 
                               SET direccion=:direccion, provincia=:provincia, distrito=:distrito,
                                   moneda=:moneda, precio=:precio, area=:area, tipo=:tipo, imagen=:imagen
                               WHERE id=:id");
        $stmt->execute([
            ":direccion" => $direccion,
            ":provincia" => $provincia,
            ":distrito" => $distrito,
            ":moneda" => $moneda,
            ":precio" => $precio,
            ":area" => $area,
            ":tipo" => $tipo,
            ":imagen" => $ruta_imagen,
            ":id" => $id
        ]);

        header("Location: listar_viviendas.php");
        exit;
    } catch (PDOException $e) {
        echo "❌ Error al actualizar vivienda: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Editar Vivienda</title>
    <style>
        body { font-family: Arial, sans-serif; background: #f8f9fa; margin: 0; padding: 20px; }
        h2 { text-align: center; color: #2c3e50; }
        form {
            background: #fff; padding: 20px; border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1); max-width: 600px; margin: 20px auto;
        }
        label { display: block; margin-top: 12px; font-weight: bold; color: #2c3e50; }
        input, select {
            width: 100%; padding: 8px; margin-top: 5px;
            border: 1px solid #ccc; border-radius: 5px;
        }
        .precio-container { display: flex; gap: 10px; }
        .precio-container select { width: 30%; }
        .precio-container input { width: 70%; }
        button {
            margin-top: 20px; width: 100%; padding: 10px;
            background: #3498db; color: white; border: none;
            border-radius: 6px; font-size: 16px; cursor: pointer;
        }
        button:hover { background: #2980b9; }
        .preview { margin-top: 10px; text-align: center; }
        .preview img { max-width: 200px; border-radius: 8px; }
       .back {
    display: block;
    width: 97%;
    text-align: center;
    background: #6c757d;
    color: white;
    padding: 10px;
    border-radius: 6px;
    text-decoration: none;
    font-size: 16px;
    margin: 15px auto 0 auto;
    max-width: 600px; /* igual que tu formulario */
    transition: 0.2s;
}

.back:hover {
    background: #5a6268;
}

    </style>
</head>
<body>

<h2>✏ Editar Vivienda</h2>

<form method="POST" enctype="multipart/form-data">
    <label for="direccion">Dirección:</label>
    <input type="text" name="direccion" value="<?= htmlspecialchars($vivienda['direccion']); ?>" required>

    <label for="provincia">Provincia:</label>
    <input id="provincia" name="provincia" type="text" value="<?= htmlspecialchars($vivienda['provincia']); ?>" required>

    <label for="distrito">Distrito:</label>
    <input id="distrito" name="distrito" type="text" value="<?= htmlspecialchars($vivienda['distrito']); ?>" required>

    <label for="precio">Precio:</label>
    <div class="precio-container">
        <select name="moneda" required>
            <option value="Soles" <?= $vivienda['moneda'] === "Soles" ? "selected" : "" ?>>S/ (Soles)</option>
            <option value="Dólares" <?= $vivienda['moneda'] === "Dólares" ? "selected" : "" ?>>$ (Dólares)</option>
        </select>
        <input type="number" step="0.01" name="precio" value="<?= htmlspecialchars($vivienda['precio']); ?>" required>
    </div>

    <label for="area">Área (m²):</label>
    <input type="number" step="0.01" name="area" value="<?= htmlspecialchars($vivienda['area']); ?>" required>

    <label for="tipo">Tipo:</label>
    <select name="tipo" required>
        <option value="Departamento" <?= $vivienda['tipo'] === "Departamento" ? "selected" : "" ?>>Departamento</option>
        <option value="Casa" <?= $vivienda['tipo'] === "Casa" ? "selected" : "" ?>>Casa</option>
        <option value="Dúplex" <?= $vivienda['tipo'] === "Dúplex" ? "selected" : "" ?>>Dúplex</option>
        <option value="Otro" <?= $vivienda['tipo'] === "Otro" ? "selected" : "" ?>>Otro</option>
    </select>

    <label for="imagen">Imagen actual:</label>
<div class="preview">
    <?php 
        // Ruta física para verificar si el archivo existe
        $ruta_fisica = "../" . $vivienda["imagen"];

        // Ruta visible para el navegador
        $ruta_web = "../" . htmlspecialchars($vivienda["imagen"]);

        if (!empty($vivienda["imagen"]) && file_exists($ruta_fisica)): 
    ?>
        <img src="<?= $ruta_web; ?>" alt="Imagen actual">
    <?php else: ?>
        <p>Sin imagen disponible</p>
    <?php endif; ?>
</div>

    <label for="imagen">Reemplazar imagen (opcional):</label>
    <input type="file" name="imagen" accept=".jpg,.jpeg,.png,.gif,.webp">

    <button type="submit">💾 Guardar Cambios</button>
    <a href="listar_viviendas.php" class="back">⬅ Volver a la lista</a>
</form>

</body>
</html>
