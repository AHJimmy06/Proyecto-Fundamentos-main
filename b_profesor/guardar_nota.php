<?php
// Mostrar errores para depuración
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Configuración de la conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Leer datos del cuerpo de la solicitud
$data = json_decode(file_get_contents('php://input'), true);

// Validar datos
if (!isset($data['estudianteId'], $data['tareaId'], $data['nota'])) {
    echo json_encode(['success' => false, 'message' => 'Faltan datos.']);
    exit;
}

$estudianteId = $data['estudianteId'];
$tareaId = $data['tareaId'];
$nota = $data['nota'];

try {
    // Verificar si ya existe una nota para este estudiante y tarea
    $stmt = $conn->prepare("SELECT COUNT(*) FROM calificaciones WHERE estudiante_id = :estudianteId AND tarea_id = :tareaId");
    $stmt->bindValue(':estudianteId', $estudianteId);
    $stmt->bindValue(':tareaId', $tareaId);
    $stmt->execute();
    $exists = $stmt->fetchColumn() > 0;

    if ($exists) {
        // Si ya existe, actualiza la nota
        $stmt = $conn->prepare("UPDATE calificaciones SET calificacion = :nota WHERE estudiante_id = :estudianteId AND tarea_id = :tareaId");
        $stmt->bindValue(':nota', $nota);
        $stmt->bindValue(':estudianteId', $estudianteId);
        $stmt->bindValue(':tareaId', $tareaId);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Nota actualizada correctamente.']);
    } else {
        // Si no existe, inserta una nueva
        $stmt = $conn->prepare("INSERT INTO calificaciones (estudiante_id, tarea_id, calificacion) VALUES (:estudianteId, :tareaId, :nota)");
        $stmt->bindValue(':estudianteId', $estudianteId);
        $stmt->bindValue(':tareaId', $tareaId);
        $stmt->bindValue(':nota', $nota);
        $stmt->execute();

        echo json_encode(['success' => true, 'message' => 'Nota creada correctamente.']);
    }
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Error al guardar la nota.', 'error' => $e->getMessage()]);
}
