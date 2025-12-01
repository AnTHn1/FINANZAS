<?php
session_start();
require_once "../conexion.php";
require_once "../Auth/auth.php";
include("../menu.php");

// Obtener todas las solicitudes con información del cliente y la vivienda
$stmt = $pdo->query("
    SELECT 
        sb.id,
        c.nombre AS cliente_nombre,
        c.dni AS cliente_dni,
        c.correo AS cliente_correo,
        v.direccion AS vivienda_direccion,
        v.precio AS vivienda_precio,
        sb.tna,
        sb.plazo_meses,
        sb.periodo_gracia,
        sb.fecha_solicitud
    FROM solicitud_bono sb
    JOIN clientes c ON sb.usuario_id = c.usuario_id
    JOIN viviendas v ON sb.vivienda_id = v.id
    ORDER BY sb.fecha_solicitud DESC
");
$solicitudes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Solicitudes de Bono Buen Pagador</title>
    <style>
        body {
            font-family: 'Poppins', sans-serif;
            background: #f4f6f9;
            margin: 0;
            padding: 0;
        }

        .container {
            max-width: 1500px;
            margin: 60px auto;
            background: #fff;
            border-radius: 10px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            padding: 30px;
        }

        h2 {
            text-align: center;
            color: #0077cc;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }

        table th, table td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: center;
        }

        table th {
            background: #0077cc;
            color: #fff;
        }

        tr:nth-child(even) {
            background-color: #f9f9f9;
        }

        tr:hover {
            background-color: #e8f4ff;
        }

        .btn {
            display: inline-block;
            padding: 8px 14px;
            background: #dc3545;
            color: white;
            text-decoration: none;
            border-radius: 6px;
            transition: 0.3s;
        }

        .btn:hover {
            background: #c82333;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 25px;
            color: #0077cc;
            text-decoration: none;
            font-weight: bold;
        }

.boton-volver {
    display: block;
    width: 20%;
    margin: 20px auto 0 auto;   /* ⬅️ CENTRA EL BOTÓN */
    background: #2c3e50;        /* Azul oscuro */
    color: white;
    padding: 12px;
    border-radius: 8px;
    text-align: center;         /* Centra el texto */
    font-size: 16px;
    text-decoration: none;      /* Quitar subrayado */
    cursor: pointer;
    transition: background 0.25s ease;
}


.boton-volver:hover {
    background: #1f2d3a;
}


    </style>
</head>
<body>

<div class="container">
    <h2>📄 Solicitudes de Bono Buen Pagador</h2>

    <?php if (count($solicitudes) > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Cliente</th>
                    <th>DNI</th>
                    <th>Correo</th>
                    <th>Vivienda</th>
                    <th>Precio Vivienda (S/)</th>
                    <th>TEA</th>
                    <th>Plazo (meses)</th>
                    <th>Periodo de Gracia</th>
                    <th>Fecha Solicitud</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($solicitudes as $index => $s): ?>
                    <tr>
                        <td><?= $index + 1 ?></td>
                        <td><?= htmlspecialchars($s['cliente_nombre']) ?></td>
                        <td><?= htmlspecialchars($s['cliente_dni']) ?></td>
                        <td><?= htmlspecialchars($s['cliente_correo']) ?></td>
                        <td><?= htmlspecialchars($s['vivienda_direccion']) ?></td>
                        <td><?= number_format($s['vivienda_precio'], 2) ?></td>
                        <td><?= number_format($s['tna'], 2) ?>%</td>
                        <td><?= $s['plazo_meses'] ?></td>
                        <td><?= $s['periodo_gracia'] ?> meses</td>
                        <td><?= htmlspecialchars($s['fecha_solicitud']) ?></td>
                        <td>
                            <a href="eliminar_solicitud.php?id=<?= $s['id'] ?>" class="btn" onclick="return confirm('¿Seguro que deseas eliminar esta solicitud?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align:center;">No hay solicitudes registradas.</p>
    <?php endif; ?>

<a href="../Crud_Viviendas/listar_viviendas.php" class="boton-volver">
    ⬅ Volver al listado de viviendas
</a>

</body>
</html>
