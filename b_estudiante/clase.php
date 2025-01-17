<?php
include('../php/verificar_acceso.php');
verificarAcceso('estudiante');

include('../php/cone.php');
$conn = Conexion(); //Conexion por PDO
// Obtener ID del estudiante
$usuarioId = $_SESSION['id'];

$estudianteNombre =$_SESSION['nombre'];

$sql = "SELECT e.id
        FROM estudiantes e
        WHERE e.usuario_id = :id_usuario";

$stmt = $conn->prepare($sql);
    
// Vincular el parámetro
$stmt->bindParam(':id_usuario', $usuarioId, PDO::PARAM_INT);

// Ejecutar la consulta
$stmt->execute();
$estudiante = $stmt->fetch(PDO::FETCH_ASSOC);
$estudiante_id = $estudiante['id'];


$query = "
    SELECT c.nombre AS curso, m.nombre AS materia, cl.tema AS tema, u.nombre AS docente, ac.nombre_archivo, ac.tipo_archivo, ac.ruta_archivo
    FROM inscripciones i
    JOIN materias m ON i.materia_id = m.id
    JOIN cursos c ON m.curso_id = c.id
    JOIN clases cl ON m.id = cl.materia_id
    JOIN usuarios u ON c.profesor_id = u.id
    LEFT JOIN archivos_clase ac ON cl.id = ac.clase_id
    WHERE i.estudiante_id = :estudiante_id
    ORDER BY cl.tema DESC
";

// Preparar la consulta
$stmt = $conn->prepare($query);

// Vincular el parámetro :estudiante_id con el valor correspondiente
$stmt->bindParam(':estudiante_id', $estudiante_id, PDO::PARAM_INT);

// Ejecutar la consulta
$stmt->execute();

// Obtener los resultados
$result = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
			  <h1 class="text-titles"><i class="zmdi zmdi-book zmdi-hc-fw"></i> Clases</h1>
			</div>
		</div>
		<div class="container-fluid ">
			<div class="row">
				<div class="col-xs-12">
					<div class="tab-pane fade active in" id="list">
						<div class="table-responsive">
							<table class="table table-hover text-center">
								<thead>
									<tr>
										<th class="text-center">Curso</th>
										<th class="text-center">Materia</th>
										<th class="text-center">Tema</th>
										<th class="text-center">Docente</th>
										<th class="text-center">Recurso complementario</th>
								</thead>
								<tbody>
								<?php foreach ($result as $row): ?>
            <tr>
                <td><?php echo htmlspecialchars($row['curso']); ?></td>
                <td><?php echo htmlspecialchars($row['materia']); ?></td>
                <td><?php echo htmlspecialchars($row['tema']); ?></td>
                <td><?php echo htmlspecialchars($row['docente']); ?></td>
                <td>
                    <?php if ($row['nombre_archivo']): ?>
                        <a href="<?php echo htmlspecialchars($row['ruta_archivo']); ?>" target="_blank">
                            <?php echo htmlspecialchars($row['nombre_archivo']); ?> (<?php echo htmlspecialchars($row['tipo_archivo']); ?>)
                        </a>
                    <?php else: ?>
                        No disponible
                    <?php endif; ?>
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
</body>
</html>
