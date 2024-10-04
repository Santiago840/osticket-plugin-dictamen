use osticket;

CREATE TABLE
    IF NOT EXISTS ostck_dictaminacion (
        id_dictaminacion INT AUTO_INCREMENT PRIMARY KEY,
        id_staff INT NOT NULL,
        id_ticket INT NOT NULL,
        id_estado INT NOT NULL,
        id_valoracion INT NOT NULL
    ) ENGINE = InnoDB;

CREATE TABLE
    IF NOT EXISTS ostck_dictaminacion_asignaciones (
        id_Asignacion INT AUTO_INCREMENT PRIMARY KEY,
        id_ticket INT NOT NULL,
        id_staff INT NOT NULL
    ) ENGINE = InnoDB;

CREATE TABLE
    IF NOT EXISTS ostck_dictaminacion_respuestas (
        id_respuesta INT AUTO_INCREMENT PRIMARY KEY,
        id_staff INT NOT NULL,
        id_ticket INT NOT NULL,
        pregunta TEXT,
        pregunta_label text,
        respuesta TEXT
    ) ENGINE = InnoDB;