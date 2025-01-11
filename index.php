<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Entrar al sitio | FISEI</title>
    <link href="css/bootstrap.css" rel="stylesheet">
</head>
<body class="bg-img">
    <div class="container menu shadow-lg rounded p-4 ">
        <div class="row">
            <div class=" text-center col">
                <img class ="navimg" src="img/nav.png"/>
                <div class="navp"></div>
                <p>Facultad de Ingeniería en Sistemas, Electrónica e Industrial</p>
            </div>
            <div class="col">
                <form method="post">
                    <div class="mb-3">
                    <?php
                        include('a_login/controlador.php');
                    ?>
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputEmail1" class="form-label">Correo Institucional</label>
                        <input type="email" class="form-control" id="exampleInputEmail1" aria-describedby="emailHelp" placeholder="@uta.edu.ec" name="usuario">
                    </div>
                    <div class="mb-3">
                        <label for="exampleInputPassword1" class="form-label">Constraseña</label>
                        <input type="password" class="form-control" id="exampleInputPassword1" name="contrasenia">
                    </div>
                    <button type="submit" class="btn btn-primary container" name="btn-ingresar">Ingresar</button>
                </form>
            </div>
        </div> 
    </div>
</body>
</html>