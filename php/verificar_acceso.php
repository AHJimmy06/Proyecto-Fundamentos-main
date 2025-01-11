<?php
function verificarAcceso($rolPermitido) {
    session_start();
    if (!isset($_SESSION['usuario']) || !isset($_SESSION['rol'])) {
        // Redirige a la página de inicio de sesión si no hay sesión
        header("Location: login.php");
        exit();
    }

    if ($_SESSION['rol'] !== $rolPermitido) {
        // Redirige a una página de error si el rol no coincide
        header("Location: ../error_acceso.php");
        exit();
    }
}
?>
