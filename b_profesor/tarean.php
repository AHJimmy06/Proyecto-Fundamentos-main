<?php 

include('../php/verificar_acceso.php');
	verificarAcceso('profesor');

include('../php/cone.php');

$profesorId = $_SESSION['id']; // Suponiendo que el ID del profesor está en la sesión
$profesorNombre = $_SESSION['nombre'];

?>

<!DOCTYPE html>
<html lang="es">
<head>
	<title>FISEI || Estudiantes</title>
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
		<nav class="full-box dashboard-Navbar bg-red">
			<ul class="full-box list-unstyled text-right">
				<li class="pull-left">
					<a href="#!" class="btn-menu-dashboard"><i class="zmdi zmdi-more-vert"></i></a>
				</li>
			</ul>
		</nav>
		<!-- Content page -->
		<div class="container-fluid">
			<div class="page-header">
			  <h1 class="text-titles"> <i class="zmdi zmdi-timer zmdi-hc-fw"></i> Tareas </h1>
			</div>
			<p class="lead"></p>
		</div>
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs bg-red" style="margin-bottom: 15px;">
                        <li><a href="tarea.php" >Asignadas</a></li>    	
                        <li class="active"><a href="#list" data-toggle="tab">Crear</a></li>
                        <li><a href="tareal.php" >Calificar</a></li>
					</ul>
					<div class="tab-pane fade active in" id="new">
                        <div class="container-fluid">
                            <div class="row">
                                <div class="col-xs-12 col-md-10 col-md-offset-1">
                                <form action="/crear_tarea" method="POST" enctype="multipart/form-data">
                                    <div class="form-group">
                                    <!-- Selección de la clase -->
                                    <label class="control-label"for="clase_id">Seleccionar clase:</label>
                                    <select class="form-control"id="clase_id" name="clase_id" required>
                                        <option value="">-- Seleccione una clase --</option>
                                            <!-- Aquí se generan dinámicamente las opciones desde la tabla clases -->
                                            <option value="1">Clase 1 - Tema: Introducción</option>
                                            <option value="2">Clase 2 - Tema: Conceptos Básicos</option>
                                    </select>
                                    </div>
                                    <div class="form-group label-floating">
                                        <!-- Descripción de la tarea -->
                                        <label class="control-label"for="descripcion">Descripción de la tarea:</label>
                                        <input class="form-control" type="text" id="descripcion" name="descripcion" required>
                                    </div>
                                    <div class="form-group label-floating">
                                        <!-- Fecha de entrega -->
                                        <label for="fecha_entrega">Fecha de entrega:</label>
                                        <input class="form-control" type="date" id="fecha_entrega" name="fecha_entrega" required>
                                    </div>
                                    <div class="form-group">
                                        <!-- Archivos adjuntos -->
                                        <label for="archivos">Adjuntar archivo (opcional):</label>
                                        <input type="file" id="archivos" name="archivos[]" multiple>
                                    </div>
                                    <p class="text-center">
                                        <!-- Botón de envío -->
                                        <button class="btn btn-info btn-raised btn-sm" type="submit">Crear tarea</button>
                                    </p>
                                </form>

                                    <div><a class="btn-form">Formato de registro</a></div>
                                </div>
                            </div>
                        </div>
					</div>
				</div>
			</div>
		</div>
	</section>

	<!-- Scripts -->
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
    	// Selecciona el formulario y el botón
    const form = document.getElementById('formestudiante');
	const cedula = document.getElementById('cedula');
	const nombre = document.getElementById('nombre');
    const submitButton = document.getElementById('formbtn');

    	// Escucha los eventos de entrada (input) en el formulario
    form.addEventListener('input', () => {
		const hasTenDigits = cedula.value.length === 10;
        // Verifica si el nombre tiene más de dos palabras (separadas por espacios)
    	const hasValidName = nombre.value.trim().split(/\s+/).length > 2;
		// Verifica si todos los campos requeridos están llenos y válidos
        const isValid = form.checkValidity() && hasTenDigits && hasValidName;
		
        submitButton.disabled = !isValid; // Habilita o deshabilita el botón
    });
</script>
<script>
	<?php if (isset($alert_message) && isset($alert_type)): ?>

        swal({  // Tipo de alerta (success, error)
			title: '<?= $alert_title ?>',
            html: `<?= $alert_message ?>`,
			type: '<?= $alert_type ?>',  // El mensaje de la alerta
            showConfirmButton: true, // Mostrar el botón de confirmación
			confirmButtonColor: '#640d14',
		  	confirmButtonText: ' Continuar',
        }).then(function () {
			window.location.href="#!";
		});
    <?php endif; ?>
</script>
</body>
</html>