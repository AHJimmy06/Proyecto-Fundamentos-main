<?php 

include('../php/verificar_acceso.php');
	verificarAcceso('profesor');

include('../php/cone.php');

$conn = Conexion();
$profesorId = $_SESSION['id']; // Suponiendo que el ID del profesor está en la sesión
$profesorNombre = $_SESSION['nombre'];

// Consultar las clases asociadas a las materias del profesor
$query = "
    SELECT c.id, m.nombre AS materia, c.tema 
    FROM clases c
    JOIN materias m ON c.materia_id = m.id
    JOIN cursos cu ON m.curso_id = cu.id
    WHERE cu.profesor_id = :profesorId
";
$stmt = $conn->prepare($query);
$stmt->execute(['profesorId' => $profesorId]);

$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);


// Lógica para procesar el formulario
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registrar'])) {
    $claseId = $_POST['clase_id'];
    $descripcion = $_POST['descripcion'];
    $fechaEntrega = $_POST['fecha_entrega'];
    $archivo = $_FILES['archivo_complementario'];

    // Validar que todos los campos estén completos
    if (!empty($claseId) && !empty($descripcion) && !empty($fechaEntrega)) {
        
        // Validar archivo
        $allowedFileType = 'application/pdf';
        $maxFileSize = 5 * 1024 * 1024; // 5MB
        if ($archivo['error'] == 0) {
            $fileType = mime_content_type($archivo['tmp_name']);
            if ($fileType != $allowedFileType) {
                echo "<script>alert('Solo se permite archivo PDF.');</script>";
            } elseif ($archivo['size'] > $maxFileSize) {
                echo "<script>alert('El archivo excede el tamaño máximo permitido de 5MB.');</script>";
            } else {
                // Subir archivo
                $fileName = uniqid() . '-' . $archivo['name'];
                $uploadDir = '../uploads/tareasp/';
                $uploadFile = $uploadDir . $fileName;

                if (move_uploaded_file($archivo['tmp_name'], $uploadFile)) {
                    // Insertar tarea en la base de datos
                    $query = "
                        INSERT INTO tareas (clase_id, descripcion, fecha_entrega)
                        VALUES (:clase_id, :descripcion, :fecha_entrega)
                    ";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([
                        'clase_id' => $claseId,
                        'descripcion' => $descripcion,
                        'fecha_entrega' => $fechaEntrega
                    ]);

                    // Obtener el ID de la tarea insertada
                    $tareaId = $conn->lastInsertId();

                    // Guardar el archivo en la base de datos
                    $query = "
                        INSERT INTO archivos_tarea (nombre_archivo, tipo_archivo, ruta_archivo, tarea_id, usuario_id)
                        VALUES (:nombre_archivo, :tipo_archivo, :ruta_archivo, :tarea_id, :usuario_id)
                    ";
                    $stmt = $conn->prepare($query);
                    $stmt->execute([
                        'nombre_archivo' => $fileName,
                        'tipo_archivo' => $allowedFileType,
                        'ruta_archivo' => $uploadFile,
                        'tarea_id' => $tareaId,
                        'usuario_id' => $profesorId
                    ]);

                    $alert_message = "La tarea se ha creado exitosamente.";
					$alert_type = "success"; // Puedes usar "error" si es el caso contrario
					$alert_title = "Éxito";

                } else {
                    echo "<script>alert('Error al subir el archivo.');</script>";
                }
            }
        } else {
            // Insertar tarea sin archivo
            $query = "
                INSERT INTO tareas (clase_id, descripcion, fecha_entrega)
                VALUES (:clase_id, :descripcion, :fecha_entrega)
            ";
            $stmt = $conn->prepare($query);
            $stmt->execute([
                'clase_id' => $claseId,
                'descripcion' => $descripcion,
                'fecha_entrega' => $fechaEntrega
            ]);

            // Mensaje de éxito sin archivo
            $alert_message = "La tarea se ha creado exitosamente.";
			$alert_type = "success"; // Puedes usar "error" si es el caso contrario
			$alert_title = "Éxito";
        }
    } else {
        echo "<script>alert('Por favor complete todos los campos obligatorios.');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
	<title>FISEI || Estudiantes</title>
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
			  <h1 class="text-titles"> <i class="zmdi zmdi-timer zmdi-hc-fw"></i> Tareas </h1>
			</div>
			<p class="lead"></p>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs bg-red" style="margin-bottom: 15px;">
                        <li><a href="tarea.php" >Asignadas</a></li>    	
                        <li class="active"><a href="#list" data-toggle="tab">Crear</a></li>
                        <li><a href="tareal.php" >Calificar</a></li>
					</ul>
					<div class="tab-pane fade active in" id="new">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-md-10 col-md-offset-1">
                                <form action="" method="POST" enctype="multipart/form-data" id="formcurso">
                                    <div class="form-group">
                                    <!-- Selección de la clase -->
                                    <label class="control-label"for="clase_id">Seleccionar clase:</label>
                                    <select class="form-control" id="clase_id" name="clase_id" required>
										<option value="">-- Seleccione una clase --</option>
										<?php foreach ($clases as $clase): ?>
											<option value="<?php echo htmlspecialchars($clase['id']); ?>">
												<?php echo htmlspecialchars($clase['materia']) . " - Tema: " . htmlspecialchars($clase['tema']); ?>
											</option>
										<?php endforeach; ?>
									</select>
                                    </div>
                                    <div class="form-group label-floating">
                                        <!-- Descripción de la tarea -->
                                        <label class="control-label"for="descripcion">Descripción de la tarea:</label>
                                        <input class="form-control" type="text" id="descripcion" name="descripcion" required>
                                    </div>
                                    <div class="form-group label-floating">
                                        <!-- Fecha de entrega -->
                                        <label for="fecha_entrega">Fecha de entrega:</label>
                                        <input class="form-control" type="date" id="fecha_entrega" name="fecha_entrega" required>
                                    </div>
                                    <div class="form-group">
										      <label class="control-label">Archivo Complementario ||  Solo se permite un archivo pdf de máximo 5MB</label>
										      <div>
										        <input type="text" readonly="" class="form-control" placeholder="Buscar...">
										        <input type="file" name="archivo_complementario">
										      </div>
										</div>
										<p class="text-center" >
										<button id="form-btn" name="registrar" type="submit" class="btn btn-info btn-raised btn-sm" disabled><i class="zmdi zmdi-floppy"></i> Crear</button>
									</p>
                                </form>

                                    <div><a class="btn-form">Formato de registro</a></div>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Scripts -->
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
	const claseSelect = document.getElementById('clase_id');
	const descripcionInput = document.getElementById('descripcion');
	const fechaEntregaInput = document.getElementById('fecha_entrega');
	const submitButton = document.getElementById('form-btn');

	form.addEventListener('input', () => {
		// Verificar que todos los campos obligatorios estén llenos
		const isClaseSelected = claseSelect.value.trim() !== "";
		const isDescripcionValid = descripcionInput.value.trim().length > 0;

		// Obtener la fecha actual y la fecha de entrega
		const currentDate = new Date().toISOString().split('T')[0]; // Fecha actual en formato YYYY-MM-DD
		const fechaEntrega = fechaEntregaInput.value;

		// Verificar si la fecha de entrega es igual o mayor a la actual
		const isFechaValida = fechaEntrega >= currentDate;

		// Habilitar o deshabilitar el botón basado en las validaciones
		submitButton.disabled = !(isClaseSelected && isDescripcionValid && isFechaValida);
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