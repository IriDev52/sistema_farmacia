-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 05-12-2025 a las 16:55:52
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
(27, 27, 5, 1, 0.50, 0.50);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) UNSIGNED NOT NULL,
  `id_producto` int(11) NOT NULL,
  `tipo_movimiento` varchar(50) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `stock_antes` decimal(10,2) NOT NULL,
  `stock_despues` decimal(10,2) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `fecha_movimiento` timestamp NOT NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `id_producto`, `tipo_movimiento`, `cantidad`, `stock_antes`, `stock_despues`, `ubicacion`, `fecha_movimiento`, `observaciones`, `usuario_id`) VALUES
(1, 1, 'Entrada', 21.00, 100.00, 121.00, 'Estante B1', '2025-06-28 22:09:11', 'Entrada de stock registrada. Y reubicado de Estante C2 a Estante B1.', NULL),
(2, 2, 'Entrada', 12.00, 50.00, 62.00, 'Estante C2', '2025-06-28 22:27:15', 'Entrada de stock registrada. Y reubicado de Estante A2 a Estante C2.', NULL),
(3, 2, 'Entrada', 20.00, 62.00, 82.00, 'Estante A2', '2025-06-28 23:43:04', 'Entrada de stock. Se suman 20 unidades. Ubicación anterior: Estante C2. Ubicación actual: Estante A2.', 1),
(4, 2, 'Entrada', 70.00, 82.00, 152.00, 'Estante A1', '2025-06-29 00:25:24', 'Entrada de stock. Se suman 70 unidades. Ubicación anterior: Estante A2. Ubicación actual: Estante A1.', 1),
(5, 2, 'Entrada', 1.00, 152.00, 153.00, 'Estante C2', '2025-06-29 00:48:19', 'Entrada de stock. Se suman 1 unidades. Ubicación anterior: Estante A1. Ubicación actual: Estante C2.', 1),
(6, 2, 'Entrada', 12.00, 153.00, 165.00, 'Estante C1', '2025-06-29 00:48:37', 'Entrada de stock. Se suman 12 unidades. Ubicación anterior: Estante C2. Ubicación actual: Estante C1.', 1),
(7, 2, 'Entrada', 2.00, 165.00, 167.00, 'Estante C2', '2025-06-29 01:52:36', 'Entrada de stock. Se suman 2 unidades. Ubicación anterior: Estante C1. Ubicación actual: Estante C2.', 1),
(8, 3, 'Entrada', 1.00, 50.00, 51.00, 'Estante C1', '2025-06-29 02:04:57', 'Entrada de stock. Se suman 1 unidades. Ubicación anterior: Estante A1. Ubicación actual: Estante C1.', 1),
(9, 4, 'Entrada', 50.00, 20.00, 70.00, 'Estante A1', '2025-07-09 13:48:37', 'Entrada de stock. Se suman 50 unidades. Ubicación anterior: Estante A1. Ubicación actual: Estante A1.', 1),
(10, 5, 'Entrada', 10.00, 20.00, 30.00, 'Estante C2', '2025-07-09 13:54:36', 'Entrada de stock. Se suman 10 unidades. Ubicación anterior: Estante A2. Ubicación actual: Estante C2.', 1);

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
  `numero_lote` varchar(50) DEFAULT NULL,
  `requiere_refrigeracion` varchar(20) NOT NULL,
  `precio_venta` float NOT NULL,
  `ubicacion` varchar(50) NOT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre_producto`, `descripcion`, `laboratorio_fabrica`, `stock_actual`, `stock_minimo`, `fecha_vencimiento`, `numero_lote`, `requiere_refrigeracion`, `precio_venta`, `ubicacion`, `estado`) VALUES
(2, 'alcohol antiséptico 129ml', 'elimina las bacterias', 'Facetico ', 162, 30, '2025-07-12', NULL, 'no', 6, '0', 'activo'),
(3, 'ampicilina 500mg', 'antibiotico', 'GENVEN', 51, 25, '2025-06-07', NULL, 'si', 0.28, 'Estante C1', 'inactivo'),
(4, 'Amoxicilina 500mg', 'Antibiótico ', 'GENVEN', 70, 9, '2025-08-09', NULL, 'no', 5.2, 'Estante A1', 'activo'),
(5, 'acetaminofén 500mg', 'Analgésico', 'PHARMA', 12, 10, '2027-07-09', NULL, 'no', 0.5, 'Estante C2', 'activo'),
(6, 'Atamel 400mg', 'Analgesico', 'GENVEN', 50, 24, '2026-12-10', NULL, 'no', 5.2, 'Estante A1', 'activo'),
(7, 'alcohol antiséptico 129ml', 'antibacterial', 'PHARMA', 0, 5, '2025-07-10', NULL, 'no', 5.2, '0', 'inactivo'),
(8, 'Tachipirin', 'jarabe para la tos ', 'GENVEN', 19, 5, '2025-07-15', NULL, 'no', 500, 'Estante B2', 'activo');

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
  `correo` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios`
--

INSERT INTO `usuarios` (`id`, `correo`, `clave`, `fecha_registro`) VALUES
(2, 'camaco81@gmail.com', '$2y$10$FKbaXpwSwVXrswswZKH9aet8V9HvoxHkQP.Ecl.n5Wp1U.Wp8mJT2', '2025-12-05 15:20:30');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuarios_client`
--

CREATE TABLE `usuarios_client` (
  `id` int(11) UNSIGNED NOT NULL,
  `cedula` varchar(20) NOT NULL,
  `nombre_completo` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `clave` varchar(255) NOT NULL,
  `direccion` text DEFAULT NULL,
  `telefono` varchar(15) DEFAULT NULL,
  `fecha_registro` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `usuarios_client`
--

INSERT INTO `usuarios_client` (`id`, `cedula`, `nombre_completo`, `email`, `clave`, `direccion`, `telefono`, `fecha_registro`) VALUES
(4, '31438777', 'Marco Camacho', 'camaco81@gmail.com', '$2y$10$yABRPfyML89JWr0ScgjkEeEM5a.cQem3ciSXKJX1zUrTzkrFiVzCK', '', '04165227711', '2025-12-05 14:41:05');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `fecha_venta` datetime DEFAULT current_timestamp(),
  `total_usd` decimal(10,2) NOT NULL,
  `tasa_bcv_usada` decimal(10,4) NOT NULL,
  `total_bs` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `fecha_venta`, `total_usd`, `tasa_bcv_usada`, `total_bs`) VALUES
(27, '2025-11-21 14:38:52', 0.50, 241.5780, 120.79);

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
-- Indices de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  ADD PRIMARY KEY (`id`);

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
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `correo` (`correo`);

--
-- Indices de la tabla `usuarios_client`
--
ALTER TABLE `usuarios_client`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `cedula` (`cedula`),
  ADD UNIQUE KEY `email` (`email`);

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
  MODIFY `id_detalle_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ubicacion`
--
ALTER TABLE `ubicacion`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuarios_client`
--
ALTER TABLE `usuarios_client`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
