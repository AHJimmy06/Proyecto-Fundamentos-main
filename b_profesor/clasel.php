<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion(); // Asumiendo que esta función retorna un objeto PDO

// Obtener ID del profesor
$profesorId = $_SESSION['id']; // Suponiendo que el ID del profesor está en la sesión
$profesorNombre = $_SESSION['nombre'];

// Obtener materias asociadas al profesor
$query = "SELECT m.id, m.nombre 
FROM materias m
INNER JOIN cursos c ON m.curso_id = c.id
WHERE c.profesor_id = :profesor_id";
$stmt = $conn->prepare($query);
$stmt->bindParam(':profesor_id', $profesorId, PDO::PARAM_INT);
$stmt->execute();
$materias = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se ha enviado el formulario
if (isset($_POST['registrar'])) {
    // Recoger los datos del formulario
    $materiaId = $_POST['materias'];
    $temaClase = $_POST['tema'];
    $archivoRuta = null;

    // Validar los campos obligatorios
    if ($materiaId && $temaClase) {
        // Verificar si el tema ya existe para la materia seleccionada
        $queryVerificar = "SELECT COUNT(*) FROM clases WHERE materia_id = :materia_id AND tema = :tema";
        $stmtVerificar = $conn->prepare($queryVerificar);
        $stmtVerificar->bindParam(':materia_id', $materiaId, PDO::PARAM_INT);
        $stmtVerificar->bindParam(':tema', $temaClase, PDO::PARAM_STR);
        $stmtVerificar->execute();
        $existeTema = $stmtVerificar->fetchColumn();

        if ($existeTema > 0) {
            // Si el tema ya existe, mostrar una alerta y detener el proceso
            $alert_message = "El tema '$temaClase' ya ha sido registrado para esta materia.";
            $alert_type = "error";
            $alert_title = "¡Error!";
        } else {
            // Manejo del archivo (si existe)
            if (isset($_FILES['archivo_complementario']) && $_FILES['archivo_complementario']['error'] == 0) {
                $maxFileSize = 5 * 1024 * 1024; // Tamaño máximo 5MB
                $carpetaDestino = '../archivos/'; // Carpeta para almacenar los archivos

                // Verificar el tamaño del archivo
                if ($_FILES['archivo_complementario']['size'] <= $maxFileSize) {
                    $nombreArchivo = $_FILES['archivo_complementario']['name'];
                    $rutaTemporal = $_FILES['archivo_complementario']['tmp_name'];

                    // Verificar la extensión del archivo (permitir solo ciertas extensiones)
                    $extensionesPermitidas = ['pdf', 'doc', 'docx', 'jpg', 'png', 'zip', 'txt']; // Agrega más extensiones si lo deseas
                    $ext = pathinfo($nombreArchivo, PATHINFO_EXTENSION);

                    if (in_array($ext, $extensionesPermitidas)) {
                        // Crear un nombre único para el archivo
                        $nombreArchivoUnico = uniqid('archivo_', true) . '.' . $ext;
                        $rutaFinal = $carpetaDestino . $nombreArchivoUnico;

                        // Intentar mover el archivo a la carpeta destino
                        if (move_uploaded_file($rutaTemporal, $rutaFinal)) {
                            $archivoRuta = $rutaFinal; // Guardar la ruta del archivo
                        } else {
                            $alert_message = "Error al mover el archivo al servidor.";
                            $alert_type = "error";
                            $alert_title = "¡Error!";
                        }
                    } else {
                        $alert_message = "El archivo tiene una extensión no permitida. Solo se permiten archivos PDF, DOC, DOCX, JPG, PNG y ZIP.";
                        $alert_type = "error";
                        $alert_title = "¡Error!";
                    }
                } else {
                    $alert_message = "El archivo no debe exceder los 5 MB.";
                    $alert_type = "error";
                    $alert_title = "¡Error!";
                }
            }

            // Registrar la clase en la base de datos
            try {
                // Insertar la clase en la tabla 'clases'
                $query = "INSERT INTO clases (materia_id, tema, fecha) VALUES (:materia_id, :tema, NOW())";
                $stmt = $conn->prepare($query);
                $stmt->bindParam(':materia_id', $materiaId, PDO::PARAM_INT);
                $stmt->bindParam(':tema', $temaClase, PDO::PARAM_STR);
                $stmt->execute();

                // Si se subió un archivo, guardar la información en la tabla 'archivos'
                if ($archivoRuta) {
                    $queryArchivo = "INSERT INTO archivos (nombre_archivo, tipo_archivo, ruta_archivo, usuario_id) 
                     VALUES (:nombre_archivo, :tipo_archivo, :ruta_archivo, :usuario_id)";
                    $stmtArchivo = $conn->prepare($queryArchivo);
                    $stmtArchivo->bindParam(':nombre_archivo', $nombreArchivo, PDO::PARAM_STR);
                    $stmtArchivo->bindParam(':tipo_archivo', $ext, PDO::PARAM_STR); // Guardar la extensión como tipo de archivo
                    $stmtArchivo->bindParam(':ruta_archivo', $archivoRuta, PDO::PARAM_STR);
                    $stmtArchivo->bindParam(':usuario_id', $profesorId, PDO::PARAM_INT);
                    $stmtArchivo->execute();
                }

                // Mostrar mensaje de éxito
                $alert_message = "Clase registrada exitosamente";
                $alert_type = "success";
                $alert_title = "¡Éxito!";
            } catch (PDOException $e) {
                // Mostrar error si ocurre algún problema
                $alert_message = "Error al registrar la clase: " . $e->getMessage();
                $alert_type = "error";
                $alert_title = "¡Error!";
            }
        }
    } else {
        // Mostrar mensaje de error si algún campo está vacío
        $alert_message = "Por favor complete todos los campos del formulario.";
        $alert_type = "error";
        $alert_title = "¡Error!";
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
                    <h4> ¡Bienvenido!</h4>
					<h3><?php echo htmlspecialchars($profesorNombre); ?></h3>
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
					<a href="clase.php">
                        <i class="zmdi zmdi-book zmdi-hc-fw"></i> Clases
					</a>
				</li>
                <li>
					<a href="tarea.php">
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
			  <h1 class="text-titles"><i class="zmdi zmdi-book zmdi-hc-fw"></i> Clases</h1>
			</div>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs" style="margin-bottom: 15px;">
					  	<li><a href="clase.php">Impartiendo</a></li>
					  	<li class="active"><a href="#new" data-toggle="tab">Nueva</a></li>
					</ul>
					<div class="tab-pane fade active in" id="new">
						<div class="container-fluid">
							<div class="row">
								<div class="col-xs-12 col-md-10 col-md-offset-1">
								<form method="POST" id="formcurso" enctype="multipart/form-data">
                                    <div class="form-group">
										<label class="control-label" for="materias">Materia</label>
										<select class="form-control" name="materias" id="materias" required>
											<option value="">Seleccione una materia</option>
											<?php foreach ($materias as $materia): ?>
                                                <option value="<?php echo htmlspecialchars($materia['id']); ?>">
                                                    <?php echo htmlspecialchars($materia['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
									</div>
                                    <div class="form-group label-floating">
										<label class="control-label" for="tema">Tema</label>
										<input class="form-control" type="text" id="tema" name="tema" required>
									</div>
                                    <div class="form-group">
										      <label class="control-label">Archivo Complementario ||  Solo se almacenaran archivos con las extensiones pdf, doc, docx, jpg, png, zip, txt</label>
										      <div>
										        <input type="text" readonly="" class="form-control" placeholder="Buscar...">
										        <input type="file" name="archivo_complementario">
										      </div>
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
    // Validar tamaño del archivo antes de enviar el formulario
    document.getElementById('formcurso').addEventListener('submit', function (e) {
    const archivo = document.querySelector('input[type="file"]'); // Input del archivo
    const maxFileSize = 5 * 1024 * 1024; // Tamaño máximo en bytes (5MB)

    // Validar si se seleccionó un archivo y si su tamaño supera el máximo
    if (archivo.files.length > 0 && archivo.files[0].size > maxFileSize) {
        e.preventDefault(); // Evitar el envío del formulario

        // Mostrar alerta con SweetAlert
        swal({
            title: 'Archivo demasiado grande',
            html: 'El archivo seleccionado no debe superar los 5 MB. Por favor, seleccione un archivo más pequeño.',
            type: 'error',
            showConfirmButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Entendido',
        });

        return false; // Terminar ejecución si el archivo no cumple
    }
});

    // Validar habilitación del botón de envío basado en los campos del formulario
    const form = document.getElementById('formcurso');
    const materia = document.getElementById('materias');
    const tema = document.getElementById('tema');
    const submitButton = document.getElementById('form-btn');

    form.addEventListener('input', () => {
        // Verificar que todos los campos obligatorios estén llenos
        const isMateriaSelected = materia.value.trim() !== "";
        const isTemaValid = tema.value.trim().length > 0;

        // Habilitar o deshabilitar el botón basado en las validaciones
        submitButton.disabled = !(isMateriaSelected && isTemaValid);
    });

    // Mostrar alerta de mensajes enviados desde el backend (si existen)
    <?php if (isset($alert_message) && isset($alert_type)): ?>
        swal({
            title: '<?= $alert_title ?>',
            html: `<?= $alert_message ?>`,
            type: '<?= $alert_type ?>',
            showConfirmButton: true,
            confirmButtonColor: '#640d14',
            confirmButtonText: 'Continuar',
        }).then(function () {
            window.location.href = "#!";
        });
    <?php endif; ?>
</script> 
</body>
</html>
