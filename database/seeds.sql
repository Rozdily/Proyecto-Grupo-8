-- SEEDS (después de migrations.sql)

SET NAMES utf8mb4;

INSERT INTO `roles` (`id_rol`, `nombre_rol`) VALUES
(1, 'administrador'),
(2, 'tutor'),
(3, 'estudiante')
ON DUPLICATE KEY UPDATE `nombre_rol` = VALUES(`nombre_rol`);

INSERT INTO `carreras` (`id_carrera`, `nombre_carrera`) VALUES
(1, 'Ingeniería de Sistemas'),
(4, 'Ingeniería Civil'),
(5, 'Administración de Empresas'),
(6, 'Medicina'),
(7, 'Contabilidad'),
(8, 'Psicología')
ON DUPLICATE KEY UPDATE `nombre_carrera` = VALUES(`nombre_carrera`);

INSERT INTO `bloques_horarios` (`id_bloque`, `nombre_bloque`, `hora_inicio`, `hora_fin`, `descripcion`) VALUES
(1, 'Mañana', '08:00:00', '10:00:00', '8:00 AM - 10:00 AM'),
(2, 'Mañana Tardío', '10:00:00', '12:00:00', '10:00 AM - 12:00 PM'),
(3, 'Tarde', '14:00:00', '16:00:00', '2:00 PM - 4:00 PM'),
(4, 'Noche', '18:00:00', '20:00:00', '6:00 PM - 8:00 PM')
ON DUPLICATE KEY UPDATE `nombre_bloque` = VALUES(`nombre_bloque`), `descripcion` = VALUES(`descripcion`);

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_carrera`) VALUES
(1, 'Base de Datos I', 1),
(2, 'Programación I', 1),
(3, 'Tecnología Web I', 1),
(4, 'Programación II', 1),
(8, 'Estadística I', 1)
ON DUPLICATE KEY UPDATE `nombre_materia` = VALUES(`nombre_materia`), `id_carrera` = VALUES(`id_carrera`);

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_carrera`) VALUES
(10, 'Física Aplicada', 4),
(11, 'Mecánica de Suelos', 4),
(12, 'Hidráulica', 4),
(13, 'Estructuras', 4)
ON DUPLICATE KEY UPDATE `nombre_materia` = VALUES(`nombre_materia`), `id_carrera` = VALUES(`id_carrera`);

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_carrera`) VALUES
(14, 'Contabilidad General', 5),
(15, 'Economía', 5),
(16, 'Marketing', 5),
(17, 'Gestión de Recursos Humanos', 5)
ON DUPLICATE KEY UPDATE `nombre_materia` = VALUES(`nombre_materia`), `id_carrera` = VALUES(`id_carrera`);

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_carrera`) VALUES
(18, 'Anatomía', 6),
(19, 'Fisiología', 6),
(20, 'Bioquímica', 6),
(21, 'Farmacología', 6)
ON DUPLICATE KEY UPDATE `nombre_materia` = VALUES(`nombre_materia`), `id_carrera` = VALUES(`id_carrera`);

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_carrera`) VALUES
(22, 'Contabilidad I', 7),
(23, 'Contabilidad II', 7),
(24, 'Auditoría', 7),
(25, 'Costos', 7)
ON DUPLICATE KEY UPDATE `nombre_materia` = VALUES(`nombre_materia`), `id_carrera` = VALUES(`id_carrera`);

INSERT INTO `materias` (`id_materia`, `nombre_materia`, `id_carrera`) VALUES
(26, 'Psicología General', 8),
(27, 'Psicología Social', 8),
(28, 'Psicología Clínica', 8),
(29, 'Psicología Organizacional', 8)
ON DUPLICATE KEY UPDATE `nombre_materia` = VALUES(`nombre_materia`), `id_carrera` = VALUES(`id_carrera`);

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre`, `apellido`, `correo`, `usuario`, `contrasena_hash`, `telefono`, `estado`) VALUES
(1, 1, 'Admin', 'Sistema', 'admin@tutorias.local', 'admin', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000001', 'activo')
ON DUPLICATE KEY UPDATE `usuario` = VALUES(`usuario`), `nombre` = VALUES(`nombre`), `apellido` = VALUES(`apellido`);

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre`, `apellido`, `correo`, `usuario`, `contrasena_hash`, `telefono`, `estado`) VALUES
(2, 2, 'Carlos', 'Docente', 'tutor@tutorias.local', 'tutor1', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000002', 'activo'),
(9, 2, 'Carlos', 'García', 'carlos.garcia@upds.edu.bo', 'tutor2', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000009', 'activo'),
(11, 2, 'Juan', 'Pérez', 'juan.perez@upds.edu.bo', 'tutor4', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000011', 'activo'),
(12, 2, 'Ana', 'Martínez', 'ana.martinez@upds.edu.bo', 'tutor5', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000012', 'activo'),
(13, 2, 'Pedro', 'Rodríguez', 'pedro.rodriguez@upds.edu.bo', 'tutor6', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000013', 'activo'),
(19, 2, 'Roberto', 'Mendoza', 'roberto.mendoza@upds.edu.bo', 'tutor11', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000019', 'activo'),
(20, 2, 'Laura', 'Sánchez', 'laura.sanchez@upds.edu.bo', 'tutor12', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000020', 'activo'),
(21, 2, 'Miguel', 'Torres', 'miguel.torres@upds.edu.bo', 'tutor13', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000021', 'activo'),
(22, 2, 'Carmen', 'Vargas', 'carmen.vargas@upds.edu.bo', 'tutor14', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000022', 'activo'),
(23, 2, 'Jorge', 'Luna', 'jorge.luna@upds.edu.bo', 'tutor15', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000023', 'activo')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `apellido` = VALUES(`apellido`), `correo` = VALUES(`correo`), `usuario` = VALUES(`usuario`);

INSERT INTO `usuarios` (`id_usuario`, `id_rol`, `nombre`, `apellido`, `correo`, `usuario`, `contrasena_hash`, `telefono`, `estado`) VALUES
(3, 3, 'María', 'Estudiante', 'estudiante@tutorias.local', 'estudiante1', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000003', 'activo'),
(10, 3, 'María', 'López', 'maria.lopez@upds.edu.bo', 'estudiante_l', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000010', 'activo'),
(14, 3, 'Luis', 'Fernández', 'luis.fernandez@upds.edu.bo', 'estudiante2', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000014', 'activo'),
(15, 3, 'Sofía', 'González', 'sofia.gonzalez@upds.edu.bo', 'estudiante3', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000015', 'activo'),
(16, 3, 'Miguel', 'Ruiz', 'miguel.ruiz@upds.edu.bo', 'estudiante4', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000016', 'activo'),
(17, 3, 'Valentina', 'Díaz', 'valentina.diaz@upds.edu.bo', 'estudiante5', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000017', 'activo'),
(18, 3, 'Diego', 'Moreno', 'diego.moreno@upds.edu.bo', 'estudiante6', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000018', 'activo'),
(24, 3, 'Gabriel', 'Ramírez', 'gabriel.ramirez@upds.edu.bo', 'estudiante7', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000024', 'activo'),
(25, 3, 'Elena', 'Castro', 'elena.castro@upds.edu.bo', 'estudiante8', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000025', 'activo'),
(26, 3, 'Fernando', 'Ortiz', 'fernando.ortiz@upds.edu.bo', 'estudiante9', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000026', 'activo'),
(27, 3, 'Andrea', 'Morales', 'andrea.morales@upds.edu.bo', 'estudiante10', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000027', 'activo'),
(28, 3, 'Ricardo', 'Flores', 'ricardo.flores@upds.edu.bo', 'estudiante11', '\$2y\$10\$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '70000028', 'activo')
ON DUPLICATE KEY UPDATE `nombre` = VALUES(`nombre`), `apellido` = VALUES(`apellido`), `correo` = VALUES(`correo`), `usuario` = VALUES(`usuario`);

INSERT INTO `tutores` (`id_tutor`, `id_usuario`, `especialidad`, `biografia`, `foto_perfil`, `perfil_linkedin`, `certificaciones`, `areas_expertise`) VALUES
(1, 2, 'Desarrollo Web y Bases de Datos', 'Docente tutor especializado en desarrollo backend y arquitecturas web.', 'default_tutor.png', 'https://linkedin.com', 'Certificación AWS Cloud Practitioner', 'Bases de Datos, Backend, Arquitectura de Software'),
(3, 9, 'Bases de Datos y Estadística', 'Profesor con 15 años de experiencia en bases de datos y estadística. Especialista en MySQL, PostgreSQL y análisis de datos.', 'default_tutor.png', 'https://linkedin.com', 'Certificación MySQL Database Administrator', 'Base de Datos, SQL, Estadística, Análisis de Datos'),
(4, 11, 'Desarrollo Web Full Stack', 'Desarrollador full stack con React, Node.js y PHP. 8 años de experiencia en proyectos web empresariales.', 'default_tutor.png', 'https://linkedin.com', 'Certificación Full Stack Developer', 'Desarrollo Web, JavaScript, React, Node.js, PHP, HTML/CSS'),
(5, 12, 'Tecnología Web', 'Ingeniero especializado en tecnologías web modernas. Experiencia en desarrollo frontend y backend.', 'default_tutor.png', 'https://linkedin.com', 'Certificación Frontend Specialist', 'Tecnología Web, Frontend, Backend, APIs'),
(6, 13, 'Bases de Datos Avanzadas', 'Ingeniero de bases de datos. Experiencia en MySQL, PostgreSQL, modelado entidad-relación y optimización de consultas.', 'default_tutor.png', 'https://linkedin.com', 'Certificación Oracle Database Professional', 'Base de Datos, SQL, MySQL, PostgreSQL, Modelado de Datos'),
(8, 19, 'Ingeniería Civil y Estructuras', 'Ingeniero civil con 12 años de experiencia en construcción y diseño estructural.', 'default_tutor.png', 'https://linkedin.com1', 'Certificación BIM Management', 'Ingeniería Civil, Estructuras, Mecánica de Suelos, Hidráulica'),
(9, 20, 'Administración de Empresas', 'Licenciada en administración con experiencia en gestión empresarial y marketing.', 'default_tutor.png', 'https://linkedin.com2', 'Diplomado en Marketing Digital', 'Administración, Economía, Marketing, Recursos Humanos'),
(10, 21, 'Medicina Interna', 'Médico general con especialización en medicina interna y urgencias.', 'default_tutor.png', 'https://linkedin.com3', 'Especialidad en Medicina Interna', 'Medicina, Anatomía, Fisiología, Farmacología'),
(11, 22, 'Contabilidad y Auditoría', 'Contadora pública con experiencia en auditoría fiscal y contabilidad financiera.', 'default_tutor.png', 'https://linkedin.com4', 'Certificación Auditoría Forense', 'Contabilidad, Auditoría, Costos, Finanzas'),
(12, 23, 'Psicología Clínica', 'Psicólogo clínico con experiencia en psicología organizacional y clínica.', 'default_tutor.png', 'https://linkedin.com5', 'Maestría en Psicología Clínica', 'Psicología, Psicología Clínica, Psicología Social, Psicología Organizacional')
ON DUPLICATE KEY UPDATE `especialidad` = VALUES(`especialidad`), `biografia` = VALUES(`biografia`);

INSERT INTO `tutor_materia` (`id_tutor`, `id_materia`) VALUES
(1, 1), (1, 3),
(3, 1), (3, 8),
(4, 2), (4, 3),
(5, 3),
(6, 1), (6, 2), (6, 8),
(8, 10), (8, 11), (8, 12), (8, 13),
(9, 14), (9, 15), (9, 16), (9, 17),
(10, 18), (10, 19), (10, 20), (10, 21),
(11, 22), (11, 23), (11, 24), (11, 25),
(12, 26), (12, 27), (12, 28), (12, 29)
ON DUPLICATE KEY UPDATE `id_tutor` = VALUES(`id_tutor`);

INSERT INTO `disponibilidad_tutor` (`id_disponibilidad`, `id_tutor`, `dia_semana`, `hora_inicio`, `hora_fin`) VALUES
(1, 1, 'Lunes', '14:00:00', '18:00:00'),
(2, 1, 'Miercoles', '14:00:00', '18:00:00'),
(3, 1, 'Viernes', '09:00:00', '12:00:00')
ON DUPLICATE KEY UPDATE `dia_semana` = VALUES(`dia_semana`), `hora_inicio` = VALUES(`hora_inicio`), `hora_fin` = VALUES(`hora_fin`);

INSERT INTO `estudiantes` (`id_estudiante`, `id_usuario`, `id_carrera`, `semestre`, `registro_universitario`) VALUES
(1, 3, 1, 4, 'RU-2026-98765'),
(2, 10, 1, 3, 'RU-2026-0002'),
(6, 14, 1, 4, 'RU-2026-0006'),
(7, 15, 1, 3, 'RU-2026-0007'),
(8, 16, 1, 5, 'RU-2026-0008'),
(9, 17, 1, 2, 'RU-2026-0009'),
(10, 18, 1, 6, 'RU-2026-0010'),
(12, 24, 4, 4, 'RU-2026-0012'),
(13, 25, 5, 3, 'RU-2026-0013'),
(14, 26, 6, 2, 'RU-2026-0014'),
(15, 27, 7, 5, 'RU-2026-0015'),
(16, 28, 8, 4, 'RU-2026-0016')
ON DUPLICATE KEY UPDATE `id_usuario` = VALUES(`id_usuario`), `id_carrera` = VALUES(`id_carrera`), `semestre` = VALUES(`semestre`);

INSERT INTO `periodos_tutoria` (`id_periodo`, `codigo`, `nombre`, `fecha_inicio`, `fecha_fin`, `activo`, `creado_por`) VALUES
(1, 'I-2026', '2026-1 Primer Semestre', '2026-03-01', '2026-07-31', 1, 1),
(2, 'II-2026', '2026-2 Segundo Semestre', '2026-08-01', '2026-12-31', 1, 1)
ON DUPLICATE KEY UPDATE `fecha_inicio` = VALUES(`fecha_inicio`), `fecha_fin` = VALUES(`fecha_fin`), `activo` = VALUES(`activo`);

INSERT INTO `tutorias` (
	`id_estudiante`, `id_tutor`, `id_materia`, `id_bloque`,
	`fecha`, `periodo`, `hora_inicio`, `hora_fin`,
	`lugar_o_enlace`, `modalidad`, `estado`, `observaciones`,
	`motivo_cancelacion`, `fecha_solicitud`
) VALUES

(2, 1, 1, 1, '2026-09-02', 'I-2026', '08:00:00', '10:00:00', 'Por asignar (Aula Física)', 'presencial', 'pendiente', 'Solicitud de apoyo en Base de Datos I, pendiente de confirmación.', '', '2026-09-01 08:30:00'),
(2, 1, 2, 2, '2026-09-04', 'I-2026', '10:00:00', '12:00:00', 'https://google.com', 'virtual', 'pendiente', 'Necesita ayuda con Programación I. Por confirmar.', '', '2026-09-03 10:15:00'),

(2, 1, 1, 1, '2026-09-01', 'I-2026', '08:00:00', '10:00:00', 'Aula 101', 'presencial', 'confirmada', 'Tutoría confirmada por el tutor para Base de Datos I.', '', '2026-08-31 09:00:00'),
(6, 3, 3, 2, '2026-09-02', 'I-2026', '10:00:00', '12:00:00', 'Aula 102', 'presencial', 'confirmada', 'Sesión confirmada para Desarrollo Web.', '', '2026-09-01 10:00:00'),

(2, 1, 1, 1, '2026-09-20', 'I-2026', '08:00:00', '10:00:00', 'Aula 101', 'presencial', 'en_proceso', 'Sesión iniciada. Trabajando en consultas SQL.', '', '2026-09-19 09:00:00'),
(6, 3, 3, 2, '2026-09-20', 'I-2026', '10:00:00', '12:00:00', 'Aula 102', 'presencial', 'en_proceso', 'Sesión en curso. Revisando desarrollo web.', '', '2026-09-19 10:00:00'),

(2, 1, 1, 1, '2026-08-25', 'I-2026', '08:00:00', '10:00:00', 'Aula 101', 'presencial', 'realizada', 'Sesión completada. Estudiante entendió consultas básicas.', '', '2026-08-24 09:00:00'),
(6, 3, 3, 2, '2026-08-26', 'I-2026', '10:00:00', '12:00:00', 'Aula 102', 'presencial', 'realizada', 'Desarrollo web completado con éxito.', '', '2026-08-25 10:00:00'),

(2, 1, 1, 1, '2026-09-14', 'I-2026', '08:00:00', '10:00:00', 'Aula 101', 'presencial', 'detenido', 'Sesión interrumpida por falla de conexión. Reagendada para el 15/09.', '', '2026-09-13 09:00:00'),

(2, 1, 1, 1, '2026-09-03', 'I-2026', '08:00:00', '10:00:00', 'No asignado', 'presencial', 'cancelada', 'Cancelada por falta de disponibilidad del tutor. Estudiante notificado.', 'El tutor no está disponible en este horario', '2026-08-30 08:00:00'),
(6, 3, 3, 2, '2026-09-05', 'I-2026', '10:00:00', '12:00:00', 'No asignado', 'presencial', 'cancelada', 'Cancelada por solicitud del estudiante. Se reprogramará.', 'El estudiante solicitó la cancelación por motivos personales', '2026-08-31 11:00:00')
ON DUPLICATE KEY UPDATE `estado` = VALUES(`estado`), `observaciones` = VALUES(`observaciones`);
