<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion(); // Asumiendo que esta función retorna un objeto PDO

// Obtener ID del profesor
$profesorId = $_SESSION['id']; // Suponiendo que el ID del profesor está en la sesión
$profesorNombre = $_SESSION['nombre'];

// Obtener las clases con tareas asociadas
$sql = "
    SELECT DISTINCT cl.id, cl.tema, m.nombre AS materia
    FROM clases cl
    JOIN materias m ON m.id = cl.materia_id
    JOIN cursos c ON c.id = m.curso_id
    WHERE c.profesor_id = :profesorId
    ORDER BY m.nombre, cl.tema
";

$stmt = $conn->prepare($sql);
$stmt->execute([':profesorId' => $profesorId]);
$clases = $stmt->fetchAll(PDO::FETCH_ASSOC);

?>
<?php
// Verifica si se ha seleccionado una clase
$tareas = []; // Inicializamos la variable para evitar errores en el primer cargado de la página

if (isset($_GET['clase_id']) && !empty($_GET['clase_id'])) {
    // Obtener el ID de la clase seleccionada
    $claseId = $_GET['clase_id'];

    // Consulta para obtener las tareas y los estudiantes que las han entregado
    $sql = "
        SELECT t.id AS tarea_id, t.descripcion AS tarea_descripcion, t.fecha_entrega, 
               e.id AS estudiante_id, e.matricula, u.nombre AS estudiante_nombre, 
               st.estado, c.tema AS clase_tema
        FROM tareas t
        JOIN clases c ON c.id = t.clase_id
        JOIN estudiantes_tareas st ON st.tarea_id = t.id
        JOIN estudiantes e ON e.id = st.estudiante_id
        JOIN usuarios u ON u.id = e.usuario_id  -- Unimos con la tabla usuarios para obtener el nombre del estudiante
        WHERE t.clase_id = :claseId
        ORDER BY u.nombre, t.fecha_entrega  -- Ahora ordenamos por el nombre del estudiante (columna u.nombre)
    ";

    // Preparar la consulta
    $stmt = $conn->prepare($sql);
    $stmt->execute([':claseId' => $claseId]);

    // Obtener los resultados
    $tareas = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <li><a href="tarea.php" >Asignadas</a></li>  
						<li><a href="tarean.php" >Crear</a></li>  	
                        <li class="active"><a href="#list" data-toggle="tab">Calificar</a></li>
					</ul>
					<div class="tab-pane fade active in" id="list">
					<div class="form-group">
					<!-- Formulario de selección de clase -->
					<form action="tareal.php" method="GET">
						<div class="form-group">
							<label class="control-label" for="clase_id">Seleccionar clase:</label>
							<select class="form-control" id="clase_id" name="clase_id" required>
								<option value="">-- Seleccione una clase --</option>
								<?php foreach ($clases as $clase): ?>
									<option value="<?php echo htmlspecialchars($clase['id']); ?>"
										<?php echo (isset($_GET['clase_id']) && $_GET['clase_id'] == $clase['id']) ? 'selected' : ''; ?>>
										<?php echo htmlspecialchars($clase['materia']) . " - Tema: " . htmlspecialchars($clase['tema']); ?>
									</option>
								<?php endforeach; ?>
							</select>
						</div>
						<button type="submit" class="btn btn-primary">Buscar tareas</button>
								</form>
                        </div>
                    </div>
						<div class="table-responsive">
						<table class="table table-hover text-center">
    <thead>
        <tr>
            <th class="text-center">Tarea</th>
            <th class="text-center">Clase</th>
            <th class="text-center">Estudiante</th>
            <th class="text-center">Entrega</th>
            <th class="text-center">Calificar</th>
        </tr>
    </thead>
    <tbody>
        <?php if (!empty($tareas)): ?>
            <?php foreach ($tareas as $tarea): ?>
                <tr>
                    <td><?php echo htmlspecialchars($tarea['tarea_descripcion']); ?></td>
                    <td><?php echo htmlspecialchars($tarea['clase_tema']); ?></td>
                    <td><?php echo htmlspecialchars($tarea['estudiante_nombre']); ?></td>
                    <td><?php echo htmlspecialchars($tarea['fecha_entrega']); ?></td>
                    <td>
                        <?php if ($tarea['estado'] == 'entregada'): ?>
                            <a href="calificar_tarea.php?tarea_id=<?php echo $tarea['tarea_id']; ?>&estudiante_id=<?php echo $tarea['estudiante_id']; ?>" class="btn btn-success">Calificar</a>
                        <?php else: ?>
                            <span class="text-danger">Tarea Atrasada</span>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
        <?php elseif (isset($_GET['clase_id'])): ?>
            <tr>
                <td colspan="5">No hay tareas enviadas para esta clase.</td>
            </tr>
        <?php endif; ?>
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
