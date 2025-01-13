<?php
include('../php/verificar_acceso.php');
verificarAcceso('profesor');

// Conexión a la base de datos
include('../php/cone.php');
$conn = Conexion(); // Asumiendo que esta función retorna un objeto PDO

// Obtener ID del profesor
$profesorId = $_SESSION['id']; // Suponiendo que el ID del profesor está en la sesión
$profesorNombre = $_SESSION['nombre'];

// Consulta SQL para obtener las clases del profesor, materia y tarea asignada
$sql = "
    SELECT c.id AS clase_id, c.fecha, m.nombre AS materia, cu.nombre AS curso, c.tema, 
           IFNULL(t.descripcion, 'No asignada') AS tarea
    FROM clases c
    JOIN materias m ON m.id = c.materia_id
    JOIN cursos cu ON cu.id = m.curso_id
    LEFT JOIN tareas t ON t.clase_id = c.id
    WHERE cu.profesor_id = :profesorId
";

// Eliminar clase si se recibe la solicitud
if (isset($_POST['clase_id'])) {
    $claseId = $_POST['clase_id'];

    // SQL para eliminar la clase
    $deleteSql = "DELETE FROM clases WHERE id = :claseId";
    $stmt = $conn->prepare($deleteSql);
    $stmt->bindParam(':claseId', $claseId, PDO::PARAM_INT);
    $stmt->execute();

    // Redirigir después de eliminar para evitar reenvío del formulario
    header("Location: clase.php" );
    exit();
}
// Preparar y ejecutar la consulta
$stmt = $conn->prepare($sql);
$stmt->bindParam(':profesorId', $profesorId, PDO::PARAM_INT);
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
			<!-- SideBar Title -->
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
					<a href="crear_tarea.php">
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
					  	<li><a href="profesoresl.php" >Nueva</a></li>
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
                                        <th class="text-center">Tarea Asignada</th>
										<th class="text-center">Eliminar Clase</th>
									</tr>
								</thead>
								<tbody>
                                <?php foreach ($clases as $clase): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($clase['fecha']) ?></td>
                                        <td><?= htmlspecialchars($clase['tema']) ?></td>
                                        <td><?= htmlspecialchars($clase['materia']) ?></td>
                                        <td><?= htmlspecialchars($clase['curso']) ?></td>
                                        <td><?= htmlspecialchars($clase['tarea']) ?></td>
                                        <td>
                                            <form action="clase.php" method="POST" id="form-eliminar-<?= htmlspecialchars($clase['clase_id']) ?>">
                                                <input type="hidden" name="clase_id" value="<?= htmlspecialchars($clase['clase_id']) ?>">
                                                <button type="button" class="btn btn-danger btn-raised btn-xs btn-ddbe" data-id="<?= htmlspecialchars($clase['clase_id']) ?>">
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
                    title: '¿Seguro que desea eliminar esta clase?',
                    text: 'Esta acción no puede deshacerse.',
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
