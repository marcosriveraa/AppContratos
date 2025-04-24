<?php
session_start();

// Verificar si la ruta está almacenada en la sesión
if (!isset($_SESSION['ruta_plantilla']) || !file_exists($_SESSION['ruta_plantilla'])) {
    echo "Archivo no encontrado o no autorizado para editar.";
    exit;
}

// Obtener el contenido del archivo
$ruta = $_SESSION['ruta_plantilla'];
$contenido = file_get_contents($ruta);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editor de Contratos</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.0.0/dist/tailwind.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/easymde/dist/easymde.min.css">
    <style>
        body {
            font-family: 'Arial', sans-serif;
            background-color: #f7fafc;
        }
        .container {
            max-width: 1200px; /* Aumentado el ancho máximo */
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            font-size: 2.5rem;
            color: #4a5568;
            font-weight: 600;
            margin-bottom: 20px;
        }
        .button {
            background-color: #3182ce;
            color: white;
            padding: 10px 20px;
            font-size: 1.25rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }
        .button:hover {
            background-color: #2b6cb0;
        }
        .editor-container {
            background-color: white;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border: 1px solid #e2e8f0;
            width: 100%; /* Asegura que ocupe todo el ancho disponible */
        }
        #editor {
            width: 100%; /* Aumentado para hacer el editor más ancho */
            height: 600px; /* Ajustado para dar más espacio de edición */
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            color: #718096;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="header">
        Editor de Contratos
    </div>

    <div class="editor-container">
        <textarea id="editor"><?= htmlspecialchars($contenido) ?></textarea>
    </div>

    <div class="mt-4">
        <button id="guardarBtn" class="button">Guardar</button>
    </div>
</div>

<script src="https://unpkg.com/easymde/dist/easymde.min.js"></script>
<script>
      const easyMDE = new EasyMDE({ 
        element: document.getElementById("editor"),
        spellChecker: false, 
        autosave: {
            enabled: false 
        },
        placeholder: "Escribe tu contenido en Markdown...",
        status: false,
    });

    document.getElementById("guardarBtn").addEventListener("click", function () {
        const contenido = easyMDE.value();

        fetch("guardar_md.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                ruta: "<?= addslashes($ruta) ?>", // Ruta almacenada en la sesión
                contenido: contenido
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.success) {
                alert("Contrato Modificado con éxito.");
                window.location.href = "dashboard.php"; // Redirigir a la página principal después de guardar
            } else {
                alert("Error al guardar el archivo.");
                console.error(data.error);
            }
        });
    });
</script>

</body>
</html>
