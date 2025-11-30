<?php
// Calcula cuota mensual usando método francés
function cuotaFrances($capital, $tasaMensual, $plazo) {
    return $capital * ($tasaMensual * pow(1 + $tasaMensual, $plazo)) / (pow(1 + $tasaMensual, $plazo) - 1);
}

// Capital vivo después de cada cuota
function capitalVivo($capitalAnterior, $amortizacion) {
    return $capitalAnterior - $amortizacion;
}

// Capital amortizado acumulado
function capitalAmortizado($prestamo, $capitalVivo) {
    return $prestamo - $capitalVivo;
}

// VAN
function valorActualNeto($flujos, $tasa, $inversion) {
    $van = -$inversion;
    foreach ($flujos as $i => $flujo) {
        $van += $flujo / pow(1 + $tasa, $i + 1);
    }
    return $van;
}

// TIR
function calcularTIR($flujos, $inversion, $tasaInicial = 0.1, $epsilon = 0.0001, $maxIter = 1000) {
    $tasa = $tasaInicial;
    for ($i = 0; $i < $maxIter; $i++) {
        $f = -$inversion;
        $df = 0;
        foreach ($flujos as $j => $flujo) {
            $f += $flujo / pow(1 + $tasa, $j + 1);
            $df -= ($j + 1) * $flujo / pow(1 + $tasa, $j + 2);
        }
        if (abs($f) < $epsilon) return $tasa;
        if (abs($df) < $epsilon) throw new Exception("Derivada cerca de cero");
        $tasa -= $f / $df;
    }
    throw new Exception("No se encontró la TIR en $maxIter iteraciones");
}
?>
