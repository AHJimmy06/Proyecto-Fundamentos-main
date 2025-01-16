<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion(); // Asumiendo que esta función retorna un objeto PDO

// Obtener ID del profesor
$profesorId = $_SESSION['id']; // Suponiendo que el ID del profesor está en la sesión
$profesorNombre = $_SESSION['nombre'];

$sql = "
    SELECT t.id AS tarea_id, cl.tema AS clase, m.nombre AS materia, cu.nombre AS curso, 
           t.descripcion AS tarea, t.fecha_entrega
    FROM tareas t
    JOIN clases cl ON t.clase_id = cl.id
    JOIN materias m ON cl.materia_id = m.id
    JOIN cursos cu ON m.curso_id = cu.id
    WHERE cu.profesor_id = :profesorId
    ORDER BY t.fecha_entrega DESC";
    
$stmt = $conn->prepare($sql);
$stmt->execute([':profesorId' => $profesorId]);
$tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $tareaId = $_POST['id'];

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // --- Eliminar archivos de la tarea ---

        // 1. Archivos entregados por los estudiantes (tabla 'archivos_estudiante_tarea')
        $stmt = $conn->prepare("SELECT ruta_archivo FROM archivos_estudiante_tarea WHERE tarea_id = :tareaId");
        $stmt->bindParam(':tareaId', $tareaId, PDO::PARAM_INT);
        $stmt->execute();
        $archivosEstudiante = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($archivosEstudiante as $archivo) {
            if (file_exists($archivo['ruta_archivo'])) {
                unlink($archivo['ruta_archivo']); // Eliminar archivo físico
            }
        }
        // Eliminar registros de archivos entregados por los estudiantes
        $stmt = $conn->prepare("DELETE FROM archivos_estudiante_tarea WHERE tarea_id = :tareaId");
        $stmt->bindParam(':tareaId', $tareaId, PDO::PARAM_INT);
        $stmt->execute();

        // 2. Archivos de la tarea (tabla 'archivos_tarea')
        $stmt = $conn->prepare("SELECT ruta_archivo FROM archivos_tarea WHERE tarea_id = :tareaId");
        $stmt->bindParam(':tareaId', $tareaId, PDO::PARAM_INT);
        $stmt->execute();
        $archivosTarea = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($archivosTarea as $archivo) {
            if (file_exists($archivo['ruta_archivo'])) {
                unlink($archivo['ruta_archivo']); // Eliminar archivo físico
            }
        }
        // Eliminar registros de archivos asociados a la tarea
        $stmt = $conn->prepare("DELETE FROM archivos_tarea WHERE tarea_id = :tareaId");
        $stmt->bindParam(':tareaId', $tareaId, PDO::PARAM_INT);
        $stmt->execute();

        // --- Eliminar calificaciones y la tarea ---

        // 3. Eliminar las calificaciones de la tarea
        $stmt = $conn->prepare("DELETE FROM calificaciones WHERE tarea_id = :tareaId");
        $stmt->bindParam(':tareaId', $tareaId, PDO::PARAM_INT);
        $stmt->execute();

        // 4. Eliminar la tarea
        $stmt = $conn->prepare("DELETE FROM tareas WHERE id = :tareaId");
        $stmt->bindParam(':tareaId', $tareaId, PDO::PARAM_INT);
        $stmt->execute();

        // Confirmar la transacción
        $conn->commit();

        // Redirigir a la página de tareas
        header('Location: tarea.php');
        exit;

    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        echo "Error al eliminar la tarea: " . $e->getMessage();
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
			  <h1 class="text-titles"><i class="zmdi zmdi-timer zmdi-hc-fw"></i> Tareas </h1>
			</div>
		</div>
		<div class="container-fluid ">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs bg-red" style="margin-bottom: 15px;">
					  	<li class="active"><a href="#list" data-toggle="tab">Asignadas</a></li>
					  	<li><a href="tarean.php" >Crear</a></li>
						<li><a href="tareal.php" >Calificar</a></li>
					</ul>
					<div class="tab-pane fade active in" id="list">
						<div class="table-responsive">
						<table class="table table-hover text-center">
							<thead>
								<tr>
									<th class="text-center">Clase</th>
									<th class="text-center">Materia</th>
									<th class="text-center">Curso</th>
									<th class="text-center">Tarea Asignada</th>
									<th class="text-center">Fecha de Entrega</th>
									<th class="text-center">Eliminar tarea</th>
								</tr>
							</thead>
							<tbody>
								<?php foreach ($tareas as $tarea): ?>
								<tr>
									<td><?= htmlspecialchars($tarea['clase']) ?></td>
									<td><?= htmlspecialchars($tarea['materia']) ?></td>
									<td><?= htmlspecialchars($tarea['curso']) ?></td>
									<td><?= htmlspecialchars($tarea['tarea']) ?></td>
									<td><?= htmlspecialchars($tarea['fecha_entrega']) ?></td>
									<td>
										<form action="tarea.php" method="POST" id="form-eliminar-<?= htmlspecialchars($tarea['tarea_id']) ?>">
											<input type="hidden" name="id" value="<?= htmlspecialchars($tarea['tarea_id']) ?>">
											<button type="button" class="btn btn-danger btn-raised btn-xs btn-ddbe" data-id="<?= htmlspecialchars($tarea['tarea_id']) ?>">
												<i class="zmdi zmdi-delete"></i>
											</button>
										</form>
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
            $('.btn-ddbe').on('click', function(){
                var claseId = $(this).data('id');
                var form = $('#form-eliminar-' + claseId); // Encontramos el formulario correspondiente

                swal({
                    title: '¿Seguro que desea eliminar esta tarea?',
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
