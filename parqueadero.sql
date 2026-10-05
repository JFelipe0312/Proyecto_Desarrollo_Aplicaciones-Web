-- =============================================
-- Sistema de Gestión de Parqueadero (SGP)
-- Base de datos MySQL para XAMPP
-- =============================================

CREATE DATABASE IF NOT EXISTS parqueadero_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parqueadero_db;

-- Tabla de usuarios del sistema
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    usuario VARCHAR(50) NOT NULL UNIQUE,
    contrasena VARCHAR(255) NOT NULL,
    rol ENUM('administrador', 'operario') NOT NULL DEFAULT 'operario',
    activo TINYINT(1) DEFAULT 1,
    creado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de tarifas por tipo de vehículo
CREATE TABLE tarifas (
    id_tarifa INT AUTO_INCREMENT PRIMARY KEY,
    tipo_vehiculo ENUM('carro', 'moto', 'bicicleta') NOT NULL UNIQUE,
    tarifa_hora DECIMAL(10,2) NOT NULL,
    tarifa_dia DECIMAL(10,2) NOT NULL,
    actualizado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Tabla de vehículos registrados
CREATE TABLE vehiculos (
    placa VARCHAR(10) PRIMARY KEY,
    tipo ENUM('carro', 'moto', 'bicicleta') NOT NULL,
    marca VARCHAR(50),
    color VARCHAR(30),
    registrado_en TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de tickets / registros de entrada y salida
CREATE TABLE tickets (
    id_ticket INT AUTO_INCREMENT PRIMARY KEY,
    placa VARCHAR(10) NOT NULL,
    tipo_vehiculo ENUM('carro', 'moto', 'bicicleta') NOT NULL,
    hora_entrada DATETIME NOT NULL,
    hora_salida DATETIME NULL,
    minutos_permanencia INT NULL,
    valor_pagado DECIMAL(10,2) NULL,
    estado ENUM('activo', 'cerrado') DEFAULT 'activo',
    id_usuario_entrada INT NOT NULL,
    id_usuario_salida INT NULL,
    FOREIGN KEY (placa) REFERENCES vehiculos(placa),
    FOREIGN KEY (id_usuario_entrada) REFERENCES usuarios(id_usuario),
    FOREIGN KEY (id_usuario_salida) REFERENCES usuarios(id_usuario)
);

-- =============================================
-- Datos iniciales
-- =============================================

-- Usuario administrador (contraseña: admin123)
INSERT INTO usuarios (nombre, usuario, contrasena, rol) VALUES
('Administrador', 'admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'administrador'),
('Operario Uno', 'operario', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'operario');

-- Tarifas iniciales (en pesos colombianos)
INSERT INTO tarifas (tipo_vehiculo, tarifa_hora, tarifa_dia) VALUES
('carro', 3000.00, 25000.00),
('moto', 1500.00, 12000.00),
('bicicleta', 500.00, 4000.00);
