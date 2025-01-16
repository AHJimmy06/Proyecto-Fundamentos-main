<?php

include('../php/verificar_acceso.php');
verificarAcceso('estudiante');

include('../php/cone.php');
$conn = Conexion();
// Obtener ID del estudiante
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

// Obtener cursos en los que el estudiante está inscrito
$sql_cursos = "SELECT COUNT(DISTINCT c.id) AS total_cursos
               FROM cursos c
               JOIN materias m ON c.id = m.curso_id
               JOIN inscripciones i ON m.id = i.materia_id
               WHERE i.estudiante_id = :estudianteId";
$stmt_cursos = $conn->prepare($sql_cursos);
$stmt_cursos->execute(['estudianteId' => $estudianteId]);
$cursos = $stmt_cursos->fetch(PDO::FETCH_ASSOC);

// Obtener materias en las que el estudiante está inscrito
$sql_materias = "SELECT COUNT(m.id) AS total_materias
                 FROM materias m
                 JOIN inscripciones i ON m.id = i.materia_id
                 WHERE i.estudiante_id = :estudianteId";
$stmt_materias = $conn->prepare($sql_materias);
$stmt_materias->execute(['estudianteId' => $estudianteId]);
$materias = $stmt_materias->fetch(PDO::FETCH_ASSOC);

// Obtener clases en las que el estudiante está inscrito
$sql_clases = "SELECT COUNT(DISTINCT cl.id) AS total_clases
               FROM clases cl
               JOIN materias m ON cl.materia_id = m.id
               JOIN inscripciones i ON m.id = i.materia_id
               WHERE i.estudiante_id = :estudianteId";
$stmt_clases = $conn->prepare($sql_clases);
$stmt_clases->execute(['estudianteId' => $estudianteId]);
$clases = $stmt_clases->fetch(PDO::FETCH_ASSOC);

// Obtener tareas totales asignadas al estudiante
$sql_tareas = "SELECT COUNT(t.id) AS total_tareas
               FROM tareas t
               JOIN clases cl ON t.clase_id = cl.id
               JOIN materias m ON cl.materia_id = m.id
               JOIN inscripciones i ON m.id = i.materia_id
               WHERE i.estudiante_id = :estudianteId";
$stmt_tareas = $conn->prepare($sql_tareas);
$stmt_tareas->execute(['estudianteId' => $estudianteId]);
$tareas = $stmt_tareas->fetch(PDO::FETCH_ASSOC);

?>

<!DOCTYPE html>
<html lang="es">
<head>
	<title>FISEI || Panel de Control</title>
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
			<ul class="full-box list-unstyled text-right ">
				<li class="pull-left">
					<a href="#!" class="btn-menu-dashboard"><i class="zmdi zmdi-more-vert"></i></a>
				</li>
			</ul>
		</nav>
		<!-- Content page -->
		<div class="container-fluid">
			<div class="page-header">
			  <h1 class="text-titles">Panel de Control</h1>
			</div>
		</div>
		<div class="full-box text-center" style="padding: 30px 10px;">
            <!-- Cursos -->
            <article class="full-box tile">
                <div class="full-box tile-title text-center text-titles text-uppercase ">
                    Cursos
                </div>
                <div class="full-box tile-icon text-center">
                    <i class="zmdi zmdi-balance zmdi-hc-fw"></i>
                </div>
                <div class="full-box tile-number text-titles">
                    <p class="full-box"><?php echo $cursos['total_cursos']; ?></p>
                    <small>En Proceso</small>
                </div>
            </article>

            <!-- Materias -->
            <article class="full-box tile">
                <div class="full-box tile-title text-center text-titles text-uppercase ">
                    Materias
                </div>
                <div class="full-box tile-icon text-center">
                    <i class="zmdi zmdi-book zmdi-hc-fw"></i>
                </div>
                <div class="full-box tile-number text-titles">
                    <p class="full-box"><?php echo $materias['total_materias']; ?></p>
                    <small>Inscritas</small>
                </div>
            </article>

            <!-- Clases -->
            <article class="full-box tile">
                <div class="full-box tile-title text-center text-titles text-uppercase ">
                    Clases
                </div>
                <div class="full-box tile-icon text-center">
                    <i class="zmdi zmdi-font zmdi-hc-fw"></i>
                </div>
                <div class="full-box tile-number text-titles">
                    <p class="full-box"><?php echo $clases['total_clases']; ?></p>
                    <small>Tomando</small>
                </div>
            </article>

            <!-- Tareas -->
            <article class="full-box tile">
                <div class="full-box tile-title text-center text-titles text-uppercase ">
                    Tareas
                </div>
                <div class="full-box tile-icon text-center">
                    <i class="zmdi zmdi-timer zmdi-hc-fw"></i>
                </div>
                <div class="full-box tile-number text-titles">
                    <p class="full-box"><?php echo $tareas['total_tareas']; ?></p>
                    <small>Totales</small>
                </div>
            </article>
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
</body>
</html>