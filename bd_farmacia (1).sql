-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Servidor: 127.0.0.1
-- Tiempo de generación: 29-01-2026 a las 18:38:46
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
(27, 27, 5, 1, 0.50, 0.50),
(28, 28, 6, 1, 5.20, 5.20),
(29, 29, 5, 4, 0.50, 2.00),
(30, 30, 6, 1, 5.20, 5.20),
(31, 31, 6, 1, 5.20, 5.20),
(32, 59, 1, 1, 1.00, 1.00),
(33, 60, 1, 1, 1.00, 1.00),
(34, 61, 1, 1, 1.00, 1.00),
(35, 62, 1, 1, 1.00, 1.00),
(36, 63, 1, 1, 1.00, 1.00),
(37, 64, 8, 1, 4.00, 4.00),
(38, 65, 8, 1, 4.00, 4.00);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `movimientos_inventario`
--

CREATE TABLE `movimientos_inventario` (
  `id` int(11) NOT NULL,
  `tipo_movimiento` varchar(50) NOT NULL,
  `cantidad` decimal(10,2) NOT NULL,
  `stock_antes` decimal(10,2) NOT NULL,
  `stock_despues` decimal(10,2) NOT NULL,
  `ubicacion` varchar(255) NOT NULL,
  `fecha_movimiento` timestamp NULL DEFAULT current_timestamp(),
  `observaciones` text DEFAULT NULL,
  `usuario_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `movimientos_inventario`
--

INSERT INTO `movimientos_inventario` (`id`, `tipo_movimiento`, `cantidad`, `stock_antes`, `stock_despues`, `ubicacion`, `fecha_movimiento`, `observaciones`, `usuario_id`) VALUES
(1, 'Entrada', 21.00, 100.00, 121.00, 'Estante B1', '2025-06-28 22:09:11', 'Entrada de stock registrada. Y reubicado de Estante C2 a Estante B1.', NULL),
(2, 'Entrada', 12.00, 50.00, 62.00, 'Estante C2', '2025-06-28 22:27:15', 'Entrada de stock registrada. Y reubicado de Estante A2 a Estante C2.', NULL),
(3, 'Entrada', 20.00, 62.00, 82.00, 'Estante A2', '2025-06-28 23:43:04', 'Entrada de stock. Se suman 20 unidades. Ubicación anterior: Estante C2. Ubicación actual: Estante A2.', 1),
(4, 'Entrada', 70.00, 82.00, 152.00, 'Estante A1', '2025-06-29 00:25:24', 'Entrada de stock. Se suman 70 unidades. Ubicación anterior: Estante A2. Ubicación actual: Estante A1.', 1),
(5, 'Entrada', 1.00, 152.00, 153.00, 'Estante C2', '2025-06-29 00:48:19', 'Entrada de stock. Se suman 1 unidades. Ubicación anterior: Estante A1. Ubicación actual: Estante C2.', 1),
(6, 'Entrada', 12.00, 153.00, 165.00, 'Estante C1', '2025-06-29 00:48:37', 'Entrada de stock. Se suman 12 unidades. Ubicación anterior: Estante C2. Ubicación actual: Estante C1.', 1),
(7, 'Entrada', 2.00, 165.00, 167.00, 'Estante C2', '2025-06-29 01:52:36', 'Entrada de stock. Se suman 2 unidades. Ubicación anterior: Estante C1. Ubicación actual: Estante C2.', 1),
(8, 'Entrada', 1.00, 50.00, 51.00, 'Estante C1', '2025-06-29 02:04:57', 'Entrada de stock. Se suman 1 unidades. Ubicación anterior: Estante A1. Ubicación actual: Estante C1.', 1),
(9, 'Entrada', 50.00, 20.00, 70.00, 'Estante A1', '2025-07-09 13:48:37', 'Entrada de stock. Se suman 50 unidades. Ubicación anterior: Estante A1. Ubicación actual: Estante A1.', 1),
(10, 'Entrada', 10.00, 20.00, 30.00, 'Estante C2', '2025-07-09 13:54:36', 'Entrada de stock. Se suman 10 unidades. Ubicación anterior: Estante A2. Ubicación actual: Estante C2.', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `productos`
--

CREATE TABLE `productos` (
  `id` int(11) NOT NULL,
  `nombre_producto` varchar(50) NOT NULL,
  `sustancia_activa` varchar(100) DEFAULT NULL,
  `descripcion` varchar(100) NOT NULL,
  `laboratorio_fabrica` varchar(50) NOT NULL,
  `clasificacion` enum('Libre Venta','Antibiótico','Controlado') DEFAULT 'Libre Venta',
  `stock_actual` int(11) NOT NULL,
  `stock_minimo` int(11) DEFAULT 5,
  `fecha_vencimiento` date NOT NULL,
  `numero_lote` varchar(50) DEFAULT NULL,
  `requiere_refrigeracion` varchar(20) NOT NULL,
  `precio_venta` float NOT NULL,
  `precio_bs` decimal(10,2) DEFAULT 0.00,
  `ubicacion` varchar(100) DEFAULT NULL,
  `estado` enum('activo','inactivo') NOT NULL DEFAULT 'activo',
  `imagen` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

--
-- Volcado de datos para la tabla `productos`
--

INSERT INTO `productos` (`id`, `nombre_producto`, `sustancia_activa`, `descripcion`, `laboratorio_fabrica`, `clasificacion`, `stock_actual`, `stock_minimo`, `fecha_vencimiento`, `numero_lote`, `requiere_refrigeracion`, `precio_venta`, `precio_bs`, `ubicacion`, `estado`, `imagen`) VALUES
(1, 'artrovit', NULL, 'aliviar el dolor, reducir la inflamación y mejorar la movilidad en articulaciones y huesos', 'PHARMA ', 'Libre Venta', 97, 5, '2026-06-30', NULL, '', 1, 350.00, 'Estante A', 'activo', 'prod_1769210090.png'),
(4, 'Vitamina c', NULL, 'Esencial para el crecimiento y reparación de tejido, la producción de colágenos ', 'FARMA', 'Libre Venta', 4, 5, '2027-02-12', NULL, '', 3, 0.00, 'Estante B', 'activo', 'prod_1769210976.jpeg'),
(5, 'Apiret ', NULL, 'Jarabe para niño, baja la fiebre, alivia el malestar general y el dolor ', 'GENVEN', 'Libre Venta', 5, 5, '2026-01-25', NULL, '', 5, 0.00, 'Estante D', 'inactivo', 'prod_1769211101.jpeg'),
(6, 'Diclofenac  Potásico 50mg', NULL, 'Aliviar el dolor y la inflamación en condiciones como el reumatismo y el dolor en la columna', 'FARMA', 'Libre Venta', 20, 5, '2028-03-17', NULL, '', 16, 0.00, 'Estante A', 'activo', 'prod_1769211362.jpeg'),
(7, 'Alcohol Antisï¿½ptico 70%', NULL, 'Desinfección y limpieza', 'GENVEN', 'Libre Venta', 19, 5, '2026-01-27', NULL, '', 5, 0.00, 'Estante C', 'inactivo', 'prod_1769211559.jpeg'),
(8, 'Desloratadina 5mg', NULL, 'Antihistamínico utilizado para aliviar los síntomas de alergias', 'FARMA', 'Libre Venta', 6, 5, '2027-07-15', NULL, '', 4, 0.00, 'Estante C', 'activo', 'prod_1769211896.jpeg'),
(9, 'Azitromicina 500mg', NULL, 'Antibiótico que se utiliza para tratar diversas infecciones bacterianas', 'PHARMA', 'Libre Venta', 6, 5, '2026-10-19', NULL, '', 15, 0.00, 'Estante A', 'activo', 'prod_1769212169.jpeg'),
(10, 'Ibuprofeno 400mg', NULL, 'Reducir la fiebre y aliviar el dolor o la inflamación', 'PHARMA', 'Libre Venta', 18, 5, '2027-11-29', NULL, '', 2, 0.00, 'Estante A', 'activo', 'prod_1769212425.jpeg'),
(11, 'Acetaminofï¿½n ', NULL, 'Dolor de cabeza ', 'Phama', 'Libre Venta', 49, 5, '2026-01-25', NULL, '', 2, 0.00, 'Estante B', 'inactivo', 'prod_1769258133.jpg'),
(12, 'Biotina ', NULL, 'Vitamina ', 'PHARMA ', 'Libre Venta', 20, 5, '2026-01-23', NULL, '', 3, 0.00, 'Estante D', 'inactivo', 'prod_1769261808.webp'),
(13, 'Metformina 850mg', NULL, 'Para la diabetes', 'MK', 'Libre Venta', 25, 5, '2026-08-15', NULL, '', 20, 0.00, 'Estante D', 'activo', 'prod_1769262598.jpeg');

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
-- Estructura de tabla para la tabla `user_infiny`
--

CREATE TABLE `user_infiny` (
  `id` int(11) NOT NULL,
  `servidor` varchar(100) NOT NULL,
  `usuario` varchar(100) NOT NULL,
  `password` varchar(150) NOT NULL
) ENGINE=MyISAM DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

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
(4, 'Irimar23@gmail.com', '$2y$10$4dkv5uKevwyo5cKzRFGfwO85uQIa4kze59XQ9aqsVSsNWNlbr.xdm', '2026-01-17 13:28:53'),
(5, 'gabrielvielma91@gmail.com', '$2y$10$5r6yUxuyeWTB6TJmc/pxge4wwmDCcntprDIJTl4d6qCBnrTcnJ2ui', '2026-01-17 23:09:00'),
(6, 'irimar56montilla@gmail.com', '$2y$10$K/O3C53t8ETwGtVKajSKHuiRBCUj1Rdbwfpbr3Kcxrn6jePuATloi', '2026-01-17 23:53:23'),
(7, 'crucesvictor39@gmail.com', '$2y$10$tkGxZOrTQdUa47qyaW0FK.bzLAsm74pN4jtB/CdJ6uV/Ih/Gnu0vG', '2026-01-22 00:55:20'),
(8, 'yuliethramos955@gmail.com', '$2y$10$MX3pl1GlIQcHLQ2Yux4kEOnRwRO5ahe.1lNcw4NR75fuFNznzZ2li', '2026-01-23 21:32:24'),
(9, 'camaco81@gmail.com', '$2y$10$kYL5dxUwbRjhR4u8/mkyY.T8hkx2KrSoaSHsy2p3X7P56z8YhBK/2', '2026-01-29 14:19:39');

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
(4, '31438777', 'Marco Camacho', 'camaco81@gmail.com', '$2y$10$yABRPfyML89JWr0ScgjkEeEM5a.cQem3ciSXKJX1zUrTzkrFiVzCK', '', '04165227711', '2025-12-05 14:41:05'),
(7, '32438777', 'Marco Camacho', 'camaco82@gmail.com', '$2y$10$VFLUo/PlMB8EMv4320qYresqteVAMqQIKZ4XnnCvOz0qB4jRMu.Ku', 'Barinas', '0416522772', '2026-01-29 16:47:21'),
(8, '24114415', 'Jose perez', 'jose81@gmail.com', '$2y$10$6OY3S43N00gRFr9ag.cypeEyHi5tu4Na44t2skn/Lly9IIK2.QrEe', 'Barinas', '04162910343', '2026-01-29 16:48:17');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ventas`
--

CREATE TABLE `ventas` (
  `id` int(11) NOT NULL,
  `cedula_cliente` varchar(20) NOT NULL,
  `referencia_pago` varchar(50) DEFAULT NULL,
  `detalles_envio` text DEFAULT NULL,
  `id_producto` int(11) DEFAULT NULL,
  `cantidad` int(11) NOT NULL,
  `fecha_venta` datetime DEFAULT current_timestamp(),
  `total_usd` decimal(10,2) NOT NULL,
  `estado_pago` enum('Pendiente','Verificado','Rechazado') DEFAULT 'Pendiente',
  `fecha` timestamp NOT NULL DEFAULT current_timestamp(),
  `tasa_bcv_usada` decimal(10,4) NOT NULL,
  `total_bs` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Volcado de datos para la tabla `ventas`
--

INSERT INTO `ventas` (`id`, `cedula_cliente`, `referencia_pago`, `detalles_envio`, `id_producto`, `cantidad`, `fecha_venta`, `total_usd`, `estado_pago`, `fecha`, `tasa_bcv_usada`, `total_bs`) VALUES
(27, '', NULL, NULL, 0, 0, '2025-11-21 14:38:52', 0.50, 'Pendiente', '2026-01-29 17:25:07', 241.5780, 120.79),
(28, '', NULL, NULL, 0, 0, '2025-12-22 10:26:40', 5.20, 'Pendiente', '2026-01-29 17:25:07', 36.5000, 189.80),
(29, '', NULL, NULL, 0, 0, '2025-12-22 11:14:27', 2.00, 'Pendiente', '2026-01-29 17:25:07', 36.5000, 73.00),
(30, '', NULL, NULL, 0, 0, '2025-12-22 11:24:07', 5.20, 'Pendiente', '2026-01-29 17:25:07', 36.5000, 189.80),
(31, '', NULL, NULL, 0, 0, '2026-01-17 09:34:51', 5.20, 'Pendiente', '2026-01-29 17:25:07', 36.5000, 189.80),
(32, '', NULL, NULL, NULL, 0, '2026-01-17 18:38:13', 5.20, 'Pendiente', '2026-01-29 17:25:07', 344.5071, 1791.44),
(33, '', NULL, NULL, 6, 1, '2026-01-17 18:44:56', 5.20, 'Pendiente', '2026-01-29 17:25:07', 344.5071, 1791.44),
(34, '', NULL, NULL, 6, 3, '2026-01-21 15:14:51', 15.60, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 5458.86),
(35, '', NULL, NULL, 3, 3, '2026-01-21 15:15:28', 0.84, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 293.94),
(36, '', NULL, NULL, 4, 1, '2026-01-21 15:15:55', 5.20, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 1819.62),
(37, '', NULL, NULL, 6, 1, '2026-01-21 15:18:16', 5.20, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 1819.62),
(38, '', NULL, NULL, 3, 1, '2026-01-21 17:20:43', 0.28, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 97.98),
(39, '', NULL, NULL, 3, 1, '2026-01-21 17:22:21', 0.28, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 97.98),
(40, '', NULL, NULL, 3, 1, '2026-01-21 17:22:34', 0.28, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 97.98),
(41, '', NULL, NULL, 6, 1, '2026-01-21 17:27:08', 5.20, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 1819.62),
(42, '', NULL, NULL, 6, 1, '2026-01-21 17:27:18', 5.20, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 1819.62),
(43, '', NULL, NULL, 6, 1, '2026-01-21 17:30:02', 5.20, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 1819.62),
(44, '', NULL, NULL, 6, 1, '2026-01-21 17:33:03', 5.20, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 1819.62),
(45, '', NULL, NULL, 6, 1, '2026-01-21 17:33:33', 5.20, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 1819.62),
(46, '', NULL, NULL, 3, 1, '2026-01-21 17:37:04', 0.28, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 97.98),
(47, '', NULL, NULL, 6, 1, '2026-01-22 03:17:01', 5.20, 'Pendiente', '2026-01-29 17:25:07', 349.9272, 1819.62),
(48, '', NULL, NULL, 6, 1, '2026-01-23 13:27:48', 5.20, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1848.87),
(49, '', NULL, NULL, 5, 1, '2026-01-23 18:13:18', 5.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1777.76),
(50, '', NULL, NULL, 8, 1, '2026-01-24 04:09:22', 4.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1422.21),
(51, '', NULL, NULL, 8, 1, '2026-01-24 04:17:15', 4.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1422.21),
(52, '', NULL, NULL, 8, 1, '2026-01-24 04:39:56', 4.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1422.21),
(53, '', NULL, NULL, 8, 1, '2026-01-24 04:40:19', 4.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1422.21),
(54, '', NULL, NULL, 13, 2, '2026-01-24 05:53:21', 40.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 14222.11),
(55, '', NULL, NULL, 8, 1, '2026-01-25 08:58:17', 4.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1422.21),
(56, '', NULL, NULL, 8, 1, '2026-01-25 09:05:12', 4.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1422.21),
(57, '', NULL, NULL, 8, 1, '2026-01-25 09:05:12', 4.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1422.21),
(58, '', NULL, NULL, 4, 1, '2026-01-25 09:05:22', 3.00, 'Pendiente', '2026-01-29 17:25:07', 355.5528, 1066.66),
(59, '24114415', NULL, NULL, NULL, 0, '2026-01-29 12:54:23', 1.00, 'Rechazado', '2026-01-29 17:25:07', 0.0000, 0.00),
(60, '24114415', NULL, NULL, NULL, 0, '2026-01-29 12:55:06', 1.00, 'Verificado', '2026-01-29 17:25:07', 0.0000, 0.00),
(61, '24114415', NULL, NULL, NULL, 0, '2026-01-29 13:10:30', 1.00, 'Rechazado', '2026-01-29 17:25:07', 0.0000, 0.00),
(62, '24114415', '1234345', 'Barinas', NULL, 0, '2026-01-29 13:16:53', 1.00, 'Pendiente', '2026-01-29 17:25:07', 0.0000, 0.00),
(63, '24114415', '12239067', 'Barrancas', NULL, 0, '2026-01-29 13:33:27', 1.00, 'Verificado', '2026-01-29 17:33:27', 0.0000, 0.00),
(64, '24114415', NULL, NULL, NULL, 0, '2026-01-29 13:35:14', 4.00, 'Pendiente', '2026-01-29 17:35:14', 0.0000, 0.00),
(65, '24114415', '23412', '12 de Marzo', NULL, 0, '2026-01-29 13:35:24', 4.00, 'Pendiente', '2026-01-29 17:35:24', 0.0000, 0.00);

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
-- Indices de la tabla `user_infiny`
--
ALTER TABLE `user_infiny`
  ADD PRIMARY KEY (`id`);

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
  MODIFY `id_detalle_venta` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=39;

--
-- AUTO_INCREMENT de la tabla `movimientos_inventario`
--
ALTER TABLE `movimientos_inventario`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `productos`
--
ALTER TABLE `productos`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `ubicacion`
--
ALTER TABLE `ubicacion`
  MODIFY `id_ubicacion` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `user_infiny`
--
ALTER TABLE `user_infiny`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `usuarios`
--
ALTER TABLE `usuarios`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT de la tabla `usuarios_client`
--
ALTER TABLE `usuarios_client`
  MODIFY `id` int(11) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT de la tabla `ventas`
--
ALTER TABLE `ventas`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

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
