<?php

include('../php/verificar_acceso.php');
verificarAcceso('admin');

include('../php/cone.php');
// Trae todos lo profesores para asignarlos a un curso
$conn = Conexion();

$stmt_estudiantes = $conn->prepare("SELECT e.id, u.nombre 
                                    FROM estudiantes e 
                                    JOIN usuarios u ON e.usuario_id = u.id
                                    WHERE u.rol = 'estudiante'");
$stmt_estudiantes->execute();
$estudiantes = $stmt_estudiantes->fetchAll(PDO::FETCH_ASSOC);


$stmt_materias = $conn->prepare("SELECT m.id, m.nombre FROM materias m");
$stmt_materias->execute();
$materias = $stmt_materias->fetchAll(PDO::FETCH_ASSOC);

// Verifica si el formulario fue enviado
if (isset($_POST['registrar'])) {
    // Obtener los valores del formulario
    $estudiante_id = $_POST['estudiante_id'];
    $materia_id = $_POST['materia_id'];

    // Verificar si ya está inscrito en esa materia
    $sql_check = "SELECT * FROM inscripciones WHERE estudiante_id = :estudiante_id AND materia_id = :materia_id";
    $stmt_check = $conn->prepare($sql_check);
    $stmt_check->bindParam(':estudiante_id', $estudiante_id);
    $stmt_check->bindParam(':materia_id', $materia_id);
    $stmt_check->execute();

    if ($stmt_check->rowCount() > 0) {
        // El estudiante ya está inscrito en esa materia
        $alert_message = 'Este estudiante ya está inscrito en la materia seleccionada.';
        $alert_title = "Inscripción ya registrada";
        $alert_type = "error";
    } else {
        // Si el estudiante no está inscrito, entonces insertamos la inscripción

        // Iniciar transacción
        $conn->beginTransaction();

        // SQL para insertar la inscripción
        $sql = "INSERT INTO inscripciones (estudiante_id, materia_id) VALUES (:estudiante_id, :materia_id)";
        $stmt = $conn->prepare($sql);
        $stmt->bindParam(':estudiante_id', $estudiante_id);
        $stmt->bindParam(':materia_id', $materia_id);

        // Ejecutar la inserción
        $stmt->execute();
        $conn->commit(); // Confirmar la transacción

        // Obtener el nombre del estudiante
        $sql_estudiante = "SELECT u.nombre FROM estudiantes e JOIN usuarios u ON e.usuario_id = u.id WHERE e.id = :estudiante_id";
        $stmt_estudiante = $conn->prepare($sql_estudiante);
        $stmt_estudiante->bindParam(':estudiante_id', $estudiante_id);
        $stmt_estudiante->execute();
        $estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);
        $estudiante_nombre = $estudiante['nombre'];

        // Obtener el nombre de la materia
        $sql_materia = "SELECT nombre FROM materias WHERE id = :materia_id";
        $stmt_materia = $conn->prepare($sql_materia);
        $stmt_materia->bindParam(':materia_id', $materia_id);
        $stmt_materia->execute();
        $materia = $stmt_materia->fetch(PDO::FETCH_ASSOC);
        $materia_nombre = $materia['nombre'];
		
		// Si todo es correcto
        $alert_type = "success";
        $alert_title = "Inscripción registrada correctamente";
        $alert_message = "<strong>Estudiante: </strong>" . htmlspecialchars($estudiante_nombre) . "<br><strong>Materia: </strong>" . htmlspecialchars($materia_nombre);
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<title>FISEI || Cursos</title>
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
		<nav class="full-box dashboard-Navbar">
			<ul class="full-box list-unstyled text-right">
				<li class="pull-left">
					<a href="#!" class="btn-menu-dashboard"><i class="zmdi zmdi-more-vert"></i></a>
				</li>
			</ul>
		</nav>
		<!-- Content page -->
		<div class="container-fluid">
		<div class="page-header">
			  <h1 class="text-titles"><i class="zmdi zmdi-font zmdi-hc-fw"></i>Inscripciones  </h1>
			</div>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs" style="margin-bottom: 15px;">
					  	<li><a href="inscripciones.php">Lista</a></li>
					  	<li class="active"><a href="#new" data-toggle="tab">Registrar</a></li>
					</ul>
					<div class="tab-pane fade active in" id="new">
						<div class="container-fluid">
							<div class="row">
								<div class="col-xs-12 col-md-10 col-md-offset-1">
								<form method="POST" id="formcurso">
									<div class="form-group">
										<label class="control-label" for="materia_id">Materia</label>
										<select class="form-control" name="materia_id" id="materia_id" required>
											<option value="">Seleccione una Materia</option>
											<?php foreach ($materias as $materia): ?>
                                                    <option value="<?php echo $materia['id']; ?>"><?php echo htmlspecialchars($materia['nombre']); ?></option>
                                                <?php endforeach; ?>
										</select>
										<label class="control-label" for="estudiante_id">Estudiante</label>
										<select class="form-control" name="estudiante_id" id="estudiante_id" required>
											<option value="">Seleccione un Estudiante</option>
											<?php foreach ($estudiantes as $estudiante): ?>
											<option value="<?php echo $estudiante['id']; ?>"><?php echo htmlspecialchars($estudiante['nombre']); ?></option>
											<?php endforeach; ?>
										</select>
									</div>
									<p class="text-center" >
										<button id="form-btn" name="registrar" type="submit" class="btn btn-info btn-raised btn-sm" disabled><i class="zmdi zmdi-floppy"></i> Registrar</button>
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
        // Validación del formulario
        const form = document.querySelector('form');
        const estudianteSelect = form.querySelector('#estudiante_id');
        const materiaSelect = form.querySelector('#materia_id');
        const submitButton = form.querySelector('button');

        form.addEventListener('input', () => {
            const hasValidEstudiante = estudianteSelect.value !== "";
            const hasValidMateria = materiaSelect.value !== "";
            submitButton.disabled = !(hasValidEstudiante && hasValidMateria);
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