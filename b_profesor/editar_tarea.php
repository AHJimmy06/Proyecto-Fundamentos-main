<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Obtener el ID de la tarea desde la URL
if (isset($_GET['id'])) {
    $tareaId = $_GET['id'];

    // Obtener la tarea por ID
    $stmt = $conn->prepare("SELECT t.id, t.descripcion, t.fecha_entrega, c.id AS clase_id, c.tema AS clase_nombre, m.id AS materia_id, m.nombre AS materia_nombre, cl.id AS curso_id, cl.nombre AS curso_nombre
                            FROM tareas t
                            JOIN clases c ON t.clase_id = c.id
                            JOIN materias m ON c.materia_id = m.id
                            JOIN cursos cl ON m.curso_id = cl.id
                            WHERE t.id = :id");
    $stmt->bindValue(':id', $tareaId);
    $stmt->execute();
    $tarea = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$tarea) {
        die('Tarea no encontrada');
    }
} else {
    die('ID de tarea no especificado');
}

// Obtener las clases disponibles para el combobox
$stmt = $conn->prepare("SELECT c.id, m.nombre AS materia_nombre, cl.nombre AS curso_nombre
                        FROM clases c
                        JOIN materias m ON c.materia_id = m.id
                        JOIN cursos cl ON m.curso_id = cl.id");
$stmt->execute();
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Manejar el envío del formulario de edición
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $claseId = $_POST['clase_id'];
    $descripcion = $_POST['descripcion'];
    $fechaEntrega = $_POST['fecha_entrega'];

    if (!empty($claseId) && !empty($descripcion) && !empty($fechaEntrega)) {
        // Actualizar la tarea en la base de datos
        $stmt = $conn->prepare("UPDATE tareas SET clase_id = :clase_id, descripcion = :descripcion, fecha_entrega = :fecha_entrega WHERE id = :id");
        $stmt->bindValue(':clase_id', $claseId);
        $stmt->bindValue(':descripcion', $descripcion);
        $stmt->bindValue(':fecha_entrega', $fechaEntrega);
        $stmt->bindValue(':id', $tareaId);

        if ($stmt->execute()) {
            echo '<div class="alert alert-success">Tarea actualizada exitosamente</div>';
            header('Location: crear_tarea.php'); // Redirigir después de editar
        } else {
            echo '<div class="alert alert-danger">Error al actualizar la tarea</div>';
        }
    } else {
        echo '<div class="alert alert-warning">Todos los campos son obligatorios</div>';
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Tarea | FISEI</title>
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
        <!-- <li class="nav-item">
          <a class="nav-link" href="c_docentes.php">Agregar Estudiante</a>
        </li> -->
      </ul>
      <a class="nav-link end-0 position-absolute me-4" href="../php/csesion.php">Cerrar sesión</a>
    </div>
  </div>
</nav>

<div class="container shadow-lg rounded p-4 mt-3">
    <h2>Editar Tarea</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="clase_id" class="form-label">Clase</label>
            <select class="form-select" id="clase_id" name="clase_id" required>
                <option value="" disabled>Selecciona una clase</option>
                <?php foreach ($clases as $clase): ?>
                    <option value="<?= htmlspecialchars($clase['id']) ?>" <?= $clase['id'] == $tarea['clase_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($clase['materia_nombre']) ?> (<?= htmlspecialchars($clase['curso_nombre']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción de la tarea</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required><?= htmlspecialchars($tarea['descripcion']) ?></textarea>
        </div>
        <div class="mb-3">
            <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
            <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" value="<?= htmlspecialchars($tarea['fecha_entrega']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Tarea</button>
    </form>
</div>

<script src="../js/bootstrap.js"></script>
</body>
</html>
