-- ============================================================================
-- MIGRACIÓN: TRANSICIÓN A TUTORÍAS GRUPALES (1 A N)
-- ============================================================================

-- 1. Crear la tabla puente tutoria_estudiantes
CREATE TABLE IF NOT EXISTS `tutoria_estudiantes` (
    `id_tutoria` INTEGER NOT NULL,
    `id_estudiante` INTEGER NOT NULL,
    `estado_asistencia` ENUM('pendiente', 'asistio', 'falto') NOT NULL DEFAULT 'pendiente',
    `observaciones_estudiante` TEXT,
    `fecha_inscripcion` DATETIME DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_tutoria`, `id_estudiante`),
    FOREIGN KEY (`id_tutoria`) REFERENCES `tutorias`(`id_tutoria`) ON DELETE CASCADE,
    FOREIGN KEY (`id_estudiante`) REFERENCES `estudiantes`(`id_estudiante`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Migrar los datos existentes de la tabla tutorias a la nueva tabla puente
-- Solo inserta si la tabla puente está vacía para evitar duplicados si se corre 2 veces
INSERT IGNORE INTO `tutoria_estudiantes` (`id_tutoria`, `id_estudiante`, `observaciones_estudiante`)
SELECT `id_tutoria`, `id_estudiante`, `observaciones` 
FROM `tutorias`
WHERE `id_estudiante` IS NOT NULL;

-- 3. Eliminar la relación antigua de la tabla tutorias
-- IMPORTANTE: Si este paso falla, verifica en phpMyAdmin el nombre exacto de la llave foránea. 
-- Por defecto en tu script INIT, MySQL la nombró 'tutorias_ibfk_1'.
ALTER TABLE `tutorias` DROP FOREIGN KEY `tutorias_ibfk_1`;

-- 4. Eliminar la columna id_estudiante de la tabla principal
ALTER TABLE `tutorias` DROP COLUMN `id_estudiante`;
ALTER TABLE `tutorias` DROP COLUMN `observaciones`;