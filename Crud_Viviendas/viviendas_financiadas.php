<?php
session_start();
require_once "../conexion.php";
require_once "../Auth/auth.php";
include("../menu.php");

$usuario_id = $_SESSION['usuario_id'];

$stmt = $pdo->prepare("
    SELECT v.id, v.direccion, v.precio, sb.plazo_meses, sb.fecha_solicitud
    FROM viviendas v
    INNER JOIN solicitud_bono sb ON sb.vivienda_id = v.id
    WHERE sb.usuario_id = :usuario_id
");
$stmt->execute([':usuario_id' => $usuario_id]);
$viviendas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Viviendas Financiadas</title>

<style>
    body {
        font-family: Arial, sans-serif;
        background: #f4f6f9;
        margin: 20px;
    }

    h2 {
        color: #2c3e50;
        margin-bottom: 15px;
        font-size: 26px;
        text-align: center; /* Centrado */
    }

    /* Contenedor centrado */
    .tabla-container {
        background: white;
        padding: 20px;
        border-radius: 10px;
        box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        max-width: 900px;
        width: 100%;
        margin: 0 auto; /* Centra el div */
    }

    table {
        width: 100%;
        border-collapse: collapse;
    }

    th {
        background: #2c3e50;
        color: white;
        padding: 12px;
        font-size: 15px;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    td {
        padding: 12px;
        text-align: center;
        border-bottom: 1px solid #ddd;
    }

    tr:hover {
        background: #f1f1f1;
    }

    .btn-ver {
        background: #3498db;
        color: #fff;
        padding: 8px 12px;
        border: none;
        border-radius: 5px;
        cursor: pointer;
        font-size: 14px;
        transition: 0.2s;
    }

    .btn-ver:hover {
        background: #2980b9;
    }

    .no-registros {
        padding: 20px;
        background: #fff3cd;
        color: #856404;
        border: 1px solid #ffeeba;
        border-radius: 8px;
        margin-top: 20px;
        font-size: 16px;
        max-width: 600px;
        text-align: center;
        margin: 20px auto; /* Centra también este bloque */
    }
</style>


</head>
<body>

<h2>🏠 Viviendas Financiadas</h2>

<div class="tabla-container">

<?php if (empty($viviendas)): ?>

    <div class="no-registros">
        ⚠️ Aún no tienes viviendas financiadas registradas.
    </div>

<?php else: ?>

<table>
    <tr>
        <th>Dirección</th>
        <th>Precio</th>
        <th>Plazo</th>
        <th>Fecha Solicitud</th>
        <th>Acción</th>
    </tr>

    <?php foreach ($viviendas as $v): ?>
    <tr>
        <td><?= htmlspecialchars($v['direccion']) ?></td>
        <td>S/ <?= number_format($v['precio'], 2) ?></td>
        <td><?= $v['plazo_meses'] ?> meses</td>
        <td><?= $v['fecha_solicitud'] ?></td>

        <td>
            <form action="mostrar_cuotas.php" method="POST">
                <input type="hidden" name="vivienda_id" value="<?= $v['id'] ?>">
                <button class="btn-ver">Ver Cuotas</button>
            </form>
        </td>
    </tr>
    <?php endforeach; ?>

</table>

<?php endif; ?>

</div>

</body>
</html>
