<?php
include "../conexion.php";
include "../funciones_financieras.php"; // Tus funciones: cuotaFrances, capitalVivo

// Obtener lista de clientes
$stmtClientes = $pdo->query("SELECT * FROM clientes ORDER BY nombre ASC");
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// Manejar selección de cliente
$clienteSeleccionado = null;
if(isset($_GET['cliente_id'])){
    $clienteId = $_GET['cliente_id'];
    $stmt = $pdo->prepare("SELECT * FROM clientes WHERE id=?");
    $stmt->execute([$clienteId]);
    $clienteSeleccionado = $stmt->fetch(PDO::FETCH_ASSOC);

    // Obtener créditos de ese cliente
    $stmtCred = $pdo->prepare("SELECT * FROM creditos WHERE cliente_id=? ORDER BY id ASC");
    $stmtCred->execute([$clienteId]);
    $creditos = $stmtCred->fetchAll(PDO::FETCH_ASSOC);
}

// Manejar registro de crédito
if($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['registrar_credito'])){
    $cliente_id  = $_POST["cliente_id"];
    $monto       = $_POST["monto"];
    $plazo       = $_POST["plazo"];
    $tasaAnual   = $_POST["tasa"];
    $tipoTasa    = $_POST["tipo_tasa"];
    $gracia      = $_POST["periodo_gracia"];
    $fechaInicio = $_POST["fecha_inicio"];

    $tasaMensual = $tipoTasa === "Nominal" ? ($tasaAnual/12/100) : pow(1 + $tasaAnual/100, 1/12) - 1;
    $cuota = cuotaFrances($monto, $tasaMensual, $plazo);

    // Insertar crédito
    $stmt = $pdo->prepare("INSERT INTO creditos (cliente_id, moneda, monto, plazo, tasa, tipo_tasa, periodo_gracia, fecha_inicio)
                           VALUES (:cliente_id, :moneda, :monto, :plazo, :tasa, :tipo_tasa, :periodo_gracia, :fecha_inicio) RETURNING id");
    $stmt->execute([
        ":cliente_id" => $cliente_id,
        ":moneda" => $_POST["moneda"],
        ":monto" => $monto,
        ":plazo" => $plazo,
        ":tasa" => $tasaAnual,
        ":tipo_tasa" => $tipoTasa,
        ":periodo_gracia" => $gracia,
        ":fecha_inicio" => $fechaInicio
    ]);
    $credito_id = $stmt->fetchColumn();

    // Generar cuotas
    $capitalVivo = $monto;
    for($i=1; $i<=$plazo; $i++){
        $interes = $capitalVivo * $tasaMensual;
        $amortizacion = $cuota - $interes;
        $capitalVivo = capitalVivo($capitalVivo, $amortizacion);
        $fechaPago = date("Y-m-d", strtotime("+$i month", strtotime($fechaInicio)));

        $stmtCuota = $pdo->prepare("INSERT INTO cuotas (credito_id, numero, fecha_pago, capital, interes, cuota, capital_vivo)
                                   VALUES (:credito_id, :numero, :fecha_pago, :capital, :interes, :cuota, :capital_vivo)");
        $stmtCuota->execute([
            ":credito_id" => $credito_id,
            ":numero" => $i,
            ":fecha_pago" => $fechaPago,
            ":capital" => $amortizacion,
            ":interes" => $interes,
            ":cuota" => $cuota,
            ":capital_vivo" => $capitalVivo
        ]);
    }

    header("Location: credito_cliente.php?cliente_id=".$cliente_id);
    exit;
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Créditos por Cliente</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        h2, h3 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: center; }
        th { background-color: #2c3e50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn { padding: 6px 12px; border: none; cursor: pointer; border-radius: 4px; }
        .btn-credito { background-color: #27ae60; color: white; }
        .btn-edit { background-color: #3498db; color: white; }
        .btn-delete { background-color: #e74c3c; color: white; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>

<h2>💰 Créditos por Cliente</h2>

<!-- Selección de Cliente -->
<form method="get">
    <label>Seleccionar Cliente:</label>
    <select name="cliente_id" onchange="this.form.submit()">
        <option value="">-- Selecciona --</option>
        <?php foreach($clientes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= isset($clienteId) && $clienteId==$c['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select>
</form>

<?php if($clienteSeleccionado): ?>
    <!-- Información del Cliente -->
    <h3>Información del Cliente</h3>
    <table>
        <tr>
            <th>Nombre</th>
            <th>DNI</th>
            <th>Ingresos</th>
            <th>Teléfono</th>
            <th>Correo</th>
            <th>Acciones</th>
        </tr>
        <tr>
            <td><?= htmlspecialchars($clienteSeleccionado['nombre']) ?></td>
            <td><?= htmlspecialchars($clienteSeleccionado['dni']) ?></td>
            <td><?= htmlspecialchars($clienteSeleccionado['ingresos']) ?></td>
            <td><?= htmlspecialchars($clienteSeleccionado['telefono']) ?></td>
            <td><?= htmlspecialchars($clienteSeleccionado['correo']) ?></td>
            <td>
                <button class="btn btn-credito" onclick="document.getElementById('formCredito').style.display='block'">Registrar Crédito</button>
            </td>
        </tr>
    </table>

    <!-- Formulario de Crédito -->
    <div id="formCredito" style="display:none; margin-top:20px; padding:15px; border:1px solid #ccc; background:#fff;">
        <h3>📝 Registrar Crédito</h3>
        <form method="post">
            <input type="hidden" name="cliente_id" value="<?= $clienteSeleccionado['id'] ?>">
            <label>Moneda:</label><br>
            <select name="moneda">
                <option value="Soles">Soles</option>
                <option value="Dólares">Dólares</option>
            </select><br><br>

            <label>Monto:</label><br>
            <input type="number" step="0.01" name="monto" required><br><br>

            <label>Plazo (meses):</label><br>
            <input type="number" name="plazo" required><br><br>

            <label>Tasa (% anual):</label><br>
            <input type="number" step="0.0001" name="tasa" required><br><br>

            <label>Tipo de tasa:</label><br>
            <select name="tipo_tasa">
                <option value="Nominal">Nominal</option>
                <option value="Efectiva">Efectiva</option>
            </select><br><br>

            <label>Meses de gracia:</label><br>
            <input type="number" name="periodo_gracia" value="0"><br><br>

            <label>Fecha de inicio:</label><br>
            <input type="date" name="fecha_inicio" required><br><br>

            <button type="submit" name="registrar_credito" class="btn btn-credito">✅ Guardar Crédito</button>
        </form>
    </div>

    <!-- Créditos Registrados -->
    <h3>Créditos del Cliente</h3>
    <table>
        <tr>
            <th>ID</th>
            <th>Moneda</th>
            <th>Monto</th>
            <th>Plazo</th>
            <th>Tasa</th>
            <th>Tipo</th>
            <th>Gracia</th>
            <th>Fecha Inicio</th>
            <th>Acciones</th>
        </tr>
        <?php if(!empty($creditos)): ?>
            <?php foreach($creditos as $cr): ?>
            <tr>
                <td><?= $cr['id'] ?></td>
                <td><?= $cr['moneda'] ?></td>
                <td><?= $cr['monto'] ?></td>
                <td><?= $cr['plazo'] ?></td>
                <td><?= $cr['tasa'] ?></td>
                <td><?= $cr['tipo_tasa'] ?></td>
                <td><?= $cr['periodo_gracia'] ?></td>
                <td><?= $cr['fecha_inicio'] ?></td>
                <td>
                    <a href="editar_credito.php?id=<?= $cr['id'] ?>" class="btn btn-edit">✏ Editar</a>
                    <form action="eliminar_credito.php" method="POST" style="display:inline;">
                        <input type="hidden" name="id" value="<?= $cr['id'] ?>">
                        <button type="submit" class="btn btn-delete" onclick="return confirm('¿Seguro que deseas eliminar este crédito?')">🗑 Eliminar</button>
                    </form>
                </td>
            </tr>
            <?php endforeach; ?>
        <?php else: ?>
            <tr>
                <td colspan="9">⚠ No hay créditos registrados para este cliente</td>
            </tr>
        <?php endif; ?>
    </table>
<?php endif;
