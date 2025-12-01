<?php
session_start();
require_once "../conexion.php";
require_once "../Auth/auth.php";

// Cargar Dompdf SIN Composer (asegúrate de tener ../dompdf/autoload.inc.php)
require_once "../dompdf/autoload.inc.php";

use Dompdf\Dompdf;
use Dompdf\Options;

// Validar POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST' || !isset($_POST['vivienda_id'])) {
    exit("Acceso inválido.");
}

$vivienda_id = $_POST['vivienda_id'];
$usuario_id = $_SESSION['usuario_id'] ?? null;
if (!$usuario_id) exit("Usuario no autenticado.");

/* ================================
   OBTENER DATOS Y CALCULOS (MISMO LÓGICA OFICIAL)
   ================================ */

// Obtener vivienda
$stmt = $pdo->prepare("SELECT * FROM viviendas WHERE id = :id");
$stmt->execute([':id' => $vivienda_id]);
$vivienda = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$vivienda) exit("No existe la vivienda.");

$valor_vivienda = (float)$vivienda['precio'];

// Obtener proyección guardada
$stmtP = $pdo->prepare("
    SELECT * FROM solicitud_bono 
    WHERE usuario_id = :usuario AND vivienda_id = :vivienda LIMIT 1
");
$stmtP->execute([
    ':usuario' => $usuario_id,
    ':vivienda' => $vivienda_id
]);
$proyeccion = $stmtP->fetch(PDO::FETCH_ASSOC);
if (!$proyeccion) exit("No hay proyección guardada.");

// Lookup BBP
$stmtB = $pdo->prepare("SELECT bono FROM bbp_rangos WHERE :precio BETWEEN precio_min AND precio_max LIMIT 1");
$stmtB->execute([':precio' => $valor_vivienda]);
$bbp = $stmtB->fetch(PDO::FETCH_ASSOC);
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
// FRECUENCIA DE PAGO: 1=mensual, 2=bimestral, 3=trimestral
$frecuencia = isset($_POST['frecuencia']) 
                ? (int)$_POST['frecuencia'] 
                : (isset($proyeccion['frecuencia']) ? (int)$proyeccion['frecuencia'] : 1);

// Convertir frecuencia lógica a meses por cuota
switch($frecuencia) {
    case 1: $meses_por_cuota = 1; break;   // mensual
    case 2: $meses_por_cuota = 2; break;   // bimestral
    case 3: $meses_por_cuota = 3; break;   // trimestral
    default: $meses_por_cuota = 1; break;  // por seguridad, mensual
}


// COK fijo 5% anual
$COK_anual = 0.05;

// TEM ajustada a la frecuencia
$TEM_freq = pow(1 + $TEA, $meses_por_cuota / 12) - 1;
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
$cronograma = [];
$saldo = $monto_financiar;

for ($mes = 1; $mes <= $plazo_meses; $mes++) {
    $saldo_inicial = $saldo;

    if ($mes <= $periodo_gracia) {
        // Periodo de gracia: los primeros $periodo_gracia meses
        $pg = 'T';
        $amortizacion = 0.0;
        $interes = 0.0;
        $cuota = 0.0;
        $seguro_desgrav = 0.0;
        $seguro_inmueble = 0.0;
    } else {
        // Todos los meses después del periodo de gracia
        $pg = 'S';

        // Interés y amortización según cuota fija
        $interes = $saldo * $TEM_total;
        $amortizacion = $cuota_fija_cap_int - $interes;
        if ($amortizacion < 0) $amortizacion = 0.0;

        // Seguros y cargos
        $seguro_desgrav = $saldo * $tasa_desgravamen;
        $seguro_inmueble = $seguro_inmueble_mensual;
        $cuota = $amortizacion + $interes + $seguro_desgrav + $seguro_inmueble + $comision + $portes + $gastos_adm;
    }

// Saldo final
$saldo_final = $saldo_inicial - $amortizacion;
if ($saldo_final < 0.01) $saldo_final = 0.0;

// Guardar en cronograma
$cronograma[] = [
    'periodo' => $mes,
    'saldo_inicial' => $saldo_inicial,
    'pg' => $pg,
    'amortizacion' => $amortizacion,
    'interes' => $interes,
    'seguro_desgrav' => $seguro_desgrav,
    'seguro_inmueble' => $seguro_inmueble,
    'saldo_final' => $saldo_final,
    'cuota_mensual' => $cuota
];

// Actualizar saldo
$saldo = $saldo_final;

// 👉 CORTAR AQUÍ si ya se pagó todo
if ($saldo <= 0.0) {
    break; // salir del for, ya no hay nada que calcular
}

}

// Formateo para mostrar
function fmt($x) {
    return number_format(round($x + 0.0000001, 2), 2, '.', ',');
}

/* =============================
   ARMAR HTML DEL PDF
   ============================ */
$freq_text = ($frecuencia == 1 ? "Mensual" : ($frecuencia == 2 ? "Bimestral" : "Trimestral"));

$html = "

<!doctype html>
<html>
<head>
<meta charset='utf-8'>
<style>
    body { font-family: Arial, sans-serif; font-size:12px; color:#222; }
    h2 { text-align:center; margin-bottom:6px; }
    .meta { margin-bottom:8px; }
    .meta p { margin:3px 0; }
    table { width:100%; border-collapse: collapse; font-size:11px; margin-top:8px; }
    th, td { border: 1px solid #bbb; padding:6px 8px; text-align:right; }
    th { background:#2c3e50; color:#fff; font-weight:600; text-align:center; }
    td:first-child, th:first-child { text-align:center; }
    .summary { margin-top:6px; }
</style>
</head>
<body>
<h2>Proyección de Cuotas - ".htmlspecialchars($vivienda['direccion'])."</h2>

<div class='meta'>
    <p><strong>Precio Original:</strong> S/ ".fmt($valor_vivienda)."</p>
    <p><strong>BBP aplicado:</strong> S/ ".fmt($bono_aplicado)."</p>
    <p><strong>Total BBP aplicado:</strong> S/ ".fmt($total_bbp)."</p>
    <p><strong>Cuota Inicial:</strong> S/ ".fmt($cuota_inicial)."</p>
    <p class='summary'><strong>Monto a financiar:</strong> S/ ".fmt($monto_financiar)." &nbsp;|&nbsp;
    <strong>TEA:</strong> ".number_format($TEA * 100, 2)."% &nbsp;|&nbsp;
    <strong>Plazo:</strong> {$plazo_meses} meses &nbsp;|&nbsp;
    <strong>Periodo de Gracia:</strong> {$periodo_gracia} meses</p>
    <p class='small'>
<strong>Supuestos:</strong>
Seguro Desgrav. mensual = ".($tasa_desgravamen * 100)."% ,
Seguro inmueble anual = ".($tasa_seguro_riesgo_anual * 100)."% ,
    Frecuencia de Pago = ".$freq_text."
</p>

</div>

<table>
<thead>
<tr>
<th>Periodo</th>
<th>Saldo Inicial (S/)</th>
<th>P.G</th>
<th>Amortización (S/)</th>
<th>Interés (S/)</th>
<th>Seguro Desgrav. (S/)</th>
<th>Seguro Inmueble (S/)</th>
<th>Saldo Final (S/)</th>
<th>Cuota Mensual (S/)</th>
</tr>
</thead>
<tbody>
";

foreach ($cronograma as $row) {
    $html .= "<tr>
        <td>{$row['periodo']}</td>
        <td>".fmt($row['saldo_inicial'])."</td>
        <td>{$row['pg']}</td>
        <td>".fmt($row['amortizacion'])."</td>
        <td>".fmt($row['interes'])."</td>
        <td>".fmt($row['seguro_desgrav'])."</td>
        <td>".fmt($row['seguro_inmueble'])."</td>
        <td>".fmt($row['saldo_final'])."</td>
        <td>".fmt($row['cuota_mensual'])."</td>
    </tr>";
}

$html .= "
</tbody>
</table>
</body>
</html>
";

/* ===========================
   GENERAR PDF (mostrar en navegador en nueva pestaña)
   ============================ */

$options = new Options();
$options->set('isRemoteEnabled', true);
$options->set('isHtml5ParserEnabled', true);

$dompdf = new Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper('A4', 'landscape');
$dompdf->render();

// Enviar PDF inline (NO forzar descarga)
$dompdf->stream("proyeccion_credito.pdf", ["Attachment" => false]);
exit;
