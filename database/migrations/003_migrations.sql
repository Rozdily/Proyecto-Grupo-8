-- 1. Agregar la nueva columna (temporalmente NULL para no dar error)
ALTER TABLE `tutorias` ADD COLUMN `id_periodo` INTEGER NULL AFTER `fecha`;

-- 2. Migrar los datos existentes emparejando el texto con la tabla periodos_tutoria
UPDATE `tutorias` t
JOIN `periodos_tutoria` p ON t.periodo = p.codigo
SET t.id_periodo = p.id_periodo;

-- 3. Asignar un periodo por defecto (ID 1) a tutorías huérfanas o si la tabla periodos_tutoria estaba vacía
UPDATE `tutorias` SET `id_periodo` = 1 WHERE `id_periodo` IS NULL;

-- 4. Hacer la columna obligatoria y crear la llave foránea
ALTER TABLE `tutorias` MODIFY `id_periodo` INTEGER NOT NULL;
ALTER TABLE `tutorias` ADD CONSTRAINT `fk_tutoria_periodo` 
FOREIGN KEY (`id_periodo`) REFERENCES `periodos_tutoria`(`id_periodo`) ON DELETE RESTRICT;

-- 5. Eliminar la columna de texto vieja
ALTER TABLE `tutorias` DROP COLUMN `periodo`;

