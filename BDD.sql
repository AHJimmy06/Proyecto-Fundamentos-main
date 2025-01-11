-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS sistemaeducativo;

-- Usar la base de datos
USE sistemaeducativo;

-- Crear tabla usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,  -- ID autoincrementable
    nombre VARCHAR(100) NOT NULL,
    correo VARCHAR(100) NOT NULL,
    contrasenia VARCHAR(255) NOT NULL DEFAULT '12345',
    cedula CHAR(10) NOT NULL,
    rol ENUM('admin', 'profesor', 'estudiante') NOT NULL,
    fecha_registro TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    UNIQUE KEY correo (correo),
    UNIQUE KEY cedula (cedula)
);

-- Crear tabla cursos
CREATE TABLE IF NOT EXISTS cursos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    profesor_id INT,
    FOREIGN KEY (profesor_id) REFERENCES usuarios(id) ON DELETE SET NULL
);

-- Crear tabla profesores
CREATE TABLE IF NOT EXISTS profesores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    especialidad VARCHAR(100),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla estudiantes
CREATE TABLE IF NOT EXISTS estudiantes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    matricula VARCHAR(20) NOT NULL,
    UNIQUE KEY matricula (matricula),
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE
);

-- Crear tabla materias
CREATE TABLE IF NOT EXISTS materias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    descripcion TEXT,
    curso_id INT NOT NULL,
    FOREIGN KEY (curso_id) REFERENCES cursos(id) ON DELETE CASCADE
);

-- Crear tabla clases
CREATE TABLE IF NOT EXISTS clases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    materia_id INT NOT NULL,
    fecha DATE NOT NULL,
    tema VARCHAR(100),
    FOREIGN KEY (materia_id) REFERENCES materias(id) ON DELETE CASCADE
);

-- Crear tabla tareas
CREATE TABLE IF NOT EXISTS tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    clase_id INT NOT NULL,
    descripcion TEXT NOT NULL,
    fecha_entrega DATE NOT NULL,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE
);

-- Crear tabla calificaciones
CREATE TABLE IF NOT EXISTS calificaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    tarea_id INT NOT NULL,
    estudiante_id INT NOT NULL,
    calificacion DECIMAL(5,2) DEFAULT NULL,
    comentarios TEXT,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE
);

-- Crear tabla estudiantes_tareas
CREATE TABLE IF NOT EXISTS estudiantes_tareas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    tarea_id INT NOT NULL,
    estado ENUM('entregada', 'atrasada') DEFAULT NULL,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE
);

-- Crear tabla inscripciones
CREATE TABLE IF NOT EXISTS inscripciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    estudiante_id INT NOT NULL,
    materia_id INT NOT NULL,
    fecha_inscripcion TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP(),
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (materia_id) REFERENCES materias(id)
);

-- Crear tabla archivos
CREATE TABLE IF NOT EXISTS archivos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_archivo VARCHAR(50) NOT NULL,
    ruta_archivo TEXT NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    tarea_id INT,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE
);

-- Crear trigger para generar el correo del usuario
DELIMITER ;;
CREATE TRIGGER generar_correo_usuario BEFORE INSERT ON usuarios
FOR EACH ROW
BEGIN
    IF NEW.nombre IS NULL OR LOCATE(' ', NEW.nombre) = 0 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'El nombre debe incluir al menos un espacio para separar el apellido.';
    END IF;

    IF CHAR_LENGTH(NEW.cedula) <> 10 THEN
        SIGNAL SQLSTATE '45000' SET MESSAGE_TEXT = 'La cédula debe tener 10 dígitos.';
    END IF;

    SET @primera_letra = LOWER(SUBSTRING(NEW.nombre, 1, 1));
    SET @apellido = LOWER(SUBSTRING_INDEX(NEW.nombre, ' ', -1));
    SET @ultimos_digitos = SUBSTRING(NEW.cedula, -4);
    SET NEW.correo = CONCAT(@primera_letra, @apellido, @ultimos_digitos, '@uta.edu.ec');
END ;;
DELIMITER ;

-- Insertar un solo registro en la tabla usuarios (al final)
INSERT INTO usuarios (nombre, contrasenia, cedula, rol)
VALUES ('Jimmy Alexander Añilema Hoffmann', '12345', '1850641927', 'admin');


