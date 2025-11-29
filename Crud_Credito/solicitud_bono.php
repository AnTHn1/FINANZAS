<?php
session_start();
require_once "../conexion.php";
require_once "../Auth/auth.php";
include("../menu.php");

// Verificar si hay vivienda seleccionada
if (!isset($_POST['vivienda_id'])) {
    header("Location: ../Crud_Viviendas/listar_viviendas.php");
    exit();
}

$vivienda_id = $_POST['vivienda_id'];

// Obtener información de la vivienda
$stmt_viv = $pdo->prepare("SELECT * FROM viviendas WHERE id = :id");
$stmt_viv->execute([':id' => $vivienda_id]);
$vivienda = $stmt_viv->fetch(PDO::FETCH_ASSOC);

// Obtener datos del cliente vinculado al usuario logueado
$usuario_id = $_SESSION['usuario_id'];
$stmt_cli = $pdo->prepare("SELECT nombre, correo, ingresos FROM clientes WHERE usuario_id = :usuario_id");
$stmt_cli = $stmt_cli ?? null;
$stmt_cli = $pdo->prepare("SELECT nombre, correo, ingresos FROM clientes WHERE usuario_id = :usuario_id");
$stmt_cli->execute([':usuario_id' => $usuario_id]);
$cliente = $stmt_cli->fetch(PDO::FETCH_ASSOC);

// Obtener bono BBP según rangos desde la tabla bbp_rangos
$stmtBbp = $pdo->prepare("SELECT bono FROM bbp_rangos WHERE :precio BETWEEN precio_min AND precio_max LIMIT 1");
$stmtBbp->execute([':precio' => $vivienda['precio']]);
$bbp_row = $stmtBbp->fetch(PDO::FETCH_ASSOC);
$bbp_base = $bbp_row ? (float)$bbp_row['bono'] : 0.0;

// Si quieres mostrar un TNA/TCEA ejemplo puedes dejarlo, pero aquí lo dejamos en blanco hasta que se ingrese TEA.
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Bono Buen Pagador</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 30px;
            background-color: #f5f5f5;
        }

        h1 {
            text-align: center;
            color: #2c3e50;
        }

        .contenedor {
            display: flex;
            justify-content: center;
            align-items: flex-start;
            gap: 40px;
            margin-top: 40px;
            flex-wrap: wrap;
        }

        .info-vivienda, .formulario {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 0 10px rgba(0,0,0,0.15);
            width: 420px;
        }

        .info-vivienda img {
            width: 100%;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .info-vivienda h3 {
            color: #34495e;
            margin-bottom: 10px;
        }

        .formulario h3 {
            color: #2980b9;
            text-align: center;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
        }

        input, select {
            width: 100%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 6px;
            border: 1px solid #ccc;
            box-sizing: border-box;
        }

        .fila { display: flex; gap: 10px; }

        .boton {
            margin-top: 20px;
            width: 100%;
            background: #27ae60;
            color: white;
            padding: 12px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
        }

        .boton:hover { background: #219150; }
        .boton-volver {
    display: block;               /* Igual que width:100% del botón verde */
    width: 94.5%;
    margin-top: 20px;
    background: #2c3e50;          /* Azul oscuro */
    color: white;
    padding: 12px;
    border-radius: 8px;
    text-align: center;           /* Centra el texto */
    font-size: 16px;
    text-decoration: none;        /* Quitar subrayado */
    cursor: pointer;
    transition: background 0.25s ease;
}

.boton-volver:hover {
    background: #1f2d3a;
}


        .precio { margin-top: 10px; font-size: 15px; }

        .precio strong { color: #2c3e50; }

        .nota { font-size: 13px; color: #666; margin-top:6px; }

        .mini { font-size: 12px; color:#666; }
        
    </style>
</head>
<body>

<h1>Bienvenido al Bono Buen Pagador</h1>

<div class="contenedor">
    <!-- LADO IZQUIERDO: información de vivienda -->
    <div class="info-vivienda">
        <?php if (!empty($vivienda['imagen'])): ?>
            <img src="../<?= htmlspecialchars($vivienda['imagen']) ?>" alt="Imagen de la vivienda">
        <?php else: ?>
            <img src="../img/default.jpg" alt="Sin imagen disponible">
        <?php endif; ?>

        <h3><?= htmlspecialchars($vivienda['direccion'] ?? 'Vivienda seleccionada') ?></h3>

        <div class="precio">
            <p><strong>Precio original:</strong> S/ <?= number_format($vivienda['precio'], 2) ?></p>
            <p><strong>BBP según rango (base):</strong> S/ <?= number_format($bbp_base, 2) ?></p>
            <p><strong>Precio final (estimado):</strong> S/ <?= number_format($vivienda['precio'] - $bbp_base, 2) ?></p>
        </div>

        <div class="nota">
            <p class="mini">El Bono del Buen Pagador (BBP) se determina según rangos de precio de la vivienda.</p>
        </div>
        <!-- Tabla de TEA por bancos -->
<section style="margin-top:25px;">
    <h3 style="color:#34495e; margin-bottom:10px;">Tasas de Interés Referenciales</h3>

    <table style="width:100%; border-collapse:collapse; font-size:14px;">
        <thead>
            <tr style="background:#ecf0f1;">
                <th style="border:1px solid #ccc; padding:8px; text-align:left;">Banco</th>
                <th style="border:1px solid #ccc; padding:8px; text-align:center;">TEA (%)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="border:1px solid #ccc; padding:8px;">Scotiabank</td>
                <td style="border:1px solid #ccc; padding:8px; text-align:center;">8.98</td>
            </tr>
            <tr>
                <td style="border:1px solid #ccc; padding:8px;">Interbank</td>
                <td style="border:1px solid #ccc; padding:8px; text-align:center;">9.27</td>
            </tr>
            <tr>
                <td style="border:1px solid #ccc; padding:8px;">Banco de Crédito</td>
                <td style="border:1px solid #ccc; padding:8px; text-align:center;">9.43</td>
            </tr>
            <tr>
                <td style="border:1px solid #ccc; padding:8px;">BBVA Perú</td>
                <td style="border:1px solid #ccc; padding:8px; text-align:center;">9.02</td>
            </tr>
        </tbody>
    </table>

    <p style="font-size:12px; color:#666; margin-top:5px;">
        *Escoja la TEA de su banco.
    </p>
</section>

    </div>

    <!-- LADO DERECHO: formulario -->
    <div class="formulario">
        <h3>Ingrese los datos faltantes</h3>
        <form method="POST" action="guardar_solicitud_bono.php" id="form-bono">

            <input type="hidden" name="vivienda_id" value="<?= $vivienda_id ?>">

            <!-- campos calculados que enviaremos -->
            <input type="hidden" name="total_bbp" id="hidden_total_bbp" value="">
            <input type="hidden" name="monto_financiar" id="hidden_monto_financiar" value="">
            <input type="hidden" name="porcentaje_cuota" id="hidden_porcentaje_cuota" value="">
            <input type="hidden" name="cuota_mensual" id="hidden_cuota_mensual" value="">
            <input type="hidden" name="tcea" id="hidden_tcea" value="">

            <!-- NUEVO CONTENIDO 2: enviar tambien la frecuencia con nombre 'frecuencia' y COK -->
            <input type="hidden" name="frecuencia" id="hidden_frecuencia" value="">
            <input type="hidden" name="COK" id="hidden_COK" value="0.05">
            <!-- FIN NUEVO CONTENIDO 2 -->

            <label>Nombre:</label>
            <input type="text" name="nombre" value="<?= htmlspecialchars($cliente['nombre'] ?? '') ?>" readonly>

            <label>Correo electrónico:</label>
            <input type="email" name="correo" value="<?= htmlspecialchars($cliente['correo'] ?? '') ?>" readonly>

            <label>Ingresos mensuales:</label>
            <input type="number" name="ingresos" value="<?= htmlspecialchars($cliente['ingresos'] ?? '') ?>" readonly>

            <!-- Valor vivienda -->
            <label>Valor de la vivienda:</label>
            <input type="number" id="valor_vivienda" value="<?= htmlspecialchars($vivienda['precio']) ?>" readonly>

            <!-- Apoyo habitacional -->
            <label>¿Ha recibido apoyo habitacional?</label>
            <input type="text" value="NO" readonly>

<!-- Cuota inicial -->
<label>Cuota Inicial:
    <span title="Valor de la cuota inicial debe ser mínimo del 7.5% del valor de vivienda.">🛈</span>
</label>

<input type="number" id="cuota_inicial" name="cuota_inicial" required min="0" step="0.01">

<p id="cuota_error" style="color:red; font-size:12px; display:none;">
    La cuota inicial debe ser por lo menos el 7.5% del valor de la vivienda.
</p>


            <!-- % cuota inicial -->
            <label>% de cuota inicial:</label>
            <input type="text" id="porcentaje_cuota" readonly>

            <!-- BBP (base desde DB) -->
            <label>Bono del Buen Pagador (base):</label>
            <input type="number" id="bbp" value="<?= htmlspecialchars($bbp_base) ?>" readonly>

            <!-- Vivienda sostenible -->
            <label>¿La vivienda es sostenible?</label>
            <select id="sostenible" name="sostenible">
                <option value="1" selected>Sí</option>
                <option value="0">No</option>
            </select>

            <!-- Total BBP -->
            <label>Total BBP:</label>
            <input type="number" id="total_bbp" readonly>

            <!-- Monto a financiar -->
            <label>Monto a financiar:</label>
            <input type="number" id="monto_financiar" readonly>

            <!-- TEA -->
            <label>Tasa Efectiva Anual (%):</label>
            <input type="number" id="tea" name="tea" step="0.0001" required>

            <!-- Seguros (fijos) -->
            <label>Seguro Desgravamen Mensual (0.04%):</label>
            <input type="text" value="0.04%" readonly>

            <label>Seguro Inmueble Anual (0.30%):</label>
            <input type="text" value="0.30%" readonly>

            <!-- Plazo -->
            <label>Plazo del préstamo (60 a 300 meses):</label>
            <input type="number" id="plazo_meses" name="plazo_meses" min="60" max="300" required>

            <!-- Periodo gracia -->
            <label>Periodo de gracia (hasta 12 meses):</label>
            <input type="number" id="periodo_gracia" name="periodo_gracia" min="0" max="12" required>

            <!-- TCEA -->
            <label>TCEA:</label>
            <input type="text" id="tcea" readonly>

<!-- Frecuencia de pago -->
<label>Frecuencia de pago:</label>
<select id="frecuencia_pago" name="frecuencia_pago">
    <option value="1" selected>Mensual</option>
    <option value="2">Bimestral</option>
    <option value="3">Trimestral</option>
</select>


            <button type="submit" class="boton">Proceder</button>
            <a href="../Crud_Viviendas/listar_viviendas.php" class="boton-volver">
    ⬅ Volver al listado de viviendas
</a>

        </form>
    </div>
</div>

<script>
    
document.addEventListener("DOMContentLoaded", function () {

    const valorVivienda = <?= $vivienda['precio']; ?>; 
    const cuotaInput = document.getElementById("cuota_inicial");
    const errorMsg = document.getElementById("cuota_error");

    // 7.5% requerido
    const cuotaMinima = valorVivienda * 0.075;

    function validarCuota() {
        const valor = parseFloat(cuotaInput.value);

        if (isNaN(valor) || valor < cuotaMinima) {
            errorMsg.style.display = "block";
            cuotaInput.style.border = "2px solid red";
        } else {
            errorMsg.style.display = "none";
            cuotaInput.style.border = "2px solid #4CAF50";
        }
    }

    // Validación en tiempo real
    cuotaInput.addEventListener("input", validarCuota);

});

  
(function(){
    const toFloat = v => parseFloat(String(v).replace(/,/g, '').replace('%','')) || 0;
    const fmt2 = v => Number(v).toFixed(2);

    const valorEl = document.getElementById('valor_vivienda');
    const cuotaInicialEl = document.getElementById('cuota_inicial');
    const porcentajeEl = document.getElementById('porcentaje_cuota');
    const bbpEl = document.getElementById('bbp');
    const sostenibleEl = document.getElementById('sostenible');
    const totalBbpEl = document.getElementById('total_bbp');
    const montoFinEl = document.getElementById('monto_financiar');
    const teaEl = document.getElementById('tea');
    const plazoEl = document.getElementById('plazo_meses');
    const cuotaMensualEl = document.getElementById('cuota_mensual');
    const tceaEl = document.getElementById('tcea');

    // hidden elements
    const hidden_total_bbp = document.getElementById('hidden_total_bbp');
    const hidden_monto_financiar = document.getElementById('hidden_monto_financiar');
    const hidden_cuota_mensual = document.getElementById('hidden_cuota_mensual');
    const hidden_tcea = document.getElementById('hidden_tcea');
    const hidden_porcentaje_cuota = document.getElementById('hidden_porcentaje_cuota');

    // NUEVO CONTENIDO 2: nuevos hidden
    const hidden_frecuencia = document.getElementById('hidden_frecuencia');
    const hidden_COK = document.getElementById('hidden_COK');
    // FIN NUEVO CONTENIDO 2

function actualizarTodo() {

    const valor = toFloat(valorEl.value);
    const cuota = toFloat(cuotaInicialEl.value);
    const bbp_base = toFloat(bbpEl.value);
    let n = parseInt(plazoEl.value) || 0; // plazo original
    let tea = toFloat(teaEl.value) / 100;

    // === PERIODO DE GRACIA ===
    let gracia = parseInt(document.getElementById('periodo_gracia').value) || 0;

    // plazo real después de la gracia
    let n_real = n - gracia;
    if (n_real < 1) n_real = 1;

    if(valor <= 0) return;

    // % cuota inicial
    const pct = (cuota / valor) * 100;
    porcentajeEl.value = pct.toFixed(2) + "%";
    hidden_porcentaje_cuota.value = pct.toFixed(6);

    // Total BBP
    const sostenible = sostenibleEl.value === "1";
    const incentivoSostenible = sostenible ? 10000 : 0;
    const total_bbp = bbp_base + incentivoSostenible;
    totalBbpEl.value = fmt2(total_bbp);
    hidden_total_bbp.value = total_bbp;

    // Monto a financiar
    let mf = valor - cuota - total_bbp;
    if (mf < 0) mf = 0;
    montoFinEl.value = fmt2(mf);
    hidden_monto_financiar.value = fmt2(mf);

    // === FRECUENCIA ===
    let frecuencia = parseInt(document.getElementById('frecuencia_pago').value) || 1;
    let meses_por_cuota = frecuencia;

    // Guardar frecuencia en hidden para que el servidor la reciba con nombre 'frecuencia'
    hidden_frecuencia.value = frecuencia; // NUEVO CONTENIDO 2

    // Cuotas totales según frecuencia con plazo REAL
    let n_cuotas = Math.ceil(n_real / meses_por_cuota);

    // === CALCULAR TEM REAL ===
    // TEM = (1 + TEA)^(1/12) - 1
    let tem = tea > 0 ? Math.pow(1 + tea, 1/12) - 1 : 0;

    // === TASA POR PERIODO SEGÚN FRECUENCIA ===
    // Si es mensual → 1 mes
    // Si es bimestral → TEM compuesto 2 meses
    // Si es trimestral → TEM compuesto 3 meses
    let i_freq = Math.pow(1 + tem, meses_por_cuota) - 1;

    // === CALCULAR CUOTA ===
    let cuota_mensual = 0;
    if (mf > 0 && n_cuotas > 0 && i_freq > 0) {
        cuota_mensual = mf * (i_freq / (1 - Math.pow(1 + i_freq, -n_cuotas)));
    }

    cuotaMensualEl.value = fmt2(cuota_mensual);
    hidden_cuota_mensual.value = fmt2(cuota_mensual);

    // === CALCULAR TCEA ===
    let tcea = 0;
    if (cuota_mensual > 0) {

        // seguros ajustados por frecuencia
        let seg_des = mf * 0.0004 * meses_por_cuota;
        let seg_riesgo = (mf * 0.003 / 12) * meses_por_cuota;

        let costos_mensuales = cuota_mensual + seg_des + seg_riesgo;
        let costo_total = (costos_mensuales * n_cuotas) - mf;

        // usar plazo REAL (n_real) para la TCEA
        tcea = Math.pow(1 + (costo_total / mf), 12 / n_real) - 1;
    }

    tceaEl.value = (tcea * 100).toFixed(3) + "%";
    hidden_tcea.value = (tcea * 100).toFixed(6);

    // NUEVO CONTENIDO 2: actualizar valor de COK en hidden por si lo cambias mas adelante
    hidden_COK.value = hidden_COK.value || "0.05";
    // FIN NUEVO CONTENIDO 2
}

// EVENTOS
['input','change'].forEach(ev=>{
    cuotaInicialEl.addEventListener(ev, actualizarTodo);
    sostenibleEl.addEventListener(ev, actualizarTodo);
    teaEl.addEventListener(ev, actualizarTodo);
    plazoEl.addEventListener(ev, actualizarTodo);
    document.getElementById('frecuencia_pago').addEventListener(ev, actualizarTodo);
});

[valorEl, cuotaInicialEl, bbpEl, sostenibleEl, teaEl, plazoEl].forEach(el => {
    el.addEventListener('input', actualizarTodo);
    el.addEventListener('change', actualizarTodo);
});

document.getElementById('frecuencia_pago').addEventListener('change', actualizarTodo);

window.addEventListener('DOMContentLoaded', actualizarTodo);





(function(){
    const form = document.getElementById('form-bono');
    const btn = form.querySelector('button[type="submit"]');
    const cuotaError = document.getElementById('cuota_error');
    const cuotaInput = document.getElementById('cuota_inicial');

    form.addEventListener('submit', async function(e){
        e.preventDefault(); // evita envío normal

        // Ejecutar una vez más el cálculo para asegurar hidden actualizados
        try {
            actualizarTodo(); // NUEVO CONTENIDO 2: forzar actualización antes de enviar
        } catch (err) {
            console.warn('Error al forzar actualizar:', err);
        }

        // Validación mínima: si existe error de cuota, no enviar
        if (cuotaError && cuotaError.style.display !== 'none') {
            alert('Corrige la cuota inicial: debe ser al menos el 7.5% del valor de la vivienda.');
            cuotaInput.focus();
            return;
        }

        // Validación: asegurar que monto_financiar y cuota_inicial no estén vacíos
        const hiddenMf = document.getElementById('hidden_monto_financiar').value;
        const hiddenCuota = parseFloat(document.getElementById('cuota_inicial').value) || 0;
        if (hiddenMf === "" || isNaN(parseFloat(hiddenMf))) {
            alert('El monto a financiar no está calculado. Revisa los datos.');
            return;
        }
        if (hiddenCuota <= 0) {
            alert('Ingrese una cuota inicial válida.');
            cuotaInput.focus();
            return;
        }

        // Evitar doble envío
        btn.disabled = true;
        const originalText = btn.innerHTML;
        btn.innerHTML = 'Enviando...';

        try {
            // DEBUG: mostrar FormData para verificar exactamente qué se enviará
            const formDataForDebug = new FormData(form);
            // Logear pares clave/valor para debugging (se puede eliminar luego)
            console.groupCollapsed('DEBUG FormData a enviar');
            for (let pair of formDataForDebug.entries()) {
                console.log(pair[0]+ ': ' + pair[1]);
            }
            console.groupEnd();

            const resp = await fetch(form.action, {
                method: 'POST',
                body: formDataForDebug,
                credentials: 'same-origin'
            });

            if (!resp.ok) {
                // si el servidor devuelve error http
                const text = await resp.text().catch(()=>null);
                throw new Error('Error en el servidor. ' + (text ? (' Detalle: '+text) : ''));
            }

             //si guardar_solicitud_bono.php devuelve JSON con flag exitoso:
             const json = await resp.json().catch(()=>null);
             if(json && json.success !== true) throw new Error(json.message || 'No fue posible guardar.');

            // Redirigir a la página que quieres
            window.location.href = "../Crud_Viviendas/listar_viviendas.php";

        } catch (err) {
            console.error(err);
            alert('Error al enviar el formulario: ' + (err.message || 'Intenta nuevamente.'));
            btn.disabled = false;
            btn.innerHTML = originalText;
        }
    });
})();


})();

</script>

</body>
</html>
