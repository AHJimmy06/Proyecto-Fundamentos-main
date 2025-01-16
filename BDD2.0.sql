-- Crear base de datos
CREATE DATABASE IF NOT EXISTS sistemaeducativo;

-- Usar la base de datos
USE sistemaeducativo;

-- Crear tabla usuarios
CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
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
    fecha TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
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

-- Crear tabla para gestionar archivos subidos por el maestro al crear una nueva clase
CREATE TABLE IF NOT EXISTS archivos_clase (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_archivo VARCHAR(50) NOT NULL,
    ruta_archivo TEXT NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    clase_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (clase_id) REFERENCES clases(id) ON DELETE CASCADE
);

-- Crear tabla para gestionar archivos subidos por el maestro al crear una nueva tarea
CREATE TABLE IF NOT EXISTS archivos_tarea (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_archivo VARCHAR(50) NOT NULL,
    ruta_archivo TEXT NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL,
    tarea_id INT NOT NULL,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE
);

-- Crear tabla para gestionar archivos subidos por los estudiantes al momento de entregar una tarea
CREATE TABLE IF NOT EXISTS archivos_estudiante_tarea (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_archivo VARCHAR(50) NOT NULL,
    ruta_archivo TEXT NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    estudiante_id INT NOT NULL,
    tarea_id INT NOT NULL,
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE
);
