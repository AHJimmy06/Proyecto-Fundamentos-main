<?php

include('../php/verificar_acceso.php');
verificarAcceso('estudiante');

include('../php/cone.php');
$conn = Conexion();
// Obtener ID del usuario
$usuarioId = $_SESSION['id'];
$estudianteNombre =$_SESSION['nombre'];

// Obtener el estudiante_id relacionado con el usuario
$sql_estudiante = "SELECT e.id AS estudiante_id 
                   FROM estudiantes e
                   WHERE e.usuario_id = :usuarioId";
$stmt_estudiante = $conn->prepare($sql_estudiante);
$stmt_estudiante->execute(['usuarioId' => $usuarioId]);
$estudiante = $stmt_estudiante->fetch(PDO::FETCH_ASSOC);

$estudianteId = $estudiante['estudiante_id'];

// Obtener las tareas pendientes para el estudiante
$sql_tareas = "SELECT t.id AS tarea_id, t.descripcion, t.fecha_entrega, c.nombre AS curso_nombre, m.nombre AS materia_nombre, cl.tema
               FROM tareas t
               JOIN clases cl ON t.clase_id = cl.id
               JOIN materias m ON cl.materia_id = m.id
               JOIN cursos c ON m.curso_id = c.id
               LEFT JOIN estudiantes_tareas et ON t.id = et.tarea_id AND et.estudiante_id = :estudianteId
               WHERE et.id IS NULL AND t.fecha_entrega >= CURDATE()"; // Solo tareas pendientes
$stmt_tareas = $conn->prepare($sql_tareas);
$stmt_tareas->execute(['estudianteId' => $estudianteId]);
$tareas = $stmt_tareas->fetchAll(PDO::FETCH_ASSOC);


// Procesar la entrega de la tarea cuando se suba un archivo
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_FILES['archivo']) && $_FILES['archivo']['error'] == 0) {
        // Obtener información del archivo
        $archivoTmpName = $_FILES['archivo']['tmp_name'];
        $archivoNombre = $_FILES['archivo']['name'];
        $archivoTipo = $_FILES['archivo']['type'];
        $archivoRuta = 'uploads/' . $archivoNombre; // Ruta donde se guardará el archivo

        // Validación del tipo de archivo (opcional)
        $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document', 'text/plain'];
        if (!in_array($archivoTipo, $allowedTypes)) {
            echo 'El archivo debe ser PDF, DOC, DOCX o TXT.';
            exit();
        }

        // Mover el archivo a la carpeta de destino
        if (move_uploaded_file($archivoTmpName, $archivoRuta)) {
            // Insertar información del archivo en la base de datos
            $sql_insertar_archivo = "INSERT INTO archivos_estudiante_tarea (nombre_archivo, tipo_archivo, ruta_archivo, estudiante_id, tarea_id) 
                                     VALUES (:nombre_archivo, :tipo_archivo, :ruta_archivo, :estudiante_id, :tarea_id)";
            $stmt = $conn->prepare($sql_insertar_archivo);
            $stmt->execute([
                'nombre_archivo' => $archivoNombre,
                'tipo_archivo' => $archivoTipo,
                'ruta_archivo' => $archivoRuta,
                'estudiante_id' => $estudianteId,
                'tarea_id' => $_POST['tarea_id']
            ]);

            // Marcar la tarea como entregada
            $sql_actualizar_estado = "UPDATE estudiantes_tareas SET estado = 'entregada' WHERE tarea_id = :tarea_id AND estudiante_id = :estudiante_id";
            $stmt = $conn->prepare($sql_actualizar_estado);
            $stmt->execute(['tarea_id' => $_POST['tarea_id'], 'estudiante_id' => $estudianteId]);

            echo 'Tarea entregada correctamente.';
        } else {
            echo 'Hubo un problema al mover el archivo. Intenta nuevamente.';
        }
    } else {
        echo 'No se ha subido ningún archivo o ocurrió un error.';
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
					<h3><?php echo htmlspecialchars($estudianteNombre); ?></h3>
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
				<li>
					<a href="promedios.php">
						<i class="zmdi zmdi-font zmdi-hc-fw"></i>Promedios 
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
			  <h1 class="text-titles"><i class="zmdi zmdi-timer zmdi-hc-fw"></i> Tareas </h1>
			</div>
		</div>
		<div class="container-fluid ">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs bg-red" style="margin-bottom: 15px;">
					  	<li class="active"><a href="#list" data-toggle="tab">Tareas Pendientes</a></li>
					  	<li><a href="tarea_e.php" >Tareas Entregadas</a></li>
						<li><a href="tarea_a.php" >Tareas Atrasadas</a></li>
					</ul>
					<div class="tab-pane fade active in" id="list">
					<!-- Formulario de entrega de tarea (oculto inicialmente) -->
					<div id="form-container" style="display:none;">
						<form action="tarea.php" method="POST" enctype="multipart/form-data">
							<input type="hidden" name="tarea_id" id="tareaId">
							<label for="archivo">Selecciona el archivo de la tarea:</label>
							<input type="file" name="archivo" id="archivo" required>
							<button type="submit">Entregar Tarea</button>
						</form>
					</div>

						<div class="table-responsive">
							<table class="table table-hover text-center">
								<thead>
									<tr>
										<th class="text-center">Tareas Pendientes</th>
										<th class="text-center">Fecha de entrega</th>
										<th class="text-center">Curso</th>
										<th class="text-center">Clase</th>
										<th class="text-center">Realizar entrega</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($tareas as $tarea): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($tarea['descripcion']); ?></td>
                                        <td><?php echo htmlspecialchars($tarea['fecha_entrega']); ?></td>
                                        <td><?php echo htmlspecialchars($tarea['curso_nombre']); ?></td>
                                        <td><?php echo htmlspecialchars($tarea['materia_nombre']) . " - " . htmlspecialchars($tarea['tema']); ?></td>
                                       <!-- Asegúrate de mostrar la lista de tareas pendientes como antes -->
										<td>
											<!-- Botón para entregar -->
											<button class="btn btn-primary btn-ddbe" data-id="<?php echo $tarea['tarea_id']; ?>">Entregar</button>
										</td>

                                    </tr>
                                    <?php endforeach; ?>
								</tbody>
							</table>
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
    $(document).ready(function(){
        // Al hacer clic en "Entregar"
        $('.btn-ddbe').on('click', function(){
            var tareaId = $(this).data('id');  // Obtener el ID de la tarea

            swal({
                title: '¿Seguro que deseas entregar esta tarea?',
                text: 'Después de confirmar, podrás subir el archivo de la tarea.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#640d14',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Confirmar',
                cancelButtonText: 'Cancelar'
            }).then(function(result) {
                if (result.isConfirmed) {
                    // Mostrar el formulario de entrega de tarea
                    $('#form-container').show();   // Mostrar el formulario
                    $('#tareaId').val(tareaId);    // Establecer el ID de la tarea en el formulario

                    // Opcional: Cambiar el texto del botón o deshabilitarlo
                    $('.btn-ddbe').prop('disabled', true);  // Desactivar el botón de entrega para evitar múltiples clics
                    $(this).text('Tarea entregada').prop('disabled', true);  // Cambiar el texto del botón
                }
            });
        });
    });
</script>


</body>
</html>
