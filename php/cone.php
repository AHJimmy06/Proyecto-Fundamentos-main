<?php
function Conexion(): PDO {
    $server = 'localhost';
    $usuario = "root";
    $clave = "Juanpatitoracista1234."; // Recomendación: Usa variables de entorno para esto.
    try {
        $conn = new PDO("mysql:host=" . $server . ";dbname=sistemaeducativo", $usuario, $clave);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn; // Devuelves la conexión.
    } catch (PDOException $e) {
        // Mejor manejar el error de la conexión y mostrar el mensaje.
        echo "Error de conexión: " . $e->getMessage();
        exit(); // Detenemos la ejecución si la conexión falla.
    }
}
?>
