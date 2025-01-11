<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Eliminar clase
if (isset($_GET['id'])) {
    $id = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM clases WHERE id = :id");
    $stmt->bindValue(':id', $id);

    if ($stmt->execute()) {
        header('Location: crear_clase.php'); // Redirigir después de eliminar
    } else {
        echo '<div class="alert alert-danger">Error al eliminar la clase</div>';
    }
} else {
    echo "ID de clase no especificado.";
    exit;
}
