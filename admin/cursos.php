<?php 
include('../php/verificar_acceso.php');
verificarAcceso('admin');

include('../php/cone.php');

// Mostrar registros de cursos
$conn = Conexion();
$stmt = $conn->prepare("
    SELECT cursos.id, cursos.nombre, usuarios.nombre AS profesor
    FROM cursos
    LEFT JOIN usuarios ON cursos.profesor_id = usuarios.id
");
$stmt->execute();
$cursos = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Verificar si se ha enviado el formulario para eliminar un curso
if (isset($_POST['id'])) {
    $id_curso = $_POST['id'];

	// Eliminar inscripciones relacionadas con el curso
    $conn = Conexion();
    $stmt = $conn->prepare("DELETE FROM inscripciones WHERE materia_id IN (SELECT id FROM materias WHERE curso_id = :curso_id)");
    $stmt->bindParam(':curso_id', $id_curso, PDO::PARAM_INT);
    $stmt->execute();

    // Eliminar materias asociadas al curso
    $stmt = $conn->prepare("DELETE FROM materias WHERE curso_id = :curso_id");
    $stmt->bindParam(':curso_id', $id_curso, PDO::PARAM_INT);
    $stmt->execute();

    // Eliminar al curso de la base de datos
    $conn = Conexion();
    $stmt = $conn->prepare("DELETE FROM cursos WHERE id = :id");
    $stmt->bindParam(':id', $id_curso, PDO::PARAM_INT);

    if ($stmt->execute()) {
        // Redirigir a la misma página después de la eliminación
        header("Location: cursos.php");
        exit; // Asegurarse de que el código después de la redirección no se ejecute
    } else {
        echo "Error al eliminar el curso";
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
					<h3>Administrador</h3>
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
					<a href="#!" class="btn-sideBar-SubMenu">
						<i class="zmdi zmdi-account-add zmdi-hc-fw"></i> Usuarios <i class="zmdi zmdi-caret-down pull-right"></i>
					</a>
					<ul class="list-unstyled full-box">
						<li>
							<a href="profesores.php"><i class="zmdi zmdi-male-alt zmdi-hc-fw"></i> Profesor</a>
						</li>
						<li>
							<a href="estudiantes.php"><i class="zmdi zmdi-face zmdi-hc-fw"></i> Estudiante</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#!" class="btn-sideBar-SubMenu">
						<i class="zmdi zmdi-case zmdi-hc-fw"></i> Administración <i class="zmdi zmdi-caret-down pull-right"></i>
					</a>
					<ul class="list-unstyled full-box">
						<li>
							<a href="cursos.php"><i class="zmdi zmdi-timer zmdi-hc-fw"></i> Cursos</a>
						</li>
						<li>
							<a href="materias.php"><i class="zmdi zmdi-book zmdi-hc-fw"></i> Materias</a>
						</li>
						<li>
							<a href="inscripciones.php"><i class="zmdi zmdi-font zmdi-hc-fw"></i> Inscripciones</a>
						</li>
					</ul>
				</li>
				<li>
					<a href="#!" class="btn-sideBar-SubMenu">
						<i class="zmdi zmdi-shield-security zmdi-hc-fw"></i> General <i class="zmdi zmdi-caret-down pull-right"></i>
					</a>
					<ul class="list-unstyled full-box">
						<li>
							<a href="datosgenerales.php"><i class="zmdi zmdi-balance zmdi-hc-fw"></i> Datos Generales</a>
						</li>
					</ul>
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
					  	<li class="active"><a href="#list" data-toggle="tab">Lista</a></li>
					  	<li><a href="cursosl.php">Registrar</a></li>
					</ul>
					<div class="tab-pane fade active in" id="list">
						<div class="table-responsive">
							<table class="table table-hover text-center">
								<thead>
									<tr>
										<th class="text-center">id</th>
										<th class="text-center">Nombre</th>
										<th class="text-center">Profesor</th>
										<th class="text-center">Borrar curso</th>
									</tr>
								</thead>
								<tbody>
									<?php foreach ($cursos as $curso): ?>
                                    <tr>
                                        <td><?= htmlspecialchars($curso['id']) ?></td>
                                        <td><?= htmlspecialchars($curso['nombre']) ?></td>
                                        <td><?= htmlspecialchars($curso['profesor']) ?></td>
                                        <td>
                                            <form action="cursos.php" method="POST" id="form-eliminar-<?= htmlspecialchars($curso['id']) ?>">
                                                <input type="hidden" name="id" value="<?= htmlspecialchars($curso['id']) ?>">
                                                <button type="button" class="btn btn-danger btn-raised btn-xs btn-ddbe" data-id="<?= htmlspecialchars($curso['id']) ?>">
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
	<!--====== Scripts -->
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
                var courseId = $(this).data('id');
                var form = $('#form-eliminar-' + courseId); // Encontramos el formulario correspondiente

                swal({
                    title: '¿Seguro que desea eliminar este curso?',
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