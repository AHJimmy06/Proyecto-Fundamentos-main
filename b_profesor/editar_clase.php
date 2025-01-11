<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Obtener los datos de la clase a editar
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("SELECT * FROM clases WHERE id = :id");
    $stmt->bindValue(':id', $id);
    $stmt->execute();
    $clase = $stmt->fetch(PDO::FETCH_ASSOC);

    // Si no existe la clase con ese ID
    if (!$clase) {
        echo "Clase no encontrada.";
        exit;
    }

    // Actualización de la clase
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $materiaId = $_POST['materia_id'];
        $fecha = $_POST['fecha'];
        $tema = $_POST['tema'];

        if (!empty($materiaId) && !empty($fecha) && !empty($tema)) {
            $stmt = $conn->prepare("UPDATE clases SET materia_id = :materia_id, fecha = :fecha, tema = :tema WHERE id = :id");
            $stmt->bindValue(':materia_id', $materiaId);
            $stmt->bindValue(':fecha', $fecha);
            $stmt->bindValue(':tema', $tema);
            $stmt->bindValue(':id', $id);

            if ($stmt->execute()) {
                echo '<div class="alert alert-success">Clase actualizada exitosamente</div>';
                header('Location: crear_clase.php'); // Redirigir después de editar
            } else {
                echo '<div class="alert alert-danger">Error al actualizar la clase</div>';
            }
        } else {
            echo '<div class="alert alert-warning">Todos los campos son obligatorios</div>';
        }
    }
} else {
    echo "ID de clase no especificado.";
    exit;
}

// Obtener materias disponibles para el combobox
$stmt = $conn->prepare("SELECT m.id, m.nombre, c.nombre AS curso_nombre FROM materias m JOIN cursos c ON m.curso_id = c.id");
$stmt->execute();
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Clase</title>
    <link href="../css/bootstrap.css" rel="stylesheet">
</head>
<body class="bg-body-tertiary">
<div class="container shadow-lg rounded p-4 mt-3">
    <h2>Editar Clase</h2>
    <form method="POST" action="">
        <div class="mb-3">
            <label for="materia_id" class="form-label">Materia</label>
            <select class="form-select" id="materia_id" name="materia_id" required>
                <option value="" disabled>Selecciona una materia</option>
                <?php foreach ($materias as $materia): ?>
                    <option value="<?= htmlspecialchars($materia['id']) ?>" <?= $materia['id'] == $clase['materia_id'] ? 'selected' : '' ?>>
                        <?= htmlspecialchars($materia['nombre']) ?> (<?= htmlspecialchars($materia['curso_nombre']) ?>)
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="mb-3">
            <label for="fecha" class="form-label">Fecha</label>
            <input type="date" class="form-control" id="fecha" name="fecha" value="<?= htmlspecialchars($clase['fecha']) ?>" required>
        </div>
        <div class="mb-3">
            <label for="tema" class="form-label">Tema</label>
            <input type="text" class="form-control" id="tema" name="tema" value="<?= htmlspecialchars($clase['tema']) ?>" required>
        </div>
        <button type="submit" class="btn btn-primary">Actualizar Clase</button>
    </form>
</div>
<script src="../js/bootstrap.js"></script>
</body>
</html>
