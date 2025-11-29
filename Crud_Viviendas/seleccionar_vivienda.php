<?php
session_start();
require_once "../conexion.php";
require_once "../Auth/auth.php"; 
include("../menu.php");

if (!isset($_GET['vivienda_id'])) {
    header("Location: listar_viviendas.php");
    exit();
}

$vivienda_id = $_GET['vivienda_id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos de la vivienda
$stmt = $pdo->prepare("SELECT * FROM viviendas WHERE id = :id");
$stmt->execute([':id' => $vivienda_id]);
$vivienda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vivienda) die("Vivienda no encontrada.");

// Calcular BBP
$stmtBbp = $pdo->prepare("SELECT bono FROM bbp_rangos WHERE :precio BETWEEN precio_min AND precio_max LIMIT 1");
$stmtBbp->execute([':precio' => $vivienda['precio']]);
$bbp = $stmtBbp->fetch(PDO::FETCH_ASSOC);
$bono_aplicado = $bbp ? $bbp['bono'] : 0;
$precio_final = $vivienda['precio'] - $bono_aplicado;

// Verificar si ya existe proyección
$stmtProyeccion = $pdo->prepare("SELECT COUNT(*) AS total FROM solicitud_bono WHERE usuario_id = :usuario_id AND vivienda_id = :vivienda_id");
$stmtProyeccion->execute([
    ':usuario_id' => $usuario_id,
    ':vivienda_id' => $vivienda_id
]);
$result = $stmtProyeccion->fetch(PDO::FETCH_ASSOC);
$tieneProyeccion = ($result && $result['total'] > 0);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Detalle de Vivienda</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
            color: #333;
        }

        /* ===== IMAGEN PRINCIPAL ===== */
        .vivienda-hero {
            position: relative;
            width: 100vw;
            height: 75vh;
            overflow: hidden;
        }

        .vivienda-hero img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .vivienda-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.45);
        }

        .vivienda-title {
            position: absolute;
            bottom: 60px;
            left: 50%;
            transform: translateX(-50%);
            text-align: center;
            color: #fff;
        }

        .vivienda-title h2 {
            font-size: 2.4rem;
            margin: 0;
            font-weight: 700;
        }

        .vivienda-title p {
            font-size: 1.2rem;
            margin-top: 8px;
        }

        /* ===== CONTENEDOR INFERIOR ===== */
        .vivienda-container {
            max-width: 900px;
            margin: 40px auto;
            background: #fff;
            border-radius: 15px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 35px 40px;
        }

        .bono-section {
            text-align: center;
            background: #f0f9ff;
            border-radius: 10px;
            padding: 25px;
        }

        .bono-section h3 {
            color: #0077cc;
            font-size: 1.5rem;
            margin-bottom: 10px;
        }

        .bono-section p {
            margin: 8px 0;
            font-size: 1.1rem;
        }

        .btn {
            display: inline-block;
            padding: 12px 22px;
            background: #28a745;
            color: white;
            border: none;
            border-radius: 6px;
            text-decoration: none;
            font-weight: bold;
            transition: background 0.3s;
            margin-top: 20px;
        }

        .btn:hover { background: #218838; }

        .btn-back {
            background: #2c3e50;
            margin-top: 25px;
        }

        .btn-back:hover { background: #0056b3; }

        @media (max-width: 768px) {
            .vivienda-title h2 { font-size: 1.8rem; }
            .vivienda-title p { font-size: 1rem; }
            .vivienda-container { padding: 25px; }
        }
    </style>
</head>
<body>

<!-- ===== IMAGEN PRINCIPAL ===== -->
<div class="vivienda-hero">
    <?php
$ruta_hero = !empty($vivienda['imagen']) && file_exists("../" . $vivienda['imagen'])
    ? "../" . $vivienda['imagen']
    : "../img/casa.png";
?>
<img src="<?= htmlspecialchars($ruta_hero) ?>" alt="Imagen de la vivienda">

    <div class="vivienda-overlay"></div>
    <div class="vivienda-title">
        <h2><?= htmlspecialchars($vivienda['direccion']) ?></h2>
        <p><?= htmlspecialchars($vivienda['distrito']) ?> · <?= htmlspecialchars($vivienda['provincia']) ?> · <?= htmlspecialchars($vivienda['area']) ?> m² · <?= htmlspecialchars($vivienda['tipo']) ?></p>
    </div>
</div>

<!-- ===== CONTENEDOR DETALLES ===== -->
<div class="vivienda-container">
    <div class="bono-section">
        <h3>🏡 ¡Bono Buen Pagador Disponible!</h3>
        <p><b>Precio Original:</b> S/ <?= number_format($vivienda['precio'],2) ?></p>
        <p><b>BBP Aplicado:</b> S/ <?= number_format($bono_aplicado,2) ?></p>
        <p><b>Precio Final:</b> S/ <?= number_format($precio_final,2) ?></p>

        <?php if ($tieneProyeccion): ?>
            <!-- ✅ Corregimos la ruta: apunta a Crud_Credito -->
            <form action="../Crud_Viviendas/mostrar_cuotas.php" method="POST">
                <input type="hidden" name="vivienda_id" value="<?= $vivienda['id'] ?>">
                <button type="submit" class="btn">Ver Cuotas</button>
            </form>
        <?php else: ?>
            <!-- ✅ Corregimos la ruta: apunta a Crud_Credito -->
            <form action="../Crud_Credito/solicitud_bono.php" method="POST">
                <input type="hidden" name="vivienda_id" value="<?= $vivienda['id'] ?>">
                <button type="submit" class="btn">Solicitar Bono Buen Pagador</button>
            </form>
        <?php endif; ?>

        <a href="listar_viviendas.php" class="btn btn-back">⬅ Volver a listar viviendas</a>
    </div>
</div>

</body>
</html>
