<?php
require_once "Auth/auth.php"; // protege la página
include "conexion.php";
include("menu.php");

// Verificar que el usuario esté logueado
if (!isset($_SESSION['usuario_id'])) {
    header("Location: Auth/login.php");
    exit;
}

$usuario_id = $_SESSION['usuario_id'];

// Conexión PDO
$pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;", $user, $password, [
    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
]);

// Procesar actualización
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $nombre   = $_POST['nombre'] ?? '';
    $dni      = $_POST['dni'] ?? '';
    $ingresos = $_POST['ingresos'] ?? 0;
    $telefono = $_POST['telefono'] ?? '';
    $correo   = $_POST['correo'] ?? '';

    try {
$stmt = $pdo->prepare("UPDATE clientes 
    SET nombre = :nombre, dni = :dni, ingresos = :ingresos,
        telefono = :telefono, correo = :correo
    WHERE usuario_id = :usuario_id");

$stmt->execute([
    ":nombre" => $nombre,
    ":dni" => $dni,
    ":ingresos" => $ingresos,
    ":telefono" => $telefono,
    ":correo" => $correo,
    ":usuario_id" => $usuario_id
]);


        $mensaje = "Datos actualizados correctamente.";
    } catch (PDOException $e) {
        $mensaje = "Error al actualizar: " . $e->getMessage();
    }
}

// Obtener los datos del usuario actual
$stmt = $pdo->prepare("SELECT * FROM clientes WHERE usuario_id = :usuario_id");
$stmt->execute([":usuario_id" => $usuario_id]);
$cliente = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$cliente) {
    die("No se encontraron datos del usuario.");
}
?>

<h2 class="titulo-perfil"> Mi Perfil</h2>

<?php if (isset($mensaje)): ?>
    <p class="mensaje-estado"><?= htmlspecialchars($mensaje) ?></p>
<?php endif; ?>

<div class="perfil-contenedor">

    <form method="post" class="perfil-card">

        <div class="form-grupo">
            <label>Nombre</label>
            <input type="text" name="nombre" 
                value="<?= htmlspecialchars($cliente['nombre']) ?>" required>
        </div>

        <div class="form-grupo">
            <label>DNI</label>
            <input type="text" name="dni" 
                value="<?= htmlspecialchars($cliente['dni']) ?>" required>
        </div>

        <div class="form-grupo">
            <label>Ingresos</label>
            <input type="number" name="ingresos" 
                value="<?= htmlspecialchars($cliente['ingresos']) ?>" required>
        </div>

        <div class="form-grupo">
            <label>Teléfono</label>
            <input type="text" name="telefono" 
                value="<?= htmlspecialchars($cliente['telefono']) ?>">
        </div>

        <div class="form-grupo full">
            <label>Correo</label>
            <input type="email" name="correo" 
                value="<?= htmlspecialchars($cliente['correo']) ?>">
        </div>

        <div class="botonera">
            <button type="submit" class="btn-guardar">💾 Guardar cambios</button>

<a href="Crud_Viviendas/viviendas_financiadas.php" class="btn-viviendas">
    Ver Viviendas Financiadas
</a>
        </div>

    </form>

</div>

<style>
    body {
        font-family: "Segoe UI", sans-serif;
        background: #eef1f7 !important;
    }

.titulo-perfil {
    text-align: center;      /* Centra el texto */
    margin: 20px auto 10px;  /* Quita el margen izquierdo y lo centra */
    color: #2c3e50;
    font-size: 26px;
}


    .mensaje-estado {
    text-align: center;
    margin-top: 15px;
    font-weight: bold;
    color: #2c3e50;
    }

    .perfil-contenedor {
        width: 100%;
        display: flex;
        justify-content: center;
        margin-top: 20px;
    }

    .perfil-card {
        width: 90%;
        max-width: 600px;
        background: #d7dceb;
        padding: 25px;
        border-radius: 15px;
        box-shadow: 0 6px 16px rgba(0,0,0,0.08);
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 18px;
    }

    .form-grupo {
        display: flex;
        flex-direction: column;
    }

    .form-grupo.full {
        grid-column: span 2;
    }

    label {
        margin-bottom: 6px;
        font-weight: 600;
        color: #2c3e50;
    }

    input {
        padding: 12px;
        border-radius: 8px;
        border: 1px solid #c5c9d8;
        background: white;
        font-size: 15px;
        transition: .3s border-color;
    }

    input:focus {
        border-color: #7a8bed;
        outline: none;
    }

    .botonera {
        grid-column: span 2;
        display: flex;
        justify-content: space-between;
        margin-top: 10px;
    }

    .btn-guardar {
        background: #7a8bed;
        color: white;
        border: none;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: bold;
        cursor: pointer;
        transition: .3s;
    }

    .btn-guardar:hover {
        background: #6877db;
    }

    .btn-viviendas {
        background: #9bbcf8;
        color: #1a2e6b;
        padding: 12px 25px;
        border-radius: 8px;
        font-weight: bold;
        text-decoration: none;
        transition: .3s;
    }

    .btn-viviendas:hover {
        background: #7aa7f6;
    }
</style>
