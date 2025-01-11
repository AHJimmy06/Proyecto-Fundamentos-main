<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $materiaId = $_POST['materia_id'];
  $fecha = $_POST['fecha'];
  $tema = $_POST['tema'];

  if (!empty($materiaId) && !empty($fecha) && !empty($tema)) {
      // Insertar nueva clase en la base de datos
      $stmt = $conn->prepare("INSERT INTO clases (materia_id, fecha, tema) VALUES (:materia_id, :fecha, :tema)");
      $stmt->bindValue(':materia_id', $materiaId);
      $stmt->bindValue(':fecha', $fecha);
      $stmt->bindValue(':tema', $tema);

      if ($stmt->execute()) {
          echo '<div class="alert alert-success">Clase creada exitosamente</div>';
          echo '<script>document.getElementById("fecha").value = ""; document.getElementById("tema").value = ""; document.getElementById("materia_id").selectedIndex = 0;</script>';
      } else {
          echo '<div class="alert alert-danger">Error al crear la clase</div>';
      }
  } else {
      echo '<div class="alert alert-warning">Todos los campos son obligatorios</div>';
  }
}


// Obtener materias disponibles para el combobox
$stmt = $conn->prepare("SELECT m.id, m.nombre, m.descripcion, c.nombre AS curso_nombre 
                        FROM materias m
                        JOIN cursos c ON m.curso_id = c.id");
$stmt->execute();
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener clases registradas
$stmt = $conn->prepare("SELECT clases.id, materias.nombre AS materia_nombre, cursos.nombre AS curso_nombre, clases.fecha, clases.tema
                        FROM clases
                        JOIN materias ON clases.materia_id = materias.id
                        JOIN cursos ON materias.curso_id = cursos.id");
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
        <!-- <li class="nav-item">
          <a class="nav-link" href="c_docentes.php">Agregar Estudiante</a>
        </li> -->
      </ul>
      <a class="nav-link end-0 position-absolute me-4" href="../php/csesion.php">Cerrar sesion</a>
    </div>
  </div>
</nav>

<div class="container shadow-lg rounded p-4 mt-3">
    <h2>Crear Clase</h2>
    <form method="POST" action="">
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
            <input type="date" class="form-control" id="fecha" name="fecha" required>
        </div>
        <div class="mb-3">
            <label for="tema" class="form-label">Tema</label>
            <input type="text" class="form-control" id="tema" name="tema" required>
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
</body>
</html>
