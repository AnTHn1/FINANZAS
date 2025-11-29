<?php
require_once "../Auth/auth.php"; // protege la página
include("../conexion.php");
include("../menu.php");

if (isset($_GET["id"])) {
    $id = $_GET["id"];

    try {
        // 1️⃣ Obtener la ruta de la imagen antes de eliminar
        $stmt = $pdo->prepare("SELECT imagen FROM viviendas WHERE id = :id");
        $stmt->execute([":id" => $id]);
        $vivienda = $stmt->fetch(PDO::FETCH_ASSOC);

        // 2️⃣ Eliminar el registro de la base de datos
        $stmt = $pdo->prepare("DELETE FROM viviendas WHERE id = :id");
        $stmt->execute([":id" => $id]);

        // 3️⃣ Si tenía imagen, eliminar el archivo físico
        if ($vivienda && !empty($vivienda["imagen"])) {
            $ruta_imagen = "../" . $vivienda["imagen"];

            if (file_exists($ruta_imagen)) {
                unlink($ruta_imagen);
            }
        }

        // 4️⃣ Redirigir al listado
        header("Location: listar_viviendas.php");
        exit;

    } catch (PDOException $e) {

        echo '
        <div style="
            max-width: 500px;
            margin: 80px auto;
            background: #ffe6e6;
            border-left: 6px solid #e74c3c;
            padding: 25px;
            border-radius: 10px;
            font-family: Arial, sans-serif;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        ">
            <h2 style="color:#c0392b; margin-bottom: 10px;">❌ No se pudo eliminar la vivienda</h2>

            <p style="color:#333; font-size:16px; margin-bottom: 20px;">
                Esta vivienda no puede eliminarse porque ya tiene solicitudes registradas.
            </p>

            <a href="listar_viviendas.php" style="
                display:inline-block;
                padding: 10px 20px;
                background: #3498db;
                color: white;
                text-decoration:none;
                border-radius: 6px;
                font-weight:bold;
            ">
                ⬅ Volver a la lista
            </a>
        </div>
        ';
    }

} else {
    echo '
    <div style="
        max-width: 500px;
        margin: 80px auto;
        background: #fff3cd;
        border-left: 6px solid #f1c40f;
        padding: 25px;
        border-radius: 10px;
        font-family: Arial, sans-serif;
        text-align: center;
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    ">
        <h2 style="color:#b7950b; margin-bottom: 10px;">⚠ ID no válido</h2>

        <p style="color:#555; font-size:16px; margin-bottom: 20px;">
            No se recibió un ID válido para eliminar.
        </p>

        <a href="listar_viviendas.php" style="
            display:inline-block;
            padding: 10px 20px;
            background: #3498db;
            color: white;
            text-decoration:none;
            border-radius: 6px;
            font-weight:bold;
        ">
            ⬅ Volver a la lista
        </a>
    </div>
    ';
}
?>
