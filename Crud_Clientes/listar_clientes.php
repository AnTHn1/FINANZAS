<?php
include "../conexion.php";

try {
    $pdo = new PDO("pgsql:host=$host;port=$port;dbname=$dbname;", $user, $password, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $stmt = $pdo->query("SELECT * FROM clientes ORDER BY id DESC");

    echo "<h2>📋 Lista de Clientes</h2>";
    echo "<table border='1' cellpadding='5'>";
    echo "<tr><th>ID</th><th>Nombre</th><th>DNI</th><th>Ingresos</th><th>Teléfono</th><th>Correo</th><th>Acciones</th></tr>";

    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo "<tr>
                <td>{$row['id']}</td>
                <td>{$row['nombre']}</td>
                <td>{$row['dni']}</td>
                <td>{$row['ingresos']}</td>
                <td>{$row['telefono']}</td>
                <td>{$row['correo']}</td>
                <td>
                    <a href='editar_cliente.php?id={$row['id']}'>✏️ Editar</a> | 
                    <a href='eliminar_cliente.php?id={$row['id']}' onclick='return confirm(\"¿Seguro?\")'>🗑 Eliminar</a>
                </td>
              </tr>";
    }
    echo "</table>";

    echo "<br><a href='../index.php'>⬅️ Volver al formulario</a>";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
