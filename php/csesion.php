<?php
// logout.php
session_start(); // Iniciar sesión si no está iniciada aún

// Eliminar todas las variables de sesión
session_unset();

// Destruir la sesión
session_destroy();

// Redirigir al login o página principal
header("Location: ../index.php"); // Redirige al login después de cerrar sesión
exit();
?>
