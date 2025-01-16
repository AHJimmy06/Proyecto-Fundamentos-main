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

$sql_tareas_entregadas = "
    SELECT 
        c.nombre AS curso_nombre,
        t.descripcion AS tarea_descripcion,
        et.estado AS tarea_estado,
        t.fecha_entrega AS tarea_fecha_entrega,  -- Fecha de entrega de la tarea
        aet.fecha_subida AS tarea_fecha_subida,  -- Fecha de subida desde la tabla 'archivos_estudiante_tarea'
        cal.calificacion AS calificacion,
        cal.comentarios AS comentarios
    FROM 
        estudiantes_tareas et
    JOIN tareas t ON et.tarea_id = t.id
    JOIN clases cl ON t.clase_id = cl.id
    JOIN materias m ON cl.materia_id = m.id
    JOIN cursos c ON m.curso_id = c.id
    LEFT JOIN calificaciones cal ON t.id = cal.tarea_id AND et.estudiante_id = cal.estudiante_id
    LEFT JOIN archivos_estudiante_tarea aet ON et.estudiante_id = aet.estudiante_id AND et.tarea_id = aet.tarea_id
    WHERE et.estudiante_id = :estudianteId AND et.estado = 'entregada'
";
$stmt_tareas_entregadas = $conn->prepare($sql_tareas_entregadas);
$stmt_tareas_entregadas->execute(['estudianteId' => $estudianteId]);
$tareas_entregadas = $stmt_tareas_entregadas->fetchAll(PDO::FETCH_ASSOC);

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
					  	<li><a href="tarea.php" >Tareas Pendientes</a></li>
					  	<li class="active"><a href="#list" data-toggle="tab">Tareas Entregadas</a></li>
						<li><a href="tarea_a.php" >Tareas Atrasadas</a></li>
					</ul>
					<div class="tab-pane fade active in" id="list">
						<div class="table-responsive">
							<table class="table table-hover text-center">
								<thead>
									<tr>
										<th class="text-center">Curso</th>
										<th class="text-center">Tarea Entregada</th>
										<th class="text-center">Fecha de subida</th>
										<th class="text-center">Fecha de entrega</th>
										<th class="text-center">Calificación</th>
										<th class="text-center">Comentarios</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($tareas_entregadas as $tarea): ?>
									<tr>
										<td class="text-center"><?php echo htmlspecialchars($tarea['curso_nombre']); ?></td>
										<td class="text-center"><?php echo htmlspecialchars($tarea['tarea_descripcion']); ?></td>
										<td class="text-center"><?php echo htmlspecialchars($tarea['tarea_fecha_subida']); ?></td>
										<td class="text-center"><?php echo htmlspecialchars($tarea['tarea_fecha_entrega']); ?></td>
										<td class="text-center"><?php echo htmlspecialchars($tarea['calificacion']); ?></td>
										<td class="text-center"><?php echo htmlspecialchars($tarea['comentarios']); ?></td>
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
            $('.btn-ddbe').on('click', function(){
                var claseId = $(this).data('id');
                var form = $('#form-eliminar-' + claseId); // Encontramos el formulario correspondiente

                swal({
                    title: '¿Seguro que desea eliminar esta clase?',
                    text: 'Esta acción eliminará la clase y todos sus registros asociados, incluyendo archivos y tareas.',
                    type: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#640d14',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar'
                }).then(function () {
                    form.submit(); // Enviamos el formulario si el usuario confirma
                });
            });
        });
    </script>

</body>
</html>
