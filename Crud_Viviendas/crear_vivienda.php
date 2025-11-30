<?php
require_once "../Auth/auth.php";
include("../conexion.php");
include("../menu.php");

// --- Asegurar carpeta de imágenes ---
$carpetaImg = "../img/";
if (!is_dir($carpetaImg)) {
    mkdir($carpetaImg, 0777, true);
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $direccion = trim($_POST["direccion"]);
    $provincia = trim($_POST["provincia"]);
    $distrito = trim($_POST["distrito"]);
    $moneda = $_POST["moneda"];
    $precio = $_POST["precio"];
    $area = $_POST["area"];
    $tipo = $_POST["tipo"];
    $imagen = null;

    // --- Manejo seguro de imagen ---
    if (isset($_FILES["imagen"]) && $_FILES["imagen"]["error"] === UPLOAD_ERR_OK) {
        $nombreOriginal = basename($_FILES["imagen"]["name"]);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ["jpg", "jpeg", "png", "gif", "webp"];

        if (in_array($extension, $permitidas)) {
            if ($_FILES["imagen"]["size"] <= 5 * 1024 * 1024) {
                $nuevoNombre = uniqid("vivienda_") . "." . $extension;
                $rutaDestino = $carpetaImg . $nuevoNombre;

                if (move_uploaded_file($_FILES["imagen"]["tmp_name"], $rutaDestino)) {
                    $imagen = "img/" . $nuevoNombre;
                } else {
                    echo "<p style='color:red;'>❌ Error al mover la imagen al directorio destino.</p>";
                }
            } else {
                echo "<p style='color:red;'>⚠️ La imagen excede el tamaño máximo permitido (5 MB).</p>";
            }
        } else {
            echo "<p style='color:red;'>⚠️ Solo se permiten imágenes JPG, PNG, GIF o WEBP.</p>";
        }
    }

    try {
        $stmt = $pdo->prepare("INSERT INTO viviendas 
            (direccion, provincia, distrito, moneda, precio, area, tipo, imagen) 
            VALUES (:direccion, :provincia, :distrito, :moneda, :precio, :area, :tipo, :imagen)");
        $stmt->execute([
            ":direccion" => $direccion,
            ":provincia" => $provincia,
            ":distrito" => $distrito,
            ":moneda" => $moneda,
            ":precio" => $precio,
            ":area" => $area,
            ":tipo" => $tipo,
            ":imagen" => $imagen
        ]);
        header("Location: listar_viviendas.php?msg=added");
        exit;
    } catch (PDOException $e) {
        echo "❌ Error al guardar la vivienda: " . htmlspecialchars($e->getMessage());
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Agregar Vivienda</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            background: #f8f9fa;
        }

        main {
            display: flex;
            justify-content: center;
            padding: 40px 20px;
        }

        form {
            background: #fff;
            padding: 25px;
            border-radius: 10px;
            box-shadow: 0 3px 10px rgba(0,0,0,0.15);
            width: 100%;
            max-width: 550px;
        }

        h2 {
            color: #2c3e50;
            text-align: center;
            margin-bottom: 15px;
        }

        label {
            display: block;
            margin-top: 12px;
            font-weight: bold;
            color: #2c3e50;
        }

        input, select {
            width: 100%;
            padding: 9px;
            margin-top: 5px;
            border: 1px solid #ccc;
            border-radius: 6px;
            box-sizing: border-box;
        }

        input[type="file"] {
            border: none;
        }

        .precio-group {
            display: flex;
            gap: 10px;
        }

        .precio-group select {
            width: 40%;
        }

        .precio-group input {
            width: 60%;
        }

        button {
            margin-top: 20px;
            padding: 12px;
            width: 100%;
            background: #2ecc71;
            border: none;
            color: #fff;
            border-radius: 6px;
            font-size: 16px;
            cursor: pointer;
            transition: background 0.3s;
        }

        button:hover {
            background: #27ae60;
        }

        .back {
            display: block;
            margin-top: 15px;
            text-align: center;
            text-decoration: none;
            color: #2980b9;
        }

        .boton-volver {
    display: block;               /* Igual que width:100% del botón verde */
    width: 96%;
    margin-top: 20px;
    background: #2c3e50;          /* Azul oscuro */
    color: white;
    padding: 12px;
    border-radius: 8px;
    text-align: center;           /* Centra el texto */
    font-size: 16px;
    text-decoration: none;        /* Quitar subrayado */
    cursor: pointer;
    transition: background 0.25s ease;
}

.boton-volver:hover {
    background: #1f2d3a;
}
    </style>
</head>
<body>
    <!-- Contenido principal centrado -->
    <main>
        <form method="POST" enctype="multipart/form-data">
            <h2> Agregar Nueva Vivienda</h2>

            <label>Dirección:</label>
            <input type="text" name="direccion" required>

            <label>Provincia:</label>
            <input type="text" id="provinciaInput" name="provincia" list="listaProvincias" required>
            <datalist id="listaProvincias"></datalist>

            <label>Distrito:</label>
            <input type="text" id="distritoInput" name="distrito" list="listaDistritos" required>
            <datalist id="listaDistritos"></datalist>

<label>Precio:</label>
<div class="precio-group">
    <select name="moneda" required>
        <option value="Soles">Soles (S/)</option>
        <option value="Dólares">Dólares ($)</option>
    </select>
    <input type="number" name="precio" id="precio" step="0.01" placeholder="Monto" required>
    <small id="mensaje-precio" style="color:#e74c3c; display:none; font-size:12px;">
        Valor del inmueble debe estar dentro del rango de S/67,400 hasta S/362,100
    </small>
</div>

            <label>Área (m²):</label>
            <input type="number" name="area" step="0.01" required>

            <label>Tipo:</label>
            <select name="tipo" required>
                <option value="Departamento">Departamento</option>
                <option value="Casa">Casa</option>
                <option value="Dúplex">Dúplex</option>
                <option value="Otro">Otro</option>
            </select>

            <label>Imagen de la vivienda:</label>
            <input type="file" name="imagen" accept="image/*">

            <button type="submit">💾 Guardar Vivienda</button>
                        <a href="../Crud_Viviendas/listar_viviendas.php" class="boton-volver">
    ⬅ Volver al listado de viviendas
</a>
        </form>
    </main>

<script>
    // === Datos de provincias y distritos (resumen inicial) ===
    const dataPeru = {
        "Lima": ["Lima", "Miraflores", "Surco", "San Borja", "Callao", "San Isidro"],
        "Arequipa": ["Arequipa", "Cayma", "Yanahuara", "Cerro Colorado"],
        "Cusco": ["Cusco", "Wanchaq", "San Sebastián", "San Jerónimo"],
        "La Libertad": ["Trujillo", "Florencia de Mora", "El Porvenir"],
        "Piura": ["Piura", "Sullana", "Talara"]
    };

    const provinciaInput = document.getElementById("provinciaInput");
    const distritoInput = document.getElementById("distritoInput");
    const listaProvincias = document.getElementById("listaProvincias");
    const listaDistritos = document.getElementById("listaDistritos");
    const precioInput = document.getElementById("precio"); // Input de precio
    const mensajePrecio = document.getElementById("mensaje-precio");

    // Cargar provincias
    Object.keys(dataPeru).forEach(prov => {
        const opt = document.createElement("option");
        opt.value = prov;
        listaProvincias.appendChild(opt);
    });

    // Cambiar distritos según provincia
    provinciaInput.addEventListener("input", () => {
        const provincia = provinciaInput.value;
        listaDistritos.innerHTML = "";
        if (dataPeru[provincia]) {
            dataPeru[provincia].forEach(dist => {
                const opt = document.createElement("option");
                opt.value = dist;
                listaDistritos.appendChild(opt);
            });
        }
    });

    // Validación de precio dentro del rango
    if (precioInput) {
        precioInput.addEventListener("input", function() {
            const valor = parseFloat(this.value);
            if (valor && (valor < 67400 || valor > 362100)) {
                mensajePrecio.style.display = "block";
            } else {
                mensajePrecio.style.display = "none";
            }
        });
    }
</script>


</body>
</html>
