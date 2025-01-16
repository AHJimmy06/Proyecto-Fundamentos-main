-- Eliminar la tabla archivos original
DROP TABLE IF EXISTS archivos;

-- Crear tabla para gestionar archivos subidos por el maestro al crear una nueva clase
CREATE TABLE IF NOT EXISTS archivos_clase (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre_archivo VARCHAR(255) NOT NULL,
    tipo_archivo VARCHAR(50) NOT NULL,
    ruta_archivo TEXT NOT NULL,
    fecha_subida TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    usuario_id INT NOT NULL, -- Relación con el profesor que sube el archivo
    clase_id INT NOT NULL, -- Relación con la clase creada
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
    usuario_id INT NOT NULL, -- Relación con el profesor que sube el archivo
    tarea_id INT NOT NULL, -- Relación con la tarea creada
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
    estudiante_id INT NOT NULL, -- Relación con el estudiante que sube el archivo
    tarea_id INT NOT NULL, -- Relación con la tarea entregada
    FOREIGN KEY (estudiante_id) REFERENCES estudiantes(id) ON DELETE CASCADE,
    FOREIGN KEY (tarea_id) REFERENCES tareas(id) ON DELETE CASCADE
);

