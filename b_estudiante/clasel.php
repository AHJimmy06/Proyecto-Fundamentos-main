<?php

include('../php/verificar_acceso.php');
verificarAcceso('estudiante');

include('../php/cone.php');
$conn = Conexion();
// Obtener ID del profesor
$estudianteId = $_SESSION['id'];
$estudianteNombre =$_SESSION['nombre'];

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
		<div class="container-fluid">
			<div class="row">
				<div class="col-xs-12">
					<ul class="nav nav-tabs" style="margin-bottom: 15px;">
					  	<li><a href="clase.php">Impartiendo</a></li>
					  	<li class="active"><a href="#new" data-toggle="tab">Nueva</a></li>
					</ul>
					<div class="tab-pane fade active in" id="new">
						<div class="container-fluid">
							<div class="row">
								<div class="col-xs-12 col-md-10 col-md-offset-1">
								<form method="POST" id="formcurso" enctype="multipart/form-data">
                                    <div class="form-group">
										<label class="control-label" for="materias">Materia</label>
										<select class="form-control" name="materias" id="materias" required>
											<option value="">Seleccione una materia</option>
											<?php foreach ($materias as $materia): ?>
                                                <option value="<?php echo htmlspecialchars($materia['id']); ?>">
                                                    <?php echo htmlspecialchars($materia['nombre']); ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
									</div>
                                    <div class="form-group label-floating">
										<label class="control-label" for="tema">Tema</label>
										<input class="form-control" type="text" id="tema" name="tema" required>
									</div>
                                    <div class="form-group">
										      <label class="control-label">Archivo Complementario ||  Solo se almacenaran archivos con las extensiones pdf, doc, docx, jpg, png, zip, txt</label>
										      <div>
										        <input type="text" readonly="" class="form-control" placeholder="Buscar...">
										        <input type="file" name="archivo_complementario">
										      </div>
										</div>
									<p class="text-center" >
										<button id="form-btn" name="registrar" type="submit" class="btn btn-info btn-raised btn-sm" disabled><i class="zmdi zmdi-floppy"></i> Registrar</button>
									</p>
								</form>
								</div>
							</div>
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
    // Mostrar alerta de mensajes enviados desde el backend (si existen)
    <?php if (isset($alert_message) && isset($alert_type)): ?>
        swal({
            title: '<?= $alert_title ?>',
            html: `<?= $alert_message ?>`,
            type: '<?= $alert_type ?>',
            showConfirmButton: true,
            confirmButtonColor: '#640d14',
            confirmButtonText: 'Continuar',
        }).then(function () {
            window.location.href = "#!";
        });
    <?php endif; ?>
</script> 
</body>
</html>
