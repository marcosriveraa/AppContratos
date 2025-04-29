CREATE DATABASE IF NOT EXISTS contratos;
USE contratos;

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `contratos`
CREATE TABLE `contratos` (
  `id` int NOT NULL,
  `nombre` varchar(255) NOT NULL,
  `ruta_pdf` varchar(255) NOT NULL,
  `firmado` tinyint(1) DEFAULT '0',
  `fecha_firma` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Disparadores `contratos`
DELIMITER $$
CREATE TRIGGER `fecha_firma_nueva` BEFORE UPDATE ON `contratos` FOR EACH ROW BEGIN
    IF NEW.firmado = 1 AND OLD.firmado != 1 THEN
        SET NEW.fecha_firma = CONVERT_TZ(NOW(), '+00:00', '+02:00');
    END IF;
END
$$
DELIMITER ;

-- --------------------------------------------------------

-- Estructura de tabla para la tabla `usuario`
CREATE TABLE `usuario` (
  `id` int NOT NULL,
  `correo` varchar(255) NOT NULL,
  `usuario` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;

-- Volcado de datos para la tabla `usuario`
INSERT INTO `usuario` (`id`, `correo`, `usuario`, `password`) VALUES
(1, 'admin@admin.com', 'admin', '$2y$10$zp8O8llezb83hx5r0IGVF.uzuDTqMl58WaMoNkgRQbSbYIO2ZGVn2');
-- Insertar una plantilla
INSERT INTO `plantillas` (`nombre`, `ruta`)
SELECT 'Plantilla Marco', 'plantillas_contratos\prestacion_servicios\plantilla01.md'
WHERE NOT EXISTS (
    SELECT 1 FROM `plantillas` WHERE `nombre` = 'Plantilla Marco'
);
-- Índices para tablas volcadas
ALTER TABLE `contratos`
  ADD PRIMARY KEY (`id`);

ALTER TABLE `usuario`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD UNIQUE KEY `usuario` (`usuario`);

-- AUTO_INCREMENT
ALTER TABLE `contratos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=114;

ALTER TABLE `usuario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
