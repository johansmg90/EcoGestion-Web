SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

-- 1. Tabla PERFIL
CREATE TABLE IF NOT EXISTS `perfil` (
  `id_perfil` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_rol` varchar(50) NOT NULL,
  PRIMARY KEY (`id_perfil`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Tabla USUARIO
CREATE TABLE IF NOT EXISTS `usuario` (
  `id_usuario` int(11) NOT NULL AUTO_INCREMENT,
  `rut` varchar(12) NOT NULL,
  `nombre` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `id_perfil` int(11) NOT NULL,
  PRIMARY KEY (`id_usuario`),
  UNIQUE KEY `uk_usuario_rut` (`rut`),
  KEY `fk_usuario_perfil` (`id_perfil`),
  CONSTRAINT `fk_usuario_perfil` FOREIGN KEY (`id_perfil`) REFERENCES `perfil` (`id_perfil`) ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Tabla SUCURSAL
CREATE TABLE IF NOT EXISTS `sucursal` (
  `id_sucursal` int(11) NOT NULL AUTO_INCREMENT,
  `nombre_sucursal` varchar(100) NOT NULL,
  `direccion` varchar(200) NOT NULL,
  PRIMARY KEY (`id_sucursal`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 4. Tabla CATEGORIA_RESIDUO
CREATE TABLE IF NOT EXISTS `categoria_residuo` (
  `id_categoria` int(11) NOT NULL AUTO_INCREMENT,
  `tipo_residuo` varchar(100) NOT NULL,
  `peligrosidad` varchar(50) NOT NULL,
  PRIMARY KEY (`id_categoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 5. Tabla RESIDUO
CREATE TABLE IF NOT EXISTS `residuo` (
  `id_residuo` int(11) NOT NULL AUTO_INCREMENT,
  `peso_kg` decimal(10,2) NOT NULL,
  `fecha` date NOT NULL,
  `id_categoria` int(11) NOT NULL,
  `id_sucursal` int(11) NOT NULL,
  `id_usuario_registra` int(11) NOT NULL,
  PRIMARY KEY (`id_residuo`),
  KEY `fk_residuo_categoria` (`id_categoria`),
  KEY `fk_residuo_sucursal` (`id_sucursal`),
  KEY `fk_residuo_usuario` (`id_usuario_registra`),
  CONSTRAINT `fk_residuo_categoria` FOREIGN KEY (`id_categoria`) REFERENCES `categoria_residuo` (`id_categoria`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_residuo_sucursal` FOREIGN KEY (`id_sucursal`) REFERENCES `sucursal` (`id_sucursal`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_residuo_usuario` FOREIGN KEY (`id_usuario_registra`) REFERENCES `usuario` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 6. Tabla SOLICITUD_RETIRO
CREATE TABLE IF NOT EXISTS `solicitud_retiro` (
  `id_solicitud` int(11) NOT NULL AUTO_INCREMENT,
  `fecha_prog` date NOT NULL,
  `estado` varchar(50) NOT NULL DEFAULT 'Pendiente',
  `id_residuo` int(11) NOT NULL,
  `id_usuario_reciclador` int(11) NOT NULL,
  PRIMARY KEY (`id_solicitud`),
  KEY `fk_solicitud_residuo` (`id_residuo`),
  KEY `fk_solicitud_reciclador` (`id_usuario_reciclador`),
  CONSTRAINT `fk_solicitud_residuo` FOREIGN KEY (`id_residuo`) REFERENCES `residuo` (`id_residuo`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_solicitud_reciclador` FOREIGN KEY (`id_usuario_reciclador`) REFERENCES `usuario` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 7. Tabla HISTORIAL_ESTADO (Audit Trail Inmutable)
CREATE TABLE IF NOT EXISTS `historial_estado` (
  `id_historial` int(11) NOT NULL AUTO_INCREMENT,
  `estado_nuevo` varchar(50) NOT NULL,
  `fecha_cambio` datetime NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  `id_usuario_cambio` int(11) NOT NULL,
  PRIMARY KEY (`id_historial`),
  KEY `fk_historial_solicitud` (`id_solicitud`),
  KEY `fk_historial_usuario` (`id_usuario_cambio`),
  CONSTRAINT `fk_historial_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitud_retiro` (`id_solicitud`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_historial_usuario` FOREIGN KEY (`id_usuario_cambio`) REFERENCES `usuario` (`id_usuario`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 8. Tabla COMPROBANTE
CREATE TABLE IF NOT EXISTS `comprobante` (
  `id_comprobante` int(11) NOT NULL AUTO_INCREMENT,
  `codigo_trazabilidad` varchar(100) NOT NULL,
  `id_solicitud` int(11) NOT NULL,
  PRIMARY KEY (`id_comprobante`),
  UNIQUE KEY `uk_codigo_trazabilidad` (`codigo_trazabilidad`),
  KEY `fk_comprobante_solicitud` (`id_solicitud`),
  CONSTRAINT `fk_comprobante_solicitud` FOREIGN KEY (`id_solicitud`) REFERENCES `solicitud_retiro` (`id_solicitud`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ==========================================================
-- DATOS INICIALES (SEED DATA) - Contraseña Bcrypt: 123456
-- ==========================================================
INSERT INTO `perfil` (`id_perfil`, `nombre_rol`) VALUES
(1, 'Administrador'),
(2, 'Reciclador');

INSERT INTO `usuario` (`id_usuario`, `rut`, `nombre`, `password`, `id_perfil`) VALUES
(1, '12345678-9', 'Johans Molina (Admin)', '$2b$12$v/lHOtxcv.Uihcgb15Kq.eQTKxfBIwhIB32QPiBtRUBOSzbAehKny', 1),
(2, '98765432-1', 'Carlos Soto (Reciclador)', '$2b$12$v/lHOtxcv.Uihcgb15Kq.eQTKxfBIwhIB32QPiBtRUBOSzbAehKny', 2);

INSERT INTO `sucursal` (`id_sucursal`, `nombre_sucursal`, `direccion`) VALUES
(1, 'Planta Central - Temuco', 'Av. Rudecindo Ortega 02950, Temuco'),
(2, 'Centro Acopio Norte - Victoria', 'Ruta 5 Sur Km 610, Victoria'),
(3, 'Bodega Industrial Sur - Padre Las Casas', 'Camino Niágara 1420, Padre Las Casas');

INSERT INTO `categoria_residuo` (`id_categoria`, `tipo_residuo`, `peligrosidad`) VALUES
(1, 'Aceites Industriales Usados', 'Alta'),
(2, 'Chatarra Metálica Ferrosa', 'Media'),
(3, 'Plásticos y Embalajes Industriales', 'Baja'),
(4, 'Baterías y Residuos Electrónicos', 'Alta');

INSERT INTO `residuo` (`id_residuo`, `peso_kg`, `fecha`, `id_categoria`, `id_sucursal`, `id_usuario_registra`) VALUES
(1, 250.50, '2026-11-20', 1, 1, 1),
(2, 480.00, '2026-11-22', 2, 2, 1);

INSERT INTO `solicitud_retiro` (`id_solicitud`, `fecha_prog`, `estado`, `id_residuo`, `id_usuario_reciclador`) VALUES
(1, '2026-11-25', 'Pendiente', 1, 2);

INSERT INTO `historial_estado` (`id_historial`, `estado_nuevo`, `fecha_cambio`, `id_solicitud`, `id_usuario_cambio`) VALUES
(1, 'Pendiente', '2026-11-20 10:30:00', 1, 1);

COMMIT;