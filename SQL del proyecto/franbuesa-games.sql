-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 08-06-2026 a las 03:13:24
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `franbuesa-games`
--
CREATE DATABASE IF NOT EXISTS `franbuesa-games` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `franbuesa-games`;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `auditoria`
--

CREATE TABLE `auditoria` (
  `ID_Auditoria` int(11) NOT NULL,
  `ID_Usuario` int(11) DEFAULT NULL,
  `Accion` varchar(100) NOT NULL,
  `Tabla_Afectada` varchar(50) NOT NULL,
  `Detalle` text NOT NULL,
  `IP_Direccion` varchar(45) NOT NULL,
  `Fecha_Registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuenta_jugador`
--

CREATE TABLE `cuenta_jugador` (
  `ID_Cuenta` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `ID_Juego` int(11) NOT NULL,
  `ID_Jugador_InGame` varchar(50) NOT NULL,
  `Server_ID` varchar(10) DEFAULT NULL,
  `Nickname` varchar(50) DEFAULT NULL,
  `Estado_Cuenta` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `juego`
--

CREATE TABLE `juego` (
  `ID_Juego` int(11) NOT NULL,
  `Nombre_Juego` varchar(100) NOT NULL,
  `Moneda_Nombre` varchar(50) NOT NULL,
  `Imagen_Url` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquete`
--

CREATE TABLE `paquete` (
  `ID_Paquete` int(11) NOT NULL,
  `ID_Juego` int(11) NOT NULL,
  `Cantidad_Monedas` int(11) NOT NULL,
  `Precio_Bolivares` decimal(10,2) NOT NULL,
  `Descripcion` varchar(150) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `preguntas_seguridad`
--

CREATE TABLE `preguntas_seguridad` (
  `ID_Pregunta_Seg` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `Pregunta` varchar(255) NOT NULL,
  `Respuesta` varchar(255) NOT NULL,
  `Salt` varchar(100) NOT NULL,
  `Locked_Until` datetime DEFAULT NULL,
  `Created_At` datetime NOT NULL,
  `Updated_At` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `sesiones`
--

CREATE TABLE `sesiones` (
  `ID_Sesion` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `IP_Direccion` varchar(45) NOT NULL,
  `User_Agent` varchar(255) NOT NULL,
  `Estado_Sesion` varchar(20) NOT NULL,
  `Created_At` datetime NOT NULL,
  `Last_Activity_At` datetime NOT NULL,
  `Expired_At` datetime NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `sesiones`
--

INSERT INTO `sesiones` (`ID_Sesion`, `ID_Usuario`, `IP_Direccion`, `User_Agent`, `Estado_Sesion`, `Created_At`, `Last_Activity_At`, `Expired_At`) VALUES
(1, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-02 20:52:14', '2026-06-02 20:55:13', '2026-06-02 20:56:13'),
(2, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-02 20:55:17', '2026-06-02 21:01:09', '2026-06-02 21:02:09'),
(3, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-02 21:01:16', '2026-06-02 21:01:16', '2026-06-02 21:02:16'),
(4, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-02 21:02:22', '2026-06-02 21:02:22', '2026-06-02 21:03:22'),
(5, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-02 21:03:55', '2026-06-02 21:04:20', '2026-06-02 21:05:20'),
(6, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-02 21:05:20', '2026-06-02 21:06:33', '2026-06-02 21:07:33'),
(7, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-07 19:49:47', '2026-06-07 19:49:47', '2026-06-07 19:50:47'),
(8, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-07 19:50:53', '2026-06-07 19:50:53', '2026-06-07 19:51:53'),
(9, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-07 20:05:45', '2026-06-07 20:05:45', '2026-06-07 20:06:45'),
(10, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-07 20:06:57', '2026-06-07 20:06:57', '2026-06-07 20:07:57'),
(11, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-07 20:22:28', '2026-06-07 20:22:28', '2026-06-07 20:23:28'),
(12, 3, '::1', 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/148.0.0.0 Safari/537.36', 'Inactiva', '2026-06-07 20:38:46', '2026-06-07 20:38:50', '2026-06-07 20:39:50');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaccion`
--

CREATE TABLE `transaccion` (
  `ID_Transaccion` int(11) NOT NULL,
  `ID_Usuario` int(11) NOT NULL,
  `ID_Paquete` int(11) NOT NULL,
  `ID_Cuenta` int(11) NOT NULL,
  `Metodo_Pago` varchar(50) DEFAULT NULL,
  `Referencia_Bancaria` varchar(20) NOT NULL,
  `Monto_Pagado` decimal(10,2) NOT NULL,
  `Fecha_Hora` datetime NOT NULL,
  `Estado` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

CREATE TABLE `usuario` (
  `ID_Usuario` int(11) NOT NULL,
  `Nombre_Completo` varchar(100) NOT NULL,
  `Correo_Electronico` varchar(100) NOT NULL,
  `Contraseña` varchar(255) NOT NULL,
  `Telefono` varchar(20) NOT NULL,
  `Estado_Usuario` varchar(20) DEFAULT NULL,
  `Pregunta_Seguridad_1` varchar(255) DEFAULT NULL,
  `Respuesta_Seguridad_1` varchar(255) DEFAULT NULL,
  `Pregunta_Seguridad_2` varchar(255) DEFAULT NULL,
  `Respuesta_Seguridad_2` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`ID_Usuario`, `Nombre_Completo`, `Correo_Electronico`, `Contraseña`, `Telefono`, `Estado_Usuario`, `Pregunta_Seguridad_1`, `Respuesta_Seguridad_1`, `Pregunta_Seguridad_2`, `Respuesta_Seguridad_2`) VALUES
(2, 'Carlos Gonzales', 'abc123@gmail.com', 'papita', '04121234532', 'Activo', NULL, NULL, NULL, NULL),
(3, 'Principe Alteza', 'alteza@gmail.com', 'Alteza.09', '04123333333', 'Activo', NULL, NULL, NULL, NULL),
(4, 'Gabu', 'fresita@gmail.com', 'fresita.1234', '04244444444', 'Activo', NULL, NULL, NULL, NULL);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  ADD PRIMARY KEY (`ID_Auditoria`);

--
-- Indices de la tabla `cuenta_jugador`
--
ALTER TABLE `cuenta_jugador`
  ADD PRIMARY KEY (`ID_Cuenta`),
  ADD UNIQUE KEY `ID_Usuario` (`ID_Usuario`,`ID_Juego`,`ID_Jugador_InGame`),
  ADD KEY `ID_Juego` (`ID_Juego`);

--
-- Indices de la tabla `juego`
--
ALTER TABLE `juego`
  ADD PRIMARY KEY (`ID_Juego`);

--
-- Indices de la tabla `paquete`
--
ALTER TABLE `paquete`
  ADD PRIMARY KEY (`ID_Paquete`),
  ADD KEY `ID_Juego` (`ID_Juego`);

--
-- Indices de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD PRIMARY KEY (`ID_Pregunta_Seg`),
  ADD KEY `FK_usuario_id_usuario_mapeado_a_tabla_preguntas_seguridad` (`ID_Usuario`);

--
-- Indices de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD PRIMARY KEY (`ID_Sesion`),
  ADD KEY `FK_usuario_id_usuario_mapeado_a_tabla_sesiones_seguridad` (`ID_Usuario`);

--
-- Indices de la tabla `transaccion`
--
ALTER TABLE `transaccion`
  ADD PRIMARY KEY (`ID_Transaccion`),
  ADD KEY `ID_Usuario` (`ID_Usuario`),
  ADD KEY `ID_Paquete` (`ID_Paquete`),
  ADD KEY `ID_Cuenta` (`ID_Cuenta`);

--
-- Indices de la tabla `usuario`
--
ALTER TABLE `usuario`
  ADD PRIMARY KEY (`ID_Usuario`),
  ADD UNIQUE KEY `Correo_Electronico` (`Correo_Electronico`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `auditoria`
--
ALTER TABLE `auditoria`
  MODIFY `ID_Auditoria` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cuenta_jugador`
--
ALTER TABLE `cuenta_jugador`
  MODIFY `ID_Cuenta` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `juego`
--
ALTER TABLE `juego`
  MODIFY `ID_Juego` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `paquete`
--
ALTER TABLE `paquete`
  MODIFY `ID_Paquete` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  MODIFY `ID_Pregunta_Seg` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `sesiones`
--
ALTER TABLE `sesiones`
  MODIFY `ID_Sesion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `transaccion`
--
ALTER TABLE `transaccion`
  MODIFY `ID_Transaccion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario`
--
ALTER TABLE `usuario`
  MODIFY `ID_Usuario` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `cuenta_jugador`
--
ALTER TABLE `cuenta_jugador`
  ADD CONSTRAINT `FK_juego_a_cuenta_jugador` FOREIGN KEY (`ID_Juego`) REFERENCES `juego` (`ID_Juego`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_juego_cuenta` FOREIGN KEY (`ID_Juego`) REFERENCES `juego` (`ID_Juego`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_usuario_a_cuenta_jugador` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_usuario_cuenta` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `paquete`
--
ALTER TABLE `paquete`
  ADD CONSTRAINT `FK_juego_a_paquete_monedas` FOREIGN KEY (`ID_Juego`) REFERENCES `juego` (`ID_Juego`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_juego_id_juego_mapeado_a_tabla_paquete` FOREIGN KEY (`ID_Juego`) REFERENCES `juego` (`ID_Juego`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_juego_id_vinculado_a_paquete_juegos` FOREIGN KEY (`ID_Juego`) REFERENCES `juego` (`ID_Juego`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_juego_paquete` FOREIGN KEY (`ID_Juego`) REFERENCES `juego` (`ID_Juego`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `preguntas_seguridad`
--
ALTER TABLE `preguntas_seguridad`
  ADD CONSTRAINT `FK_usuario_id_usuario_mapeado_a_tabla_preguntas_seguridad` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `sesiones`
--
ALTER TABLE `sesiones`
  ADD CONSTRAINT `FK_usuario_id_usuario_mapeado_a_tabla_sesiones_seguridad` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `transaccion`
--
ALTER TABLE `transaccion`
  ADD CONSTRAINT `FK_cuenta_jugador_id_cuenta_mapeado_a_tabla_transaccion` FOREIGN KEY (`ID_Cuenta`) REFERENCES `cuenta_jugador` (`ID_Cuenta`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_paquete_id_paquete_mapeado_a_tabla_transaccion` FOREIGN KEY (`ID_Paquete`) REFERENCES `paquete` (`ID_Paquete`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `FK_usuario_id_usuario_mapeado_a_tabla_transaccion` FOREIGN KEY (`ID_Usuario`) REFERENCES `usuario` (`ID_Usuario`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
