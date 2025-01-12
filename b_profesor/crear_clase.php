<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Obtener ID del profesor
$profesorId = $_SESSION['id']; // Suponiendo que el ID del profesor está en la sesión

// Peso máximo en bytes (5 MB)
const MAX_FILE_SIZE = 5 * 1024 * 1024;

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materiaId = $_POST['materia_id'];
    $fecha = $_POST['fecha'];
    $tema = $_POST['tema'];
    $archivoSubido = isset($_FILES['material_apoyo']) ? $_FILES['material_apoyo'] : null;

    if (!empty($materiaId) && !empty($fecha) && !empty($tema)) {
        try {
            // Validar tamaño del archivo
            if ($archivoSubido && $archivoSubido['error'] === UPLOAD_ERR_OK) {
                if ($archivoSubido['size'] > MAX_FILE_SIZE) {
                    throw new Exception('El archivo supera el tamaño máximo permitido de 5 MB.');
                }
            }

            // Iniciar transacción
            $conn->beginTransaction();

            // Insertar nueva clase en la base de datos
            $stmt = $conn->prepare("INSERT INTO clases (materia_id, fecha, tema) VALUES (:materia_id, :fecha, :tema)");
            $stmt->bindValue(':materia_id', $materiaId);
            $stmt->bindValue(':fecha', $fecha);
            $stmt->bindValue(':tema', $tema);
            $stmt->execute();

            $claseId = $conn->lastInsertId(); // Obtener el ID de la clase creada

            // Manejar archivo subido
            if ($archivoSubido && $archivoSubido['error'] === UPLOAD_ERR_OK) {
                $nombreArchivo = basename($archivoSubido['name']);
                $rutaArchivo = '../archivos/' . uniqid() . '_' . $nombreArchivo;
                $tipoArchivo = $archivoSubido['type'];

                // Mover archivo al directorio de almacenamiento
                if (move_uploaded_file($archivoSubido['tmp_name'], $rutaArchivo)) {
                    // Insertar registro en la tabla `archivos`
                    $stmtArchivo = $conn->prepare(
                        "INSERT INTO archivos (nombre_archivo, tipo_archivo, ruta_archivo, usuario_id, tarea_id) 
                         VALUES (:nombre_archivo, :tipo_archivo, :ruta_archivo, :usuario_id, NULL)"
                    );
                    $stmtArchivo->bindValue(':nombre_archivo', $nombreArchivo);
                    $stmtArchivo->bindValue(':tipo_archivo', $tipoArchivo);
                    $stmtArchivo->bindValue(':ruta_archivo', $rutaArchivo);
                    $stmtArchivo->bindValue(':usuario_id', $profesorId);
                    $stmtArchivo->execute();
                } else {
                    throw new Exception('Error al mover el archivo al directorio de almacenamiento.');
                }
            }

            $conn->commit();
            echo '<div class="alert alert-success">Clase creada exitosamente con material de apoyo.</div>';
        } catch (Exception $e) {
            $conn->rollBack();
            echo '<div class="alert alert-danger">Error al crear la clase: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
    } else {
        echo '<div class="alert alert-warning">Todos los campos son obligatorios.</div>';
    }
}

// Obtener materias disponibles para el profesor
$stmt = $conn->prepare(
    "SELECT m.id, m.nombre, m.descripcion, c.nombre AS curso_nombre 
     FROM materias m
     JOIN cursos c ON m.curso_id = c.id
     WHERE c.profesor_id = :profesor_id"
);
$stmt->bindValue(':profesor_id', $profesorId);
$stmt->execute();
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener clases registradas
$stmt = $conn->prepare(
    "SELECT clases.id, materias.nombre AS materia_nombre, cursos.nombre AS curso_nombre, clases.fecha, clases.tema
     FROM clases
     JOIN materias ON clases.materia_id = materias.id
     JOIN cursos ON materias.curso_id = cursos.id
     WHERE cursos.profesor_id = :profesor_id"
);
$stmt->bindValue(':profesor_id', $profesorId);
$stmt->execute();
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profesor | FISEI</title>
    <link href="../css/bootstrap.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<nav class="navbar navbar-expand-lg bg-white">
  <div class="container-fluid">
  <img src="../img/nav.png" alt="Logo" width="130" height="70" class="d-inline-block align-text-top">
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNav">
      <ul class="navbar-nav">
        <li class="nav-item">
          <a class="nav-link" href="crear_clase.php">Crear Clase</a>
        </li>
        <li class="nav-item">
          <a class="nav-link" href="crear_tarea.php">Crear Tarea</a>
        </li>
      </ul>
      <a class="nav-link end-0 position-absolute me-4" href="../php/csesion.php">Cerrar sesion</a>
    </div>
  </div>
</nav>

<div class="container shadow-lg rounded p-4 mt-3">
    <h2>Crear Clase</h2>
    <form method="POST" action="" enctype="multipart/form-data">
        <div class="mb-3">
            <label for="materia_id" class="form-label">Materia</label>
            <select class="form-select" id="materia_id" name="materia_id" required>
                <option value="" disabled selected>Selecciona una materia</option>
                <?php foreach ($materias as $materia): ?>
                    <option value="<?= htmlspecialchars($materia['id']) ?>">
                        <?= htmlspecialchars($materia['nombre']) ?> (<?= htmlspecialchars($materia['curso_nombre']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="fecha" class="form-label">Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha" value="<?= date('Y-m-d') ?>" disabled>
            <input type="hidden" name="fecha" value="<?= date('Y-m-d') ?>">
        </div>
        <div class="mb-3">
            <label for="tema" class="form-label">Tema</label>
            <input type="text" class="form-control" id="tema" name="tema" required>
        </div>
        <div class="mb-3">
            <label for="material_apoyo" class="form-label">Material de Apoyo</label>
            <input type="file" class="form-control" id="material_apoyo" name="material_apoyo">
        </div>
        <button type="submit" class="btn btn-primary">Crear Clase</button>
    </form>

    <h3 class="mt-4">Clases Registradas</h3>
    <?php if (count($clases) > 0): ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Materia</th>
                    <th>Curso</th>
                    <th>Fecha</th>
                    <th>Tema</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($clases as $clase): ?>
                    <tr>
                        <td><?= htmlspecialchars($clase['id']) ?></td>
                        <td><?= htmlspecialchars($clase['materia_nombre']) ?></td>
                        <td><?= htmlspecialchars($clase['curso_nombre']) ?></td>
                        <td><?= htmlspecialchars($clase['fecha']) ?></td>
                        <td><?= htmlspecialchars($clase['tema']) ?></td>
                        <td>
                            <a href="editar_clase.php?id=<?= $clase['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_clase.php?id=<?= $clase['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta clase?')">Eliminar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="mt-3">No se han registrado clases</p>
    <?php endif; ?>
</div>

<script src="../js/bootstrap.js"></script>   
<script>
document.querySelector('form').addEventListener('submit', function(e) {
    const archivo = document.getElementById('material_apoyo').files[0];
    if (archivo && archivo.size > 5 * 1024 * 1024) {
        e.preventDefault();
        alert('El archivo no puede superar los 5 MB.');
    }
});
</script>
</body>
</html>
