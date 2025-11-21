 DDL para el Sistema de Farmacia Cognitiva (I²G y M-RECS)
-- Utiliza sintaxis estándar compatible con MySQL.

-- Crear la base de datos si no existe y seleccionarla para su uso
CREATE DATABASE IF NOT EXISTS system_farmacy;
USE system_farmacy;

-- --------------------------------------------------------
-- 1. TBL_USUARIOS: Roles y Credenciales (Incluye rol para UC10 - Anulación de Bloqueo)
-- --------------------------------------------------------
CREATE TABLE TBL_USUARIOS (
    id_usuario INT PRIMARY KEY AUTO_INCREMENT,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    rol ENUM('Vendedor', 'Farmacéutico', 'Gerente', 'Administrador') NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    activo BOOLEAN DEFAULT TRUE
);

-- --------------------------------------------------------
-- 2. TBL_CLIENTES: Almacena información y historial clínico relevante para M-RECS
-- --------------------------------------------------------
CREATE TABLE TBL_CLIENTES (
    id_cliente INT PRIMARY KEY AUTO_INCREMENT,
    cedula VARCHAR(20) UNIQUE NOT NULL,
    nombre VARCHAR(100) NOT NULL,
    apellido VARCHAR(100) NOT NULL,
    fecha_nacimiento DATE,
    -- Historial Clínico: Texto libre o JSON para condiciones y medicamentos de uso crónico
    historial_clinico_json JSON,
    telefono VARCHAR(20)
);

-- --------------------------------------------------------
-- 3. TBL_MEDICAMENTOS: Catálogo de productos y datos de riesgo para M-RECS
-- --------------------------------------------------------
CREATE TABLE TBL_MEDICAMENTOS (
    id_sku INT PRIMARY KEY AUTO_INCREMENT,
    nombre_comercial VARCHAR(150) NOT NULL,
    principio_activo VARCHAR(150) NOT NULL,
    concentracion VARCHAR(50),
    categoria VARCHAR(50), -- Ej: Analgésico, Antibiótico, Cardiovascular
    -- Clasificación de riesgo: usada por M-RECS para pre-filtrar interacciones
    clasificacion_riesgo_mrecs VARCHAR(50) 
);

-- --------------------------------------------------------
-- 4. TBL_INVENTARIO: Almacena el stock físico y las predicciones de I²G
-- --------------------------------------------------------
CREATE TABLE TBL_INVENTARIO (
    id_inventario INT PRIMARY KEY AUTO_INCREMENT,
    sku_id INT NOT NULL,
    stock_actual INT NOT NULL DEFAULT 0,
    costo_unitario DECIMAL(10, 2) NOT NULL,
    precio_venta DECIMAL(10, 2) NOT NULL,
    fecha_caducidad DATE NOT NULL,
    lote VARCHAR(50) NOT NULL,
    -- Columna clave I²G: Stock Mínimo sugerido por el modelo ML
    stock_min_ml INT DEFAULT 0, 
    FOREIGN KEY (sku_id) REFERENCES TBL_MEDICAMENTOS(id_sku)
);
-- Nota: La estrategia FEFO (First Expired, First Out) será manejada por la aplicación
-- buscando el id_inventario con la fecha_caducidad más cercana al momento de la venta.

-- --------------------------------------------------------
-- 5. TBL_VENTAS: Encabezado de la transacción
-- --------------------------------------------------------
CREATE TABLE TBL_VENTAS (
    id_venta INT PRIMARY KEY AUTO_INCREMENT,
    id_cliente INT,
    id_vendedor INT NOT NULL,
    fecha_hora_venta DATETIME NOT NULL,
    total DECIMAL(10, 2) NOT NULL,
    estado ENUM('Completada', 'Bloqueada', 'Anulada') NOT NULL,
    FOREIGN KEY (id_cliente) REFERENCES TBL_CLIENTES(id_cliente),
    FOREIGN KEY (id_vendedor) REFERENCES TBL_USUARIOS(id_usuario)
);

-- --------------------------------------------------------
-- 6. TBL_DETALLE_VENTA: Detalle de la transacción con flags de M-RECS
-- --------------------------------------------------------
CREATE TABLE TBL_DETALLE_VENTA (
    id_detalle INT PRIMARY KEY AUTO_INCREMENT,
    id_venta INT NOT NULL,
    id_inventario INT NOT NULL, -- Apunta al inventario específico (incluye lote y caducidad)
    cantidad INT NOT NULL,
    precio_unitario DECIMAL(10, 2) NOT NULL,
    subtotal DECIMAL(10, 2) NOT NULL,
    
    -- Columna clave M-RECS: Flag de detección de riesgo
    riesgo_detectado BOOLEAN DEFAULT FALSE, 
    -- Columna clave M-RECS: Descripción de la interacción/problema detectado
    motivo_bloqueo VARCHAR(255) DEFAULT NULL, 
    -- Columna clave M-RECS/UC10: ID del Gerente que anula el bloqueo
    anulacion_gerente INT DEFAULT NULL, 
    
    FOREIGN KEY (id_venta) REFERENCES TBL_VENTAS(id_venta),
    FOREIGN KEY (id_inventario) REFERENCES TBL_INVENTARIO(id_inventario),
    FOREIGN KEY (anulacion_gerente) REFERENCES TBL_USUARIOS(id_usuario)
);

-- --------------------------------------------------------
-- 7. TBL_PREDICCIONES_ML: Histórico y Métricas del Modelo I²G
-- --------------------------------------------------------
CREATE TABLE TBL_PREDICCIONES_ML (
    id_prediccion INT PRIMARY KEY AUTO_INCREMENT,
    sku_id INT NOT NULL,
    fecha_entrenamiento DATE NOT NULL, -- Fecha en que se generó la predicción
    horizonte_inicio DATE NOT NULL, -- Inicio del período de predicción
    horizonte_fin DATE NOT NULL, -- Fin del período de predicción
    -- Output directo de la IA para un SKU en un horizonte
    cantidad_sugerida INT NOT NULL, 
    
    -- Métricas de rendimiento I²G (para el Dashboard Gerencial)
    mape DECIMAL(5, 2), -- Mean Absolute Percentage Error (%)
    mae DECIMAL(10, 2), -- Mean Absolute Error (en unidades)

    FOREIGN KEY (sku_id) REFERENCES TBL_MEDICAMENTOS(id_sku)
);

-- --------------------------------------------------------
-- 8. TBL_AUDITORIA_MRECS: Registro detallado de intervenciones clínicas
-- --------------------------------------------------------
CREATE TABLE TBL_AUDITORIA_MRECS (
    id_auditoria INT PRIMARY KEY AUTO_INCREMENT,
    id_detalle INT NOT NULL,
    fecha_hora DATETIME NOT NULL,
    tipo_alerta ENUM('Interacción', 'Dosis Inapropiada', 'Alergia', 'Historial Crónico') NOT NULL,
    descripcion_completa TEXT,
    accion_tomada ENUM('Bloqueo Automático', 'Advertencia Farmacéutico', 'Anulación Gerencial') NOT NULL,
    FOREIGN KEY (id_detalle) REFERENCES TBL_DETALLE_VENTA(id_detalle)
);