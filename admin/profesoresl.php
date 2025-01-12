<?php 
include('../php/verificar_acceso.php');
verificarAcceso('admin');

include('../php/cone.php');

// Mostrar registros de profesores
$conn = Conexion();
$stmt = $conn->prepare("SELECT * FROM usuarios WHERE rol = 'profesor' ");
$stmt->execute();
$profesores = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Registrar un nuevo profesor
if(isset($_POST['registrar']))
{
    $nombre = $_POST['nombre'];
    $cedula = $_POST['cedula'];
    $especialidad = $_POST['especialidad'];

    // Verificar si el profesor ya está registrado
    $sql_check = "SELECT * FROM usuarios WHERE cedula = :cedula";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':cedula', $cedula);
    $stmt_check->execute();

    if($stmt_check->rowCount() > 0) {
        // Si el profesor ya está registrado
        $alert_message = '';
        $alert_title = "Profesor ya registrado";
        $alert_type = "error";
    } else {
        try {
            // Iniciar transacción
            $conn->beginTransaction();

            // Registrar el usuario como profesor en la tabla 'usuarios'
            $sql = "INSERT INTO usuarios (nombre, cedula, rol) VALUES (:nombre, :cedula, :rol)";
            $stmt1 = $conn->prepare($sql);
            $rol = 'profesor';  // Definir el rol como profesor
            $stmt1->bindParam(':nombre', $nombre);
            $stmt1->bindParam(':cedula', $cedula);
            $stmt1->bindParam(':rol', $rol);
            $stmt1->execute();

            // Obtener el ID del nuevo profesor insertado
            $userId = $conn->lastInsertId();

            // Insertar especialidad en la tabla 'profesores'
            $sql_profesor = "INSERT INTO profesores (usuario_id, especialidad) VALUES (:usuario_id, :especialidad)";
            $stmt_profesor = $conn->prepare($sql_profesor);
            $stmt_profesor->bindParam(':usuario_id', $userId);
            $stmt_profesor->bindParam(':especialidad', $especialidad);
            $stmt_profesor->execute();

            // Confirmar transacción
            $conn->commit();

            // Obtener el correo y contraseña del profesor recién creado
            $sql_get = "SELECT correo, contrasenia FROM usuarios WHERE cedula = :cedula";
            $stmt_get = $conn->prepare($sql_get);
            $stmt_get->bindParam(':cedula', $cedula);
            $stmt_get->execute();
            $user_data = $stmt_get->fetch(PDO::FETCH_ASSOC);

            $correo = $user_data['correo'];
            $contrasenia = $user_data['contrasenia'];

            // Mensaje de éxito
            $alert_type = "success";
            $alert_title = "Registrado correctamente";
            $alert_message = '<h1 class="text-titles full-box list-unstyled">Correo: <small>' . $correo . '</small></h1>
            <h1 class="text-titles">Contraseña: <small>' . $contrasenia . '</small></h1>';
        } catch (Exception $e) {
            // Si ocurre algún error, revertir transacción
            $conn->rollBack();
            $alert_message = "Hubo un error al registrar el profesor. Intenta nuevamente.";
            $alert_title = "Error";
            $alert_type = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
	<title>FISEI || Materia</title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
	<link rel="stylesheet" href="./css/main.css">
</head>
<body>
	<!-- SideBar -->
	<section class="full-box cover dashboard-sideBar ">
		<div class="full-box btn-menu-dashboard "></div>
		<div class="full-box dashboard-sideBar-ct ">
			<!--SideBar Title -->
			<div class="full-box text-uppercase text-center text-titles dashboard-sideBar-title ">
			 	<img src="../img/nav.png" width="250px"> <i class="zmdi zmdi-close btn-menu-dashboard visible-xs"></i>
			</div>
			<!-- SideBar User info -->
			<div class="full-box dashboard-sideBar-UserInfo text-center">
					<h3>Administrador</h3>
				<ul class="full-box list-unstyled text-center">
					<li >
						<a href="#!" class="btn-exit-system">
							<i class="zmdi zmdi-power zmdi-hc-fw"></i>Cerrar Sesión
						</a>
					</li>
				</ul>
			</div>
			<!-- SideBar Menu -->
			<ul class="list-unstyled full-box dashboard-sideBar-Menu">
				<li>
					<a href="paneldecontrol.php">
						<i class="zmdi zmdi-view-dashboard zmdi-hc-fw"></i> Panel de Control
					</a>
				</li>
				<li>
					<a href="#!" class="btn-sideBar-SubMenu">
						<i class="zmdi zmdi-account-add zmdi-hc-fw"></i> Usuarios <i class="zmdi zmdi-caret-down pull-right"></i>
					</a>
					<ul class="list-unstyled full-box">
						<li>
							<a href="profesores.php"><i class="zmdi zmdi-male-alt zmdi-hc-fw"></i> Profesor</a>
						</li>
						<li>
							<a href="estudiantes.php"><i class="zmdi zmdi-face zmdi-hc-fw"></i> Estudiante</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#!" class="btn-sideBar-SubMenu">
						<i class="zmdi zmdi-case zmdi-hc-fw"></i> Administración <i class="zmdi zmdi-caret-down pull-right"></i>
					</a>
					<ul class="list-unstyled full-box">
						<li>
							<a href="cursos.php"><i class="zmdi zmdi-timer zmdi-hc-fw"></i> Cursos</a>
						</li>
						<li>
							<a href="materias.php"><i class="zmdi zmdi-book zmdi-hc-fw"></i> Materias</a>
						</li>
						<li>
							<a href="inscripciones.php"><i class="zmdi zmdi-font zmdi-hc-fw"></i> Inscripciones</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#!" class="btn-sideBar-SubMenu">
						<i class="zmdi zmdi-shield-security zmdi-hc-fw"></i> General <i class="zmdi zmdi-caret-down pull-right"></i>
					</a>
					<ul class="list-unstyled full-box">
						<li>
							<a href="datosgenerales.php"><i class="zmdi zmdi-balance zmdi-hc-fw"></i> Datos Generales</a>
						</li>
					</ul>
				</li>
			</ul>
		</div>
	</section>

	<!-- Content page-->
	<section class="full-box dashboard-contentPage">
		<!-- NavBar -->
		<nav class="full-box dashboard-Navbar bg-red">
			<ul class="full-box list-unstyled text-right">
				<li class="pull-left">
					<a href="#!" class="btn-menu-dashboard"><i class="zmdi zmdi-more-vert"></i></a>
				</li>
			</ul>
		</nav>
		<!-- Content page -->
		<div class="container-fluid">
			<div class="page-header">
			  <h1 class="text-titles"><i class="zmdi zmdi-male-alt zmdi-hc-fw"></i> Profesores</h1>
			</div>
		</div>
		<div class="container-fluid ">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs bg-red" style="margin-bottom: 15px;">
					  	<li><a href="profesores.php">Lista</a></li>
					  	<li class="active"><a href="#new" data-toggle="tab">Registrar</a></li>
					</ul>
					<div class="tab-pane fade active in" id="new">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-md-10 col-md-offset-1">
                                    <form method="POST" id="formestudiante">
                                        <fieldset>Datos del Profesor</fieldset>
                                        <div class="form-group label-floating">
                                            <label class="control-label" for="nombre">Nombre Completo</label>
                                            <input class="form-control" type="text" id="nombre" name="nombre" required>
                                        </div>
                                        <div class="form-group label-floating">
                                            <label class="control-label" for="cedula">Cédula</label>
                                            <input class="form-control" type="text" maxlength="10" id="cedula" name="cedula" required pattern="[0-9]+">
                                        </div>
                                        <div class="form-group label-floating">
                                            <label class="control-label" for="especialidad">Especialidad</label>
                                            <input class="form-control" type="text" maxlength="100" id="especialidad" name="especialidad" pattern="[A-Za-záéíóúÁÉÍÓÚ\s]+" required>
                                        </div>
                                        <p class="text-center">
                                            <button  id="formbtn" name="registrar" type="submit" class="btn btn-info btn-raised btn-sm" disabled><i class="zmdi zmdi-floppy"></i> Registrar</button>
                                        </p>
                                    </form>
                                    <div><a class="btn-formpro">Formato de registro</a></div>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</section>
	<!--====== Scripts -->
	<script src="./js/jquery-3.1.1.min.js"></script>
	<script src="./js/sweetalert2.min.js"></script>
	<script src="./js/bootstrap.min.js"></script>
	<script src="./js/material.min.js"></script>
	<script src="./js/ripples.min.js"></script>
	<script src="./js/jquery.mCustomScrollbar.concat.min.js"></script>
	<script src="./js/main.js"></script>
	<script>
		$.material.init();
	</script>
    <script>
    // Selecciona el formulario y los campos
    const form = document.getElementById('formestudiante');
    const cedula = document.getElementById('cedula');
    const nombre = document.getElementById('nombre');
    const especialidad = document.getElementById('especialidad');
    const submitButton = document.getElementById('formbtn');

    // Función para verificar que la cédula tenga exactamente 10 dígitos
    const hasTenDigits = () => cedula.value.length === 10;

    // Función para verificar que el nombre tenga más de una palabra
    const hasValidName = () => nombre.value.trim().split(/\s+/).length > 1;

    // Función para verificar que la especialidad sea válida si se ingresa (solo letras y espacios)
    const isValidSpecialty = () => {
        // Si el campo de especialidad está vacío, es válido. Si no, debe contener solo letras y espacios.
        if (especialidad.value === "") return true;
        const regex = /^[A-Za-záéíóúÁÉÍÓÚ\s]+$/;
        return regex.test(especialidad.value);
    };

    // Escucha los eventos de entrada (input) en el formulario
    form.addEventListener('input', () => {
        // Verifica si todos los campos requeridos están llenos y válidos
        const isValid = form.checkValidity() && hasTenDigits() && hasValidName() && isValidSpecialty();

        // Habilita o deshabilita el botón de enviar
        submitButton.disabled = !isValid;
    });
</script>
<script>
	<?php if (isset($alert_message) && isset($alert_type)): ?>

        swal({  // Tipo de alerta (success, error)
			title: '<?= $alert_title ?>',
            html: `<?= $alert_message ?>`,
			type: '<?= $alert_type ?>',  // El mensaje de la alerta
            showConfirmButton: true, // Mostrar el botón de confirmación
			confirmButtonColor: '#640d14',
		  	confirmButtonText: ' Continuar',
        }).then(function () {
			window.location.href="#!";
		});
    <?php endif; ?>
</script>

</body>
</html>