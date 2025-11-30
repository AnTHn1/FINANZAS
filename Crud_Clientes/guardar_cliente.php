<?php
// guardar_cliente.php
// Procesamiento primero (antes de imprimir HTML)
require_once "../Auth/auth.php"; // protege la página (ya inicia sesión)
include "../conexion.php";

$errors = [];
$old = [
    'nombre' => '',
    'dni' => '',
    'ingresos' => '',
    'telefono' => '',
    'correo' => ''
];

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // recoger y sanitizar
    $nombre   = trim($_POST["nombre"] ?? '');
    $dni      = trim($_POST["dni"] ?? '');
    $ingresos = $_POST["ingresos"] ?? null;
    $telefono = trim($_POST["telefono"] ?? '');
    $correo   = trim($_POST["correo"] ?? '');

    // guardar valores para re-render en caso de error
    $old = [
        'nombre' => $nombre,
        'dni' => $dni,
        'ingresos' => $ingresos,
        'telefono' => $telefono,
        'correo' => $correo
    ];

    // validaciones básicas
    if ($nombre === '') $errors[] = "El nombre es obligatorio.";
    if ($dni === '') $errors[] = "El DNI es obligatorio.";
    if ($ingresos === '' || !is_numeric($ingresos)) $errors[] = "Los ingresos son obligatorios y deben ser numéricos.";
    if ($telefono === '') $errors[] = "El teléfono es obligatorio.";
    if ($correo !== '' && !filter_var($correo, FILTER_VALIDATE_EMAIL)) $errors[] = "El correo no tiene formato válido.";

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("INSERT INTO clientes (nombre, dni, ingresos, telefono, correo) 
                                   VALUES (:nombre, :dni, :ingresos, :telefono, :correo)");
            $stmt->execute([
                ":nombre"   => $nombre,
                ":dni"      => $dni,
                ":ingresos" => $ingresos,
                ":telefono" => $telefono,
                ":correo"   => $correo
            ]);

            // Mensaje flash y redirección (POST -> Redirect -> GET)
            $_SESSION['mensaje'] = "✅ Cliente guardado correctamente.";
            header("Location: listar_cliente.php");
            exit;
        } catch (PDOException $e) {
            // si quieres, quita el mensaje de error real en producción
            $errors[] = "Error al guardar en la base de datos: " . $e->getMessage();
        }
    }
}

// aquí ya podemos incluir menu y renderizar HTML
include "../menu.php";
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        h2 { color: #2c3e50; }
        form { background: #fff; padding: 20px; border-radius: 8px; max-width: 480px; }
        label { display: block; margin-top: 10px; font-weight: 600; color:#333; }
        input { width: 100%; padding: 8px; margin-top: 6px; border:1px solid #ccc; border-radius:5px; }
        button { margin-top: 15px; padding: 10px; background: #2ecc71; color: white; border: none; border-radius: 4px; cursor: pointer; font-weight:bold; }
        button:hover { opacity: 0.95; }
        .errors { background:#ffe6e6; border:1px solid #f5c6cb; color:#721c24; padding:10px; border-radius:6px; margin-bottom:12px; }
        .back { display:inline-block; margin-top:12px; text-decoration:none; color:#3498db; font-weight:bold; }
    </style>
</head>
<body>
    <main>
        <h2>➕ Agregar Cliente</h2>

        <!-- mostrar errores -->
        <?php if (!empty($errors)): ?>
            <div class="errors">
                <ul style="margin:0 0 0 18px;">
                    <?php foreach ($errors as $err): ?>
                        <li><?= htmlspecialchars($err) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <label>Nombre:</label>
            <input type="text" name="nombre" required value="<?= htmlspecialchars($old['nombre']) ?>">

            <label>DNI:</label>
            <input type="text" name="dni" required value="<?= htmlspecialchars($old['dni']) ?>">

            <label>Ingresos:</label>
            <input type="number" name="ingresos" step="0.01" required value="<?= htmlspecialchars($old['ingresos']) ?>">

            <label>Teléfono:</label>
            <input type="text" name="telefono" required value="<?= htmlspecialchars($old['telefono']) ?>">

            <label>Correo:</label>
            <input type="email" name="correo" required value="<?= htmlspecialchars($old['correo']) ?>">

            <button type="submit">Guardar Cliente</button>
        </form>

        <br>
        <a class="back" href="listar_cliente.php">⬅️ Volver a la lista de clientes</a>
    </main>
</body>
</html>
