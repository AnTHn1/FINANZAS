<?php 
session_start();
require_once "../conexion.php"; // Conexión con PDO

$error = "";

// =====================================================
//   PROCESO DE REGISTRO (Usuario + Cliente vinculado)
// =====================================================
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Datos de usuario
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);
    $rol      = trim($_POST["rol"]);

    // Datos del cliente
    $nombre   = trim($_POST["nombre"]);
    $dni      = trim($_POST["dni"]);
    $ingresos = trim($_POST["ingresos"]);
    $telefono = trim($_POST["telefono"]);
    $correo   = trim($_POST["correo"]);

    // ===========================
    // VALIDACIONES
    // ===========================

    // 1️⃣ Campos obligatorios
    if (
        empty($username) || empty($password) || empty($rol) ||
        empty($nombre) || empty($dni) || empty($ingresos) ||
        empty($telefono) || empty($correo)
    ) {
        $error = "⚠️ Todos los campos son obligatorios.";
    }
    // 2️⃣ username máximo 32 caracteres
    elseif (strlen($username) > 32) {
        $error = "⚠️ El nombre de usuario no puede exceder 32 caracteres.";
    }
    // 3️⃣ DNI debe ser numérico y exactamente 8 dígitos
    elseif (!ctype_digit($dni) || strlen($dni) !== 8) {
        $error = "⚠️ El DNI debe contener exactamente 8 dígitos numéricos.";
    }
    // 4️⃣ Teléfono debe ser numérico y exactamente 9
    elseif (!ctype_digit($telefono) || strlen($telefono) !== 9) {
        $error = "⚠️ El teléfono debe contener exactamente 9 dígitos numéricos.";
    }
    // 5️⃣ Correo válido
    elseif (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
        $error = "⚠️ Ingrese un correo electrónico válido.";
    }

    // 🚨 SI HAY ERROR, SALIR
    if (!empty($error)) {
        goto fin;
    }

    try {

        // Encriptar contraseña
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Insertar usuario
        $stmt = $pdo->prepare("
            INSERT INTO usuarios (username, password, rol) 
            VALUES (:username, :password, :rol)
            RETURNING id
        ");
        $stmt->execute([
            ':username' => $username,
            ':password' => $hashedPassword,
            ':rol'      => $rol
        ]);

        $usuario_id = $stmt->fetchColumn();

        // Insertar cliente
        $stmt2 = $pdo->prepare("
            INSERT INTO clientes (usuario_id, nombre, dni, ingresos, telefono, correo)
            VALUES (:usuario_id, :nombre, :dni, :ingresos, :telefono, :correo)
        ");
        $stmt2->execute([
            ':usuario_id' => $usuario_id,
            ':nombre'     => $nombre,
            ':dni'        => $dni,
            ':ingresos'   => $ingresos,
            ':telefono'   => $telefono,
            ':correo'     => $correo
        ]);

        $_SESSION['mensaje'] = "🎉 Usuario registrado correctamente.";
        header("Location: login.php");
        exit;

    } catch (PDOException $e) {

        // DNI duplicado
        if ($e->getCode() == 23505 && strpos($e->getMessage(), 'clientes_dni') !== false) {
            $error = "⚠️ El DNI ya está registrado.";
        }
        // Username duplicado
        elseif ($e->getCode() == 23505 && strpos($e->getMessage(), 'usuarios_username') !== false) {
            $error = "⚠️ El usuario ya existe.";
        }
        // Correo duplicado
        elseif ($e->getCode() == 23505 && strpos($e->getMessage(), 'clientes_correo') !== false) {
            $error = "⚠️ El correo ya está registrado.";
        }
        else {
            $error = "❌ Error: " . $e->getMessage();
        }
    }
}

fin:
$username = $username ?? "";
$nombre   = $nombre ?? "";
$dni      = $dni ?? "";
$ingresos = $ingresos ?? "";
$telefono = $telefono ?? "";
$correo   = $correo ?? "";
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Registro de Usuario y Cliente</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f8f9fa;
            margin: 20px;
        }
        h2 {
            text-align: center;
            color: #2c3e50;
        }
        form {
            background: #fff;
            padding: 25px;
            border-radius: 8px;
            max-width: 420px;
            margin: 0 auto;
            box-shadow: 0 2px 6px rgba(0,0,0,0.1);
        }
        label {
            display: block;
            margin-top: 10px;
            font-weight: bold;
            color: #34495e;
        }
        input, select {
            width: 95%;
            padding: 8px;
            margin-top: 5px;
            border-radius: 5px;
            border: 1px solid #ccc;
        }
        button {
            width: 80%;
            margin-top: 15px;
            padding: 10px;
            background: #0056b3;
            color: #fff;
            border: none;
            border-radius: 5px;
            cursor: pointer;
        }
        button:hover {
            background: #298bccff;
        }
        .error {
            color: red;
            text-align: center;
            margin-bottom: 10px;
        }
.back {
    display: block;
    width: fit-content;
    margin: 15px auto 0;
    text-align: center;
}

    </style>
</head>
<body>

<h2>📝 Registro de Usuario</h2>


<?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

<form method="POST">
<!-- Usuario -->
<label>Usuario:</label>
<input type="text" name="username" maxlength="32" 
       value="<?php echo htmlspecialchars($username); ?>" required>

<label>Contraseña:</label>
<input type="password" name="password" required>

<label>Rol:</label>
<input type="text" name="rol" value="cliente" readonly>

<!-- Cliente -->
<label>Nombre completo:</label>
<input type="text" name="nombre" 
       value="<?php echo htmlspecialchars($nombre); ?>" required>

<label>DNI (8 dígitos):</label>
<input type="text" name="dni" minlength="8" maxlength="8"
       pattern="[0-9]{8}"
       value="<?php echo htmlspecialchars($dni); ?>" required>

<label>Ingresos mensuales:</label>
<input type="number" name="ingresos" 
       value="<?php echo htmlspecialchars($ingresos); ?>" required>

<label>Teléfono (9 dígitos):</label>
<input type="text" name="telefono" minlength="9" maxlength="9"
       pattern="[0-9]{9}"
       value="<?php echo htmlspecialchars($telefono); ?>" required>

<label>Correo:</label>
<input type="email" name="correo"
       value="<?php echo htmlspecialchars($correo); ?>" required>

    <div style="text-align: center;">
        <button>Registrarme</button>
    </div>
</form>

    <a href="login.php" class="back">⬅️ ¿Ya tienes cuenta? Inicia sesión</a>

</body>
</html>
