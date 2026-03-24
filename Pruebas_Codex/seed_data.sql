-- Base de datos de ejemplo para la app (SQL estándar)
-- Contiene empleados y sus roles para uso de desarrollo y pruebas.

-- Si usas MySQL (XAMPP), crea y usa esta base de datos:
CREATE DATABASE IF NOT EXISTS mayhem_db;
USE mayhem_db;

CREATE TABLE IF NOT EXISTS employees (
    id INTEGER PRIMARY KEY AUTO_INCREMENT,
    nombre TEXT NOT NULL,
    apellidos TEXT NOT NULL,
    dni_pasaporte TEXT NOT NULL UNIQUE,
    username TEXT NOT NULL UNIQUE,
    password TEXT NOT NULL,
    fecha_nacimiento DATE,
    email TEXT NOT NULL UNIQUE,
    institucion TEXT,
    pais TEXT,
    motivo TEXT,
    fecha_inicio DATE,
    fecha_fin DATE,
    grupo TEXT,
    foto_url TEXT,
    rol TEXT NOT NULL
);

-- Datos simulados
INSERT INTO employees (nombre, apellidos, dni_pasaporte, username, password, fecha_nacimiento, email, institucion, pais, motivo, fecha_inicio, fecha_fin, grupo, foto_url, rol) VALUES
('Francisco', 'García Martínez', '12345678X', 'francisco.garcia', 'franco123', '1990-05-14', 'francisco.garcia@universidad.es', 'Universidad de Ejemplo', 'España', 'Investigador Postdoctoral', '2026-01-15', '2027-01-14', 'A', 'https://i.pravatar.cc/160?img=12', 'empleado'),
('María', 'López', '87654321L', 'maria.lopez', 'maria123', '1993-08-09', 'marialopez@universidad.es', 'Universidad de Ejemplo', 'España', 'Investigador Predoctoral', '2025-09-01', '2026-09-01', 'A', 'https://i.pravatar.cc/160?img=15', 'empleado'),
('Luis', 'Martínez', '34567890P', 'luis.martinez', 'luis123', '1988-11-22', 'luismartinez@universidad.es', 'Universidad de Ejemplo', 'España', 'PDI', '2025-02-10', '2026-02-10', 'A', 'https://i.pravatar.cc/160?img=48', 'empleado'),
('Ana', 'Gómez', '56789012Q', 'ana.gomez', 'ana123', '1995-03-30', 'anagomez@universidad.es', 'Universidad de Ejemplo', 'España', 'TFG', '2025-11-05', '2026-11-05', 'A', 'https://i.pravatar.cc/160?img=33', 'empleado'),
('Roberto', 'Sánchez', '23456789M', 'roberto.sanchez', 'coord123', '1982-10-17', 'roberto.sanchez@universidad.es', 'Universidad de Ejemplo', 'España', 'Coordinador de grupo', '2024-09-01', '2027-09-01', 'A', 'https://i.pravatar.cc/160?img=5', 'coordinador'),
('Laura', 'Pérez', '45678901N', 'laura.perez', 'seguridad123', '1986-07-08', 'laura.perez@universidad.es', 'Universidad de Ejemplo', 'España', 'Seguridad', '2024-01-15', '2026-01-15', 'A', 'https://i.pravatar.cc/160?img=30', 'seguridad'),
('Admin', 'Principal', '00000000T', 'admin', 'admin123', '1980-01-01', 'admin@universidad.es', 'Universidad de Ejemplo', 'España', 'Administración', '2023-01-01', '2030-01-01', 'A', 'https://i.pravatar.cc/160?img=1', 'admin');
