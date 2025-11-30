<?php
include("../conexion.php");
require_once "../Auth/auth.php"; // protege la página
include("../menu.php");

// Obtener todas las viviendas
$stmt = $pdo->query("SELECT * FROM viviendas ORDER BY id DESC");
$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener rol del usuario desde sesión
$usuario_rol = $_SESSION['rol'];
$usuario_id = $_SESSION['usuario_id']; // <-- la clave correcta en tu sesión

if ($usuario_rol === 'cliente') {

    // Cliente solo ve:
    // - Su propia vivienda financiada
    // - Viviendas no financiadas por nadie
    $stmt = $pdo->prepare("
        SELECT v.*
        FROM viviendas v
        WHERE 
            v.id NOT IN (SELECT vivienda_id FROM solicitud_bono) -- No financiadas
            OR
            v.id IN (SELECT vivienda_id FROM solicitud_bono WHERE usuario_id = :usuario_id) -- Su vivienda financiada
        ORDER BY v.id DESC
    ");

    $stmt->execute([":usuario_id" => $usuario_id]);

} else { 
    // Admin ve todo
    $stmt = $pdo->query("SELECT * FROM viviendas ORDER BY id DESC");
}

$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Viviendas</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f8f9fa;
        }

        h2 {
            color: #2c3e50;
            margin: 30px auto;
            text-align: center;
        }

        .btn-add {
            display: block;
            margin: 0 auto 30px;
            width: fit-content;
            padding: 10px 15px;
            background: #2ecc71;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
            font-weight: bold;
        }
        .btn-add:hover {
            background: #27ae60;
        }

        /* Contenedor centrado */
        .cards {
            display: flex;
            flex-wrap: wrap;
            justify-content: center; /* centra las cards */
            gap: 20px;
            padding: 20px;
        }

        .card {
            display: flex;
            flex-direction: column;
            background: #fff;
            border-radius: 8px;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
            overflow: hidden;
            width: 400px;
            transition: transform 0.2s;
        }

        .card:hover {
            transform: scale(1.02);
        }

        .card img {
            width: 100%;
            height: 200px;
            object-fit: cover;
            background: #ddd;
        }

        .info {
            padding: 15px;
        }

        .info h3 {
            margin: 0 0 10px;
            color: #2c3e50;
        }

        .info p {
            margin: 5px 0;
            font-size: 14px;
            color: #555;
        }

        .price {
            font-weight: bold;
            color: #27ae60;
        }

        .actions {
            margin-top: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .actions a {
            text-decoration: none;
            padding: 7px 10px;
            border-radius: 4px;
            font-size: 13px;
            text-align: center;
            flex: 1;
        }

        .actions a.edit { background: #3498db; color: #fff; }
        .actions a.delete { background: #e74c3c; color: #fff; }
        .actions a.select { background: #2ecc71; color: #fff; }
        .actions a.edit:hover { background: #2980b9; }
        .actions a.delete:hover { background: #c0392b; }
        .actions a.select:hover { background: #27ae60; }

        .empty {
            margin: 30px auto;
            padding: 15px;
            background: #ffeaa7;
            border: 1px solid #fdcb6e;
            border-radius: 6px;
            text-align: center;
            color: #636e72;
            width: fit-content;
        }
    </style>
</head>
<body>

<h2>🏠 Gestión de Viviendas</h2>

<?php if ($usuario_rol == 'admin'): ?>
    <a href="crear_vivienda.php" class="btn-add">➕ Agregar Vivienda</a>
<?php endif; ?>

<?php if (count($viviendas) > 0): ?>
    <div class="cards">
        <?php foreach ($viviendas as $v): ?>
            <div class="card">
<?php
$ruta_imagen = !empty($v['imagen']) && file_exists("../" . $v['imagen'])
    ? "../" . $v['imagen']
    : "../img/casa.png";
?>
<img src="<?= htmlspecialchars($ruta_imagen) ?>" alt="Imagen de Vivienda">

                <div class="info">
                    <h3><?= htmlspecialchars($v['direccion']) ?></h3>
                    <p><b>Provincia:</b> <?= htmlspecialchars($v['provincia']) ?></p>
                    <p><b>Distrito:</b> <?= htmlspecialchars($v['distrito']) ?></p>
                    <p><b>Área:</b> <?= htmlspecialchars($v['area']) ?> m²</p>
                    <p><b>Tipo:</b> <?= htmlspecialchars($v['tipo']) ?></p>
                    <p class="price">
                        <b>Precio:</b>
                        <?= htmlspecialchars($v['moneda']) ?> <?= number_format($v['precio'], 2) ?>
                    </p>

                    <div class="actions">
                        <a href="seleccionar_vivienda.php?vivienda_id=<?= $v['id'] ?>" class="select">Seleccionar</a>
                        <?php if ($usuario_rol == 'admin'): ?>
                            <a href="editar_vivienda.php?id=<?= $v['id'] ?>" class="edit">✏ Editar</a>
                            <a href="eliminar_vivienda.php?id=<?= $v['id']; ?>"
                               class="delete"
                               onclick="return confirm('¿Seguro que deseas eliminar esta vivienda?')">🗑 Eliminar</a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
<?php else: ?>
    <div class="empty">⚠ No hay viviendas registradas</div>
<?php endif; ?>

</body>
</html>
