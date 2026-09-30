<?php

/**
 * ARCHIVO: models/usuarios/UsuarioModel/index.php
 * Administra el CRUD de la tabla 'usuarios', relaciones con roles, estudiantes y tutores,
 * paginación, filtros, validaciones de duplicados y actualización completa.
 */
class UsuarioModel
{

    private $pdo;

    public function __construct($conexionBaseDatos)
    {
        $this->pdo = $conexionBaseDatos;
    }

    // 1. LEER TODOS LOS USUARIOS (Con INNER JOIN para ver el nombre del rol)
    public function obtenerTodos()
    {
        $sql = "SELECT u.id_usuario, u.id_rol, r.nombre_rol, u.nombre, u.apellido, 
                       u.correo, u.usuario, u.telefono, u.estado, u.fecha_registro 
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                ORDER BY u.fecha_registro DESC";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 2. LEER UN USUARIO POR ID
    public function obtenerPorId($id_usuario)
    {
        $sql = "SELECT id_usuario, id_rol, nombre, apellido, correo, usuario, telefono, estado 
                FROM usuarios 
                WHERE id_usuario = ?";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_usuario]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. OBTENER POR USUARIO (Para Login)
    public function obtenerPorUsuario($usuario)
    {
        $usuario = trim((string) $usuario);

        if ($usuario === '') {
            return null;
        }

        $sql = "SELECT u.id_usuario, u.id_rol, r.nombre_rol, u.nombre, u.apellido,
                       u.correo, u.usuario, u.contrasena_hash, u.estado
                FROM usuarios u
                INNER JOIN roles r ON u.id_rol = r.id_rol
                WHERE (u.usuario = :usuario OR u.correo = :correo)
                LIMIT 1";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([
            ':usuario' => $usuario,
            ':correo' => $usuario,
        ]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 4. CREAR USUARIO CON DATOS EXTENDIDOS (Transaccional)
    public function crearCompleto($id_rol, $nombre, $apellido, $correo, $usuario, $contrasena_plana, $telefono, $extra_data = [])
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Insertar en tabla usuarios
            $contrasena_hash = password_hash($contrasena_plana, PASSWORD_DEFAULT);
            $sql = "INSERT INTO usuarios (id_rol, nombre, apellido, correo, usuario, contrasena_hash, telefono, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, 'activo')";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_rol, $nombre, $apellido, $correo, $usuario, $contrasena_hash, $telefono]);

            $id_usuario = $this->pdo->lastInsertId();

            // 2. Insertar en tabla específica según el rol
            if ($id_rol == 3) {
                // DATOS DE ESTUDIANTE
                $semestre = $extra_data['semestre'] ?? 1;
                $id_carrera = $extra_data['id_carrera'] ?? 1; // Valor de resguardo
                $registro_universitario = $extra_data['registro_universitario'] ?? 'RU-PENDIENTE';

                $sqlEst = "INSERT INTO estudiantes (id_usuario, id_carrera, semestre, registro_universitario) VALUES (?, ?, ?, ?)";
                $stmtEst = $this->pdo->prepare($sqlEst);
                $stmtEst->execute([$id_usuario, $id_carrera, $semestre, $registro_universitario]);
            } elseif ($id_rol == 2) {
                // DATOS DE TUTOR (Se añade foto_perfil por defecto para evitar el error SQL)
                $sqlTut = "INSERT INTO tutores (id_usuario, especialidad, biografia, foto_perfil, perfil_linkedin, certificaciones, areas_expertise) 
                           VALUES (?, ?, ?, ?, ?, ?, ?)";
                $stmtTut = $this->pdo->prepare($sqlTut);
                $stmtTut->execute([
                    $id_usuario,
                    $extra_data['especialidad'] ?? 'Sin especialidad',
                    $extra_data['biografia'] ?? 'Sin biografía',
                    'default_tutor.png', // <-- Solución al bug de foto_perfil
                    $extra_data['perfil_linkedin'] ?? 'Sin perfil',
                    $extra_data['certificaciones'] ?? 'Sin certificaciones',
                    $extra_data['areas_expertise'] ?? 'Sin áreas registradas'
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // 5. ACTUALIZAR USUARIO COMPLETO (Base de datos transaccional)
    public function actualizarCompleto($id_usuario, $id_rol, $nombre, $apellido, $correo, $usuario, $telefono, $estado, $extra_data = [])
    {
        try {
            $this->pdo->beginTransaction();

            // 1. Actualizar tabla base 'usuarios'
            $sql = "UPDATE usuarios 
                    SET id_rol = ?, nombre = ?, apellido = ?, correo = ?, usuario = ?, telefono = ?, estado = ? 
                    WHERE id_usuario = ?";
            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([$id_rol, $nombre, $apellido, $correo, $usuario, $telefono, $estado, $id_usuario]);

            // 2. Actualizar tabla específica según el rol
            if ($id_rol == 3 && isset($extra_data['semestre'])) {
                // Para estudiante permitimos actualizar carrera y semestre (RU es inmutable)
                $sqlEst = "UPDATE estudiantes SET semestre = ?, id_carrera = ? WHERE id_usuario = ?";
                $stmtEst = $this->pdo->prepare($sqlEst);
                $stmtEst->execute([
                    $extra_data['semestre'],
                    $extra_data['id_carrera'] ?? 1,
                    $id_usuario
                ]);
            } elseif ($id_rol == 2) {
                // Actualizamos los datos profesionales del tutor (sin tocar la foto por ahora)
                $sqlTut = "UPDATE tutores SET especialidad = ?, biografia = ?, perfil_linkedin = ?, certificaciones = ?, areas_expertise = ? WHERE id_usuario = ?";
                $stmtTut = $this->pdo->prepare($sqlTut);
                $stmtTut->execute([
                    $extra_data['especialidad'] ?? 'Sin especialidad',
                    $extra_data['biografia'] ?? 'Sin biografía',
                    $extra_data['perfil_linkedin'] ?? 'Sin perfil',
                    $extra_data['certificaciones'] ?? 'Sin certificaciones',
                    $extra_data['areas_expertise'] ?? 'Sin áreas registradas',
                    $id_usuario
                ]);
            }

            $this->pdo->commit();
            return true;
        } catch (Exception $e) {
            $this->pdo->rollBack();
            throw $e;
        }
    }

    // 6. CAMBIAR CONTRASEÑA
    public function cambiarContrasena($id_usuario, $nueva_contrasena_plana)
    {
        $contrasena_hash = password_hash($nueva_contrasena_plana, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios SET contrasena_hash = ? WHERE id_usuario = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$contrasena_hash, $id_usuario]);
    }

    // 7. ELIMINAR USUARIO FÍSICAMENTE
    public function eliminar($id_usuario)
    {
        $sql = "DELETE FROM usuarios WHERE id_usuario = ?";
        $stmt = $this->pdo->prepare($sql);

        return $stmt->execute([$id_usuario]);
    }

    // 8. VERIFICAR DUPLICADOS (Correo o Usuario)
    public function existeDuplicado($correo, $usuario, $id_usuario = null)
    {
        $sql = "SELECT id_usuario FROM usuarios WHERE (correo = :correo OR usuario = :usuario)";
        $params = [
            ':correo' => $correo,
            ':usuario' => $usuario
        ];

        if ($id_usuario !== null) {
            $sql .= " AND id_usuario != :id_usuario";
            $params[':id_usuario'] = $id_usuario;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetch(PDO::FETCH_ASSOC) !== false;
    }

    // ------------------------------------------------------------------------
    // MÉTODOS DE PAGINACIÓN Y FILTRADO (Cajón 1)
    // ------------------------------------------------------------------------

    public function contarUsuariosPorRol($id_rol, $buscar = '', $filtro_grado = 'todos')
    {
        $sql = "SELECT COUNT(*) FROM usuarios u";

        if ($id_rol == 3) {
            $sql .= " INNER JOIN estudiantes e ON u.id_usuario = e.id_usuario";
        }

        $sql .= " WHERE u.id_rol = :id_rol";
        $params = [':id_rol' => $id_rol];

        if (!empty($buscar)) {
            // CORRECCIÓN: Separamos los nombres de los parámetros
            $sql .= " AND (u.nombre LIKE :buscar1 OR u.correo LIKE :buscar2)";
            $params[':buscar1'] = "%$buscar%";
            $params[':buscar2'] = "%$buscar%";
        }

        if ($id_rol == 3) {
            if ($filtro_grado === 'regulares') {
                $sql .= " AND e.semestre < 9";
            } elseif ($filtro_grado === 'egresados') {
                $sql .= " AND e.semestre >= 9";
            }
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchColumn();
    }

    public function obtenerUsuariosPorRol($id_rol, $buscar = '', $limite = 10, $offset = 0, $filtro_grado = 'todos')
    {
        if ($id_rol == 3) {
            $sql = "SELECT u.*, e.semestre, e.registro_universitario, e.id_carrera, c.nombre_carrera 
                    FROM usuarios u 
                    INNER JOIN estudiantes e ON u.id_usuario = e.id_usuario
                    INNER JOIN carreras c ON e.id_carrera = c.id_carrera";
        } elseif ($id_rol == 2) {
            $sql = "SELECT u.*, t.especialidad, t.biografia, t.perfil_linkedin, t.certificaciones, t.areas_expertise 
                    FROM usuarios u 
                    INNER JOIN tutores t ON u.id_usuario = t.id_usuario";
        } else {
            $sql = "SELECT u.* FROM usuarios u";
        }

        $sql .= " WHERE u.id_rol = :id_rol";
        $params = [':id_rol' => $id_rol];

        if (!empty($buscar)) {
            // CORRECCIÓN: Separamos los nombres de los parámetros
            $sql .= " AND (u.nombre LIKE :buscar1 OR u.correo LIKE :buscar2)";
            $params[':buscar1'] = "%$buscar%";
            $params[':buscar2'] = "%$buscar%";
        }

        if ($id_rol == 3) {
            if ($filtro_grado === 'regulares') {
                $sql .= " AND e.semestre < 9";
            } elseif ($filtro_grado === 'egresados') {
                $sql .= " AND e.semestre >= 9";
            }
        }

        $sql .= " ORDER BY u.id_usuario DESC LIMIT " . (int)$limite . " OFFSET " . (int)$offset;

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
