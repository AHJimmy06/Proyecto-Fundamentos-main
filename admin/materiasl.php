<?php

include('../php/verificar_acceso.php');
	verificarAcceso('admin');

include('../php/cone.php');
// Trae todos los cursos para asignarlos a la materia
$conn = Conexion();
$stmt = $conn->prepare("SELECT * FROM cursos");
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST['registrar'])) {
    // Obtener los valores del formulario
    $nombre = $_POST['nombre'];
    $descripcion = $_POST['descripcion'];
    $curso_id = $_POST['curso_id'];

    // Verificar si la materia ya está registrada (en caso de que el nombre sea único)
    $sql_check = "SELECT * FROM materias WHERE nombre = :nombre AND curso_id = :curso_id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':nombre', $nombre);
    $stmt_check->bindParam(':curso_id', $curso_id);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        // La materia ya está registrada
        $alert_message = '';
        $alert_title = "Materia ya registrada";
        $alert_type = "error";
    } else {
        // Si la materia no está registrada, entonces insertamos un nuevo registro
        
        // Iniciar transacción para insertar la nueva materia
        $conn->beginTransaction();

        // SQL para insertar la nueva materia
        $sql = "INSERT INTO materias (nombre, descripcion, curso_id) VALUES (:nombre, :descripcion, :curso_id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':nombre', $nombre);
        $stmt->bindParam(':descripcion', $descripcion);
        $stmt->bindParam(':curso_id', $curso_id);

        // Ejecutar la inserción
        $stmt->execute();
        $conn->commit(); // Confirmar la transacción

        // Obtener el nombre del curso registrado para mostrar en el mensaje
        $sql_curso = "SELECT nombre FROM cursos WHERE id = :curso_id";
        $stmt_curso = $conn->prepare($sql_curso);
        $stmt_curso->bindParam(':curso_id', $curso_id);
        $stmt_curso->execute();
        $curso_data = $stmt_curso->fetch(PDO::FETCH_ASSOC);
        $curso_nombre = $curso_data['nombre'];

        // Si todo es correcto
        $alert_type = "success";
        $alert_title = "Materia registrada correctamente";
        $alert_message = "<strong><h1>Materia: </strong>" . htmlspecialchars($nombre) . "<strong><p>Curso: </strong>" . htmlspecialchars($curso_nombre) . "</p></h1>";
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
		<nav class="full-box dashboard-Navbar">
			<ul class="full-box list-unstyled text-right bg-red">
				<li class="pull-left">
					<a href="#!" class="btn-menu-dashboard"><i class="zmdi zmdi-more-vert"></i></a>
				</li>
			</ul>
		</nav>
		<!-- Content page -->
		<div class="container-fluid">
			<div class="page-header">
			  <h1 class="text-titles"><i class="zmdi zmdi-book zmdi-hc-fw"></i>Materias</h1>
			</div>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs bg-red" style="margin-bottom: 15px;">
					  	<li><a href="materias.php">Lista</a></li>
					  	<li class="active"><a href="#new" data-toggle="tab">Registrar</a></li>
					</ul>
					<div class="tab-pane fade active in" id="new">
						<div class="container-fluid">
							<div class="row">
								<div class="col-xs-12 col-md-10 col-md-offset-1">
									<form method="POST" id="formcurso">
										<div class="form-group label-floating">
											<label class="control-label" for="nombre">Nombre</label>
											<input class="form-control" type="text" id="nombre" name="nombre" required>
										</div>
										<div class="form-group label-floating">
											<label class="control-label" for="descripcion">Descripción</label>
											<input class="form-control" type="text" id="descripcion" name="descripcion" required>
										</div>
										<div class="form-group">
											<label class="control-label" for="curso_id">Curso</label>
											<select class="form-control" name="curso_id" id="curso_id" required>
											<option value="">Seleccione un curso</option>
												<?php foreach ($cursos as $curso): ?>
													<option value="<?php echo $curso['id']; ?>"><?php echo htmlspecialchars($curso['nombre']); ?></option>
												<?php endforeach; ?>
											</select>
										</div>
										<p class="text-center">
											<button id="form-btn" name="registrar" class="btn btn-info btn-raised btn-sm" type="submit" disabled><i class="zmdi zmdi-floppy"></i> Registrar</button>
										</p>
									</form>
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
    // Selecciona el formulario y los campos necesarios
    const form = document.querySelector('form');
    const nombre = document.getElementById('nombre');
    const descripcion = document.getElementById('descripcion');
    const selectProfesor = form.querySelector('select');
    const submitButton = form.querySelector('button');

    // Escucha los eventos de entrada (input) en el formulario
    form.addEventListener('input', () => {
        // Verifica si el nombre tiene contenido
        const hasValidName = nombre.value.trim().length > 0;
        // Verifica si la descripción tiene contenido
        const hasValidDescription = descripcion.value.trim().length > 0;
        // Verifica si el select tiene un valor diferente de vacío
        const isProfesorSelected = selectProfesor.value !== "";

        // Verifica si todos los campos requeridos están llenos y válidos
        const isValid = hasValidName && hasValidDescription && isProfesorSelected;
        
        // Habilita o deshabilita el botón según el estado de validación
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