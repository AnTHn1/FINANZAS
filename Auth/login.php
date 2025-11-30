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
        $_SESSION['mensaje'] = "❌ Todos los campos son obligatorios.";
$_SESSION['tipo'] = "error";

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
                $_SESSION['mensaje'] = "❌ Contraseña incorrecta.";
$_SESSION['tipo'] = "error";
            }
        } else {
$_SESSION['mensaje'] = "❌ Usuario no encontrado.";
$_SESSION['tipo'] = "error";

        }
    }
}
?>
<?php if (isset($_SESSION['mensaje'])): ?>
<div id="toast" class="toast show <?= ($_SESSION['tipo'] ?? '') ?>">
    <?= $_SESSION['mensaje']; ?>
</div>
<?php unset($_SESSION['mensaje'], $_SESSION['tipo']); endif; ?>


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
        .back {
    display: block;
    width: fit-content;
    margin: 15px auto 0;
    text-align: center;
}

.toast {
    position: fixed;
    bottom: 20px;
    right: 20px;
    background: #28a745;
    color: #fff;
    padding: 14px 20px;
    border-radius: 8px;
    font-size: 16px;
    box-shadow: 0 2px 12px rgba(0, 0, 0, 0.3);
    opacity: 0;
    transform: translateY(20px);
    transition: all 0.4s ease;
    z-index: 9999;
}

.toast.show {
    opacity: 1;
    transform: translateY(0);
}

/* Toast de error (opcional) */
.toast.error {
    background: #dc3545;
}

</style>
</head>


<body>

    <h2 style="text-align:center;">🔑 Iniciar Sesión</h2>
<?php if (isset($_SESSION['mensaje'])): ?>
<div id="toast" class="toast show">
    <?php echo $_SESSION['mensaje']; ?>
</div>
<?php unset($_SESSION['mensaje']); endif; ?>


    <form method="POST" action="">
        <label>Usuario:</label>
        <input type="text" name="username" required>

        <label>Contraseña:</label>
        <input type="password" name="password" required>

        <button type="submit">Ingresar</button>
    </form>

    <a href="registrar.php" class="back">¿No tienes cuenta? Regístrate aquí</a>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const toast = document.getElementById("toast");
    if (toast) {
        setTimeout(() => {
            toast.style.opacity = "0";
            toast.style.transform = "translateY(20px)";
        }, 3000); // se oculta después de 3 segundos
    }
});
</script>

</body>
</html>
