<?php
session_start();
include("../conexion.php");

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $username = $_POST["username"];
    $password = $_POST["password"];

    $stmt = $pdo->prepare("SELECT * FROM usuarios WHERE username = :username");
    $stmt->execute([":username" => $username]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user && password_verify($password, $user["password"])) {
        $_SESSION["usuario_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        $_SESSION["rol"] = $user["rol"];
        header("Location: ../index.php");
        exit;
    } else {
        echo "❌ Usuario o contraseña incorrectos.";
        echo "<br><a href='login.php'>⬅ Volver</a>";
    }
}
?>
