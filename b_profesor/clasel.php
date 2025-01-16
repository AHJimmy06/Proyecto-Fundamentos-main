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

// Verificar si el formulario fue enviado
if (isset($_POST['registrar'])) {
    // Obtener datos del formulario
    $materiaId = $_POST['materias'];
    $tema = $_POST['tema'];
    $fecha = date('Y-m-d H:i:s'); // Fecha y hora actual

    // Verificar si ya existe una clase con la misma materia, tema y fecha
    $query = "SELECT * FROM clases WHERE materia_id = :materia_id AND tema = :tema AND fecha = :fecha";
    $stmt = $conn->prepare($query);
    $stmt->bindParam(':materia_id', $materiaId, PDO::PARAM_INT);
    $stmt->bindParam(':tema', $tema, PDO::PARAM_STR);
    $stmt->bindParam(':fecha', $fecha, PDO::PARAM_STR);
    $stmt->execute();

    if ($stmt->rowCount() > 0) {
        // La clase ya existe, mostrar un mensaje de error
        $alert_message = "Ya existe una clase con la misma materia, tema y fecha.";
        $alert_type = "error";
        $alert_title = "Error de registro";
    } else {
        // Si no existe, proceder a insertar la nueva clase
        $queryInsert = "INSERT INTO clases (materia_id, fecha, tema) VALUES (:materia_id, :fecha, :tema)";
        $stmtInsert = $conn->prepare($queryInsert);
        $stmtInsert->bindParam(':materia_id', $materiaId, PDO::PARAM_INT);
        $stmtInsert->bindParam(':fecha', $fecha, PDO::PARAM_STR);
        $stmtInsert->bindParam(':tema', $tema, PDO::PARAM_STR);
        $stmtInsert->execute();
        
        // Obtener el ID de la clase recién creada
        $claseId = $conn->lastInsertId();

        // Procesar el archivo complementario
        if (isset($_FILES['archivo_complementario']) && $_FILES['archivo_complementario']['error'] == UPLOAD_ERR_OK) {
            // Validar el archivo
            $file = $_FILES['archivo_complementario'];
            $allowedFileType = 'application/pdf';
            $maxFileSize = 5 * 1024 * 1024; // 5MB

            if ($file['type'] !== $allowedFileType) {
                $alert_message = "Solo se permite un archivo PDF.";
                $alert_type = "error";
                $alert_title = "Error en el archivo";
            } elseif ($file['size'] > $maxFileSize) {
                $alert_message = "El archivo no debe superar los 5MB.";
                $alert_type = "error";
                $alert_title = "Error en el archivo";
            } else {
                // Mover el archivo al directorio de destino
                $uploadDir = '../uploads/clases/';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $fileName = time() . "_" . basename($file['name']);
                $filePath = $uploadDir . $fileName;

                if (move_uploaded_file($file['tmp_name'], $filePath)) {
                    // Registrar el archivo en la base de datos
                    $queryFile = "INSERT INTO archivos_clase (nombre_archivo, tipo_archivo, ruta_archivo, usuario_id, clase_id) 
                                  VALUES (:nombre_archivo, :tipo_archivo, :ruta_archivo, :usuario_id, :clase_id)";
                    $stmtFile = $conn->prepare($queryFile);
                    $stmtFile->bindParam(':nombre_archivo', $file['name'], PDO::PARAM_STR);
                    $stmtFile->bindParam(':tipo_archivo', $file['type'], PDO::PARAM_STR);
                    $stmtFile->bindParam(':ruta_archivo', $filePath, PDO::PARAM_STR);
                    $stmtFile->bindParam(':usuario_id', $profesorId, PDO::PARAM_INT);
                    $stmtFile->bindParam(':clase_id', $claseId, PDO::PARAM_INT);
                    $stmtFile->execute();

                    $alert_message = "Clase registrada exitosamente con el archivo complementario.";
                    $alert_type = "success";
                    $alert_title = "Éxito";
                } else {
                    $alert_message = "Hubo un error al cargar el archivo.";
                    $alert_type = "error";
                    $alert_title = "Error en el archivo";
                }
            }
        }
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
										      <label class="control-label">Archivo Complementario ||  Solo se permite un archivo pdf de máximo 5MB</label>
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
   // Validar tamaño del archivo y tipo antes de enviar el formulario
        document.getElementById('formcurso').addEventListener('submit', function (e) {
            const archivo = document.querySelector('input[type="file"]'); // Input del archivo
            const maxFileSize = 5 * 1024 * 1024; // Tamaño máximo en bytes (5MB)
            const allowedFileType = 'application/pdf'; // Tipo de archivo permitido (PDF)

            // Validar si se seleccionó un archivo
            if (archivo.files.length > 0) {
                const file = archivo.files[0];

                // Validar si el archivo es un PDF
                if (file.type !== allowedFileType) {
                    e.preventDefault(); // Evitar el envío del formulario

                    // Mostrar alerta con SweetAlert
                    swal({
                        title: 'Tipo de archivo no válido',
                        html: 'Por favor, seleccione un archivo en formato PDF.',
                        type: 'error',
                        showConfirmButton: true,
                        confirmButtonColor: '#d33',
                        confirmButtonText: 'Entendido',
                    });

                    return false; // Terminar ejecución si el archivo no es un PDF
                }

                // Validar si el tamaño del archivo supera el máximo
                if (file.size > maxFileSize) {
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

                    return false; // Terminar ejecución si el archivo supera el tamaño
                }
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
