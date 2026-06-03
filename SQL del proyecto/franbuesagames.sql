-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1:3306
-- Tiempo de generación: 24-02-2026 a las 22:48:33
-- Versión del servidor: 8.4.7
-- Versión de PHP: 8.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `franbuesagames`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuenta_jugador`
--

DROP TABLE IF EXISTS `cuenta_jugador`;
CREATE TABLE IF NOT EXISTS `cuenta_jugador` (
  `ID_Cuenta` int NOT NULL AUTO_INCREMENT,
  `ID_Usuario` int NOT NULL,
  `ID_Juego` int NOT NULL,
  `ID_Jugador_InGame` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Server_ID` varchar(10) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Nickname` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Estado_Cuenta` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID_Cuenta`),
  UNIQUE KEY `ID_Usuario` (`ID_Usuario`,`ID_Juego`,`ID_Jugador_InGame`),
  KEY `ID_Juego` (`ID_Juego`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `juego`
--

DROP TABLE IF EXISTS `juego`;
CREATE TABLE IF NOT EXISTS `juego` (
  `ID_Juego` int NOT NULL AUTO_INCREMENT,
  `Nombre_Juego` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Moneda_Nombre` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Imagen_Url` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID_Juego`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `paquete`
--

DROP TABLE IF EXISTS `paquete`;
CREATE TABLE IF NOT EXISTS `paquete` (
  `ID_Paquete` int NOT NULL AUTO_INCREMENT,
  `ID_Juego` int NOT NULL,
  `Cantidad_Monedas` int NOT NULL,
  `Precio_Bolivares` decimal(10,2) NOT NULL,
  `Descripcion` varchar(150) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID_Paquete`),
  KEY `ID_Juego` (`ID_Juego`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `transaccion`
--

DROP TABLE IF EXISTS `transaccion`;
CREATE TABLE IF NOT EXISTS `transaccion` (
  `ID_Transaccion` int NOT NULL AUTO_INCREMENT,
  `ID_Usuario` int NOT NULL,
  `ID_Paquete` int NOT NULL,
  `ID_Cuenta` int NOT NULL,
  `Metodo_Pago` varchar(50) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `Referencia_Bancaria` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Monto_Pagado` decimal(10,2) NOT NULL,
  `Fecha_Hora` datetime NOT NULL,
  `Estado` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID_Transaccion`),
  KEY `ID_Usuario` (`ID_Usuario`),
  KEY `ID_Paquete` (`ID_Paquete`),
  KEY `ID_Cuenta` (`ID_Cuenta`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `transaccion`
--

INSERT INTO `transaccion` (`ID_Transaccion`, `ID_Usuario`, `ID_Paquete`, `ID_Cuenta`, `Metodo_Pago`, `Referencia_Bancaria`, `Monto_Pagado`, `Fecha_Hora`, `Estado`) VALUES
(1, 1, 1, 1, 'Transferencia', 'abc123', 100.00, '2026-02-04 00:00:00', 'Pendiente'),
(2, 1, 1, 1, 'Transferencia', '3272912879821t91t947', 50.00, '2026-02-11 00:00:00', 'Pendiente');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario`
--

DROP TABLE IF EXISTS `usuario`;
CREATE TABLE IF NOT EXISTS `usuario` (
  `ID_Usuario` int NOT NULL AUTO_INCREMENT,
  `Nombre_Completo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Correo_Electronico` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Contraseña` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Telefono` varchar(20) COLLATE utf8mb4_unicode_ci NOT NULL,
  `Estado_Usuario` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  PRIMARY KEY (`ID_Usuario`),
  UNIQUE KEY `Correo_Electronico` (`Correo_Electronico`)
) ENGINE=MyISAM AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario`
--

INSERT INTO `usuario` (`ID_Usuario`, `Nombre_Completo`, `Correo_Electronico`, `Contraseña`, `Telefono`, `Estado_Usuario`) VALUES
(2, 'Carlos Gonzales', 'abc123@gmail.com', 'papita', '04121234532', 'Activo');
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
