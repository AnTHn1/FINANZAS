<?php
include "../conexion.php";
include "../funciones_financieras.php"; // Tus funciones como cuotaFrances y capitalVivo

// Obtener lista de clientes para el select
$stmtClientes = $pdo->query("SELECT id, nombre FROM clientes ORDER BY nombre ASC");
$clientes = $stmtClientes->fetchAll(PDO::FETCH_ASSOC);

// Obtener crédito a editar
$id = $_GET['id'] ?? null;
if(!$id){
    echo "❌ ID de crédito no proporcionado.";
    exit;
}

$stmt = $pdo->prepare("SELECT * FROM creditos WHERE id = ?");
$stmt->execute([$id]);
$credito = $stmt->fetch(PDO::FETCH_ASSOC);

if(!$credito){
    echo "❌ Crédito no encontrado.";
    exit;
}

// Procesar formulario
if($_SERVER["REQUEST_METHOD"] === "POST"){
    $cliente_id  = $_POST["cliente_id"];
    $moneda      = $_POST["moneda"];
    $monto       = $_POST["monto"];
    $plazo       = $_POST["plazo"];
    $tasaAnual   = $_POST["tasa"];
    $tipoTasa    = $_POST["tipo_tasa"];
    $gracia      = $_POST["periodo_gracia"];
    $fechaInicio = $_POST["fecha_inicio"];

    // Actualizar crédito
    $stmt = $pdo->prepare("UPDATE creditos 
                           SET cliente_id=:cliente_id, moneda=:moneda, monto=:monto, plazo=:plazo, tasa=:tasa, tipo_tasa=:tipo_tasa, periodo_gracia=:periodo_gracia, fecha_inicio=:fecha_inicio 
                           WHERE id=:id");
    $stmt->execute([
        ":cliente_id" => $cliente_id,
        ":moneda" => $moneda,
        ":monto" => $monto,
        ":plazo" => $plazo,
        ":tasa" => $tasaAnual,
        ":tipo_tasa" => $tipoTasa,
        ":periodo_gracia" => $gracia,
        ":fecha_inicio" => $fechaInicio,
        ":id" => $id
    ]);

    // Eliminar cuotas antiguas y recalcular
    $pdo->prepare("DELETE FROM cuotas WHERE credito_id=?")->execute([$id]);

    $tasaMensual = $tipoTasa === "Nominal" ? ($tasaAnual/12/100) : pow(1 + $tasaAnual/100, 1/12) - 1;
    $cuota = cuotaFrances($monto, $tasaMensual, $plazo);

    $capitalVivo = $monto;
    for($i=1; $i<=$plazo; $i++){
        $interes = $capitalVivo * $tasaMensual;
        $amortizacion = $cuota - $interes;
        $capitalVivo = capitalVivo($capitalVivo, $amortizacion);
        $fechaPago = date("Y-m-d", strtotime("+$i month", strtotime($fechaInicio)));

        $stmtCuota = $pdo->prepare("INSERT INTO cuotas (credito_id, numero, fecha_pago, capital, interes, cuota, capital_vivo)
                                    VALUES (:credito_id, :numero, :fecha_pago, :capital, :interes, :cuota, :capital_vivo)");
        $stmtCuota->execute([
            ":credito_id" => $id,
            ":numero" => $i,
            ":fecha_pago" => $fechaPago,
            ":capital" => $amortizacion,
            ":interes" => $interes,
            ":cuota" => $cuota,
            ":capital_vivo" => $capitalVivo
        ]);
    }

    // Alert y redirección a la vista del cliente
    echo "<script>
            alert('✅ Crédito actualizado y cuotas recalculadas correctamente');
            window.location.href = 'credito_cliente.php?cliente_id={$cliente_id}';
          </script>";
    exit;
}

?>

<h2>✏️ Editar Crédito</h2>
<form method="post">
    <input type="hidden" name="id" value="<?= $credito['id'] ?>">
    <input type="hidden" name="cliente_id" value="<?= $credito['cliente_id'] ?>">

    <label>Cliente:</label><br>
    <select name="cliente_id" required>
        <?php foreach($clientes as $c): ?>
            <option value="<?= $c['id'] ?>" <?= $c['id']==$credito['cliente_id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($c['nombre']) ?>
            </option>
        <?php endforeach; ?>
    </select><br><br>

    <label>Moneda:</label><br>
    <select name="moneda">
        <option value="Soles" <?= $credito['moneda']=="Soles" ? "selected" : "" ?>>Soles</option>
        <option value="Dólares" <?= $credito['moneda']=="Dólares" ? "selected" : "" ?>>Dólares</option>
    </select><br><br>

    <label>Monto:</label><br>
    <input type="number" step="0.01" name="monto" value="<?= $credito['monto'] ?>" required><br><br>

    <label>Plazo (meses):</label><br>
    <input type="number" name="plazo" value="<?= $credito['plazo'] ?>" required><br><br>

    <label>Tasa (% anual):</label><br>
    <input type="number" step="0.0001" name="tasa" value="<?= $credito['tasa'] ?>" required><br><br>

    <label>Tipo de tasa:</label><br>
    <select name="tipo_tasa">
        <option value="Nominal" <?= $credito['tipo_tasa']=="Nominal" ? "selected" : "" ?>>Nominal</option>
        <option value="Efectiva" <?= $credito['tipo_tasa']=="Efectiva" ? "selected" : "" ?>>Efectiva</option>
    </select><br><br>

    <label>Meses de gracia:</label><br>
    <input type="number" name="periodo_gracia" value="<?= $credito['periodo_gracia'] ?>"><br><br>

    <label>Fecha de inicio:</label><br>
    <input type="date" name="fecha_inicio" value="<?= $credito['fecha_inicio'] ?>" required><br><br>

    <!-- Botón Actualizar -->
    <button type="submit" class="btn" style="background-color:#27ae60; color:white; padding:8px 15px; border:none; border-radius:5px;">
        ✅ Actualizar Crédito
    </button>

    <!-- Botón Volver -->
    <a href="credito_cliente.php?cliente_id=<?= $credito['cliente_id'] ?>" 
       class="btn" style="background-color:#3498db; color:white; padding:8px 15px; border:none; border-radius:5px; text-decoration:none; margin-left:10px;">
       ⬅️ Volver
    </a>
</form>
