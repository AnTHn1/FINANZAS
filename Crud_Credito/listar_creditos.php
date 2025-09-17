<?php
include "../conexion.php";

// Obtener todos los créditos con nombre de cliente
$stmt = $pdo->query("
    SELECT cr.id, cl.nombre AS cliente, cr.moneda, cr.monto, cr.plazo, cr.tasa, cr.tipo_tasa, cr.periodo_gracia, cr.fecha_inicio
    FROM creditos cr
    INNER JOIN clientes cl ON cr.cliente_id = cl.id
    ORDER BY cr.id ASC
");
$creditos = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>Listado de Créditos</title>
<style>
body { font-family: Arial,sans-serif; margin:20px; background:#f8f9fa; }
h2 { color:#2c3e50; }
table { width:100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
table, th, td { border: 1px solid #ddd; }
th, td { padding:10px; text-align:center; }
th { background-color:#2c3e50; color:white; }
tr:nth-child(even){background:#f2f2f2;}
.btn{padding:6px 12px; border:none; cursor:pointer; border-radius:4px;}
.btn-edit{background:#3498db; color:white;}
.btn-delete{background:#e74c3c; color:white;}
.btn:hover{opacity:0.9;}
</style>
</head>
<body>
<h2>📋 Listado de Créditos</h2>
<table>
<thead>
<tr>
<th>ID</th>
<th>Cliente</th>
<th>Moneda</th>
<th>Monto</th>
<th>Plazo</th>
<th>Tasa</th>
<th>Tipo</th>
<th>Gracia</th>
<th>Fecha Inicio</th>
<th>Acciones</th>
</tr>
</thead>
<tbody>
<?php if(count($creditos) > 0): ?>
    <?php foreach($creditos as $c): ?>
        <tr>
            <td><?= $c['id'] ?></td>
            <td><?= htmlspecialchars($c['cliente']) ?></td>
            <td><?= $c['moneda'] ?></td>
            <td><?= $c['monto'] ?></td>
            <td><?= $c['plazo'] ?></td>
            <td><?= $c['tasa'] ?></td>
            <td><?= $c['tipo_tasa'] ?></td>
            <td><?= $c['periodo_gracia'] ?></td>
            <td><?= $c['fecha_inicio'] ?></td>
            <td>
                <form style="display:inline;" action="credito_cliente.php" method="GET">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-edit">✏ Editar</button>
                </form>
                <form style="display:inline;" action="eliminar_credito.php" method="POST" class="form-eliminar">
                    <input type="hidden" name="id" value="<?= $c['id'] ?>">
                    <button class="btn btn-delete">🗑 Eliminar</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
<?php else: ?>
<tr><td colspan="10">⚠ No hay créditos registrados</td></tr>
<?php endif; ?>
</tbody>
</table>

<script>
// Confirmación antes de eliminar
document.querySelectorAll(".form-eliminar").forEach(form => {
    form.addEventListener("submit", function(e){
        if(!confirm("¿Seguro que deseas eliminar este crédito?")){
            e.preventDefault();
        }
    });
});
</script>
</body>
</html>
