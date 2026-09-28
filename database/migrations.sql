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
