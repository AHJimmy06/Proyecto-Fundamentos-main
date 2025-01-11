<?php
// Incluir archivo para verificar acceso
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Verificar si se ha recibido el ID de la tarea
if (isset($_GET['id'])) {
    $tareaId = $_GET['id'];

    // Preparar la consulta para eliminar la tarea
    $stmt = $conn->prepare("DELETE FROM tareas WHERE id = :id");
    $stmt->bindValue(':id', $tareaId);

    if ($stmt->execute()) {
        // Redirigir a la página de crear tareas con un mensaje de éxito
        header('Location: crear_tarea.php?mensaje=Eliminada con éxito');
        exit();
    } else {
        // Si hay un error, redirigir con un mensaje de error
        header('Location: crear_tarea.php?mensaje=Error al eliminar la tarea');
        exit();
    }
} else {
    // Si no se ha recibido un ID, redirigir con un mensaje de error
    header('Location: crear_tarea.php?mensaje=ID de tarea no válido');
    exit();
}
?>
