<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion(); // Asumiendo que esta función retorna un objeto PDO

// Obtener ID del profesor
$profesorId = $_SESSION['id']; // el ID del profesor está en la sesión
$profesorNombre = $_SESSION['nombre'];

// Obtener las clases impartidas por el profesor
$sql = "SELECT c.id AS clase_id, 
       c.fecha, 
       c.tema, 
       m.nombre AS materia, 
       co.nombre AS curso, 
       ac.ruta_archivo AS recurso_complementario, 
       t.descripcion AS tarea_descripcion
FROM clases c
JOIN materias m ON c.materia_id = m.id
JOIN cursos co ON m.curso_id = co.id
LEFT JOIN archivos_clase ac ON ac.clase_id = c.id
LEFT JOIN tareas t ON t.clase_id = c.id
WHERE co.profesor_id = :profesorId
ORDER BY c.fecha DESC";

$stmt = $conn->prepare($sql);
$stmt->bindParam(':profesorId', $profesorId, PDO::PARAM_INT);
$stmt->execute();

$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);
/// Procesar eliminación de clase
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['clase_id'])) {
    $claseId = $_POST['clase_id'];

    try {
        // Iniciar transacción
        $conn->beginTransaction();

        // --- Eliminar archivos relacionados a la clase ---
        // 1. Archivos de la tabla 'archivos_clase'
        $stmt = $conn->prepare("SELECT ruta_archivo FROM archivos_clase WHERE clase_id = :claseId");
        $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
        $stmt->execute();
        $archivosClase = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($archivosClase as $archivo) {
            if (file_exists($archivo['ruta_archivo'])) {
                unlink($archivo['ruta_archivo']); // Eliminar archivo físico
            }
        }
        $stmt = $conn->prepare("DELETE FROM archivos_clase WHERE clase_id = :claseId");
        $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
        $stmt->execute();

        // 2. Archivos de la tabla 'archivos_tarea'
        $stmt = $conn->prepare("SELECT ruta_archivo FROM archivos_tarea WHERE tarea_id IN (SELECT id FROM tareas WHERE clase_id = :claseId)");
        $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
        $stmt->execute();
        $archivosTarea = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($archivosTarea as $archivo) {
            if (file_exists($archivo['ruta_archivo'])) {
                unlink($archivo['ruta_archivo']); // Eliminar archivo físico
            }
        }
        $stmt = $conn->prepare("DELETE FROM archivos_tarea WHERE tarea_id IN (SELECT id FROM tareas WHERE clase_id = :claseId)");
        $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
        $stmt->execute();

        // 3. Archivos de la tabla 'archivos_estudiante_tarea'
        $stmt = $conn->prepare("SELECT ruta_archivo FROM archivos_estudiante_tarea WHERE tarea_id IN (SELECT id FROM tareas WHERE clase_id = :claseId)");
        $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
        $stmt->execute();
        $archivosEstudiante = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($archivosEstudiante as $archivo) {
            if (file_exists($archivo['ruta_archivo'])) {
                unlink($archivo['ruta_archivo']); // Eliminar archivo físico
            }
        }
        $stmt = $conn->prepare("DELETE FROM archivos_estudiante_tarea WHERE tarea_id IN (SELECT id FROM tareas WHERE clase_id = :claseId)");
        $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
        $stmt->execute();

        // --- Eliminar registros relacionados en cascada ---
        // 4. Eliminar tareas relacionadas a la clase
        $stmt = $conn->prepare("DELETE FROM tareas WHERE clase_id = :claseId");
        $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
        $stmt->execute();

        // 5. Eliminar la clase
        $stmt = $conn->prepare("DELETE FROM clases WHERE id = :claseId");
        $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
        $stmt->execute();

        // Confirmar transacción
        $conn->commit();

        // Redirigir a la página de clases
        header('Location: clase.php');
        exit;

    } catch (Exception $e) {
        // Revertir la transacción en caso de error
        $conn->rollBack();
        echo "Error al eliminar la clase: " . $e->getMessage();
    }
} else {
    echo "Solicitud inválida.";
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
		<div class="container-fluid ">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs bg-red" style="margin-bottom: 15px;">
					  	<li class="active"><a href="#list" data-toggle="tab">Impartiendo</a></li>
					  	<li><a href="clasel.php" >Nueva</a></li>
					</ul>
					<div class="tab-pane fade active in" id="list">
						<div class="table-responsive">
							<table class="table table-hover text-center">
								<thead>
									<tr>
										<th class="text-center">Fecha de la Clase</th>
										<th class="text-center">Clase</th>
										<th class="text-center">Materia</th>
										<th class="text-center">Curso</th>
										<th class="text-center">Recurso complementario</th>
                                        <th class="text-center">Tarea Asignada</th>
										<th class="text-center">Eliminar Clase</th>
									</tr>
								</thead>
								<tbody>
								<?php foreach ($clases as $clase): ?>
									<tr>
										<td><?php echo date('d/m/Y H:i', strtotime($clase['fecha'])); ?></td>
										<td><?php echo htmlspecialchars($clase['tema']); ?></td>
										<td><?php echo htmlspecialchars($clase['materia']); ?></td>
										<td><?php echo htmlspecialchars($clase['curso']); ?></td>
										
										<td>
											<?php if ($clase['recurso_complementario']): ?>
												<a href="../clases/<?php echo htmlspecialchars($clase['recurso_complementario']); ?>" target="_blank">
													Ver Recurso
												</a>
											<?php else: ?>
												No disponible
											<?php endif; ?>
										</td>
										<td>
											<?php if ($clase['tarea_descripcion']): ?>
												<?php echo htmlspecialchars($clase['tarea_descripcion']); ?>
											<?php else: ?>
												No asignada
											<?php endif; ?>
										</td>
										<td> 
											 <!-- Formulario de eliminación -->
											 <form id="form-eliminar-<?php echo $clase['clase_id']; ?>" action="clase.php" method="POST" style="display: none;">
												<input type="hidden" name="clase_id" value="<?php echo $clase['clase_id']; ?>">
											</form>
											<button class="btn btn-danger btn-ddbe" data-id="<?php echo $clase['clase_id']; ?>">Eliminar</button>
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
