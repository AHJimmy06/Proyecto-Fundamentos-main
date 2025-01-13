<?php

include('../php/verificar_acceso.php');
verificarAcceso('profesor');

include('../php/cone.php');
$conn = Conexion();
// Obtener ID del profesor
$profesorId = $_SESSION['id'];
$profesorNombre =$_SESSION['nombre'];

// Obtener el número de cursos que imparte el profesor
$queryCursos = "SELECT COUNT(*) AS total_cursos FROM cursos WHERE profesor_id = :profesorId";
$stmtCursos = $conn->prepare($queryCursos);
$stmtCursos->bindParam(':profesorId', $profesorId, PDO::PARAM_INT);
$stmtCursos->execute();
$rowCursos = $stmtCursos->fetch(PDO::FETCH_ASSOC);
$totalCursos = $rowCursos['total_cursos'];

// Obtener el número de materias que imparte el profesor
$queryMaterias = "SELECT COUNT(*) AS total_materias FROM materias WHERE curso_id IN (SELECT id FROM cursos WHERE profesor_id = :profesorId)";
$stmtMaterias = $conn->prepare($queryMaterias);
$stmtMaterias->bindParam(':profesorId', $profesorId, PDO::PARAM_INT);
$stmtMaterias->execute();
$rowMaterias = $stmtMaterias->fetch(PDO::FETCH_ASSOC);
$totalMaterias = $rowMaterias['total_materias'];

// Obtener el número de clases que imparte el profesor
$queryClases = "SELECT COUNT(*) AS total_clases FROM clases WHERE materia_id IN (SELECT id FROM materias WHERE curso_id IN (SELECT id FROM cursos WHERE profesor_id = :profesorId))";
$stmtClases = $conn->prepare($queryClases);
$stmtClases->bindParam(':profesorId', $profesorId, PDO::PARAM_INT);
$stmtClases->execute();
$rowClases = $stmtClases->fetch(PDO::FETCH_ASSOC);
$totalClases = $rowClases['total_clases'];

// Obtener el número de tareas asignadas por el profesor
$queryTareas = "SELECT COUNT(*) AS total_tareas FROM tareas WHERE clase_id IN (SELECT id FROM clases WHERE materia_id IN (SELECT id FROM materias WHERE curso_id IN (SELECT id FROM cursos WHERE profesor_id = :profesorId)))";
$stmtTareas = $conn->prepare($queryTareas);
$stmtTareas->bindParam(':profesorId', $profesorId, PDO::PARAM_INT);
$stmtTareas->execute();
$rowTareas = $stmtTareas->fetch(PDO::FETCH_ASSOC);
$totalTareas = $rowTareas['total_tareas'];

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
					<a href="crear_clase.php">
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
                    <p class="full-box"><?php echo $totalCursos; ?></p>
                    <small>Impartiendo</small>
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
                    <p class="full-box"><?php echo $totalMaterias; ?></p>
                    <small>Impartiendo</small>
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
                    <p class="full-box"><?php echo $totalClases; ?></p>
                    <small>Impartiendo</small>
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
                    <p class="full-box"><?php echo $totalTareas; ?></p>
                    <small>Asignadas</small>
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