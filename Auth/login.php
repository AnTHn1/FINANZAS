<?php
session_start();
require_once "../conexion.php"; // tu conexión PDO
include("../menu.php");
$error = "";

// Procesar formulario
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = trim($_POST["username"]);
    $password = trim($_POST["password"]);

    if (empty($username) || empty($password)) {
        $error = "Todos los campos son obligatorios.";
    } else {
        // Buscar usuario en la BD
        $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
        $stmt->execute([':username' => $username]);
        $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($usuario) {
            // Verificar contraseña
            if (password_verify($password, $usuario['password'])) {
                // Guardar datos en sesión
                $_SESSION['usuario_id'] = $usuario['id'];
                $_SESSION['username'] = $usuario['username'];
                $_SESSION['rol'] = $usuario['rol'];

                // Redirigir según rol
                if ($usuario['rol'] === 'admin') {
                    header("Location: ../landingpage.php"); // Admin va al index principal
                } else {
                    header("Location: ../landingpage.php"); // Cliente también puede ir al index
                }
                exit;
            } else {
                $error = "Contraseña incorrecta.";
            }
        } else {
            $error = "Usuario no encontrado.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Iniciar Sesión</title>
    <style>
        body { font-family: Arial; background:#f8f9fa; margin:20px; }
        form { background:#fff; padding:20px; border-radius:8px; max-width:400px; margin:auto; box-shadow:0 2px 6px rgba(0,0,0,0.1); }
        label { display:block; margin-top:10px; font-weight:bold; }
        input { width:100%; padding:8px; margin-top:5px; border:1px solid #ccc; border-radius:5px; }
        button { margin-top:15px; padding:10px; width:100%; background:#3498db; border:none; color:#fff; border-radius:5px; cursor:pointer; }
        button:hover { background:#2980b9; }
        .error { color:red; margin-top:10px; }
        .back { display:block; margin-top:15px; text-align:center; }
    </style>
</head>
<body>

    <h2 style="text-align:center;">🔑 Iniciar Sesión</h2>

    <?php if (!empty($error)) echo "<div class='error'>$error</div>"; ?>
    <?php if (!empty($_SESSION['mensaje'])) { 
        echo "<div class='mensaje'>".$_SESSION['mensaje']."</div>"; 
        unset($_SESSION['mensaje']); 
    } ?>

    <form method="POST" action="">
        <label>Usuario:</label>
        <input type="text" name="username" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <button type="submit">Ingresar</button>
    </form>

    <a href="registrar.php" class="back">¿No tienes cuenta? Regístrate aquí</a>

</body>
</html>
