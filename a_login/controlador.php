<?php
if (!empty($_POST)) {
    if (empty($_POST["usuario"]) || empty($_POST["contrasenia"])) {
        echo '<div class="alert alert-danger text-center">INGRESE SU CORREO Y CONTRASEÑA</div>';
    } else {
        include('php/cone.php');
        $conn = Conexion();
        $usuario = $_POST["usuario"];
        $clave = $_POST["contrasenia"];
       
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE correo = :usuario AND contrasenia = :clave");
        $stmt->bindValue(':usuario', $usuario);
        $stmt->bindValue(':clave', $clave);
        $stmt->execute();
        $resultado = $stmt->fetch(PDO::FETCH_ASSOC);
    
        if ($resultado) {
            session_start();
            $_SESSION['usuario'] = $resultado['correo'];
            $_SESSION['rol'] = $resultado['rol'];
            $_SESSION['id']=$resultado['id'];
            
            $tipo = $resultado['rol'];
            
            if ($tipo === 'admin') {
                header("Location: admin/paneldecontrol.php");
            } elseif ($tipo === 'profesor') {
                header("Location: b_profesor/crear_clase.php");
            } elseif ($tipo === 'estudiante') {
                header("Location: b_estudiante/estudiante.php");
            }
            exit();
        } else {
            echo '<div class="alert alert-danger text-center">USUARIO O CONTRASEÑA INCORRECTOS</div>';
        }
    }
}
?>
