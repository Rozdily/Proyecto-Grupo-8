-- INIT

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS `roles` (
	`id_rol` INTEGER AUTO_INCREMENT,
	`nombre_rol` VARCHAR(30) NOT NULL UNIQUE,
	PRIMARY KEY(`id_rol`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `usuarios` (
	`id_usuario` INTEGER AUTO_INCREMENT,
	`id_rol` INTEGER NOT NULL,
	`nombre` VARCHAR(100) NOT NULL,
	`apellido` VARCHAR(100) NOT NULL,
	`correo` VARCHAR(150) NOT NULL UNIQUE,
	`usuario` VARCHAR(50) NOT NULL UNIQUE,
	`contrasena_hash` VARCHAR(255) NOT NULL,
	`telefono` VARCHAR(20) NOT NULL,
	`estado` ENUM('activo', 'inactivo', 'pendiente') NOT NULL DEFAULT 'activo',
	`fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `registro_intentos` (
	`id_registro` INTEGER AUTO_INCREMENT,
	`ip_origen` VARCHAR(45) NOT NULL,
	`fecha_hora` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_registro`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `registro_accesos` (
	`id_acceso` INTEGER AUTO_INCREMENT,
	`id_usuario` INTEGER DEFAULT NULL,
	`fecha_hora` DATETIME DEFAULT CURRENT_TIMESTAMP,
	`ip_origen` VARCHAR(45) NOT NULL,
	`resultado` ENUM('exitoso', 'fallido') NOT NULL,
	PRIMARY KEY(`id_acceso`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `carreras` (
	`id_carrera` INTEGER AUTO_INCREMENT,
	`nombre_carrera` VARCHAR(150) NOT NULL,
	PRIMARY KEY(`id_carrera`),
	CONSTRAINT `uq_carrera_nombre` UNIQUE (`nombre_carrera`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bloques_horarios` (
	`id_bloque` INTEGER AUTO_INCREMENT,
	`nombre_bloque` VARCHAR(30) NOT NULL UNIQUE,
	`hora_inicio` TIME NOT NULL,
	`hora_fin` TIME NOT NULL,
	`descripcion` VARCHAR(200) NOT NULL,
	PRIMARY KEY(`id_bloque`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `materias` (
	`id_materia` INTEGER AUTO_INCREMENT,
	`nombre_materia` VARCHAR(150) NOT NULL,
	`id_carrera` INTEGER NOT NULL,
	PRIMARY KEY(`id_materia`),
	CONSTRAINT `uq_materia_carrera` UNIQUE (`id_carrera`, `nombre_materia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `notificaciones` (
	`id_notificacion` INTEGER AUTO_INCREMENT,
	`id_usuario` INTEGER NOT NULL,
	`tipo` VARCHAR(30) NOT NULL,
	`text_mensaje` VARCHAR(255) NOT NULL,
	`url_enlace` VARCHAR(200) NOT NULL,
	`leida` TINYINT NOT NULL DEFAULT 0,
	`fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_notificacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `bitacora_mg` (
	`id` INTEGER AUTO_INCREMENT,
	`usuario` VARCHAR(50) NOT NULL,
	`accion` VARCHAR(100) NOT NULL,
	`tabla` VARCHAR(60) NOT NULL,
	`id_registro` INTEGER NOT NULL,
	`datos_antes` JSON NOT NULL,
	`datos_despues` JSON NOT NULL,
	`ip` VARCHAR(45) NOT NULL,
	`fecha` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `estudiantes` (
	`id_estudiante` INTEGER AUTO_INCREMENT,
	`id_usuario` INTEGER NOT NULL UNIQUE,
	`id_carrera` INTEGER NOT NULL,
	`semestre` TINYINT NOT NULL,
	`registro_universitario` VARCHAR(30) NOT NULL,
	PRIMARY KEY(`id_estudiante`),
	CONSTRAINT `uq_estudiante_ru` UNIQUE (`registro_universitario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tutores` (
	`id_tutor` INTEGER AUTO_INCREMENT,
	`id_usuario` INTEGER NOT NULL UNIQUE,
	`especialidad` VARCHAR(150) NOT NULL,
	`biografia` TEXT NOT NULL,
	`foto_perfil` VARCHAR(500) NOT NULL,
	`perfil_linkedin` VARCHAR(255) NOT NULL,
	`certificaciones` TEXT NOT NULL,
	`areas_expertise` VARCHAR(500) NOT NULL,
	PRIMARY KEY(`id_tutor`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tutor_materia` (
	`id_tutor` INTEGER NOT NULL,
	`id_materia` INTEGER NOT NULL,
	PRIMARY KEY(`id_tutor`, `id_materia`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `disponibilidad_tutor` (
	`id_disponibilidad` INTEGER AUTO_INCREMENT,
	`id_tutor` INTEGER NOT NULL,
	`dia_semana` ENUM('Lunes', 'Martes', 'Miercoles', 'Jueves', 'Viernes', 'Sabado') NOT NULL,
	`hora_inicio` TIME NOT NULL,
	`hora_fin` TIME NOT NULL,
	PRIMARY KEY(`id_disponibilidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tutor_bloque_seleccionado` (
	`id_tutor` INTEGER NOT NULL,
	`id_bloque` INTEGER NOT NULL,
	`fecha_seleccion` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_tutor`, `id_bloque`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `periodos_tutoria` (
	`id_periodo` INTEGER AUTO_INCREMENT,
	`codigo` VARCHAR(20) NOT NULL UNIQUE,
	`nombre` VARCHAR(100) NOT NULL,
	`fecha_inicio` DATE NOT NULL,
	`fecha_fin` DATE NOT NULL,
	`activo` TINYINT NOT NULL DEFAULT 1,
	`creado_por` INTEGER NOT NULL,
	`fecha_creacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_periodo`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tutorias` (
	`id_tutoria` INTEGER AUTO_INCREMENT,
	`id_estudiante` INTEGER NOT NULL,
	`id_tutor` INTEGER NOT NULL,
	`id_materia` INTEGER NOT NULL,
	`id_bloque` INTEGER NOT NULL,
	`fecha` DATE NOT NULL,
	`periodo` VARCHAR(30) NOT NULL DEFAULT 'I-2026',
	`hora_inicio` TIME NOT NULL,
	`hora_fin` TIME NOT NULL,
	`modalidad` ENUM('presencial', 'virtual') NOT NULL DEFAULT 'presencial',
	`lugar_o_enlace` VARCHAR(200) NOT NULL,
	`estado` ENUM('pendiente', 'confirmada', 'realizada', 'cancelada', 'en_proceso', 'detenido') NOT NULL DEFAULT 'pendiente',
	`observaciones` TEXT NOT NULL,
	`motivo_cancelacion` VARCHAR(255) NOT NULL,
	`fecha_solicitud` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_tutoria`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `evaluaciones_tutoria` (
	`id_evaluacion` INTEGER AUTO_INCREMENT,
	`id_tutoria` INTEGER NOT NULL UNIQUE,
	`calificacion` TINYINT NOT NULL CHECK(`calificacion` BETWEEN 1 AND 5),
	`comentario` TEXT NOT NULL,
	`fecha_evaluacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_evaluacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `seguimiento_sesion` (
	`id_seguimiento` INTEGER AUTO_INCREMENT,
	`id_tutoria` INTEGER NOT NULL UNIQUE,
	`asistio` ENUM('si', 'no') NOT NULL,
	`temas_tratados` TEXT NOT NULL,
	`avance` ENUM('sin_avance', 'parcial', 'logrado') NOT NULL,
	`recommendations` TEXT NOT NULL,
	`fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_seguimiento`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `parametros_mg` (
	`clave` VARCHAR(60),
	`valor` VARCHAR(100) NOT NULL,
	`descripcion` TEXT NOT NULL,
	`fuente` VARCHAR(50) NOT NULL,
	`estado_evidencia` ENUM('confirmado', 'pendiente', 'propuesta') NOT NULL,
	`actualizado_por` INTEGER DEFAULT NULL,
	`fecha_actualizacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`clave`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `modalidades_grado` (
	`id_modalidad` INTEGER AUTO_INCREMENT,
	`codigo` VARCHAR(20) NOT NULL UNIQUE,
	`nombre` VARCHAR(100) NOT NULL,
	`requiere_tutor` TINYINT NOT NULL DEFAULT 1,
	`flujo` ENUM('perfil_mg', 'examen_areas', 'excelencia') NOT NULL DEFAULT 'perfil_mg',
	`activa` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY(`id_modalidad`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `cohortes_mg` (
	`id_cohorte` INTEGER AUTO_INCREMENT,
	`codigo` VARCHAR(30) NOT NULL UNIQUE,
	`nombre` VARCHAR(100) NOT NULL,
	`fecha_inicio` DATE NOT NULL,
	`fecha_fin` DATE NOT NULL,
	`activa` TINYINT NOT NULL DEFAULT 1,
	PRIMARY KEY(`id_cohorte`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calendario_mg` (
	`id_hito` INTEGER AUTO_INCREMENT,
	`id_cohorte` INTEGER NOT NULL,
	`etapa` ENUM('previa', 'mg1', 'mg2') NOT NULL,
	`tipo` ENUM('taller', 'asignacion_tutor', 'asignacion_tribunal', 'informe', 'defensa', 'ingreso_mg2', 'otro') NOT NULL,
	`nombre` VARCHAR(150) NOT NULL,
	`orden` TINYINT NOT NULL DEFAULT 1,
	`fecha_limite` DATE NOT NULL,
	`avance_esperado_pct` TINYINT NOT NULL CHECK(`avance_esperado_pct` BETWEEN 0 AND 100),
	PRIMARY KEY(`id_hito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expedientes_mg` (
	`id_expediente` INTEGER AUTO_INCREMENT,
	`id_estudiante` INTEGER NOT NULL,
	`id_modalidad` INTEGER NOT NULL,
	`id_cohorte` INTEGER NOT NULL,
	`etapa_actual` ENUM('previa', 'mg1', 'mg2', 'finalizado') NOT NULL DEFAULT 'previa',
	`estado` ENUM('activo', 'aprobado', 'reprobado', 'abandono', 'retirado') NOT NULL DEFAULT 'activo',
	`titulo_trabajo` VARCHAR(255) NOT NULL,
	`fecha_inicio` DATE NOT NULL,
	`fecha_cierre` DATE NOT NULL,
	`observaciones` TEXT NOT NULL,
	PRIMARY KEY(`id_expediente`),
	CONSTRAINT `uq_estudiante_modalidad_cohorte` UNIQUE (`id_estudiante`, `id_modalidad`, `id_cohorte`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `expediente_etapas` (
	`id` INTEGER AUTO_INCREMENT,
	`id_expediente` INTEGER NOT NULL,
	`etapa` ENUM('previa', 'mg1', 'mg2', 'finalizado') NOT NULL,
	`fecha_inicio` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
	`fecha_fin` DATETIME NOT NULL,
	`resultado` VARCHAR(255) NOT NULL,
	`registrado_por` INTEGER DEFAULT NULL,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `asignaciones_tutor` (
	`id_asignacion` INTEGER AUTO_INCREMENT,
	`id_expediente` INTEGER NOT NULL,
	`id_tutor` INTEGER NOT NULL,
	`fecha_asignacion` DATE NOT NULL,
	`fecha_fin` DATE NOT NULL,
	`estado` ENUM('vigente', 'finalizada', 'reemplazada') NOT NULL DEFAULT 'vigente',
	`motivo_fin` TEXT NOT NULL,
	`referencia_decanatura` VARCHAR(100) NOT NULL,
	`disponibilidad_consultada` TINYINT NOT NULL DEFAULT 0,
	`numero_carta` INTEGER NOT NULL,
	`observaciones` TEXT NOT NULL,
	`registrado_por` INTEGER DEFAULT NULL,
	PRIMARY KEY(`id_asignacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `tribunales_defensa` (
	`id` INTEGER AUTO_INCREMENT,
	`id_expediente` INTEGER NOT NULL,
	`etapa` ENUM('mg1', 'mg2') NOT NULL,
	`id_tutor` INTEGER NOT NULL,
	`orden` TINYINT NOT NULL DEFAULT 1,
	`fecha_asignacion` DATE NOT NULL,
	`estado` ENUM('vigente', 'reemplazado') NOT NULL DEFAULT 'vigente',
	`registrado_por` INTEGER DEFAULT NULL,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `defensas_mg` (
	`id_defensa` INTEGER AUTO_INCREMENT,
	`id_expediente` INTEGER NOT NULL,
	`etapa` ENUM('mg1', 'mg2') NOT NULL,
	`fecha` DATE NOT NULL,
	`hora_inicio` TIME NOT NULL,
	`hora_fin` TIME NOT NULL,
	`ambiente` VARCHAR(100) NOT NULL,
	`estado` ENUM('programada', 'realizada', 'reprogramada', 'cancelada') NOT NULL DEFAULT 'programada',
	`obs_fondo` TEXT NOT NULL,
	`obs_forma` TEXT NOT NULL,
	PRIMARY KEY(`id_defensa`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `calificaciones_mg` (
	`id` INTEGER AUTO_INCREMENT,
	`id_defensa` INTEGER NOT NULL UNIQUE,
	`nota` DECIMAL(5,2) NOT NULL,
	`observaciones` TEXT NOT NULL,
	`publicada` TINYINT NOT NULL DEFAULT 0,
	`registrada_por` INTEGER DEFAULT NULL,
	`fecha_registro` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `reuniones_mg` (
	`id_reunion` INTEGER AUTO_INCREMENT,
	`id_asignacion` INTEGER NOT NULL,
	`fecha` DATE NOT NULL,
	`hora_inicio` TIME NOT NULL,
	`hora_fin` TIME NOT NULL,
	`modalidad` ENUM('presencial', 'virtual') NOT NULL DEFAULT 'presencial',
	`lugar_o_enlace` VARCHAR(255) NOT NULL,
	`temas` TEXT NOT NULL,
	`avance_sesion` TEXT NOT NULL,
	`observaciones` TEXT NOT NULL,
	`asistio_estudiante` ENUM('si', 'no') NOT NULL DEFAULT 'si',
	`asistio_tutor` ENUM('si', 'no') NOT NULL DEFAULT 'si',
	`estado_validacion` ENUM('registrada', 'validada', 'observada') NOT NULL DEFAULT 'registrada',
	`registrada_por` INTEGER DEFAULT NULL,
	`validada_por` INTEGER DEFAULT NULL,
	`fecha_validacion` DATETIME NOT NULL,
	PRIMARY KEY(`id_reunion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `informes_avance` (
	`id_informe` INTEGER AUTO_INCREMENT,
	`id_expediente` INTEGER NOT NULL,
	`id_hito` INTEGER NOT NULL,
	`porcentaje_avance` TINYINT NOT NULL CHECK(`porcentaje_avance` BETWEEN 0 AND 100),
	`fecha_presentacion` DATE NOT NULL,
	`formato` ENUM('digital', 'fisico') NOT NULL DEFAULT 'digital',
	`respaldo_fisico` TINYINT NOT NULL DEFAULT 0,
	`observaciones` TEXT NOT NULL,
	`registrado_por` INTEGER DEFAULT NULL,
	PRIMARY KEY(`id_informe`),
	CONSTRAINT `uq_expediente_hito` UNIQUE (`id_expediente`, `id_hito`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `alertas_atendidas` (
	`id` INTEGER AUTO_INCREMENT,
	`tipo_alerta` VARCHAR(60) NOT NULL,
	`id_referencia` INTEGER NOT NULL,
	`atendida_por` INTEGER DEFAULT NULL,
	`nota` TEXT NOT NULL,
	`fecha` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `plantillas_documento` (
	`id_plantilla` INTEGER AUTO_INCREMENT,
	`codigo` VARCHAR(60) NOT NULL UNIQUE,
	`nombre` VARCHAR(150) NOT NULL,
	`cuerpo_html` LONGTEXT NOT NULL,
	`version` INTEGER NOT NULL DEFAULT 1,
	`activa` TINYINT NOT NULL DEFAULT 1,
	`actualizado_por` INTEGER DEFAULT NULL,
	PRIMARY KEY(`id_plantilla`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `documentos_generados` (
	`id` INTEGER AUTO_INCREMENT,
	`id_plantilla` INTEGER NOT NULL,
	`tipo` VARCHAR(50) NOT NULL,
	`id_expediente` INTEGER NOT NULL,
	`destinatario` VARCHAR(150) NOT NULL,
	`numero_correlativo` INTEGER NOT NULL,
	`contenido_snapshot` LONGTEXT NOT NULL,
	`generado_por` INTEGER DEFAULT NULL,
	`fecha_generacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `contadores_documento` (
	`tipo` VARCHAR(50) NOT NULL,
	`anio` INTEGER NOT NULL,
	`ultimo_numero` INTEGER NOT NULL DEFAULT 0,
	PRIMARY KEY(`tipo`, `anio`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `importaciones_mg` (
	`id_importacion` INTEGER AUTO_INCREMENT,
	`archivo_nombre` VARCHAR(150) NOT NULL,
	`total_filas` INTEGER NOT NULL DEFAULT 0,
	`filas_exito` INTEGER NOT NULL DEFAULT 0,
	`filas_error` INTEGER NOT NULL DEFAULT 0,
	`ejecutado_por` INTEGER DEFAULT NULL,
	`fecha_importacion` DATETIME DEFAULT CURRENT_TIMESTAMP,
	PRIMARY KEY(`id_importacion`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS `importaciones_mg_detalle` (
	`id` INTEGER AUTO_INCREMENT,
	`id_importacion` INTEGER NOT NULL,
	`fila_numero` INTEGER NOT NULL,
	`resultado` ENUM('exito', 'advertencia', 'error') NOT NULL,
	`mensaje_error` TEXT NOT NULL,
	`datos_fila` TEXT NOT NULL,
	PRIMARY KEY(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- MIGRACIONES (después de init.sql)

SET NAMES utf8mb4;

ALTER TABLE `usuarios`
  ADD FOREIGN KEY(`id_rol`) REFERENCES `roles`(`id_rol`)
  ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `registro_accesos`
  ADD FOREIGN KEY(`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `estudiantes`
  ADD FOREIGN KEY(`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`id_carrera`) REFERENCES `carreras`(`id_carrera`)
  ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `tutores`
  ADD FOREIGN KEY(`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `materias`
  ADD FOREIGN KEY(`id_carrera`) REFERENCES `carreras`(`id_carrera`)
  ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `tutor_materia`
  ADD FOREIGN KEY(`id_tutor`) REFERENCES `tutores`(`id_tutor`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`id_materia`) REFERENCES `materias`(`id_materia`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `disponibilidad_tutor`
  ADD FOREIGN KEY(`id_tutor`) REFERENCES `tutores`(`id_tutor`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `tutor_bloque_seleccionado`
  ADD FOREIGN KEY(`id_tutor`) REFERENCES `tutores`(`id_tutor`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`id_bloque`) REFERENCES `bloques_horarios`(`id_bloque`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `tutorias`
  ADD FOREIGN KEY(`id_estudiante`) REFERENCES `estudiantes`(`id_estudiante`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`id_tutor`) REFERENCES `tutores`(`id_tutor`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`id_materia`) REFERENCES `materias`(`id_materia`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`id_bloque`) REFERENCES `bloques_horarios`(`id_bloque`)
  ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `evaluaciones_tutoria`
  ADD FOREIGN KEY(`id_tutoria`) REFERENCES `tutorias`(`id_tutoria`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `seguimiento_sesion`
  ADD FOREIGN KEY(`id_tutoria`) REFERENCES `tutorias`(`id_tutoria`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `notificaciones`
  ADD FOREIGN KEY(`id_usuario`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `parametros_mg`
  ADD FOREIGN KEY(`actualizado_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `calendario_mg`
  ADD FOREIGN KEY(`id_cohorte`) REFERENCES `cohortes_mg`(`id_cohorte`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `expedientes_mg`
  ADD FOREIGN KEY(`id_estudiante`) REFERENCES `estudiantes`(`id_estudiante`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`id_modalidad`) REFERENCES `modalidades_grado`(`id_modalidad`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`id_cohorte`) REFERENCES `cohortes_mg`(`id_cohorte`)
  ON UPDATE CASCADE ON DELETE NO ACTION;

ALTER TABLE `expediente_etapas`
  ADD FOREIGN KEY(`id_expediente`) REFERENCES `expedientes_mg`(`id_expediente`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`registrado_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `asignaciones_tutor`
  ADD FOREIGN KEY(`id_expediente`) REFERENCES `expedientes_mg`(`id_expediente`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`id_tutor`) REFERENCES `tutores`(`id_tutor`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`registrado_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `tribunales_defensa`
  ADD FOREIGN KEY(`id_expediente`) REFERENCES `expedientes_mg`(`id_expediente`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`id_tutor`) REFERENCES `tutores`(`id_tutor`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`registrado_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `defensas_mg`
  ADD FOREIGN KEY(`id_expediente`) REFERENCES `expedientes_mg`(`id_expediente`)
  ON UPDATE NO ACTION ON DELETE CASCADE;

ALTER TABLE `calificaciones_mg`
  ADD FOREIGN KEY(`id_defensa`) REFERENCES `defensas_mg`(`id_defensa`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`registrada_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `reuniones_mg`
  ADD FOREIGN KEY(`id_asignacion`) REFERENCES `asignaciones_tutor`(`id_asignacion`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`registrada_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL,
  ADD FOREIGN KEY(`validada_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `informes_avance`
  ADD FOREIGN KEY(`id_expediente`) REFERENCES `expedientes_mg`(`id_expediente`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`id_hito`) REFERENCES `calendario_mg`(`id_hito`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`registrado_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `alertas_atendidas`
  ADD FOREIGN KEY(`atendida_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `plantillas_documento`
  ADD FOREIGN KEY(`actualizado_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `documentos_generados`
  ADD FOREIGN KEY(`id_plantilla`) REFERENCES `plantillas_documento`(`id_plantilla`)
  ON UPDATE CASCADE ON DELETE NO ACTION,
  ADD FOREIGN KEY(`id_expediente`) REFERENCES `expedientes_mg`(`id_expediente`)
  ON UPDATE NO ACTION ON DELETE CASCADE,
  ADD FOREIGN KEY(`generado_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `importaciones_mg`
  ADD FOREIGN KEY(`ejecutado_por`) REFERENCES `usuarios`(`id_usuario`)
  ON UPDATE NO ACTION ON DELETE SET NULL;

ALTER TABLE `importaciones_mg_detalle`
  ADD FOREIGN KEY(`id_importacion`) REFERENCES `importaciones_mg`(`id_importacion`)
  ON UPDATE NO ACTION ON DELETE CASCADE;
