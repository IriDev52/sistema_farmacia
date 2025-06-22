-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 23-06-2025 a las 01:17:55
-- Versión del servidor: 10.4.32-MariaDB
-- Versión de PHP: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `bd_farmacia`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `detalle_venta`
--

CREATE TABLE `detalle_venta` (
  `id_detalle_venta` int(11) NOT NULL,
  `id_venta` int(11) NOT NULL,
  `id_producto` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL,
  `precio_unitario` decimal(10,2) NOT NULL,
  `subtotal` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `detalle_venta`
--

INSERT INTO `detalle_venta` (`id_detalle_venta`, `id_venta`, `id_producto`, `cantidad`, `precio_unitario`, `subtotal`) VALUES
(1, 1, 4, 1, 12.00, 12.00),
(2, 2, 4, 1, 12.00, 12.00),
(3, 3, 4, 1, 12.00, 12.00),
(4, 4, 4, 1, 12.00, 12.00),
(5, 5, 4, 1, 12.00, 12.00),
(6, 6, 4, 4, 12.00, 48.00),
(7, 10, 4, 1, 12.00, 12.00),
(8, 11, 4, 1, 12.00, 12.00),
(9, 12, 4, 1, 12.00, 12.00),
(10, 13, 4, 1, 12.00, 12.00),
(11, 14, 4, 1, 12.00, 12.00),
(12, 15, 4, 1, 12.00, 12.00),
(13, 16, 4, 1, 12.00, 12.00),
(14, 17, 4, 1, 12.00, 12.00),
(15, 18, 4, 1, 12.00, 12.00),
(16, 19, 4, 1, 12.00, 12.00),
(17, 20, 4, 1, 12.00, 12.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre_producto` varchar(50) NOT NULL,
  `descripcion` varchar(100) NOT NULL,
  `laboratorio_fabrica` varchar(50) NOT NULL,
  `stock_actual` int(11) NOT NULL,
  `stock_minimo` int(11) NOT NULL,
  `fecha_vencimiento` date NOT NULL,
  `requiere_refrigeracion` varchar(20) NOT NULL,
  `precio_venta` float NOT NULL,
  `ubicacion` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre_producto`, `descripcion`, `laboratorio_fabrica`, `stock_actual`, `stock_minimo`, `fecha_vencimiento`, `requiere_refrigeracion`, `precio_venta`, `ubicacion`) VALUES
(4, 'Acetaminfen', 'Para el dolor de cabeza', 'Santa inez', 10, 0, '2025-06-07', 'no', 12, ''),
(6, 'Tegragrip', 'Malestar general', 'La republica', 16, 0, '2025-06-07', 'no', 0, ''),
(7, 'Ninazo', 'Congestión Nasal', 'Gen Ven', 6, 0, '2026-02-22', 'no', 6, 'Estante A1');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `producto_ubicacion`
--

CREATE TABLE `producto_ubicacion` (
  `ID_Producto` int(11) NOT NULL,
  `ID_ubicacion` int(11) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `producto_ubicacion`
--

INSERT INTO `producto_ubicacion` (`ID_Producto`, `ID_ubicacion`, `cantidad`) VALUES
(4, 1, 0),
(4, 2, 5),
(6, 1, 6);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ubicacion`
--

CREATE TABLE `ubicacion` (
  `id_ubicacion` int(11) NOT NULL,
  `descripcion_ubicacion` varchar(50) NOT NULL,
  `cantidad` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `ubicacion`
--

INSERT INTO `ubicacion` (`id_ubicacion`, `descripcion_ubicacion`, `cantidad`) VALUES
(1, 'Estante b1', 0),
(2, 'Estante b2', 0);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios`
--

CREATE TABLE `usuarios` (
  `id` int(11) NOT NULL,
  `correo` varchar(30) NOT NULL,
  `clave` varchar(30) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `correo`, `clave`) VALUES
(1, 'Irimar23@gmail.com', 'Irimar123#');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `fecha_venta` datetime DEFAULT current_timestamp(),
  `total` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `fecha_venta`, `total`) VALUES
(1, '2025-06-08 19:39:47', 12.00),
(2, '2025-06-15 21:50:44', 12.00),
(3, '2025-06-22 17:31:29', 12.00),
(4, '2025-06-22 17:50:30', 12.00),
(5, '2025-06-22 17:50:32', 12.00),
(6, '2025-06-22 17:55:07', 48.00),
(10, '2025-06-22 18:03:13', 12.00),
(11, '2025-06-22 18:13:47', 12.00),
(12, '2025-06-22 18:16:34', 12.00),
(13, '2025-06-22 18:19:21', 12.00),
(14, '2025-06-22 18:22:25', 12.00),
(15, '2025-06-22 18:24:25', 12.00),
(16, '2025-06-22 18:26:36', 12.00),
(17, '2025-06-22 18:36:33', 12.00),
(18, '2025-06-22 18:37:10', 12.00),
(19, '2025-06-22 18:58:07', 12.00),
(20, '2025-06-22 18:59:29', 12.00);

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD PRIMARY KEY (`id_detalle_venta`),
  ADD KEY `id_venta` (`id_venta`),
  ADD KEY `id_producto` (`id_producto`);

--
-- Indices de la tabla `productos`
--
ALTER TABLE `productos`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `producto_ubicacion`
--
ALTER TABLE `producto_ubicacion`
  ADD UNIQUE KEY `ID_Producto` (`ID_Producto`,`ID_ubicacion`),
  ADD KEY `ID_ubicacion` (`ID_ubicacion`);

--
-- Indices de la tabla `ubicacion`
--
ALTER TABLE `ubicacion`
  ADD PRIMARY KEY (`id_ubicacion`);

--
-- Indices de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ventas`
--
ALTER TABLE `ventas`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  MODIFY `id_detalle_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `ubicacion`
--
ALTER TABLE `ubicacion`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `detalle_venta`
--
ALTER TABLE `detalle_venta`
  ADD CONSTRAINT `detalle_venta_ibfk_1` FOREIGN KEY (`id_venta`) REFERENCES `ventas` (`id`),
  ADD CONSTRAINT `detalle_venta_ibfk_2` FOREIGN KEY (`id_producto`) REFERENCES `productos` (`id`);

--
-- Filtros para la tabla `producto_ubicacion`
--
ALTER TABLE `producto_ubicacion`
  ADD CONSTRAINT `producto_ubicacion_ibfk_1` FOREIGN KEY (`ID_Producto`) REFERENCES `productos` (`id`),
  ADD CONSTRAINT `producto_ubicacion_ibfk_2` FOREIGN KEY (`ID_ubicacion`) REFERENCES `ubicacion` (`id_ubicacion`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
