<?php
include "conexion.php";

// Obtener todos los clientes con el usuario asignado
$stmt = $pdo->query("SELECT c.id, c.nombre, c.dni, c.ingresos, c.telefono, c.correo, u.username 
                     FROM clientes c
                     LEFT JOIN usuarios u ON c.usuario_id = u.id
                     ORDER BY c.id ASC");

$clientes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Clientes</title>

    <!-- ================= CSS ================= -->
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f8f9fa; }
        h2 { color: #2c3e50; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; background: #fff; }
        table, th, td { border: 1px solid #ddd; }
        th, td { padding: 10px; text-align: center; }
        th { background-color: #2c3e50; color: white; }
        tr:nth-child(even) { background-color: #f2f2f2; }
        .btn { padding: 6px 12px; border: none; cursor: pointer; border-radius: 4px; }
        .btn-edit { background-color: #3498db; color: white; }
        .btn-delete { background-color: #e74c3c; color: white; }
        .btn-add { background-color: #2ecc71; color: white; margin-bottom: 15px; }
        .btn:hover { opacity: 0.9; }
    </style>
</head>
<body>

    <!-- ================= HTML ================= -->
    <h2>📋 Gestión de Clientes</h2>

    <!-- Botón para agregar cliente -->
    <a href="Crud_Clientes/guardar_cliente.php">
        <button class="btn btn-add">➕ Agregar Cliente</button>
    </a>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <!-- <th>Usuario</th> -->
                <th>Nombre</th>
                <th>DNI</th>
                <th>Ingresos</th>
                <th>Teléfono</th>
                <th>Correo</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (count($clientes) > 0): ?>
                <?php foreach ($clientes as $cliente): ?>
                    <tr>
                        <td><?= htmlspecialchars($cliente["id"]) ?></td>
                        <?php // <td><?= htmlspecialchars($cliente["username"]) ?></td>
                        <td><?= htmlspecialchars($cliente["nombre"]) ?></td>
                        <td><?= htmlspecialchars($cliente["dni"]) ?></td>
                        <td><?= htmlspecialchars($cliente["ingresos"]) ?></td>
                        <td><?= htmlspecialchars($cliente["telefono"]) ?></td>
                        <td><?= htmlspecialchars($cliente["correo"]) ?></td>
                        <td>
                    <!-- Botón Editar -->
                    <form action="Crud_Clientes/form_editar.php" method="GET" style="display:inline;">
                         <input type="hidden" name="id" value="<?= $cliente["id"] ?>">
                         <button type="submit" class="btn btn-edit">✏ Editar</button>
                    </form>

                    <!-- Botón Eliminar -->
                     <form action="Crud_Clientes/eliminar_cliente.php" method="POST" style="display:inline;" class="form-eliminar">
                        <input type="hidden" name="id" value="<?= $cliente["id"] ?>">
                        <button type="submit" class="btn btn-delete">🗑 Eliminar</button>
                    </form>

                    <!-- Nuevo botón: Registrar Crédito -->
                    <form action="Crud_Credito/credito_cliente.php" method="GET" style="display:inline;">
                        <input type="hidden" name="cliente_id" value="<?= $cliente["id"] ?>">
                        <button type="submit" class="btn btn-add" style="background-color:#f39c12;">💰 Registrar Crédito</button>
                    </form>
                        </td>

                    </tr>
                <?php endforeach; ?>
            <?php else: ?>
                <tr>
                    <td colspan="8">⚠ No hay clientes registrados</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>

    <!-- ================= JS ================= -->
    <script>
        // Confirmación antes de eliminar
        document.querySelectorAll(".form-eliminar").forEach(form => {
            form.addEventListener("submit", function(e) {
                if (!confirm("¿Seguro que deseas eliminar este cliente?")) {
                    e.preventDefault();
                }
            });
        });
    </script>

</body>
</html>
