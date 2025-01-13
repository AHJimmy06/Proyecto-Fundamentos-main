<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion();

// Obtener ID del profesor
$profesorId = $_SESSION['id']; // Suponiendo que el ID del profesor está en la sesión

// Peso máximo en bytes (5 MB)
const MAX_FILE_SIZE = 5 * 1024 * 1024;

// Manejar envío del formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $materiaId = $_POST['materia_id'];
    $fecha = $_POST['fecha'];
    $tema = $_POST['tema'];
    $archivoSubido = isset($_FILES['material_apoyo']) ? $_FILES['material_apoyo'] : null;

    if (!empty($materiaId) && !empty($fecha) && !empty($tema)) {
        try {
            // Validar tamaño del archivo
            if ($archivoSubido && $archivoSubido['error'] === UPLOAD_ERR_OK) {
                if ($archivoSubido['size'] > MAX_FILE_SIZE) {
                    throw new Exception('El archivo supera el tamaño máximo permitido de 5 MB.');
                }
            }

            // Iniciar transacción
            $conn->beginTransaction();

            // Insertar nueva clase en la base de datos
            $stmt = $conn->prepare("INSERT INTO clases (materia_id, fecha, tema) VALUES (:materia_id, :fecha, :tema)");
            $stmt->bindValue(':materia_id', $materiaId);
            $stmt->bindValue(':fecha', $fecha);
            $stmt->bindValue(':tema', $tema);
            $stmt->execute();

            $claseId = $conn->lastInsertId(); // Obtener el ID de la clase creada

            // Manejar archivo subido
            if ($archivoSubido && $archivoSubido['error'] === UPLOAD_ERR_OK) {
                $nombreArchivo = basename($archivoSubido['name']);
                $rutaArchivo = '../archivos/' . uniqid() . '_' . $nombreArchivo;
                $tipoArchivo = $archivoSubido['type'];

                // Mover archivo al directorio de almacenamiento
                if (move_uploaded_file($archivoSubido['tmp_name'], $rutaArchivo)) {
                    // Insertar registro en la tabla `archivos`
                    $stmtArchivo = $conn->prepare(
                        "INSERT INTO archivos (nombre_archivo, tipo_archivo, ruta_archivo, usuario_id, tarea_id) 
                         VALUES (:nombre_archivo, :tipo_archivo, :ruta_archivo, :usuario_id, NULL)"
                    );
                    $stmtArchivo->bindValue(':nombre_archivo', $nombreArchivo);
                    $stmtArchivo->bindValue(':tipo_archivo', $tipoArchivo);
                    $stmtArchivo->bindValue(':ruta_archivo', $rutaArchivo);
                    $stmtArchivo->bindValue(':usuario_id', $profesorId);
                    $stmtArchivo->execute();
                } else {
                    throw new Exception('Error al mover el archivo al directorio de almacenamiento.');
                }
            }

            $conn->commit();
            $alert_type = "success";
            $alert_title = "Clase creada correctamente";
            $alert_message = "Clase creada exitosamente con material de apoyo.";
        } catch (Exception $e) {
            $conn->rollBack();
            $alert_type = "error";
            $alert_title = "Error al crear la clase";
            $alert_message = "Error al crear la clase: " . htmlspecialchars($e->getMessage());
        }
    } else {
        $alert_type = "warning";
        $alert_title = "Campos incompletos";
        $alert_message = "Todos los campos son obligatorios.";
    }
}

// Obtener materias disponibles para el profesor
$stmt = $conn->prepare(
    "SELECT m.id, m.nombre, m.descripcion, c.nombre AS curso_nombre 
     FROM materias m
     JOIN cursos c ON m.curso_id = c.id
     WHERE c.profesor_id = :profesor_id"
);
$stmt->bindValue(':profesor_id', $profesorId);
$stmt->execute();
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Obtener clases registradas
$stmt = $conn->prepare(
    "SELECT clases.id, materias.nombre AS materia_nombre, cursos.nombre AS curso_nombre, clases.fecha, clases.tema
     FROM clases
     JOIN materias ON clases.materia_id = materias.id
     JOIN cursos ON materias.curso_id = cursos.id
     WHERE cursos.profesor_id = :profesor_id"
);
$stmt->bindValue(':profesor_id', $profesorId);
$stmt->execute();
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
					<h3>Profesor</h3>
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
					<a href="paneldecontrol.php">
                        <i class="zmdi zmdi-book zmdi-hc-fw"></i> Clases
					</a>
				</li>
                <li>
					<a href="paneldecontrol.php">
                        <i class="zmdi zmdi-timer zmdi-hc-fw"></i> Tareas 
					</a>
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
			  <h1 class="text-titles"><i class="zmdi zmdi-timer zmdi-hc-fw"></i> Cursos </h1>
			</div>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs" style="margin-bottom: 15px;">
					  	<li><a href="cursos.php">Lista</a></li>
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
										<label class="control-label" for="profesor_id">Profesor a cargo</label>
										<select class="form-control" name="profesor_id" id="profesor_id" required>
											<option value="">Seleccione un profesor</option>
											<?php foreach ($profesores as $profesor): ?>
												<option value="<?php echo $profesor['id']; ?>"><?php echo htmlspecialchars($profesor['nombre']); ?></option>
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
    document.querySelector('form').addEventListener('submit', function(e) {
        const archivo = document.getElementById('material_apoyo').files[0];
        if (archivo && archivo.size > 5 * 1024 * 1024) {
            e.preventDefault();
            alert('El archivo no puede superar los 5 MB.');
        }
    });
    </script>

    <script>
    <?php if (isset($alert_message) && isset($alert_type)): ?>
        Swal.fire({
            title: '<?= $alert_title ?>',
            text: '<?= $alert_message ?>',
            type: '<?= $alert_type ?>',
            showConfirmButton: true, // Mostrar el botón de confirmación
            confirmButtonColor: '#640d14',
            confirmButtonText: ' Continuar',
        });
    <?php endif; ?>
    </script>

</body>
</html>
