<?php
session_start();  // Iniciar sesión

// Verificar si el usuario está logueado
if (!isset($_SESSION['usuario'])) {
    // Si no está logueado, redirigir al formulario de login
    header("Location: login_dashboard.php");
    exit();
}

require_once 'db.php';


    $stmt = $pdo->prepare("SELECT COUNT(*) AS total, SUM(firmado = 1) AS total_firmado, SUM(firmado = 0) AS pendientes_firma FROM contratos");
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/datatables.net@1.12.1-dt/css/jquery.dataTables.min.css">
    <style>
        /* Estilos generales */
/* Estilos generales */
body {
    font-family: 'Montserrat', sans-serif;
    background-color: #f8f9fa; /* Fondo claro para el cuerpo */
    margin: 0;
    color: black; /* Cambiar el color de la letra a negro */
}

/* Barra de navegación lateral */
.sidebar {
    background-color: #343a40;
    color: white;
    height: 100vh;
    padding-top: 20px;
    position: fixed;
    top: 0;
    left: 0;
    bottom: 0;
    width: 250px;
    transition: all 0.3s ease-in-out; /* Transición suave para el sidebar */
}

/* Contenedor de la información del usuario */
.user-info {
    background-color: white;
    border-radius: 10px;
    padding: 20px;
    margin-bottom: 20px;
    text-align: center; /* Centrar la imagen y texto */
    box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1); /* Sombra para el contenedor */
}

.user-info img {
    width: 60px;
    height: 60px;
    object-fit: cover;
    border-radius: 50%;
    margin-bottom: 10px;
}

.user-info h5 {
    color: black;
    margin-top: 3px;
    font-weight: 600; /* Negrita para el nombre */
}

.user-info small {
    color: #6c757d;
    font-size: 0.875rem;
}

/* Enlaces de la barra lateral */
.nav-link {
    color: black;
    padding: 12px 20px;
    border-radius: 8px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    transition: background-color 0.3s ease, padding 0.3s ease; /* Transición suave */
}

.nav-link:hover, .nav-link.active {
    background-color: #007bff;
    padding-left: 30px; /* Efecto de expansión al hacer hover */
    color: white;
}

/* Color cuando el enlace está activo */
.nav-link.active {
    background-color: #007bff;
    color: white;
    padding-left: 30px; /* Efecto de expansión al hacer hover */
}

.nav-link:hover {
    background-color: #f0f0f0;
    color: black;
}

/* Contenido principal */
main {
    margin-left: 260px;
    padding: 20px;
    transition: margin-left 0.3s ease; /* Transición suave en la parte principal */
}

.dataTables_wrapper .dataTables_paginate .paginate_button {
    padding: 0.5em 1em;
    margin-left: 4px;
    margin-right: 4px;
    background-color: #007bff;
    color: white !important;
    border: 1px solid #007bff;
    border-radius: 4px;
    text-decoration: none;
    cursor: pointer;
    font-size: 14px;
}

.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white !important;
}

.dataTables_wrapper .dataTables_paginate .paginate_button.current {
    background-color: #0056b3;
    border-color: #0056b3;
    color: white !important;
}

.dataTables_wrapper .dataTables_paginate {
    margin-top: 10px;
    text-align: center;
}

main h1 {
    font-size: 2rem;
    color: #333;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: relative;
        height: auto;
    }

    main {
        margin-left: 0;
    }
}


    </style>
</head>
<body>

<div class="container-fluid">
    <div class="row">
        <!-- Barra de Navegación Lateral -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky">
                <div class="user-info p-3 text-center">
                    <img src="admin_avatar.png" alt="Avatar" class="rounded-circle mb-2">
                    <h5><?php echo isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario'; ?></h5>
                    <small><?php echo isset($_SESSION['correo']) ? $_SESSION['correo'] : 'Sin Correo Electronico'; ?></small>
                </div>
                <hr>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link active" href="#" id="dashboard">
                            <i class="bi bi-house-door"></i>
                            Dashboard
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="contratosfirmados">
                            <i class="bi bi-check-circle"></i>
                            Contratos Firmados
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id ="contratosporfirmar">
                            <i class="bi bi-file-earmark"></i>
                            Contratos por Firmar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="#" id="editarPlantillas">
                            <i class="bi bi-pencil-square"></i>
                            Editar Plantillas
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link" id="recargar">
                            <i class="bi bi-box-door-closed"></i>
                            Recargar
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">
                            <i class="bi bi-box-door-closed"></i>
                            Cerrar Sesión
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Contenido Principal -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-4">
            <h1 class="h2 mt-4">¡Bienvenido, <?php echo isset($_SESSION['usuario']) ? $_SESSION['usuario'] : 'Usuario'; ?>!</h1>
            <div class="row mt-4">
                <div class="col-md-3 card-container">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-center">Total de Contratos</h3>
                            <p class="card-text" style="font-size: 2rem; color: #007bff;">
                            <h2 class="text-center"><?php echo $result['total'] ?? 0; ?></h2>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 card-container">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-center">Contratos Firmados</h3>
                            <p class="card-text" style="font-size: 2rem; color: #007bff;">
                            <h2 class="text-center"><?php echo $result['total_firmado'] ?? 0; ?></h2>
                            </p>
                        </div>
                    </div>
                </div>

                <div class="col-md-3 card-container">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h3 class="card-title text-center">Pendientes de Firma</h3>
                            <p class="card-text" style="font-size: 2rem; color: #007bff;">
                            <h2 class="text-center"><?php echo $result['pendientes_firma'] ?? 0; ?></h2>
                            </p>
                        </div>
                    </div>
                </div>
            </div>


            <!-- Contenido dinámico -->
            <div class="row mt-4" id="dynamicContent" style="display: none;">
                <!-- Asegúrate de que ocupa las mismas 9 columnas que los cards -->
                <div class="col-md-9">
                <table id="contractsTable" class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre del Contrato</th>
                <th>Fecha de Firma</th>
                <th>Descarga</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            
        </tbody>
    </table>
                </div>
                </div>
                <!-- Segundo Contenedor de Contenido Dinámico -->
    <div class="row mt-4" id="dynamicContent2" style="display: none;">
        <div class="col-md-9">
                <table id="contractsTable2" class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>#</th>
                <th>Nombre del Contrato</th>
                <th>Fecha de Firma</th>
                <th>Descarga</th>
                <th>Estado</th>
                <th>Firmar</th>
            </tr>
        </thead>
        <tbody>
        </tbody>
    </table>
        </div>
    </div>
    <div class="row mt-4" id="dynamicEditor" style="display: none;">
  <div class="col-md-9">

    <!-- Botón para crear nueva plantilla -->
    <div class="d-flex justify-content-end mb-2">
      <button class="btn btn-success" id="btnNuevaPlantilla" data-bs-toggle="modal" data-bs-target="#modalNuevaPlantilla">
        <i class="bi bi-plus-lg"></i> Nueva Plantilla
      </button>
    </div>

    <!-- Tabla de plantillas -->
    <table id="plantillastable" class="table table-bordered table-striped mt-2">
      <thead>
        <tr>
          <th>#</th>
          <th>Contrato</th>
          <th>Acciones</th>
          <th>Campos</th>
        </tr>
      </thead>
      <tbody>
      </tbody>
    </table>

  </div>
</div>

<!-- Modal para añadir nueva plantilla -->
<div class="modal fade" id="modalNuevaPlantilla" tabindex="-1" aria-labelledby="modalNuevaPlantillaLabel" aria-hidden="true">
  <div class="modal-dialog">
    <form id="formNuevaPlantilla">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="modalNuevaPlantillaLabel">Crear Nueva Plantilla</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
        </div>
        <div class="modal-body">
          <div class="mb-3">
            <label for="nombrePlantilla" class="form-label">Nombre de la Plantilla</label>
            <input type="text" class="form-control" id="nombrePlantilla" name="nombre" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-primary" id="guardarPlantilla">Guardar</button>
        </div>
      </div>
    </form>
  </div>
</div>

<!-- Modal para definir orden de campos -->
<div class="modal fade" id="modalOrdenCampos" tabindex="-1" aria-labelledby="modalOrdenCamposLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="modalOrdenCamposLabel">Definir Orden de Campos</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
      </div>
      <div class="modal-body">
        <form id="formOrdenCampos">
          <div id="contenedorCamposOrden" class="row g-3">
            <!-- Aquí se cargarán dinámicamente los campos -->
          </div>
        </form>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
        <button type="button" class="btn btn-primary" onclick="guardarOrdenCampos()">Guardar orden</button>
      </div>
    </div>
  </div>
</div>



        </main>
    </div>
</div>

<!-- Bootstrap JS y Popper.js -->
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/jquery@3.6.0/dist/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/datatables.net@1.12.1/js/jquery.dataTables.min.js"></script>


<script>

$(document).ready(function() {
    const options = {
        "language": {
            "lengthMenu": "Mostrar _MENU_ registros por página",
            "zeroRecords": "No se encontraron registros",
            "info": "Mostrando página _PAGE_ de _PAGES_",
            "infoEmpty": "No hay registros disponibles",
            "infoFiltered": "(filtrado de _MAX_ registros en total)",
            "search": "Buscar:",
            "paginate": {
                "first": "Primero",
                "previous": "Anterior",
                "next": "Siguiente",
                "last": "Último"
            }
        },
        "order": [[0, "desc"]],  // Ojo: 'order' va FUERA de 'language'
        "responsive": true,      // Opcional pero recomendado
        "autoWidth": false        // Opcional para que no fije anchos raros
    };

    $('#contractsTable').DataTable(options);
    $('#contractsTable2').DataTable(options);
    $('#plantillastable').DataTable(options);
});

    document.addEventListener("DOMContentLoaded", function () {
    const btnContratosFirmados = document.getElementById('contratosfirmados');
    const btnContratosPorFirmar = document.getElementById('contratosporfirmar');
    const btnDashboard = document.getElementById('dashboard');
    const dynamicContent1 = document.getElementById('dynamicContent');
    const dynamicContent2 = document.getElementById('dynamicContent2');
    const btnrecargar = document.getElementById('recargar');
    cargarContratos();
    // Función para ocultar todos los contenidos dinámicos y desactivar botones
    function ocultarTodosLosContenidos() {
        dynamicContent1.style.display = 'none';
        dynamicContent2.style.display = 'none';
        btnContratosFirmados.classList.remove('active');
        btnContratosPorFirmar.classList.remove('active');
        btnDashboard.classList.remove('active');
        dynamicEditor.style.display = 'none';
        btnEditarPlantillas.classList.remove('active');

    }
    btnrecargar.addEventListener('click', function () {
        window.location.reload();
    });

    const btnEditarPlantillas = document.getElementById('editarPlantillas');
    const dynamicEditor = document.getElementById('dynamicEditor');

btnEditarPlantillas.addEventListener('click', function () {
    ocultarTodosLosContenidos();
    cargarPlantilla(); // Cargar las plantillas al hacer clic
    dynamicEditor.style.display = 'block';
    btnEditarPlantillas.classList.add('active');
});
    // Evento para "Contratos Firmados"
    btnContratosFirmados.addEventListener('click', function () {
        ocultarTodosLosContenidos(); // Ocultar los demás contenidos
        cargarContratos();
        dynamicContent1.style.display = 'block';
        btnContratosFirmados.classList.add('active');
    });

    // Evento para "Contratos por Firmar"
    btnContratosPorFirmar.addEventListener('click', function () {
        ocultarTodosLosContenidos(); // Ocultar los demás contenidos
        cargarContratospendientes();
        dynamicContent2.style.display = 'block';
        btnContratosPorFirmar.classList.add('active');
    });

    // Evento para "Dashboard"
    btnDashboard.addEventListener('click', function () {
        ocultarTodosLosContenidos(); // Oculta cualquier contenido dinámico
        btnDashboard.classList.add('active');
    });
});

function cargarContratos() {
    fetch("obtener_contratos.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            let table = $("#contractsTable").DataTable();
            table.clear().draw();
            data.forEach(contrato => {
                table.row.add([
                    contrato.id,
                    contrato.nombre,
                    contrato.fecha_firma,
                    `<a href="${contrato.ruta_pdf}" target="_blank">Ver PDF</a>`,
                    contrato.firmado ? "Documento Firmado" : "Pendiente Firma",
                ]).draw(false);
            });
        })
        .catch(error => console.error("Error al cargar los contratos:", error));
}

const botonGuardar = document.getElementById("guardarPlantilla");

botonGuardar.addEventListener("click", () => {
    const inputNombre = document.getElementById("nombrePlantilla"); // Asegúrate de tener este input en el modal
    const nombre = inputNombre.value.trim();

    if (!nombre) {
        alert("El nombre de la plantilla no puede estar vacío.");
        return;
    }

    // Depuración: Verifica el valor de 'nombre'
    console.log("Nombre de la plantilla:", nombre);

    fetch("crear_plantilla.php", {
    method: "POST",
    headers: {
        "Content-Type": "application/json" // Cambiar a JSON
    },
    body: JSON.stringify({
        nombre: nombre
    }) // Convertir los datos a JSON
})
.then(response => {
    // Depuración: Verifica el contenido de la respuesta
    return response.text(); // Usar text() en lugar de json()
})
.then(text => {
    console.log("Respuesta del servidor (raw):", text); // Ver el contenido completo de la respuesta
    try {
        const data = JSON.parse(text); // Intentamos parsear el contenido como JSON
        console.log("Respuesta parseada como JSON:", data);
        if (data.success) {
            alert("Plantilla creada correctamente.");
            cargarPlantilla(); // Recargar tabla con nuevas plantillas
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalNuevaPlantilla'));
            modal.hide();
            inputNombre.value = ""; // Limpiar campo
        } else {
            alert(data.error || "Ha ocurrido un error.");
        }
    } catch (error) {
        console.error("Error al parsear la respuesta JSON:", error);
        alert("Error al crear la plantilla. Respuesta del servidor no válida.");
    }
})
.catch(error => {
    console.error("Error al crear plantilla:", error);
    alert("Error al crear la plantilla.");
});
});



function firmarContrato(contratoId) {
    fetch(`obtener_ruta.php?id=${contratoId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error("Error:", data.error);
            } else {
                // Redirigir con el pdf_path para mostrar el PDF, y con id_contrato para marcarlo luego
                window.location.href = `previsualizar_contrato_dash.html?pdf_path=${encodeURIComponent(data.ruta_pdf)}&id_contrato=${contratoId}`;
            }
        })
        .catch(error => console.error("Error al obtener la ruta del contrato:", error));
}




     

function cargarContratospendientes() {
    fetch("obtener_pendientes.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }

            let table = $("#contractsTable2").DataTable();
            table.clear().draw(); // Limpiar datos previos
            data.forEach(contrato => {
                table.row.add([
                    contrato.id,
                    contrato.nombre,
                    contrato.fecha_firma,
                    `<a href="${contrato.ruta_pdf}" target="_blank">Ver PDF</a>`,
                    contrato.firmado ? "Documento Firmado" : "Pendiente Firma",
                    `<button onclick="firmarContrato(${contrato.id})">Firmar</button>`
                ]).draw(false);
            });
        })
        .catch(error => console.error("Error al cargar los contratos:", error));
}

function cargarPlantilla() {
    fetch("cargar_plantillas.php")
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error(data.error);
                return;
            }
            let table = $("#plantillastable").DataTable();
            table.clear().draw(); // Limpiar datos previos
            data.forEach(plantilla => {
                table.row.add([
                    plantilla.id,
                    plantilla.nombre,
                    `<button class="btn btn-sm btn-primary" onclick="editarPlantilla(${plantilla.id})">Editar</button>`,
                    `<button class="btn btn-sm btn-secondary" onclick="definirOrdenCampos(${plantilla.id})">Definir orden de campos</button>`
                ]).draw(false);
            });
            console.log(data);
        })
        .catch(error => console.error("Error al cargar las plantillas:", error));
}

function editarPlantilla(id) {
    // Realizamos la petición para obtener la ruta de la plantilla
    fetch("obtener_ruta_plantilla.php", {
        method: "POST",
        headers: {
            "Content-Type": "application/json"
        },
        body: JSON.stringify({ id: id })
    })
    .then(response => response.json())
    .then(data => {
        if (data.error) {
            alert("Error al obtener la ruta: " + data.error);
            return;
        }

        // Enviar la ruta obtenida al servidor de forma segura usando sesión o token
        fetch("cargar_editor.php", {
            method: "POST",
            headers: {
                "Content-Type": "application/json"
            },
            body: JSON.stringify({ ruta: data.ruta })
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert("Error al cargar el editor: " + data.error);
                return;
            }
            // Redirigir al editor de manera privada (con datos en sesión o token)
            window.location.href = "editor.php";
        })
        .catch(error => console.error("Error al redirigir al editor:", error));
    })
    .catch(error => console.error("Error al obtener la ruta de la plantilla:", error));
}

function definirOrdenCampos(idPlantilla) {
    fetch('obtener_campos.php?idPlantilla=' + idPlantilla)
        .then(response => response.text())
        .then(texto => {
            console.log("Respuesta bruta del servidor:", texto);

            try {
                const json = JSON.parse(texto);
                console.log("JSON parseado correctamente:", json);
                return json;
            } catch (e) {
                console.error("❌ Error al parsear JSON:", e);
                throw new Error("Respuesta no válida del servidor.");
            }
        })
        .then(campos => {
            const contenedor = document.getElementById("contenedorCamposOrden");
            contenedor.innerHTML = ""; // Limpiar antes de añadir

            // Agregar campo oculto con el id de la plantilla
            const form = document.getElementById("formOrdenCampos");
            let inputIdPlantilla = document.getElementById("id_plantilla_orden");

            if (!inputIdPlantilla) {
                inputIdPlantilla = document.createElement("input");
                inputIdPlantilla.type = "hidden";
                inputIdPlantilla.name = "id_plantilla";
                inputIdPlantilla.id = "id_plantilla_orden";
                form.appendChild(inputIdPlantilla);
            }

            inputIdPlantilla.value = idPlantilla;

            campos.forEach((campo) => {
                const div = document.createElement("div");
                div.className = "col-md-6 d-flex align-items-center mb-2";

                const ordenValue = campo.orden !== null ? campo.orden : "";

                div.innerHTML = `
                    <label class="form-label me-3 flex-shrink-0" style="min-width: 150px;">${campo.nombre}</label>
                    <input type="hidden" name="id_campo[]" value="${campo.id_campo}">
                    <input type="number" name="orden[]" class="form-control" value="${ordenValue}" min="1" placeholder="Sin orden">
                `;

                contenedor.appendChild(div);
            });

            // Abrir el modal
            const modal = new bootstrap.Modal(document.getElementById('modalOrdenCampos'));
            modal.show();
        })
        .catch(error => {
            console.error("⚠️ Error al obtener campos:", error);
            alert("No se pudieron cargar los campos.");
        });
}




function guardarOrdenCampos() {
    const form = document.getElementById('formOrdenCampos');
    const formData = new FormData(form);

    // Asegúrate de tener el id_plantilla cargado en el formulario
    if (!formData.has('id_plantilla')) {
        const idPlantilla = document.getElementById('id_plantilla_orden')?.value;
        if (idPlantilla) {
            formData.append('id_plantilla', idPlantilla);
        } else {
            alert("⚠️ No se encontró el ID de la plantilla.");
            return;
        }
    }

    fetch('guardar_orden.php', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert('✅ Orden guardado correctamente.');
            const modal = bootstrap.Modal.getInstance(document.getElementById('modalOrdenCampos'));
            modal.hide();

            // Opcional: refrescar vista o tabla si lo deseas
            // actualizarTabla(); 
        } else {
            alert('❌ Error al guardar: ' + (data.message || ''));
        }
    })
    .catch(err => {
        console.error('❌ Error al guardar:', err);
        alert('Error inesperado al guardar.');
    });
}

</script>

</body>
</html>

