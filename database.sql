CREATE DATABASE ecogestion_db;
USE ecogestion_db;

-- (Pega aquí la creación de PERFIL y USUARIO)

CREATE TABLE SUCURSAL (
    id_sucursal INT AUTO_INCREMENT PRIMARY KEY,
    nombre_sucursal VARCHAR(100) NOT NULL,
    direccion VARCHAR(200) NOT NULL
);

-- (Pega CATEGORIA_RESIDUO)

CREATE TABLE RESIDUO (
    id_residuo INT AUTO_INCREMENT PRIMARY KEY,
    peso_kg DECIMAL(10,2) NOT NULL,
    fecha DATE NOT NULL,
    id_categoria INT,
    id_sucursal INT,
    id_usuario_registra INT,
    FOREIGN KEY (id_categoria) REFERENCES CATEGORIA_RESIDUO(id_categoria),
    FOREIGN KEY (id_sucursal) REFERENCES SUCURSAL(id_sucursal),
    FOREIGN KEY (id_usuario_registra) REFERENCES USUARIO(id_usuario)
);

-- (Pega SOLICITUD_RETIRO y COMPROBANTE)

CREATE TABLE HISTORIAL_ESTADO (
    id_historial INT AUTO_INCREMENT PRIMARY KEY,
    estado_nuevo VARCHAR(50) NOT NULL,
    fecha_cambio DATETIME NOT NULL,
    id_solicitud INT,
    id_usuario_cambio INT,
    FOREIGN KEY (id_solicitud) REFERENCES SOLICITUD_RETIRO(id_solicitud),
    FOREIGN KEY (id_usuario_cambio) REFERENCES USUARIO(id_usuario)
);