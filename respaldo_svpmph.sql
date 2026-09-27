-- phpMyAdmin SQL Dump
-- version 5.2.3
-- https://www.phpmyadmin.net/
--
-- Servidor: localhost:3306
-- Tiempo de generación: 26-09-2026 a las 04:59:35
-- Versión del servidor: 8.0.30
-- Versión de PHP: 8.1.10

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Base de datos: `svpmph_dbl`
--

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `actividad`
--

CREATE TABLE `actividad` (
  `id` int NOT NULL,
  `actividad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `archivos`
--

CREATE TABLE `archivos` (
  `id` int NOT NULL,
  `url` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_id` int NOT NULL,
  `archivo` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `verificado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `archivos`
--

INSERT INTO `archivos` (`id`, `url`, `user_id`, `archivo`, `verificado`) VALUES
(47, NULL, 19, ' certificado de asistente o carta de culminacion', 0),
(48, 'documento/archivo_1789872322_6aaf48c2cc2c1.pdf', 19, 'Fotocopia de la cedula', 0),
(49, NULL, 19, 'titulo', 0),
(50, NULL, 19, ' titulo de bachiller', 0),
(51, NULL, 19, 'curriculum', 0),
(52, 'documento/archivo_1790056733_6ab2191db6048.pdf', 32, 'Fotocopia de la cedula', 1),
(53, 'documento/archivo_1790056748_6ab2192c0e9cf.pdf', 32, ' titulo de bachiller', 1),
(54, NULL, 32, 'curriculum', 0),
(55, NULL, 32, ' certificado de asistente o carta de culminacion', 0),
(56, 'documento/archivo_1790056781_6ab2194de574e.png', 32, 'titulo', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `asignaturas`
--

CREATE TABLE `asignaturas` (
  `id` int NOT NULL,
  `asignatura` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `codigo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `horas_teoricas` int DEFAULT NULL,
  `horas_practicas` int DEFAULT NULL,
  `programa_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `asignaturas`
--

INSERT INTO `asignaturas` (`id`, `asignatura`, `codigo`, `horas_teoricas`, `horas_practicas`, `programa_id`) VALUES
(1, 'Vendeta', 'Jhskajskas', 500, 700, 23),
(11, 'vendeta-4 tu sabes q lo q', 'vendeta-4', 120, 30, 20);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `bancos`
--

CREATE TABLE `bancos` (
  `id` int NOT NULL,
  `banco` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cargos`
--

CREATE TABLE `cargos` (
  `id` int NOT NULL,
  `cargo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `institucion_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cargos`
--

INSERT INTO `cargos` (`id`, `cargo`, `institucion_id`) VALUES
(1, 'medico', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuentas`
--

CREATE TABLE `cuentas` (
  `id` int NOT NULL,
  `usuario` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `contrasena` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `estado` int NOT NULL,
  `codigo` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `fecha_codigo` datetime DEFAULT NULL,
  `token_intentos` int DEFAULT '0',
  `rol_id` int NOT NULL,
  `correo` varchar(200) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `correo_pendiente` varchar(200) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `cuentas`
--

INSERT INTO `cuentas` (`id`, `usuario`, `contrasena`, `estado`, `codigo`, `fecha_codigo`, `token_intentos`, `rol_id`, `correo`, `correo_pendiente`) VALUES
(19, 'miguel', '$2y$10$YTkhvW0w66OGQEo.oPGm0.my6iXu.McEE5aGN2npo6aW.Dgfe6Lj.', 1, NULL, NULL, 0, 5, 'miguelbucarito2025@gmail.com', NULL),
(29, 'noli', '$2y$10$gib9qb3fIjCdUZy1KPxXRutNNRaap6BKAzEDPDJlqu1TMYOwn.4iC', 1, '554117', '2026-09-02 23:17:01', 0, 4, 'miguelbuca02@gmail.com', 'miguelbuca02@gmail.com'),
(30, 'eduardo', '$2y$10$Cn7u.TEhkKL29F75tBLKE.WQqDYLYVd96LTP9eWwGhgDu3mwGg5MG', 1, '234032', '2026-09-05 20:42:55', 0, 1, 'joseeduardolista@gmail.com', 'joseeduardolista@gmail.com'),
(31, 'winder', '$2y$10$ixzuy9rK9wN.h2fhAADb2O9gT77qOIydtoPk03a1niIBsVMXfCZ26', 1, '361874', '2026-09-05 20:46:12', 0, 1, 'miguelbuca@gmail.com', 'miguelbuca@gmail.com'),
(32, 'root', '$2y$10$KKjLcG9uMPacfERrQtRu.O43qr8qoAfwc9rrhzNMvbnIxaskDLfCe', 1, '638597', '2026-09-05 22:37:02', 0, 1, 'miguelbuasca@gmail.com', 'miguelbuasca@gmail.com');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `cuotas`
--

CREATE TABLE `cuotas` (
  `id` int NOT NULL,
  `user_id` int NOT NULL,
  `cuota` int NOT NULL,
  `pago_id` int DEFAULT NULL,
  `monto` decimal(10,2) NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos`
--

CREATE TABLE `datos` (
  `id` int NOT NULL,
  `nombre` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `apellido` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `s_nombre` varchar(40) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `s_apellido` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `id_cedula` int NOT NULL,
  `tlf` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `edad` date DEFAULT NULL,
  `foto` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `ingreso` datetime DEFAULT NULL,
  `cuenta_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `datos`
--

INSERT INTO `datos` (`id`, `nombre`, `apellido`, `s_nombre`, `s_apellido`, `id_cedula`, `tlf`, `direccion`, `edad`, `foto`, `ingreso`, `cuenta_id`) VALUES
(14, 'Miguel', 'Bucarito', 'Josue', 'Itanare', 31107226, '04262320438', 'san jose de guanipa\r\ncalle los pinos sector los olivos casa #273', '2005-06-23', 'perfiles/usuario_19_1787275878.jpg', '2026-08-20 21:31:05', 19),
(16, 'Noli', 'Bucarito', 'Jose', 'Itanare', 12967186, '04262320437', 'san jose de guanipa\r\ncalle los pinos sector los olivos casa #273', '1972-06-26', '', '2026-09-02 23:00:05', 29),
(17, 'Eduardo', 'Lista', 'Jose', 'Bucarito', 31107227, '04262320431', 'san jose de guanipa\r\ncalle los pinos sector los olivos casa #273', '2008-02-26', 'perfiles/usuario_30_1788653729.jpg', '2026-09-05 20:14:31', 30),
(18, 'Winder', 'Bucarito', 'Margarito', 'Itanare', 31107228, '04262320439', 'san jose de guanipa\r\ncalle los pinos sector los olivos casa #273', '1972-06-26', NULL, '2026-09-05 20:18:27', 31),
(20, 'Miguel', 'Bucarito', NULL, NULL, 31107220, '04262320432', 'san jose de guanipa\r\ncalle los pinos sector los olivos casa #273', '1988-09-05', NULL, '2026-09-05 22:10:55', 32);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `datos_laborales`
--

CREATE TABLE `datos_laborales` (
  `id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `institucion_id` int NOT NULL,
  `cargo_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `datos_laborales`
--

INSERT INTO `datos_laborales` (`id`, `cuenta_id`, `institucion_id`, `cargo_id`) VALUES
(7, 19, 1, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `destinario`
--

CREATE TABLE `destinario` (
  `id` int NOT NULL,
  `destinario` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `oferta_id` int NOT NULL,
  `cedula_id` int NOT NULL,
  `datos` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes`
--

CREATE TABLE `docentes` (
  `id` int NOT NULL,
  `cedula_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `docentes_asignados`
--

CREATE TABLE `docentes_asignados` (
  `id` int NOT NULL,
  `docente_id` int NOT NULL,
  `seccion_id` int NOT NULL,
  `asignatura_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `estudiantes`
--

CREATE TABLE `estudiantes` (
  `id` int NOT NULL,
  `cedula_id` int NOT NULL,
  `nucleo_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `evaluaciones`
--

CREATE TABLE `evaluaciones` (
  `id` int NOT NULL,
  `docente_id` int NOT NULL,
  `actividad_id` int NOT NULL,
  `valor` decimal(5,2) NOT NULL,
  `fecha` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `facilitador_oferta`
--

CREATE TABLE `facilitador_oferta` (
  `id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `oferta_id` int NOT NULL,
  `estado` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `facilitador_oferta`
--

INSERT INTO `facilitador_oferta` (`id`, `cuenta_id`, `oferta_id`, `estado`) VALUES
(16, 32, 2, 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `gremio`
--

CREATE TABLE `gremio` (
  `id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `mencion_id` int NOT NULL,
  `estado` tinyint(1) DEFAULT NULL,
  `creacion` date NOT NULL,
  `codigo` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `promocion_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `gremio`
--

INSERT INTO `gremio` (`id`, `cuenta_id`, `mencion_id`, `estado`, `creacion`, `codigo`, `promocion_id`) VALUES
(15, 19, 2, 0, '2026-09-19', 'SVPMPH-TSU-00019', 4),
(16, 32, 3, 0, '2026-09-22', 'SVPMPH-ASIST-00032', 4);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `inscripcion`
--

CREATE TABLE `inscripcion` (
  `id` int NOT NULL,
  `cedula_id` int NOT NULL,
  `oferta_id` int NOT NULL,
  `seccion_id` int NOT NULL,
  `ingreso` datetime DEFAULT NULL,
  `status` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `institucion`
--

CREATE TABLE `institucion` (
  `id` int NOT NULL,
  `institucion` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `institucion`
--

INSERT INTO `institucion` (`id`, `institucion`) VALUES
(1, 'Hospital Felipe Guevara Rojas de el tigre'),
(2, 'Porteccion Civil el tigre'),
(3, 'Poteccion Civil  Guanipa');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `mencion`
--

CREATE TABLE `mencion` (
  `id` int NOT NULL,
  `mencion` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requisitos` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT NULL,
  `codigo` varchar(20) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `mencion`
--

INSERT INTO `mencion` (`id`, `mencion`, `img`, `requisitos`, `estado`, `codigo`) VALUES
(2, 'Asistente', 'rol/img_1789512252_6aa9ca3ca9c1b.jpg', 'Fotocopia de la cedula, titulo de bachiller,curriculum, certificado de asistente o carta de culminacion', 1, 'ASIST'),
(3, 'TSU', 'rol/img_1789512290_6aa9ca623bf1b.jpg', 'Fotocopia de la cedula, titulo de bachiller,titulo', 1, 'TSU'),
(4, 'Licenciado', 'rol/img_1789512315_6aa9ca7b568b9.jpg', 'Fotocopia de la cedula, titulo de bachiller', 1, 'LIC'),
(5, 'Operador de Ambulancia', 'rol/img_1789512378_6aa9caba6d33e.jpg', 'Fotocopia de la cedula, titulo de bachiller', 1, 'OPER-AMB'),
(7, 'Operador de A', 'carrera/img_1789524761_6aa9fb1926ae0.webp', 'Fotocopia de la cedula, titulo de bachiller', 1, NULL);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modalidad`
--

CREATE TABLE `modalidad` (
  `id` int NOT NULL,
  `modalidad` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `modalidad`
--

INSERT INTO `modalidad` (`id`, `modalidad`) VALUES
(1, 'Diaria'),
(2, 'Semanal'),
(3, 'Quincenal'),
(4, 'Mensual'),
(5, 'TrimesTral'),
(6, 'Semestral'),
(7, 'Anual');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `modalidad_programa`
--

CREATE TABLE `modalidad_programa` (
  `id` int NOT NULL,
  `programa_id` int NOT NULL,
  `nucleo_id` int NOT NULL,
  `modalidad_id` int NOT NULL,
  `cantidad` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `notas`
--

CREATE TABLE `notas` (
  `id` int NOT NULL,
  `actividad_id` int NOT NULL,
  `nota` int NOT NULL,
  `estudiante_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `nucleo`
--

CREATE TABLE `nucleo` (
  `id` int NOT NULL,
  `nucleo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `direccion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `logo` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `nucleo`
--

INSERT INTO `nucleo` (`id`, `nucleo`, `descripcion`, `direccion`, `logo`, `img`) VALUES
(1, 'Hospital general de el Tigre', 'ahora si tigre', 'dale ps manolo', 'logos/logo_1787761186_6a8f122283440.png', 'img/img_1787761188_6a8f122429355.png'),
(2, 'El Inana el tigrito', 'es un centro de no se donde coño', 'en la parada de los olivos', 'logos/logo_1787769048_6a8f30d8cd811.jpg', 'img/img_1787770646_6a8f3716eec2e.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `ofertas`
--

CREATE TABLE `ofertas` (
  `id` int NOT NULL,
  `nucleo_id` int NOT NULL,
  `programa_id` int NOT NULL,
  `estado` tinyint(1) NOT NULL,
  `fecha_ini` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL,
  `costo_inscripcion` decimal(10,2) NOT NULL,
  `costo_total` decimal(10,2) NOT NULL,
  `cuotas` int NOT NULL,
  `modo_cuotas` int NOT NULL,
  `flyer` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `ofertas`
--

INSERT INTO `ofertas` (`id`, `nucleo_id`, `programa_id`, `estado`, `fecha_ini`, `fecha_fin`, `costo_inscripcion`, `costo_total`, `cuotas`, `modo_cuotas`, `flyer`) VALUES
(1, 1, 26, 1, '2026-08-27', '2026-08-30', 10.00, 130.00, 13, 4, 'flyers/flyer_1787806669_6a8fc3cd52d12.png'),
(2, 2, 23, 1, '2026-08-30', '2026-10-04', 5.00, 10.00, 1, 1, 'flyers/flyer_1787806839_6a8fc47728404.jpg'),
(4, 1, 20, 1, '2026-09-12', '2026-10-03', 0.00, 0.00, 1, 1, 'flyers/flyer_1789257103_6aa5e58fdbb2c.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pagos`
--

CREATE TABLE `pagos` (
  `id` int NOT NULL,
  `cuota_id` int NOT NULL,
  `metodo` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `referencia` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `banco_origen_id` int NOT NULL,
  `destinario_id` int NOT NULL,
  `fecha` datetime NOT NULL,
  `status` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `pensum`
--

CREATE TABLE `pensum` (
  `id` int NOT NULL,
  `p_modalidad_id` int NOT NULL,
  `asignatura_id` int NOT NULL,
  `fecha_ini` date DEFAULT NULL,
  `fecha_fin` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `programas`
--

CREATE TABLE `programas` (
  `id` int NOT NULL,
  `programa` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `requisitos` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `logo` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `duracion` varchar(30) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `tipo_programa` int NOT NULL,
  `estado` tinyint(1) NOT NULL,
  `certificado` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `programas`
--

INSERT INTO `programas` (`id`, `programa`, `descripcion`, `requisitos`, `logo`, `duracion`, `tipo_programa`, `estado`, `certificado`) VALUES
(16, 'wmmwm', 'mmd', 'dmdm', NULL, 'ddmd', 1, 1, NULL),
(18, 'smms', 'kdkdk', 'kdkkdkd', NULL, 'ddkk', 1, 1, NULL),
(20, 'docificacion de medicamentos', 'dmslkd', 'kdkdnfk', NULL, 'dmakslsk', 1, 1, NULL),
(23, 'taller de vias parenterales', 'no se me ocurrio nada manolo', 'no tengo idea', 'logos/logo_1787619339_6a8ce80b68efd.jpg', 'un dia', 2, 1, 'certificados/certificado_1787622411_6a8cf40bb0079.jpg'),
(26, 'juego de tronos', 'no se me ocurre nada', 'se un lamebotas, tener un titulo de pendejo, ser un pajuo de nivel Eduardo', 'logos/logo_1787771489_6a8f3a61148f4.jpg', 'toda la jodida tarde', 1, 1, 'certificados/certificado_1787771491_6a8f3a631ebec.png');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `promocion`
--

CREATE TABLE `promocion` (
  `id` int NOT NULL,
  `promocion` varchar(300) COLLATE utf8mb4_unicode_ci NOT NULL,
  `periodo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(300) COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `promocion`
--

INSERT INTO `promocion` (`id`, `promocion`, `periodo`, `img`) VALUES
(3, 'Dr Adixa Catro', '2026-2027', 'rol/img_1789523912_6aa9f7c847201.jpg'),
(4, 'Mayor General Gloria Castillo', '2025-2026', 'rol/img_1789523951_6aa9f7efbeb53.jpg'),
(5, 'Anterior a la Pandemia', '2000-2019', 'rol/img_1789524103_6aa9f8877f255.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `rol`
--

CREATE TABLE `rol` (
  `id` int NOT NULL,
  `rol` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `descripcion` varchar(500) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `img` varchar(300) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `rol`
--

INSERT INTO `rol` (`id`, `rol`, `descripcion`, `img`) VALUES
(1, 'Usuario', 'Rol base para aspirantes, estudiantes y agremiados', 'rol/img_1788317937_6a9790f1cf064.png'),
(2, 'Docente', 'Encargado de Evaluar  los Estudiantes y cargar las notas', 'rol/img_1788395553_6a98c021295e6.jpg'),
(3, 'Facilitador', 'Admnistra el programa que se le a asignado', 'rol/img_1788317881_6a9790b93b424.png'),
(4, 'Presidente', 'El Líder  SVPMPH', 'rol/img_1788318218_6a97920aa5e93.png'),
(5, 'Administrador', 'El encargado de Toda la Plataforma del Sistema', 'rol/img_1788395869_6a98c15d7e49d.jpg');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `secciones`
--

CREATE TABLE `secciones` (
  `id` int NOT NULL,
  `seccion` varchar(50) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `oferta_id` int NOT NULL,
  `cantidad_max` int NOT NULL,
  `grupo_whatsapp` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `estado` tinyint(1) DEFAULT '1'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `secciones`
--

INSERT INTO `secciones` (`id`, `seccion`, `oferta_id`, `cantidad_max`, `grupo_whatsapp`, `estado`) VALUES
(1, 'Corte 1A', 4, 50, 'https://chat.whatsapp.com/LhQhZuKm6rC1K2dX8XhaeE', 1),
(6, 'Corte 2B', 4, 20, 'https://chat.whatsapp.com/LhQhZuKm6rC1K2dX8XhaeE', 1),
(7, 'Corte 3B', 4, 15, 'https://chat.whatsapp.com/LhQhZuKm6rC1K2dX8XhaeE', 1),
(8, 'Corte 1A', 2, 15, 'https://chat.whatsapp.com/L2dJQHIKI4WGMgmxaQLqts', 1);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `terminos`
--

CREATE TABLE `terminos` (
  `id` int NOT NULL,
  `titulo` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `version` varchar(10) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `contenido` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `estado` tinyint(1) NOT NULL,
  `fecha` date DEFAULT NULL,
  `rol_id` int NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `terminos`
--

INSERT INTO `terminos` (`id`, `titulo`, `version`, `contenido`, `estado`, `fecha`, `rol_id`) VALUES
(20, 'TÉRMINOS, CONDICIONES Y POLÍTICA DE PRIVACIDAD', '1.9', '<body>\r\n  <div class=\"container-terminos\">\r\n    <h1>T&Eacute;RMINOS, CONDICIONES Y POL&Iacute;TICA DE PRIVACIDAD</h1>\r\n    <span>\r\n      <b>\r\n        Sociedad Venezolana de Profesionales en Medicina\r\n        Prehospitalaria(SVPMPH)\r\n      </b>\r\n    </span>\r\n    <hr>\r\n\r\n    <div class=\"container-indice\">\r\n      <h3>Indice General</h3>\r\n      <ul class=\"menu-indice\">\r\n        <li>\r\n          <a href=\"#index-1\">1. Objeto y Naturaleza de la Plataforma</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-2\">2. Progresi&oacute;n de Roles y Acceso Inicial</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-3\">3. Finalidad del Almacenamiento y Uso de Datos</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-4\">4. C&oacute;digo de &Eacute;tica, Reputaci&oacute;n e Imagen Institucional</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-5\">5. R&eacute;gimen de Referencias Profesionales y Empleo</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-6\">6. R&eacute;gimen Disciplinario y Bloqueo de Cuentas</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-7\">7. Marco Legal, Respaldo Institucional y Tuici&oacute;n del Estado</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-8\">8. Limitaci&oacute;n de Responsabilidad M&eacute;dica y Operativa\r\n            (Disclaimer)</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-9\">9. Caducidad y Recertificaci&oacute;n de Credenciales</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-10\">10. Advertencia sobre Delitos Inform&aacute;ticos y Falsificaci&oacute;n</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-11\">11. Confidencialidad, Privacidad y Propiedad Intelectual</a>\r\n        </li>\r\n      </ul>\r\n    </div>\r\n    <h2 id=\"index-1\">1. Objeto y Naturaleza de la Plataforma</h2>\r\n    <p>\r\n      El presente documento regula el acceso, registro y uso de la plataforma\r\n      web oficial de la Sociedad Venezolana de Profesionales en Medicina\r\n      Prehospitalaria (<b>svpmph.org</b>). La plataforma opera como un sistema\r\n      integral de gesti&oacute;n gremial, acad&eacute;mica y operativa destinado a\r\n      registrar, validar, certificar y avalar a los profesionales, estudiantes\r\n      y egresados del &aacute;rea prehospitalaria conforme a las normativas de la\r\n      Rep&uacute;blica Bolivariana de Venezuela.\r\n    </p>\r\n    <h2 id=\"index-2\">2. Progresi&oacute;n de Roles y Acceso Inicial</h2>\r\n    <ul>\r\n      <li>\r\n        <b>Rol Inicial / Registro Base:</b> Todo usuario que ingresa a la\r\n        plataforma debe pasar obligatoriamente por este nivel inicial. Al\r\n        registrarse mediante nombre de usuario, contrase&ntilde;a y correo\r\n        electr&oacute;nico (destinado exclusivamente a la autenticaci&oacute;n y\r\n        recuperaci&oacute;n de credenciales), el usuario adquiere la capacidad de\r\n        visualizar informaci&oacute;n general del portal y emitir comentarios.\r\n      </li>\r\n      <li>\r\n        <b> Selecci&oacute;n de Trayectoria:</b> El Rol Inicial constituye una etapa\r\n        de tr&aacute;nsito obligatorio. Desde este punto, el usuario decidir&aacute; y\r\n        postular&aacute; su transici&oacute;n formal hacia el perfil de Estudiante (mediante\r\n        la inscripci&oacute;n en talleres o cursos) o su consolidaci&oacute;n como\r\n        Profesional / Egresado.\r\n      </li>\r\n      <li>\r\n        <b>Definici&oacute;n de Egresado:</b> Se consideran egresados aquellos\r\n        participantes que completaron satisfactoriamente sus ciclos formativos\r\n        y planes de estudio aprobados por la instituci&oacute;n.\r\n      </li>\r\n    </ul>\r\n    <h2 id=\"index-3\">3. Finalidad del Almacenamiento y Uso de Datos</h2>\r\n    <p>\r\n      Los datos personales, demogr&aacute;ficos y laborales recopilados (tales como\r\n      nombre, apellido, segundo nombre, segundo apellido, c&eacute;dula de identidad,\r\n      edad, direcci&oacute;n, procedencia y estatus laboral de adscripci&oacute;n a cuerpos\r\n      de seguridad, bomberos, protecci&oacute;n civil o personal hospitalario) tienen\r\n      como prop&oacute;sito estricto:\r\n    </p>\r\n\r\n    <ul>\r\n      <li>\r\n        Gestionar el control acad&eacute;mico, expedici&oacute;n de notas y emisi&oacute;n de\r\n        certificados oficiales.\r\n      </li>\r\n      <li>\r\n        Mantener el registro interno, control de solvencias y carnetizaci&oacute;n de\r\n        los miembros.\r\n      </li>\r\n      <li>\r\n        Servir como plataforma de verificaci&oacute;n oficial de credenciales ante\r\n        organismos de seguridad del Estado, empresas p&uacute;blicas o privadas (por\r\n        ejemplo, en despliegues operativos o &aacute;reas industriales de alto\r\n        riesgo) y ante el Ministerio del Poder Popular para la Salud.\r\n      </li>\r\n      <li>\r\n        <i>Nota de Beneficios:</i> La verificaci&oacute;n de adscripci&oacute;n a\r\n        instituciones hospitalarias p&uacute;blicas aliadas permite la aplicaci&oacute;n de\r\n        esquemas de exoneraci&oacute;n o gratuidad en los procesos acad&eacute;micos\r\n        correspondientes.\r\n      </li>\r\n    </ul>\r\n\r\n    <h2 id=\"index-4\">\r\n      4. C&oacute;digo de &Eacute;tica, Reputaci&oacute;n e Imagen Institucional\r\n    </h2>\r\n    <ul>\r\n      <li>\r\n        <b> Representaci&oacute;n Gremial:</b> Los usuarios, estudiantes y egresados\r\n        forman parte activa de la SVPMPH. Queda terminantemente prohibido usar\r\n        el nombre, insignias, logotipos o avales de la sociedad en vano, de\r\n        forma il&iacute;cita o en detrimento de la moral y las buenas costumbres.\r\n        Cualquier acto que manche el prestigio de la instituci&oacute;n acarrear&aacute; la\r\n        expulsi&oacute;n inmediata del gremio y las acciones legales pertinentes.\r\n      </li>\r\n      <li>\r\n        <b>Participaci&oacute;n en Eventos:</b> La asistencia a los eventos,\r\n        asambleas y convocatorias oficiales es de car&aacute;cter voluntario; sin\r\n        embargo,\r\n        <b>se recomienda enf&aacute;ticamente la participaci&oacute;n activa</b> como\r\n        muestra de compromiso gremial y superaci&oacute;n profesional continua.\r\n      </li>\r\n    </ul>\r\n\r\n    <h2 id=\"index-5\">5. R&eacute;gimen de Referencias Profesionales y Empleo</h2>\r\n    <ul>\r\n      <li>\r\n        <b> Exenci&oacute;n de Agencia de Empleo:</b> La SVPMPH\r\n        <b>no funciona como una agencia de colocaci&oacute;n laboral</b> ni garantiza\r\n        contrataciones directas en el mercado de trabajo.\r\n      </li>\r\n      <li>\r\n        <b>Sistema de Referencias Institucionales:</b> Si una compa&ntilde;&iacute;a p&uacute;blica\r\n        o privada solicita perfiles certificados, la instituci&oacute;n emitir&aacute;\r\n        evaluaciones basadas estrictamente en los registros internos del\r\n        usuario. La constancia de buen rendimiento, la participaci&oacute;n en\r\n        eventos y la asistencia acumulada registrada en la plataforma otorgan\r\n        puntuaci&oacute;n diferenciada y un trato preferencial en las referencias que\r\n        la sociedad emita ante terceros.\r\n      </li>\r\n    </ul>\r\n    <h2 id=\"index-6\">6. R&eacute;gimen Disciplinario y Bloqueo de Cuentas</h2>\r\n    <p>\r\n      La administraci&oacute;n se reserva el derecho de suspender, bloquear o revocar\r\n      de manera temporal o definitiva el acceso a cualquier cuenta bajo los\r\n      siguientes criterios:\r\n    </p>\r\n    <ul>\r\n      <li>\r\n        <b> Acumulaci&oacute;n de Deudas:</b> Incumplimiento reiterado en el pago de\r\n        cuotas acad&eacute;micas, aportes o aranceles administrativos generados por\r\n        los programas en los que participa.\r\n      </li>\r\n      <li>\r\n        <b>Infracci&oacute;n Normativa:</b> Violaci&oacute;n comprobada de los reglamentos\r\n        internos de la instituci&oacute;n, de los programas acad&eacute;micos o de las\r\n        directrices &eacute;ticas exigidas a los miembros.\r\n      </li>\r\n    </ul>\r\n    <h2 id=\"index-7\">\r\n      7. Marco Legal, Respaldo Institucional y Tuici&oacute;n del Estado\r\n    </h2>\r\n    <ul>\r\n      <li>\r\n        <b> Fundamento Legal:</b> La recolecci&oacute;n, tratamiento y resguardo de\r\n        los datos personales y acad&eacute;micos en esta plataforma se rigen bajo los\r\n        principios consagrados en la Constituci&oacute;n de la Rep&uacute;blica Bolivariana\r\n        de Venezuela, en concordancia con las normativas vigentes sobre\r\n        protecci&oacute;n de datos, derecho al honor, la intimidad y la\r\n        confidencialidad de la informaci&oacute;n.\r\n      </li>\r\n      <li>\r\n        <b> Tuici&oacute;n y Respaldo del Estado:</b> La SVPMPH desarrolla sus\r\n        actividades bajo los lineamientos de los entes rectores en materia de\r\n        salud y seguridad ciudadana, manteniendo canales de interoperabilidad,\r\n        validaci&oacute;n y certificaci&oacute;n con el\r\n        <b>Ministerio del Poder Popular para la Salud (MPPS)</b>, as&iacute; como la\r\n        debida articulaci&oacute;n con los comandos de\r\n        <b>Protecci&oacute;n Civil, Cuerpos de Bomberos</b> e instituciones\r\n        hospitalarias p&uacute;blicas y privadas aliadas.\r\n      </li>\r\n      <li>\r\n        <b>Autoridad de Control:</b> Cualquier requerimiento de informaci&oacute;n\r\n        por parte de los &oacute;rganos de seguridad del Estado o del Ministerio de\r\n        Salud se ejecutar&aacute; en estricto cumplimiento de los procedimientos\r\n        legales establecidos, garantizando la seguridad y soberan&iacute;a de los\r\n        datos de los agremiados.\r\n      </li>\r\n    </ul>\r\n\r\n    <h2 id=\"index-8\">\r\n      8. Limitaci&oacute;n de Responsabilidad M&eacute;dica y Operativa (Disclaimer)\r\n    </h2>\r\n\r\n    <p>\r\n      La plataforma web de la SVPMPH es un canal estrictamente acad&eacute;mico,\r\n      administrativo y de gesti&oacute;n gremial,\r\n      <b>\r\n        no constituyendo una central de emergencias ni una entidad prestataria\r\n        de servicios m&eacute;dicos directos\r\n      </b>\r\n      . La SVPMPH no asume responsabilidad civil, penal, administrativa ni\r\n      solidaria por la praxis m&eacute;dica, t&eacute;cnica, de rescate o de campo que los\r\n      agremiados realicen de manera individual o institucional en el ejercicio\r\n      de sus funciones profesionales. Cada usuario responde legalmente por sus\r\n      propias acciones, decisiones operativas y actos de servicio en terreno.\r\n    </p>\r\n    <h2 id=\"index-9\">9. Caducidad y Recertificaci&oacute;n de Credenciales</h2>\r\n    <p>\r\n      Debido a la constante evoluci&oacute;n de los protocolos de atenci&oacute;n en la\r\n      medicina prehospitalaria, los certificados, constancias y avales\r\n      emitidos a trav&eacute;s de la plataforma est&aacute;n sujetos a vigencias\r\n      determinadas. Al cumplirse el ciclo de expiraci&oacute;n de una credencial, el\r\n      estatus de egresado o profesional activo en el sistema requerir&aacute;\r\n      obligatoriamente la aprobaci&oacute;n de un proceso de recertificaci&oacute;n o\r\n      actualizaci&oacute;n normado por la instituci&oacute;n.\r\n    </p>\r\n    <h2 id=\"index-10\">\r\n      10. Advertencia sobre Delitos Inform&aacute;ticos y Falsificaci&oacute;n\r\n    </h2>\r\n    <p>\r\n      Cualquier intento de falsificaci&oacute;n de documentos, alteraci&oacute;n de notas,\r\n      adulteraci&oacute;n de credenciales, subida de archivos fraudulentos o\r\n      usurpaci&oacute;n de identidad profesional dentro de la plataforma constituye\r\n      una violaci&oacute;n grave tipificada y penada por la\r\n      <b>Ley Especial contra los Delitos Inform&aacute;ticos</b> de la Rep&uacute;blica\r\n      Bolivariana de Venezuela. Estas acciones dar&aacute;n lugar a la expulsi&oacute;n\r\n      inmediata del gremio, anulaci&oacute;n de registros y la puesta a disposici&oacute;n\r\n      de las autoridades competentes.\r\n    </p>\r\n    <h2 id=\"index-11\">\r\n      11. Confidencialidad, Privacidad y Propiedad Intelectual\r\n    </h2>\r\n    <ul>\r\n      <li>\r\n        <b>Privacidad:</b>\r\n        El acceso a los expedientes detallados est&aacute; estrictamente limitado al\r\n        administrador general, directivos autorizados, al Ministerio de Salud\r\n        y a los organismos de seguridad competentes bajo requerimiento legal\r\n        formal. Los datos jam&aacute;s ser&aacute;n comercializados a terceros.\r\n      </li>\r\n      <li>\r\n        <b>Propiedad Intelectual: </b>Todo el contenido did&aacute;ctico, gu&iacute;as,\r\n        dise&ntilde;os, bases de datos y evaluaciones estructuradas en los programas\r\n        de formaci&oacute;n son propiedad exclusiva de la SVPMPH. Su reproducci&oacute;n,\r\n        plagio o distribuci&oacute;n no autorizada est&aacute; terminantemente prohibida.\r\n      </li>\r\n    </ul>\r\n  </div>\r\n<script>\r\n  document.addEventListener(\'click\', function(e) {\r\n    // Buscar si el elemento presionado es un enlace que comienza con #\r\n    const anchor = e.target.closest(\'a[href^=\"#\"]\');\r\n\r\n    if (anchor) {\r\n      e.preventDefault(); // Evita que la etiqueta <base> altere la navegaci&oacute;n\r\n\r\n      const targetId = anchor.getAttribute(\'href\');\r\n      const targetElement = document.querySelector(targetId);\r\n\r\n      if (targetElement) {\r\n        // Realiza el desplazamiento suave de forma nativa\r\n        targetElement.scrollIntoView({\r\n          behavior: \'smooth\',\r\n          block: \'start\'\r\n        });\r\n      }\r\n    }\r\n  });\r\n</script>\r\n</body>', 1, '2026-09-01', 1),
(23, NULL, NULL, NULL, 0, NULL, 4),
(24, 'TÉRMINOS, CONDICIONES Y POLÍTICA DE PRIVACIDAD', '1.1', '<body>\r\n  <div class=\"container-terminos\">\r\n    <h1>T&Eacute;RMINOS, CONDICIONES Y POL&Iacute;TICA DE PRIVACIDAD</h1>\r\n    <span>\r\n      <b>\r\n        Sociedad Venezolana de Profesionales en Medicina\r\n        Prehospitalaria(SVPMPH)\r\n      </b>\r\n    </span>\r\n    <hr>\r\n\r\n    <div class=\"container-indice\">\r\n      <h3>Indice General</h3>\r\n      <ul class=\"menu-indice\">\r\n        <li>\r\n          <a href=\"#index-1\">1. Objeto y Naturaleza de la Plataforma</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-2\">2. Progresi&oacute;n de Roles y Acceso Inicial</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-3\">3. Finalidad del Almacenamiento y Uso de Datos</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-4\">4. C&oacute;digo de &Eacute;tica, Reputaci&oacute;n e Imagen Institucional</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-5\">5. R&eacute;gimen de Referencias Profesionales y Empleo</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-6\">6. R&eacute;gimen Disciplinario y Bloqueo de Cuentas</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-7\">7. Marco Legal, Respaldo Institucional y Tuici&oacute;n del Estado</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-8\">8. Limitaci&oacute;n de Responsabilidad M&eacute;dica y Operativa\r\n            (Disclaimer)</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-9\">9. Caducidad y Recertificaci&oacute;n de Credenciales</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-10\">10. Advertencia sobre Delitos Inform&aacute;ticos y Falsificaci&oacute;n</a>\r\n        </li>\r\n        <li>\r\n          <a href=\"#index-11\">11. Confidencialidad, Privacidad y Propiedad Intelectual</a>\r\n        </li>\r\n      </ul>\r\n    </div>\r\n    <h2 id=\"index-1\">1. Objeto y Naturaleza de la Plataforma</h2>\r\n    <p>\r\n      El presente documento regula el acceso, registro y uso de la plataforma\r\n      web oficial de la Sociedad Venezolana de Profesionales en Medicina\r\n      Prehospitalaria (<b>svpmph.org</b>). La plataforma opera como un sistema\r\n      integral de gesti&oacute;n gremial, acad&eacute;mica y operativa destinado a\r\n      registrar, validar, certificar y avalar a los profesionales, estudiantes\r\n      y egresados del &aacute;rea prehospitalaria conforme a las normativas de la\r\n      Rep&uacute;blica Bolivariana de Venezuela.\r\n    </p>\r\n    <h2 id=\"index-2\">2. Progresi&oacute;n de Roles y Acceso Inicial</h2>\r\n    <ul>\r\n      <li>\r\n        <b>Rol Inicial / Registro Base:</b> Todo usuario que ingresa a la\r\n        plataforma debe pasar obligatoriamente por este nivel inicial. Al\r\n        registrarse mediante nombre de usuario, contrase&ntilde;a y correo\r\n        electr&oacute;nico (destinado exclusivamente a la autenticaci&oacute;n y\r\n        recuperaci&oacute;n de credenciales), el usuario adquiere la capacidad de\r\n        visualizar informaci&oacute;n general del portal y emitir comentarios.\r\n      </li>\r\n      <li>\r\n        <b> Selecci&oacute;n de Trayectoria:</b> El Rol Inicial constituye una etapa\r\n        de tr&aacute;nsito obligatorio. Desde este punto, el usuario decidir&aacute; y\r\n        postular&aacute; su transici&oacute;n formal hacia el perfil de Estudiante (mediante\r\n        la inscripci&oacute;n en talleres o cursos) o su consolidaci&oacute;n como\r\n        Profesional / Egresado.\r\n      </li>\r\n      <li>\r\n        <b>Definici&oacute;n de Egresado:</b> Se consideran egresados aquellos\r\n        participantes que completaron satisfactoriamente sus ciclos formativos\r\n        y planes de estudio aprobados por la instituci&oacute;n.\r\n      </li>\r\n    </ul>\r\n    <h2 id=\"index-3\">3. Finalidad del Almacenamiento y Uso de Datos</h2>\r\n    <p>\r\n      Los datos personales, demogr&aacute;ficos y laborales recopilados (tales como\r\n      nombre, apellido, segundo nombre, segundo apellido, c&eacute;dula de identidad,\r\n      edad, direcci&oacute;n, procedencia y estatus laboral de adscripci&oacute;n a cuerpos\r\n      de seguridad, bomberos, protecci&oacute;n civil o personal hospitalario) tienen\r\n      como prop&oacute;sito estricto:\r\n    </p>\r\n\r\n    <ul>\r\n      <li>\r\n        Gestionar el control acad&eacute;mico, expedici&oacute;n de notas y emisi&oacute;n de\r\n        certificados oficiales.\r\n      </li>\r\n      <li>\r\n        Mantener el registro interno, control de solvencias y carnetizaci&oacute;n de\r\n        los miembros.\r\n      </li>\r\n      <li>\r\n        Servir como plataforma de verificaci&oacute;n oficial de credenciales ante\r\n        organismos de seguridad del Estado, empresas p&uacute;blicas o privadas (por\r\n        ejemplo, en despliegues operativos o &aacute;reas industriales de alto\r\n        riesgo) y ante el Ministerio del Poder Popular para la Salud.\r\n      </li>\r\n      <li>\r\n        <i>Nota de Beneficios:</i> La verificaci&oacute;n de adscripci&oacute;n a\r\n        instituciones hospitalarias p&uacute;blicas aliadas permite la aplicaci&oacute;n de\r\n        esquemas de exoneraci&oacute;n o gratuidad en los procesos acad&eacute;micos\r\n        correspondientes.\r\n      </li>\r\n    </ul>\r\n\r\n    <h2 id=\"index-4\">\r\n      4. C&oacute;digo de &Eacute;tica, Reputaci&oacute;n e Imagen Institucional\r\n    </h2>\r\n    <ul>\r\n      <li>\r\n        <b> Representaci&oacute;n Gremial:</b> Los usuarios, estudiantes y egresados\r\n        forman parte activa de la SVPMPH. Queda terminantemente prohibido usar\r\n        el nombre, insignias, logotipos o avales de la sociedad en vano, de\r\n        forma il&iacute;cita o en detrimento de la moral y las buenas costumbres.\r\n        Cualquier acto que manche el prestigio de la instituci&oacute;n acarrear&aacute; la\r\n        expulsi&oacute;n inmediata del gremio y las acciones legales pertinentes.\r\n      </li>\r\n      <li>\r\n        <b>Participaci&oacute;n en Eventos:</b> La asistencia a los eventos,\r\n        asambleas y convocatorias oficiales es de car&aacute;cter voluntario; sin\r\n        embargo,\r\n        <b>se recomienda enf&aacute;ticamente la participaci&oacute;n activa</b> como\r\n        muestra de compromiso gremial y superaci&oacute;n profesional continua.\r\n      </li>\r\n    </ul>\r\n\r\n    <h2 id=\"index-5\">5. R&eacute;gimen de Referencias Profesionales y Empleo</h2>\r\n    <ul>\r\n      <li>\r\n        <b> Exenci&oacute;n de Agencia de Empleo:</b> La SVPMPH\r\n        <b>no funciona como una agencia de colocaci&oacute;n laboral</b> ni garantiza\r\n        contrataciones directas en el mercado de trabajo.\r\n      </li>\r\n      <li>\r\n        <b>Sistema de Referencias Institucionales:</b> Si una compa&ntilde;&iacute;a p&uacute;blica\r\n        o privada solicita perfiles certificados, la instituci&oacute;n emitir&aacute;\r\n        evaluaciones basadas estrictamente en los registros internos del\r\n        usuario. La constancia de buen rendimiento, la participaci&oacute;n en\r\n        eventos y la asistencia acumulada registrada en la plataforma otorgan\r\n        puntuaci&oacute;n diferenciada y un trato preferencial en las referencias que\r\n        la sociedad emita ante terceros.\r\n      </li>\r\n    </ul>\r\n    <h2 id=\"index-6\">6. R&eacute;gimen Disciplinario y Bloqueo de Cuentas</h2>\r\n    <p>\r\n      La administraci&oacute;n se reserva el derecho de suspender, bloquear o revocar\r\n      de manera temporal o definitiva el acceso a cualquier cuenta bajo los\r\n      siguientes criterios:\r\n    </p>\r\n    <ul>\r\n      <li>\r\n        <b> Acumulaci&oacute;n de Deudas:</b> Incumplimiento reiterado en el pago de\r\n        cuotas acad&eacute;micas, aportes o aranceles administrativos generados por\r\n        los programas en los que participa.\r\n      </li>\r\n      <li>\r\n        <b>Infracci&oacute;n Normativa:</b> Violaci&oacute;n comprobada de los reglamentos\r\n        internos de la instituci&oacute;n, de los programas acad&eacute;micos o de las\r\n        directrices &eacute;ticas exigidas a los miembros.\r\n      </li>\r\n    </ul>\r\n    <h2 id=\"index-7\">\r\n      7. Marco Legal, Respaldo Institucional y Tuici&oacute;n del Estado\r\n    </h2>\r\n    <ul>\r\n      <li>\r\n        <b> Fundamento Legal:</b> La recolecci&oacute;n, tratamiento y resguardo de\r\n        los datos personales y acad&eacute;micos en esta plataforma se rigen bajo los\r\n        principios consagrados en la Constituci&oacute;n de la Rep&uacute;blica Bolivariana\r\n        de Venezuela, en concordancia con las normativas vigentes sobre\r\n        protecci&oacute;n de datos, derecho al honor, la intimidad y la\r\n        confidencialidad de la informaci&oacute;n.\r\n      </li>\r\n      <li>\r\n        <b> Tuici&oacute;n y Respaldo del Estado:</b> La SVPMPH desarrolla sus\r\n        actividades bajo los lineamientos de los entes rectores en materia de\r\n        salud y seguridad ciudadana, manteniendo canales de interoperabilidad,\r\n        validaci&oacute;n y certificaci&oacute;n con el\r\n        <b>Ministerio del Poder Popular para la Salud (MPPS)</b>, as&iacute; como la\r\n        debida articulaci&oacute;n con los comandos de\r\n        <b>Protecci&oacute;n Civil, Cuerpos de Bomberos</b> e instituciones\r\n        hospitalarias p&uacute;blicas y privadas aliadas.\r\n      </li>\r\n      <li>\r\n        <b>Autoridad de Control:</b> Cualquier requerimiento de informaci&oacute;n\r\n        por parte de los &oacute;rganos de seguridad del Estado o del Ministerio de\r\n        Salud se ejecutar&aacute; en estricto cumplimiento de los procedimientos\r\n        legales establecidos, garantizando la seguridad y soberan&iacute;a de los\r\n        datos de los agremiados.\r\n      </li>\r\n    </ul>\r\n\r\n    <h2 id=\"index-8\">\r\n      8. Limitaci&oacute;n de Responsabilidad M&eacute;dica y Operativa (Disclaimer)\r\n    </h2>\r\n\r\n    <p>\r\n      La plataforma web de la SVPMPH es un canal estrictamente acad&eacute;mico,\r\n      administrativo y de gesti&oacute;n gremial,\r\n      <b>\r\n        no constituyendo una central de emergencias ni una entidad prestataria\r\n        de servicios m&eacute;dicos directos\r\n      </b>\r\n      . La SVPMPH no asume responsabilidad civil, penal, administrativa ni\r\n      solidaria por la praxis m&eacute;dica, t&eacute;cnica, de rescate o de campo que los\r\n      agremiados realicen de manera individual o institucional en el ejercicio\r\n      de sus funciones profesionales. Cada usuario responde legalmente por sus\r\n      propias acciones, decisiones operativas y actos de servicio en terreno.\r\n    </p>\r\n    <h2 id=\"index-9\">9. Caducidad y Recertificaci&oacute;n de Credenciales</h2>\r\n    <p>\r\n      Debido a la constante evoluci&oacute;n de los protocolos de atenci&oacute;n en la\r\n      medicina prehospitalaria, los certificados, constancias y avales\r\n      emitidos a trav&eacute;s de la plataforma est&aacute;n sujetos a vigencias\r\n      determinadas. Al cumplirse el ciclo de expiraci&oacute;n de una credencial, el\r\n      estatus de egresado o profesional activo en el sistema requerir&aacute;\r\n      obligatoriamente la aprobaci&oacute;n de un proceso de recertificaci&oacute;n o\r\n      actualizaci&oacute;n normado por la instituci&oacute;n.\r\n    </p>\r\n    <h2 id=\"index-10\">\r\n      10. Advertencia sobre Delitos Inform&aacute;ticos y Falsificaci&oacute;n\r\n    </h2>\r\n    <p>\r\n      Cualquier intento de falsificaci&oacute;n de documentos, alteraci&oacute;n de notas,\r\n      adulteraci&oacute;n de credenciales, subida de archivos fraudulentos o\r\n      usurpaci&oacute;n de identidad profesional dentro de la plataforma constituye\r\n      una violaci&oacute;n grave tipificada y penada por la\r\n      <b>Ley Especial contra los Delitos Inform&aacute;ticos</b> de la Rep&uacute;blica\r\n      Bolivariana de Venezuela. Estas acciones dar&aacute;n lugar a la expulsi&oacute;n\r\n      inmediata del gremio, anulaci&oacute;n de registros y la puesta a disposici&oacute;n\r\n      de las autoridades competentes.\r\n    </p>\r\n    <h2 id=\"index-11\">\r\n      11. Confidencialidad, Privacidad y Propiedad Intelectual\r\n    </h2>\r\n    <ul>\r\n      <li>\r\n        <b>Privacidad:</b>\r\n        El acceso a los expedientes detallados est&aacute; estrictamente limitado al\r\n        administrador general, directivos autorizados, al Ministerio de Salud\r\n        y a los organismos de seguridad competentes bajo requerimiento legal\r\n        formal. Los datos jam&aacute;s ser&aacute;n comercializados a terceros.\r\n      </li>\r\n      <li>\r\n        <b>Propiedad Intelectual: </b>Todo el contenido did&aacute;ctico, gu&iacute;as,\r\n        dise&ntilde;os, bases de datos y evaluaciones estructuradas en los programas\r\n        de formaci&oacute;n son propiedad exclusiva de la SVPMPH. Su reproducci&oacute;n,\r\n        plagio o distribuci&oacute;n no autorizada est&aacute; terminantemente prohibida.\r\n      </li>\r\n    </ul>\r\n  </div>\r\n<script>\r\n  document.addEventListener(\'click\', function(e) {\r\n    // Buscar si el elemento presionado es un enlace que comienza con #\r\n    const anchor = e.target.closest(\'a[href^=\"#\"]\');\r\n\r\n    if (anchor) {\r\n      e.preventDefault(); // Evita que la etiqueta <base> altere la navegaci&oacute;n\r\n\r\n      const targetId = anchor.getAttribute(\'href\');\r\n      const targetElement = document.querySelector(targetId);\r\n\r\n      if (targetElement) {\r\n        // Realiza el desplazamiento suave de forma nativa\r\n        targetElement.scrollIntoView({\r\n          behavior: \'smooth\',\r\n          block: \'start\'\r\n        });\r\n      }\r\n    }\r\n  });\r\n</script>\r\n</body>', 1, '2026-09-01', 4),
(25, NULL, NULL, NULL, 1, NULL, 3),
(26, NULL, NULL, NULL, 1, NULL, 2),
(27, NULL, NULL, NULL, 1, NULL, 5);

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `tipo_programa`
--

CREATE TABLE `tipo_programa` (
  `id` int NOT NULL,
  `tipo` varchar(20) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `tipo_programa`
--

INSERT INTO `tipo_programa` (`id`, `tipo`) VALUES
(1, 'Curso'),
(2, 'Taller');

-- --------------------------------------------------------

--
-- Estructura de tabla para la tabla `usuario_terminos`
--

CREATE TABLE `usuario_terminos` (
  `id` int NOT NULL,
  `cuenta_id` int NOT NULL,
  `terminos_id` int NOT NULL,
  `fecha` datetime NOT NULL,
  `respuesta` varchar(50) COLLATE utf8mb4_unicode_ci NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Volcado de datos para la tabla `usuario_terminos`
--

INSERT INTO `usuario_terminos` (`id`, `cuenta_id`, `terminos_id`, `fecha`, `respuesta`) VALUES
(24, 29, 20, '2026-09-02 22:47:01', 'Acepto los terminos y condiciones'),
(25, 30, 20, '2026-09-05 20:12:55', 'Acepto los terminos y condiciones'),
(26, 31, 20, '2026-09-05 20:16:12', 'Acepto los terminos y condiciones'),
(27, 32, 20, '2026-09-05 22:07:01', 'Acepto los terminos y condiciones');

--
-- Índices para tablas volcadas
--

--
-- Indices de la tabla `actividad`
--
ALTER TABLE `actividad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indices de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `asignatura` (`asignatura`),
  ADD UNIQUE KEY `codigo` (`codigo`),
  ADD KEY `programa_id` (`programa_id`);

--
-- Indices de la tabla `bancos`
--
ALTER TABLE `bancos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `banco` (`banco`);

--
-- Indices de la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `institucion_id` (`institucion_id`);

--
-- Indices de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `usuario` (`usuario`),
  ADD UNIQUE KEY `correo` (`correo`),
  ADD KEY `rol_id` (`rol_id`);

--
-- Indices de la tabla `cuotas`
--
ALTER TABLE `cuotas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inscripcion_id` (`user_id`);

--
-- Indices de la tabla `datos`
--
ALTER TABLE `datos`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `id_cedula` (`id_cedula`),
  ADD UNIQUE KEY `tlf` (`tlf`),
  ADD KEY `nombre` (`nombre`),
  ADD KEY `apellido` (`apellido`),
  ADD KEY `cuenta_id` (`cuenta_id`);

--
-- Indices de la tabla `datos_laborales`
--
ALTER TABLE `datos_laborales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cedula_id` (`cuenta_id`),
  ADD KEY `institucion_id` (`institucion_id`),
  ADD KEY `cargo_id` (`cargo_id`);

--
-- Indices de la tabla `destinario`
--
ALTER TABLE `destinario`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oferta_id` (`oferta_id`),
  ADD KEY `cedula_id` (`cedula_id`);

--
-- Indices de la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cedula_id` (`cedula_id`);

--
-- Indices de la tabla `docentes_asignados`
--
ALTER TABLE `docentes_asignados`
  ADD PRIMARY KEY (`id`),
  ADD KEY `docente_id` (`docente_id`),
  ADD KEY `seccion_id` (`seccion_id`),
  ADD KEY `asignatura_id` (`asignatura_id`);

--
-- Indices de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cedula_id` (`cedula_id`),
  ADD KEY `nucleo_id` (`nucleo_id`);

--
-- Indices de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `docente_id` (`docente_id`),
  ADD KEY `actividad_id` (`actividad_id`);

--
-- Indices de la tabla `facilitador_oferta`
--
ALTER TABLE `facilitador_oferta`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_facil` (`cuenta_id`),
  ADD KEY `ofert_facil` (`oferta_id`);

--
-- Indices de la tabla `gremio`
--
ALTER TABLE `gremio`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cedula_id` (`cedula_id`),
  ADD KEY `oferta_id` (`oferta_id`),
  ADD KEY `seccion_id` (`seccion_id`);

--
-- Indices de la tabla `institucion`
--
ALTER TABLE `institucion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `mencion`
--
ALTER TABLE `mencion`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `codigo` (`codigo`);

--
-- Indices de la tabla `modalidad`
--
ALTER TABLE `modalidad`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `modalidad_programa`
--
ALTER TABLE `modalidad_programa`
  ADD PRIMARY KEY (`id`),
  ADD KEY `programa_id` (`programa_id`),
  ADD KEY `nucleo_id` (`nucleo_id`),
  ADD KEY `modalidad_id` (`modalidad_id`);

--
-- Indices de la tabla `notas`
--
ALTER TABLE `notas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `actividad_id` (`actividad_id`),
  ADD KEY `estudiante_id` (`estudiante_id`);

--
-- Indices de la tabla `nucleo`
--
ALTER TABLE `nucleo`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD PRIMARY KEY (`id`),
  ADD KEY `nucleo_id` (`nucleo_id`),
  ADD KEY `programa_id` (`programa_id`),
  ADD KEY `modo_cuotas` (`modo_cuotas`);

--
-- Indices de la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cuota_id` (`cuota_id`),
  ADD KEY `banco_origen_id` (`banco_origen_id`),
  ADD KEY `destinario_id` (`destinario_id`);

--
-- Indices de la tabla `pensum`
--
ALTER TABLE `pensum`
  ADD PRIMARY KEY (`id`),
  ADD KEY `p_modalidad_id` (`p_modalidad_id`),
  ADD KEY `asignatura_id` (`asignatura_id`);

--
-- Indices de la tabla `programas`
--
ALTER TABLE `programas`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `programa` (`programa`),
  ADD KEY `tipo_programa` (`tipo_programa`);

--
-- Indices de la tabla `promocion`
--
ALTER TABLE `promocion`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `rol`
--
ALTER TABLE `rol`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD PRIMARY KEY (`id`),
  ADD KEY `oferta_id` (`oferta_id`);

--
-- Indices de la tabla `terminos`
--
ALTER TABLE `terminos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `rol_id` (`rol_id`),
  ADD KEY `titulo` (`titulo`) USING BTREE;

--
-- Indices de la tabla `tipo_programa`
--
ALTER TABLE `tipo_programa`
  ADD PRIMARY KEY (`id`);

--
-- Indices de la tabla `usuario_terminos`
--
ALTER TABLE `usuario_terminos`
  ADD PRIMARY KEY (`id`),
  ADD KEY `cedula_id` (`cuenta_id`),
  ADD KEY `terminos_id` (`terminos_id`);

--
-- AUTO_INCREMENT de las tablas volcadas
--

--
-- AUTO_INCREMENT de la tabla `actividad`
--
ALTER TABLE `actividad`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `archivos`
--
ALTER TABLE `archivos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=57;

--
-- AUTO_INCREMENT de la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT de la tabla `bancos`
--
ALTER TABLE `bancos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `cargos`
--
ALTER TABLE `cargos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT de la tabla `cuentas`
--
ALTER TABLE `cuentas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT de la tabla `cuotas`
--
ALTER TABLE `cuotas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `datos`
--
ALTER TABLE `datos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT de la tabla `datos_laborales`
--
ALTER TABLE `datos_laborales`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `destinario`
--
ALTER TABLE `destinario`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `docentes`
--
ALTER TABLE `docentes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `docentes_asignados`
--
ALTER TABLE `docentes_asignados`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `facilitador_oferta`
--
ALTER TABLE `facilitador_oferta`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `gremio`
--
ALTER TABLE `gremio`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT de la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `institucion`
--
ALTER TABLE `institucion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `mencion`
--
ALTER TABLE `mencion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `modalidad`
--
ALTER TABLE `modalidad`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT de la tabla `modalidad_programa`
--
ALTER TABLE `modalidad_programa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `notas`
--
ALTER TABLE `notas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `nucleo`
--
ALTER TABLE `nucleo`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT de la tabla `ofertas`
--
ALTER TABLE `ofertas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT de la tabla `pagos`
--
ALTER TABLE `pagos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `pensum`
--
ALTER TABLE `pensum`
  MODIFY `id` int NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT de la tabla `programas`
--
ALTER TABLE `programas`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT de la tabla `promocion`
--
ALTER TABLE `promocion`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT de la tabla `rol`
--
ALTER TABLE `rol`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT de la tabla `secciones`
--
ALTER TABLE `secciones`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT de la tabla `terminos`
--
ALTER TABLE `terminos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- AUTO_INCREMENT de la tabla `tipo_programa`
--
ALTER TABLE `tipo_programa`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT de la tabla `usuario_terminos`
--
ALTER TABLE `usuario_terminos`
  MODIFY `id` int NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

--
-- Restricciones para tablas volcadas
--

--
-- Filtros para la tabla `archivos`
--
ALTER TABLE `archivos`
  ADD CONSTRAINT `user_archivo` FOREIGN KEY (`user_id`) REFERENCES `cuentas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `asignaturas`
--
ALTER TABLE `asignaturas`
  ADD CONSTRAINT `fk_asignaturas_programa` FOREIGN KEY (`programa_id`) REFERENCES `programas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cargos`
--
ALTER TABLE `cargos`
  ADD CONSTRAINT `fk_cargos_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `institucion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuentas`
--
ALTER TABLE `cuentas`
  ADD CONSTRAINT `fk_cuentas_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `cuotas`
--
ALTER TABLE `cuotas`
  ADD CONSTRAINT `fk_cuotas_user` FOREIGN KEY (`user_id`) REFERENCES `datos` (`id_cedula`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `datos`
--
ALTER TABLE `datos`
  ADD CONSTRAINT `user_cuenta2` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `datos_laborales`
--
ALTER TABLE `datos_laborales`
  ADD CONSTRAINT `fk_laborales_cargo` FOREIGN KEY (`cargo_id`) REFERENCES `cargos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_laborales_datos` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_laborales_institucion` FOREIGN KEY (`institucion_id`) REFERENCES `institucion` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `destinario`
--
ALTER TABLE `destinario`
  ADD CONSTRAINT `fk_destinario_datos` FOREIGN KEY (`cedula_id`) REFERENCES `datos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_destinario_oferta` FOREIGN KEY (`oferta_id`) REFERENCES `ofertas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `docentes`
--
ALTER TABLE `docentes`
  ADD CONSTRAINT `fk_docentes_datos` FOREIGN KEY (`cedula_id`) REFERENCES `datos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `docentes_asignados`
--
ALTER TABLE `docentes_asignados`
  ADD CONSTRAINT `decente_asig1` FOREIGN KEY (`docente_id`) REFERENCES `docentes` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `docente_asig2` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `docente_asig3` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `estudiantes`
--
ALTER TABLE `estudiantes`
  ADD CONSTRAINT `fk_estudiantes_datos` FOREIGN KEY (`cedula_id`) REFERENCES `datos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_estudiantes_nucleo` FOREIGN KEY (`nucleo_id`) REFERENCES `nucleo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `evaluaciones`
--
ALTER TABLE `evaluaciones`
  ADD CONSTRAINT `fk_evaluaciones_actividad` FOREIGN KEY (`actividad_id`) REFERENCES `actividad` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_evaluaciones_docente` FOREIGN KEY (`docente_id`) REFERENCES `docentes_asignados` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `facilitador_oferta`
--
ALTER TABLE `facilitador_oferta`
  ADD CONSTRAINT `ofert_facil` FOREIGN KEY (`oferta_id`) REFERENCES `ofertas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `user_facil` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `inscripcion`
--
ALTER TABLE `inscripcion`
  ADD CONSTRAINT `fk_inscripcion_datos` FOREIGN KEY (`cedula_id`) REFERENCES `datos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscripcion_oferta` FOREIGN KEY (`oferta_id`) REFERENCES `ofertas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_inscripcion_seccion` FOREIGN KEY (`seccion_id`) REFERENCES `secciones` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `modalidad_programa`
--
ALTER TABLE `modalidad_programa`
  ADD CONSTRAINT `fk_modprog_modalidad` FOREIGN KEY (`modalidad_id`) REFERENCES `modalidad` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_modprog_nucleo` FOREIGN KEY (`nucleo_id`) REFERENCES `nucleo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_modprog_programa` FOREIGN KEY (`programa_id`) REFERENCES `programas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `notas`
--
ALTER TABLE `notas`
  ADD CONSTRAINT `estuante` FOREIGN KEY (`estudiante_id`) REFERENCES `estudiantes` (`cedula_id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_notas_actividad` FOREIGN KEY (`actividad_id`) REFERENCES `actividad` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `ofertas`
--
ALTER TABLE `ofertas`
  ADD CONSTRAINT `fk_ofertas_modalidad` FOREIGN KEY (`modo_cuotas`) REFERENCES `modalidad` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ofertas_nucleo` FOREIGN KEY (`nucleo_id`) REFERENCES `nucleo` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_ofertas_programa` FOREIGN KEY (`programa_id`) REFERENCES `programas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pagos`
--
ALTER TABLE `pagos`
  ADD CONSTRAINT `fk_pagos_banco` FOREIGN KEY (`banco_origen_id`) REFERENCES `bancos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pagos_cuota` FOREIGN KEY (`cuota_id`) REFERENCES `cuotas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pagos_destinario` FOREIGN KEY (`destinario_id`) REFERENCES `destinario` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `pensum`
--
ALTER TABLE `pensum`
  ADD CONSTRAINT `fk_pensum_asignatura` FOREIGN KEY (`asignatura_id`) REFERENCES `asignaturas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_pensum_modalidad` FOREIGN KEY (`p_modalidad_id`) REFERENCES `modalidad_programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `programas`
--
ALTER TABLE `programas`
  ADD CONSTRAINT `fk_programas_tipo` FOREIGN KEY (`tipo_programa`) REFERENCES `tipo_programa` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `secciones`
--
ALTER TABLE `secciones`
  ADD CONSTRAINT `fk_secciones_oferta` FOREIGN KEY (`oferta_id`) REFERENCES `ofertas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `terminos`
--
ALTER TABLE `terminos`
  ADD CONSTRAINT `fk_terminos_rol` FOREIGN KEY (`rol_id`) REFERENCES `rol` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Filtros para la tabla `usuario_terminos`
--
ALTER TABLE `usuario_terminos`
  ADD CONSTRAINT `fk_usterm_cuenta` FOREIGN KEY (`cuenta_id`) REFERENCES `cuentas` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_usterm_terminos` FOREIGN KEY (`terminos_id`) REFERENCES `terminos` (`id`) ON DELETE CASCADE ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
