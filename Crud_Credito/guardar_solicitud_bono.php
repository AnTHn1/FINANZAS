<?php
session_start();
require_once "../conexion.php";
require_once "../Auth/auth.php";

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: ../Auth/login.php");
    exit();
}

// Verificar que llegaron los datos del formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $usuario_id = $_SESSION['usuario_id'];
    $vivienda_id = $_POST['vivienda_id'] ?? null;
    $plazo_meses = $_POST['plazo_meses'] ?? null;
    $periodo_gracia = $_POST['periodo_gracia'] ?? null;

    // TNA fija o se podría calcular según lógica adicional
    $tna = 0.08; // por ejemplo, 8% anual
    $fecha_solicitud = date('Y-m-d');

    if (!$vivienda_id || !$plazo_meses) {
        echo "⚠️ Datos incompletos.";
        exit();
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO solicitud_bono (usuario_id, vivienda_id, tna, plazo_meses, periodo_gracia, fecha_solicitud)
                               VALUES (:usuario_id, :vivienda_id, :tna, :plazo_meses, :periodo_gracia, :fecha_solicitud)");
        $stmt->execute([
            ':usuario_id' => $usuario_id,
            ':vivienda_id' => $vivienda_id,
            ':tna' => $tna,
            ':plazo_meses' => $plazo_meses,
            ':periodo_gracia' => $periodo_gracia,
            ':fecha_solicitud' => $fecha_solicitud
        ]);

        header("Location: listar_solicitudes.php");
        exit();
    } catch (PDOException $e) {
        echo "❌ Error al guardar la solicitud: " . $e->getMessage();
    }
} else {
    echo "⚠️ Método no permitido.";
}
