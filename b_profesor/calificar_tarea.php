<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Verificar si se recibió el ID de la tarea
if (!isset($_GET['id'])) {
    echo '<div class="alert alert-danger">No se especificó la tarea</div>';
    exit;
}

$tareaId = $_GET['id'];

// Obtener información de la tarea
$stmt = $conn->prepare("SELECT t.descripcion, t.fecha_entrega, m.nombre AS materia_nombre, c.tema AS clase_nombre
                        FROM tareas t
                        JOIN clases c ON t.clase_id = c.id
                        JOIN materias m ON c.materia_id = m.id
                        WHERE t.id = :tareaId");
$stmt->bindValue(':tareaId', $tareaId);
$stmt->execute();
$tarea = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$tarea) {
    echo '<div class="alert alert-danger">La tarea no existe</div>';
    exit;
}

// Obtener estudiantes asignados a la clase
$stmt = $conn->prepare("SELECT 
        u.nombre AS estudiante_nombre, 
        e.id AS estudiante_id, 
        COALESCE(et.estado, 'Sin entregar') AS estado_tarea, 
        COALESCE(ca.calificacion, 0) AS calificacion
    FROM estudiantes e
    JOIN usuarios u ON e.usuario_id = u.id
    JOIN inscripciones i ON e.id = i.estudiante_id
    JOIN materias m ON i.materia_id = m.id
    JOIN clases cl ON m.id = cl.materia_id
    JOIN tareas t ON cl.id = t.clase_id
    LEFT JOIN estudiantes_tareas et ON e.id = et.estudiante_id AND et.tarea_id = :tareaId
    LEFT JOIN calificaciones ca ON e.id = ca.estudiante_id AND ca.tarea_id = :tareaId
    WHERE t.id = :tareaId");

$stmt->bindValue(':tareaId', $tareaId);
$stmt->execute();
$estudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calificar Tarea | FISEI</title>
    <link href="../css/bootstrap.css" rel="stylesheet">
</head>

<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg bg-white">
  <div class="container-fluid">
    <a class="navbar-brand" href="crear_clase.php">
        <img src="../img/nav.png" alt="Logo" width="130" height="70" class="d-inline-block align-text-top">
    </a> 
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <!-- <li class="nav-item">
          <a class="nav-link" href="crear_clase.php">Crear Clase</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="crear_tarea.php">Crear Tarea</a>
        </li> -->
        <!-- <li class="nav-item">
          <a class="nav-link" href="c_docentes.php">Agregar Estudiante</a>
        </li> -->
      </ul>
      <a class="nav-link end-0 position-absolute me-4" href="../php/csesion.php">Cerrar sesion</a>
    </div>
  </div>
</nav>
    <div class="container shadow-lg rounded p-4 mt-3">
        <h2>Calificar Tarea: <?= htmlspecialchars($tarea['descripcion']) ?></h2>
        <p><strong>Materia:</strong> <?= htmlspecialchars($tarea['materia_nombre']) ?> | <strong>Clase:</strong> <?= htmlspecialchars($tarea['clase_nombre']) ?> | <strong>Fecha de Entrega:</strong> <?= htmlspecialchars($tarea['fecha_entrega']) ?></p>

        <?php if (count($estudiantes) > 0): ?>
            <table class="table table-bordered mt-3">
                <thead>
                    <tr>
                        <th>Nombre del Estudiante</th>
                        <th>Estado</th>
                        <th>Calificación</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($estudiantes as $estudiante): ?>
                        <tr>
                            <td><?= htmlspecialchars($estudiante['estudiante_nombre']) ?></td>
                            <td><?= $estudiante['estado_tarea'] ?></td>
                            <td>
                                <input
                                    type="number"
                                    step="0.1"
                                    min="0"
                                    max="10"
                                    class="form-control input-nota"
                                    id="nota-<?= $estudiante['estudiante_id'] ?>"
                                    value="<?= htmlspecialchars($estudiante['calificacion']) ?>"
                                    <?= $estudiante['calificacion'] > 0 ? 'disabled' : '' ?>>
                            </td>
                            <td>
                                <button
                                    class="btn btn-success btn-sm btn-guardar"
                                    id="guardar-<?= $estudiante['estudiante_id'] ?>"
                                    onclick="guardarNota(<?= $estudiante['estudiante_id'] ?>, <?= $tareaId ?>)"
                                    <?= $estudiante['calificacion'] > 0 ? 'style="display:none;"' : '' ?>>
                                    Guardar
                                </button>
                                <button
                                    class="btn btn-warning btn-sm btn-editar"
                                    id="editar-<?= $estudiante['estudiante_id'] ?>"
                                    onclick="editarNota(<?= $estudiante['estudiante_id'] ?>)"
                                    <?= $estudiante['calificacion'] == 0 ? 'style="display:none;"' : '' ?>>
                                    Editar
                                </button>
                                <a href="ver_archivos.php?estudiante_id=<?= $estudiante['estudiante_id'] ?>&tarea_id=<?= $tareaId ?>" class="btn btn-info btn-sm">Ver Archivos</a>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>

            </table>
        <?php else: ?>
            <p class="mt-3">No hay estudiantes asignados a esta clase</p>
        <?php endif; ?>
    </div>
    <script src="../js/bootstrap.js"></script>
    <script>
        // function guardarNota(estudianteId, tareaId) {
        //     const inputNota = document.getElementById(`nota-${estudianteId}`);
        //     const nota = parseFloat(inputNota.value);

        //     if (isNaN(nota) || nota < 0 || nota > 10) {
        //         alert('Por favor, ingresa una nota válida entre 0 y 10.');
        //         return;
        //     }

        //     // Enviar la nota al servidor usando fetch
        //     fetch('./guardar_nota.php', {
        //         method: 'POST',
        //         headers: {
        //             'Content-Type': 'application/json',
        //         },
        //         body: JSON.stringify({ estudianteId, tareaId, nota }),
        //     })
        //     .then(response => response.json())
        //     .then(data => {
        //         if (data.success) {
        //             alert('Nota guardada correctamente.');
        //             inputNota.disabled = true;

        //             // Cambiar los botones
        //             document.getElementById(`guardar-${estudianteId}`).style.display = 'none';
        //             document.getElementById(`editar-${estudianteId}`).style.display = 'inline-block';
        //         } else {
        //             alert('Error al guardar la nota. Intenta nuevamente.');
        //         }
        //     })
        //     .catch(error => console.error('Error:', error));
        // }

        function guardarNota(estudianteId, tareaId) {
            const inputNota = document.getElementById(`nota-${estudianteId}`);
            const nota = parseFloat(inputNota.value); // Asegúrate de convertir correctamente la nota a un número

            if (isNaN(nota) || nota < 0 || nota > 10) {
                 alert('Por favor, ingresa una nota válida entre 0 y 10.');
                 return;
           }

            fetch('./guardar_nota.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                    },
                    body: JSON.stringify({
                        estudianteId: estudianteId,
                        tareaId: tareaId,
                        nota: nota,
                    }),
                })
                .then((response) => {
                    if (!response.ok) {
                        throw new Error(`Error HTTP! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then((data) => {
                    if (data.success) {
                        alert(data.message); // Mensaje del backend
                        // Desactivar el input de nota
                        inputNota.disabled = true;
                        // Cambiar botones
                        document.getElementById(`guardar-${estudianteId}`).style.display = 'none';
                        document.getElementById(`editar-${estudianteId}`).style.display = 'inline-block';
                    } else {
                        alert(data.message || 'Error desconocido al guardar la nota.');
                    }
                })
                .catch((error) => {
                    console.error('Error en la solicitud:', error);
                    alert(error.message);
                });
        }


        function editarNota(estudianteId) {
            const inputNota = document.getElementById(`nota-${estudianteId}`);
            inputNota.disabled = false;

            // Cambiar los botones
            document.getElementById(`guardar-${estudianteId}`).style.display = 'inline-block';
            document.getElementById(`editar-${estudianteId}`).style.display = 'none';
        }
    </script>

</body>

</html>