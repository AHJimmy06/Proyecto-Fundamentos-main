<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Manejar envío del formulario para tareas
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $claseId = $_POST['clase_id'];
  $descripcion = $_POST['descripcion'];
  $fechaEntrega = $_POST['fecha_entrega'];

  if (!empty($claseId) && !empty($descripcion) && !empty($fechaEntrega)) {
      // Insertar nueva tarea en la base de datos
      $stmt = $conn->prepare("INSERT INTO tareas (clase_id, descripcion, fecha_entrega) VALUES (:clase_id, :descripcion, :fecha_entrega)");
      $stmt->bindValue(':clase_id', $claseId);
      $stmt->bindValue(':descripcion', $descripcion);
      $stmt->bindValue(':fecha_entrega', $fechaEntrega);

      if ($stmt->execute()) {
          echo '<div class="alert alert-success">Tarea creada exitosamente</div>';
          echo '<script>document.getElementById("descripcion").value = ""; document.getElementById("fecha_entrega").value = ""; document.getElementById("clase_id").selectedIndex = 0;</script>';
      } else {
          echo '<div class="alert alert-danger">Error al crear la tarea</div>';
      }
  } else {
      echo '<div class="alert alert-warning">Todos los campos son obligatorios</div>';
  }
}

// Obtener clases disponibles para el combobox
$stmt = $conn->prepare("SELECT c.id, m.nombre AS materia_nombre, cl.nombre AS curso_nombre
                        FROM clases c
                        JOIN materias m ON c.materia_id = m.id
                        JOIN cursos cl ON m.curso_id = cl.id");
$stmt->execute();
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener tareas registradas
$stmt = $conn->prepare("SELECT t.id, c.tema AS clase_nombre, m.nombre AS materia_nombre, cl.nombre AS curso_nombre, t.descripcion, t.fecha_entrega
                        FROM tareas t
                        JOIN clases c ON t.clase_id = c.id
                        JOIN materias m ON c.materia_id = m.id
                        JOIN cursos cl ON m.curso_id = cl.id");
$stmt->execute();
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<?php if (isset($_GET['mensaje'])): ?>
    <div class="alert alert-info" id="mensaje-alert">
        <?= htmlspecialchars($_GET['mensaje']) ?>
    </div>
    <script>
        // Eliminar el parámetro 'mensaje' de la URL después de mostrarlo
        const url = new URL(window.location);
        url.searchParams.delete('mensaje');
        window.history.replaceState({}, document.title, url);
    </script>
<?php endif; ?>


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
        <!-- <li class="nav-item">
          <a class="nav-link" href="c_docentes.php">Agregar Estudiante</a>
        </li> -->
      </ul>
      <a class="nav-link end-0 position-absolute me-4" href="../php/csesion.php">Cerrar sesion</a>
    </div>
  </div>
</nav>

<div class="container shadow-lg rounded p-4 mt-3">
    <h2>Crear Tarea</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="clase_id" class="form-label">Clase</label>
            <select class="form-select" id="clase_id" name="clase_id" required>
                <option value="" disabled selected>Selecciona una clase</option>
                <?php foreach ($clases as $clase): ?>
                    <option value="<?= htmlspecialchars($clase['id']) ?>">
                        <?= htmlspecialchars($clase['materia_nombre']) ?> (<?= htmlspecialchars($clase['curso_nombre']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="descripcion" class="form-label">Descripción de la tarea</label>
            <textarea class="form-control" id="descripcion" name="descripcion" rows="3" required></textarea>
        </div>
        <div class="mb-3">
            <label for="fecha_entrega" class="form-label">Fecha de Entrega</label>
            <input type="date" class="form-control" id="fecha_entrega" name="fecha_entrega" required>
        </div>
        <button type="submit" class="btn btn-primary">Crear Tarea</button>
    </form>

    <h3 class="mt-4">Tareas Registradas</h3>
    <?php if (count($tareas) > 0): ?>
        <table class="table table-bordered mt-3">
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Curso</th>
                    <th>Materia</th>
                    <th>Clase</th>
                    <th>Descripción</th>
                    <th>Fecha de Entrega</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($tareas as $tarea): ?>
                    <tr>
                        <td><?= htmlspecialchars($tarea['id']) ?></td>
                        <td><?= htmlspecialchars($tarea['curso_nombre']) ?></td>
                        <td><?= htmlspecialchars($tarea['materia_nombre']) ?></td>
                        <td><?= htmlspecialchars($tarea['clase_nombre']) ?></td>
                        <td><?= htmlspecialchars($tarea['descripcion']) ?></td>
                        <td><?= htmlspecialchars($tarea['fecha_entrega']) ?></td>
                        <td>
                            <a href="editar_tarea.php?id=<?= $tarea['id'] ?>" class="btn btn-warning btn-sm">Editar</a>
                            <a href="eliminar_tarea.php?id=<?= $tarea['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('¿Estás seguro de que deseas eliminar esta tarea?')">Eliminar</a>
                            <a href="calificar_tarea.php?id=<?= $tarea['id'] ?>" class="btn btn-success btn-sm">Calificar</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p class="mt-3">No se han registrado tareas</p>
    <?php endif; ?>
</div>


<script src="../js/bootstrap.js"></script>   
</body>
</html>
