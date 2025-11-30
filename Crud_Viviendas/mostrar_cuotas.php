<?php
session_start();
require_once "../conexion.php";
require_once "../Auth/auth.php";
include("../menu.php");

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['vivienda_id'])) {
    header("Location: listar_viviendas.php");
    exit();
}

$vivienda_id = $_POST['vivienda_id'];
$usuario_id = $_SESSION['usuario_id'];

// Obtener datos de la vivienda
$stmt = $pdo->prepare("SELECT * FROM viviendas WHERE id = :id");
$stmt->execute([':id' => $vivienda_id]);
$vivienda = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$vivienda) die("Vivienda no encontrada.");

// Obtener proyección de crédito guardada
$stmtProyeccion = $pdo->prepare("SELECT * FROM solicitud_bono 
                                 WHERE usuario_id = :usuario_id AND vivienda_id = :vivienda_id LIMIT 1");
$stmtProyeccion->execute([
    ':usuario_id' => $usuario_id,
    ':vivienda_id' => $vivienda_id
]);
$proyeccion = $stmtProyeccion->fetch(PDO::FETCH_ASSOC);

if (!$proyeccion) {
    header("Location: solicitud_bono.php?vivienda_id=$vivienda_id");
    exit();
}

// Calcular BBP (lookup en bbp_rangos)
$stmtBbp = $pdo->prepare("SELECT bono FROM bbp_rangos WHERE :precio BETWEEN precio_min AND precio_max LIMIT 1");
$stmtBbp->execute([':precio' => $vivienda['precio']]);
$bbp = $stmtBbp->fetch(PDO::FETCH_ASSOC);
$bono_aplicado = $bbp ? (float)$bbp['bono'] : 0.0;

// ====== Parámetros (tomados preferentemente de $proyeccion) ======

// Valor vivienda (desde tabla viviendas)
$valor_vivienda = (float)$vivienda['precio'];

// Cuota inicial ingresada por el usuario (en la solicitud)
$cuota_inicial = isset($proyeccion['cuota_inicial']) ? (float)$proyeccion['cuota_inicial'] : 0.0;

// % cuota inicial (no editable)
$porc_cuota_inicial = $valor_vivienda > 0 ? ($cuota_inicial / $valor_vivienda) * 100 : 0;

// Bono Buen Pagador detectado según tabla bbp_rangos
$bono_bbp = $bono_aplicado;

// TOTAL BBP: si el lookup devolvió un monto, lo usamos SIEMPRE
if ($bono_bbp > 0) {
    $total_bbp = $bono_bbp;
} else {
    // si no hay coincidencia, usamos lo que estaba guardado en solicitud_bono
    $total_bbp = isset($proyeccion['total_bbp']) ? (float)$proyeccion['total_bbp'] : 0.0;
}

// Monto a financiar
$monto_financiar = $valor_vivienda - $cuota_inicial - $total_bbp;
if ($monto_financiar < 0) $monto_financiar = 0.0;

// TEA (preferimos proyeccion['tea'], sino 'tna', sino default)
$TEA = null;
if (isset($proyeccion['tea'])) {
    $TEA = (float)$proyeccion['tea'];
} elseif (isset($proyeccion['tna'])) {
    $TEA = (float)$proyeccion['tna'];
} else {
    $TEA = 0.0898; // por defecto 8.98%
}
// Si TEA fue ingresada como porcentaje (p.ej. 8.98), convertirlo a decimal
if ($TEA > 1) $TEA = $TEA / 100.0;

// Tasas y cargos
// tasa_desgravamen: si guardaste en proyeccion, la usamos (se espera en formato decimal 0.0004 = 0.04%)
$tasa_desgravamen = isset($proyeccion['tasa_desgravamen']) ? (float)$proyeccion['tasa_desgravamen'] : 0.0004;

// tasa_seguro_riesgo: si guardaste en proyeccion (se espera anual decimal, p.ej. 0.003 = 0.30%)
$tasa_seguro_riesgo_anual = isset($proyeccion['tasa_seguro_riesgo']) ? (float)$proyeccion['tasa_seguro_riesgo'] : 0.003;

// comision/portes/gastos administrativos por cuota (valores fijos si existen)
$comision = isset($proyeccion['comision']) ? (float)$proyeccion['comision'] : 0.0;
$portes = isset($proyeccion['portes']) ? (float)$proyeccion['portes'] : 0.0;
$gastos_adm = isset($proyeccion['gastos_adm']) ? (float)$proyeccion['gastos_adm'] : 0.0;

// Plazo y periodo de gracia
$plazo_meses = isset($proyeccion['plazo_meses']) ? (int)$proyeccion['plazo_meses'] : 60;
$periodo_gracia = isset($proyeccion['periodo_gracia']) ? (int)$proyeccion['periodo_gracia'] : 0;
$fecha_solicitud = isset($proyeccion['fecha_solicitud']) ? $proyeccion['fecha_solicitud'] : '';

// ====== Cálculos financieros (método francés) ======

// TEM desde TEA
$TEM = pow(1 + $TEA, 1/12) - 1;

// Seguro de inmueble mensual (sobre valor de la vivienda)
$seguro_inmueble_mensual = ($valor_vivienda * $tasa_seguro_riesgo_anual) / 12.0;

// Meses efectivamente amortizables (excluye periodo de gracia)
$meses_amortizacion = $plazo_meses - $periodo_gracia;
if ($meses_amortizacion < 1) $meses_amortizacion = 1;

// ====== FRECUENCIA DE PAGO ======
$frecuencia = isset($proyeccion['frecuencia']) ? (int)$proyeccion['frecuencia'] : 1; // 1=mensual, 2=bimestral, 3=trimestral
$meses_por_cuota = $frecuencia;

// TEM ajustada a frecuencia
$TEM_freq = pow(1 + $TEA, $meses_por_cuota / 12) - 1;

// COK ajustada a frecuencia
$COK_anual = isset($proyeccion['COK']) ? (float)$proyeccion['COK'] : 0.05; // 5% anual por defecto
$COK_periodo = pow(1 + $COK_anual, $meses_por_cuota / 12) - 1;

// TEM total incluyendo COK
$TEM_total = (1 + $TEM_freq) * (1 + $COK_periodo) - 1;

// Cuotas totales según frecuencia
$cuotas_totales = ceil($meses_amortizacion / $meses_por_cuota);

// Cuota fija (capital + interés, francés)
if ($TEM_total > 0) {
    $den = 1 - pow(1 + $TEM_total, - $cuotas_totales);
    $cuota_fija_cap_int = $den > 0 ? ($monto_financiar * $TEM_total) / $den : $monto_financiar / $cuotas_totales;
} else {
    $cuota_fija_cap_int = $monto_financiar / $cuotas_totales;
}

// ====== Generar cronograma con frecuencia ======
$saldo = $monto_financiar;
$cronograma = [];

for ($mes = 1; $mes <= $plazo_meses; $mes++) {

    $saldo_inicial = $saldo;

    if ($mes <= $periodo_gracia) {
        // Periodo de gracia total
        $amortizacion = 0.0;
$interes = 0;
$amortizacion = 0;
$pg = 'T';

    } else {
        // Determinar si este mes toca pagar según frecuencia
        $mes_pago = ($mes - $periodo_gracia - 1) % $meses_por_cuota == 0;

        if ($mes_pago) {
            // Intereses por TEM y COK
            $interes_TEM = $saldo_inicial * $TEM_freq;
            $interes_COK = $saldo_inicial * $COK_periodo;
            $interes_total = $interes_TEM + $interes_COK;

            // Amortización
            $cap_y_int = $cuota_fija_cap_int;
            $amortizacion = $cap_y_int - $interes_total;
            if ($amortizacion < 0) $amortizacion = 0.0;
            $interes = $interes_total;
            $pg = 'S';
        } else {
            // Mes sin pago (entre cuotas)
$interes = $saldo_inicial * $TEM_total;  
$amortizacion = 0;
$pg = 'S'; // se mantiene tu lógica

        }
    }

    $seguro_desgrav = $saldo_inicial * $tasa_desgravamen;
    $seguro_inmueble = $seguro_inmueble_mensual;
    $saldo_final = $saldo_inicial - $amortizacion;
    if ($saldo_final < 0.01) $saldo_final = 0.0;

    $cuota_mensual = $amortizacion + $interes + $seguro_desgrav + $seguro_inmueble + $comision;
    $flujo_total = $cuota_mensual + $portes + $gastos_adm;

    $cronograma[] = [
        'periodo' => $mes,
        'saldo_inicial' => $saldo_inicial,
        'pg' => $pg,
        'amortizacion' => $amortizacion,
        'interes' => $interes,
        'seguro_desgrav' => $seguro_desgrav,
        'seguro_inmueble' => $seguro_inmueble,
        'saldo_final' => $saldo_final,
        'cuota_mensual' => $cuota_mensual
    ];

    $saldo = $saldo_final;
}

// Formateo para mostrar
function fmt($x) {
    return number_format(round($x + 0.0000001, 2), 2, '.', ',');
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Proyección de Cuotas</title>
    <style>
        body { font-family: Arial; background: #f8f9fa; margin: 20px; color: #2c3e50; }
        h2 { color: #2c3e50; }
        .info {
            background: #fff;
            padding: 12px 16px;
            border-radius: 8px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.06);
            max-width: 1100px;
        }
        .info p { margin: 6px 0; }
        table { width: 100%; border-collapse: collapse; margin-top: 18px; background: #fff; border-radius: 6px; overflow: hidden; }
        th, td { border: 1px solid #e6e9ee; padding: 8px 10px; text-align: right; }
        th { background: #2c3e50; color: white; text-align: center; font-weight: 600; }
        td:first-child, th:first-child { text-align: center; }
        tr:nth-child(even) { background: #fbfcfe; }
        .btn { padding: 8px 12px; background: #2c3e50; color: #fff; border: none; border-radius: 5px; cursor: pointer; text-decoration: none; display:inline-block; }
        .small { font-size: 13px; color: #6b7280; }
    </style>
</head>
<body>

<h2>📊 Proyección de Cuotas - <?= htmlspecialchars($vivienda['direccion']) ?></h2>

<div class="info">
    <p><b>Precio Original:</b> S/ <?= fmt($valor_vivienda) ?></p>
    <p><b>BBP aplicado:</b> S/ <?= fmt($bono_bbp) ?></p>
    <p><b>Precio Final (valor - bono):</b> S/ <?= fmt($valor_vivienda - $bono_bbp) ?></p>
       <b>Total BBP aplicado:</b> S/ <?= fmt($total_bbp) ?></p>

    <p><b>Monto a financiar:</b> S/ <?= fmt($monto_financiar) ?> &nbsp;|&nbsp;
       <b>TEA:</b> <?= number_format($TEA * 100, 2) ?>% &nbsp;|&nbsp;
       <b>Plazo:</b> <?= $plazo_meses ?> meses &nbsp;|&nbsp;
       <b>Periodo de Gracia:</b> <?= $periodo_gracia ?> meses</p>

<p class="small">
    <b>Supuestos utilizados:</b> 
    Seguro Desgrav. mensual = <?= ($tasa_desgravamen * 100) ?>%, 
    Seguro inmueble anual = <?= ($tasa_seguro_riesgo_anual * 100) ?>%, 
    COK (Costo de Oportunidad del Capital) anual = <?= ($COK_anual * 100) ?>%.
</p>

</div>

<table>
    <thead>
        <tr>
            <th>Periodo</th>
            <th>Saldo Inicial (S/)</th>
            <th>P.G</th>
            <th>Amortización (S/)</th>
            <th>Intereses (S/)</th>
            <th>Seguro Desgrav. (S/)</th>
            <th>Seguro Inmueble (S/)</th>
            <th>Saldo Final (S/)</th>
            <th>Cuota Mensual (S/)</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach ($cronograma as $row): ?>
            <tr>
                <td><?= $row['periodo'] ?></td>
                <td><?= fmt($row['saldo_inicial']) ?></td>
                <td><?= $row['pg'] ?></td>
                <td><?= fmt($row['amortizacion']) ?></td>
                <td><?= fmt($row['interes']) ?></td>
                <td><?= fmt($row['seguro_desgrav']) ?></td>
                <td><?= fmt($row['seguro_inmueble']) ?></td>
                <td><?= fmt($row['saldo_final']) ?></td>
                <td><?= fmt($row['cuota_mensual']) ?></td>
            </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<br>
<div style="display: flex; justify-content: space-between; margin-top: 20px; align-items: center;">

    <!-- Botón izquierda -->
    <a href="seleccionar_vivienda.php?vivienda_id=<?= $vivienda['id'] ?>" class="btn">
        ⬅ Volver a Detalle de Vivienda
    </a>

    <!-- Botón derecha -->
    <form action="generar_pdf.php" method="POST" target="_blank" style="margin: 0;">
        <input type="hidden" name="vivienda_id" value="<?= $vivienda_id ?>">
        <button class="btn" type="submit">📄 Generar PDF</button>
    </form>

</div>

</body>
</html>
