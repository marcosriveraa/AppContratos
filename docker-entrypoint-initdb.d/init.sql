CREATE DATABASE IF NOT EXISTS contratos;
USE contratos;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
SET time_zone = "+00:00";

/*!40101 SET NAMES utf8mb4 */;

-- Tabla contratos
CREATE TABLE IF NOT EXISTS `contratos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(255) NOT NULL,
  `ruta_pdf` varchar(255) NOT NULL,
  `firmado` tinyint(1) DEFAULT '0',
  `fecha_firma` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Disparador para actualizar fecha_firma
DROP TRIGGER IF EXISTS `fecha_firma_nueva`;
DELIMITER $$
CREATE TRIGGER `fecha_firma_nueva` BEFORE UPDATE ON `contratos` FOR EACH ROW BEGIN
    IF NEW.firmado = 1 AND OLD.firmado != 1 THEN
        SET NEW.fecha_firma = CONVERT_TZ(NOW(), '+00:00', '+02:00');
    END IF;
END
$$
DELIMITER ;

-- Tabla usuario
CREATE TABLE IF NOT EXISTS `usuario` (
  `id` int NOT NULL AUTO_INCREMENT,
  `correo` varchar(255) NOT NULL UNIQUE,
  `usuario` varchar(50) NOT NULL UNIQUE,
  `password` varchar(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar usuario admin solo si no existe
INSERT INTO `usuario` (`correo`, `usuario`, `password`)
SELECT 'admin@admin.com', 'admin', '$2y$10$zp8O8llezb83hx5r0IGVF.uzuDTqMl58WaMoNkgRQbSbYIO2ZGVn2'
WHERE NOT EXISTS (SELECT 1 FROM `usuario` WHERE `usuario` = 'admin');

-- Tabla plantillas
CREATE TABLE IF NOT EXISTS `plantillas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(255) NOT NULL UNIQUE,
  `ruta` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla campos
CREATE TABLE IF NOT EXISTS `campos` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `nombre` VARCHAR(50) NOT NULL UNIQUE,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Tabla campos_plantillas
CREATE TABLE IF NOT EXISTS `campos_plantillas` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `id_plantilla` INT NOT NULL,
  `id_campo` INT NOT NULL,
  `orden` INT DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_plantilla` (`id_plantilla`),
  KEY `id_campo` (`id_campo`),
  CONSTRAINT `fk_cp_plantilla` FOREIGN KEY (`id_plantilla`) REFERENCES `plantillas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_cp_campo` FOREIGN KEY (`id_campo`) REFERENCES `campos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- Insertar campos solo si no existen
INSERT IGNORE INTO `campos` (`nombre`) VALUES 
('Calendario'),
('Denominación Social'),
('Domicilio Fiscal'),
('Identificación Fiscal'),
('Nombre Apoderado'),
('Lugar Notaria'),
('Notario'),
('Número Protocolo');

-- Insertar plantilla solo si no existe
INSERT INTO `plantillas` (`nombre`, `ruta`)
SELECT 'Plantilla Marco', 'plantillas_contratos/prestacion_servicios/plantilla01.md'
WHERE NOT EXISTS (SELECT 1 FROM `plantillas` WHERE `nombre` = 'Plantilla Marco');
