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

    // Datos adicionales del cliente
    $nombre   = trim($_POST["nombre"]);
    $dni      = trim($_POST["dni"]);
    $ingresos = trim($_POST["ingresos"]);
    $telefono = trim($_POST["telefono"]);
    $correo   = trim($_POST["correo"]);

    // Validar campos
    if (
        empty($username) || empty($password) || empty($rol) ||
        empty($nombre) || empty($dni) || empty($ingresos) ||
        empty($telefono) || empty($correo)
    ) {
        $error = "⚠️ Todos los campos son obligatorios.";
    } else {
        try {
            // 1️⃣ Encriptar contraseña
            $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

            // 2️⃣ Insertar en tabla usuarios
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

            // Obtener el ID del nuevo usuario
            $usuario_id = $stmt->fetchColumn();

            // 3️⃣ Insertar datos del cliente vinculados al usuario
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

            // Redirigir al login
            $_SESSION['mensaje'] = "✅ Usuario registrado correctamente.";
            header("Location: login.php");
            exit;

        } catch (PDOException $e) {
            if ($e->getCode() == 23505) { // username duplicado
                $error = "⚠️ El nombre de usuario ya existe. Elige otro.";
            } else {
                $error = "❌ Error en el registro: " . $e->getMessage();
            }
        }
    }
}
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
            text-align: center;
            margin-top: 15px;
            color: #3498db;
        }
    </style>
</head>
<body>

    <h2>📝 Registro de Usuario</h2>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>

    <form method="POST">
        <!-- Datos de usuario -->
        <label>Usuario:</label>
        <input type="text" name="username" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

<label>Rol:</label>
<input type="text" name="rol" value="cliente" readonly >

        <!-- Datos del cliente -->
        <label>Nombre completo:</label>
        <input type="text" name="nombre" required>

        <label>DNI:</label>
        <input type="text" name="dni" required>

        <label>Ingresos (mensuales):</label>
        <input type="number" name="ingresos" required>

        <label>Teléfono:</label>
        <input type="text" name="telefono" required>

        <label>Correo:</label>
        <input type="email" name="correo" required>

<div style="text-align: center;">
    <button>
        Registrar Usuario y Cliente
    </button>
</div>

    </form>

    <a href="login.php" class="back">⬅️ ¿Ya tienes cuenta? Inicia sesión</a>

</body>
</html>
