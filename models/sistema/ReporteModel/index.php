<?php
/**
 * ARCHIVO: models/reportes/ReporteModel/index.php
 * Modelo para generar las métricas, KPIs y datos estadísticos para el Dashboard de Rendimiento.
 * Actualizado para filtrar mediante id_periodo en lugar de la columna de texto.
 */
class ReporteModel {
    
    private $pdo;

    public function __construct($conexionBaseDatos) {
        $this->pdo = $conexionBaseDatos;
    }

    /**
     * Extrae todos los datos estructurados para los gráficos y KPIs del periodo seleccionado.
     */
    public function obtenerDatosDashboard($id_periodo = 1) {
        $datos = [
            'kpis' => $this->obtenerKPIs($id_periodo),
            'materias_demanda' => $this->obtenerDemandaMaterias($id_periodo),
            'tutorias_estado' => $this->obtenerTutoriasPorEstado($id_periodo),
            'distribucion_tutor' => $this->obtenerDistribucionTutores($id_periodo),
            'sesiones_mes' => $this->obtenerSesionesPorMes($id_periodo),
            'desempeno_docente' => $this->obtenerDesempenoDocente($id_periodo)
        ];
        return $datos;
    }

    // ========================================================================
    // 1. INDICADORES CLAVE DE RENDIMIENTO (KPIs)
    // ========================================================================
    private function obtenerKPIs($id_periodo) {
        $kpis = [
            'total_sesiones' => 0,
            'horas_dictadas' => 0,
            'asistencia_pct' => 0,
            'satisfaccion_promedio' => 0,
            'alumnos_activos_mg' => 0
        ];

        // 1. Total de Sesiones Registradas
        $stmt = $this->pdo->prepare("SELECT COUNT(*) FROM tutorias WHERE id_periodo = ?");
        $stmt->execute([$id_periodo]);
        $kpis['total_sesiones'] = $stmt->fetchColumn() ?: 0;

        // 2. Horas Dictadas (Solo sesiones en estado 'realizada')
        $stmt = $this->pdo->prepare("
            SELECT SUM(TIME_TO_SEC(TIMEDIFF(hora_fin, hora_inicio))) / 3600 AS horas 
            FROM tutorias 
            WHERE id_periodo = ? AND estado = 'realizada'
        ");
        $stmt->execute([$id_periodo]);
        $horas = $stmt->fetchColumn();
        $kpis['horas_dictadas'] = $horas ? round($horas, 1) : 0;

        // 3. Porcentaje de Asistencia Estudiantil
        $stmt = $this->pdo->prepare("
            SELECT 
                (SUM(CASE WHEN ss.asistio = 'si' THEN 1 ELSE 0 END) / COUNT(*)) * 100 AS pct
            FROM seguimiento_sesion ss
            INNER JOIN tutorias t ON ss.id_tutoria = t.id_tutoria
            WHERE t.id_periodo = ?
        ");
        $stmt->execute([$id_periodo]);
        $asistencia = $stmt->fetchColumn();
        $kpis['asistencia_pct'] = $asistencia ? round($asistencia, 1) : 0;

        // 4. Satisfacción Promedio (Evaluaciones de Estudiantes)
        $stmt = $this->pdo->prepare("
            SELECT AVG(e.calificacion) AS promedio 
            FROM evaluaciones_tutoria e
            INNER JOIN tutorias t ON e.id_tutoria = t.id_tutoria
            WHERE t.id_periodo = ?
        ");
        $stmt->execute([$id_periodo]);
        $satisfaccion = $stmt->fetchColumn();
        $kpis['satisfaccion_promedio'] = $satisfaccion ? round($satisfaccion, 1) : 0;

        // 5. Tesistas Activos en Modalidad de Grado (No depende del periodo de tutorías regulares)
        $stmt = $this->pdo->query("SELECT COUNT(*) FROM expedientes_mg WHERE estado = 'activo'");
        $kpis['alumnos_activos_mg'] = $stmt->fetchColumn() ?: 0;

        return $kpis;
    }

    // ========================================================================
    // 2. MATERIAS CON MAYOR DEMANDA (Gráfico de Barras)
    // ========================================================================
    private function obtenerDemandaMaterias($id_periodo) {
        $sql = "SELECT m.nombre_materia, COUNT(t.id_tutoria) AS total 
                FROM tutorias t
                INNER JOIN materias m ON t.id_materia = m.id_materia
                WHERE t.id_periodo = ?
                GROUP BY t.id_materia
                ORDER BY total DESC
                LIMIT 5";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_periodo]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = []; $data = [];
        foreach ($resultados as $row) {
            $labels[] = $row['nombre_materia'];
            $data[] = (int)$row['total'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    // ========================================================================
    // 3. ESTADOS DE TUTORÍAS (Gráfico de Torta)
    // ========================================================================
    private function obtenerTutoriasPorEstado($id_periodo) {
        $sql = "SELECT estado, COUNT(*) AS total 
                FROM tutorias 
                WHERE id_periodo = ?
                GROUP BY estado
                ORDER BY total DESC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_periodo]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = []; $data = [];
        foreach ($resultados as $row) {
            $labels[] = ucfirst(str_replace('_', ' ', $row['estado']));
            $data[] = (int)$row['total'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    // ========================================================================
    // 4. DISTRIBUCIÓN POR TUTOR (Gráfico de Torta)
    // ========================================================================
    private function obtenerDistribucionTutores($id_periodo) {
        $sql = "SELECT CONCAT(u.nombre, ' ', u.apellido) AS nombre_tutor, COUNT(t.id_tutoria) AS total 
                FROM tutorias t
                INNER JOIN tutores tu ON t.id_tutor = tu.id_tutor
                INNER JOIN usuarios u ON tu.id_usuario = u.id_usuario
                WHERE t.id_periodo = ?
                GROUP BY t.id_tutor
                ORDER BY total DESC
                LIMIT 5";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_periodo]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = []; $data = [];
        foreach ($resultados as $row) {
            $labels[] = $row['nombre_tutor'];
            $data[] = (int)$row['total'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    // ========================================================================
    // 5. CANTIDAD DE SESIONES POR MES (Gráfico de Torta)
    // ========================================================================
    private function obtenerSesionesPorMes($id_periodo) {
        $sql = "SELECT MONTH(fecha) AS mes, COUNT(*) AS total 
                FROM tutorias 
                WHERE id_periodo = ?
                GROUP BY MONTH(fecha)
                ORDER BY mes ASC";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_periodo]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $nombres_meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
            7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        $labels = []; $data = [];
        foreach ($resultados as $row) {
            $labels[] = $nombres_meses[(int)$row['mes']] ?? 'Mes ' . $row['mes'];
            $data[] = (int)$row['total'];
        }
        return ['labels' => $labels, 'data' => $data];
    }

    // ========================================================================
    // 6. DESEMPEÑO DOCENTE (Gráfico de Barras)
    // ========================================================================
    private function obtenerDesempenoDocente($id_periodo) {
        $sql = "SELECT CONCAT(u.nombre, ' ', u.apellido) AS nombre_tutor, AVG(e.calificacion) AS promedio 
                FROM evaluaciones_tutoria e
                INNER JOIN tutorias t ON e.id_tutoria = t.id_tutoria
                INNER JOIN tutores tu ON t.id_tutor = tu.id_tutor
                INNER JOIN usuarios u ON tu.id_usuario = u.id_usuario
                WHERE t.id_periodo = ?
                GROUP BY t.id_tutor
                ORDER BY promedio DESC
                LIMIT 5";
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute([$id_periodo]);
        $resultados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $labels = []; $data = [];
        foreach ($resultados as $row) {
            $labels[] = $row['nombre_tutor'];
            $data[] = round((float)$row['promedio'], 1);
        }
        return ['labels' => $labels, 'data' => $data];
    }
}
?>