-- 1. Agregar las columnas de control a la tabla de tutorías
ALTER TABLE `tutorias`
ADD COLUMN `tope_clases` INT NOT NULL DEFAULT 1 AFTER `estado`,
ADD COLUMN `clases_impartidas` INT NOT NULL DEFAULT 0 AFTER `tope_clases`;

-- 2. Permitir que una misma tutoría tenga múltiples seguimientos (clases)
-- (Primero creamos un índice normal para no romper la llave foránea, y luego borramos el índice ÚNICO)
ALTER TABLE `seguimiento_sesion` ADD INDEX `idx_tutoria_fk` (`id_tutoria`);
ALTER TABLE `seguimiento_sesion` DROP INDEX `id_tutoria`;

-- Limite de estudiantes en cada tutoria
ALTER TABLE `tutorias`
ADD COLUMN `limite_estudiantes` INT NOT NULL DEFAULT 8 AFTER `estado`;