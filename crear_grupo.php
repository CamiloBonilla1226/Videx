<?php
/**
 * crear_grupo.php
 * Crea un nuevo grupo registrando un reporte de generación 0 con datos iniciales
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    // Iniciar sesión
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    // Incluir funciones y config
    include_once('funciones.php');
    include_once('config.php');

    // Verificar autenticación
    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit();
    }

    // Obtener datos del POST
    $data = json_decode(file_get_contents('php://input'), true);
    error_log('DEBUG: crear_grupo.php - datos recibidos: ' . json_encode($data));

    // Validar datos obligatorios del grupo
    if (!$data['nombre']) {
        throw new Exception('El nombre del grupo es obligatorio');
    }

    $idFacilitador = $_SESSION['id'];
    $nombre = $data['nombre'];
    $descripcion = $data['descripcion'] ?? '';
    $ciudad = $data['ciudad'] ?? '';
    $barrio = $data['barrio'] ?? '';
    $direccion = $data['direccion'] ?? '';
    $lider = $data['lider'] ?? '';
    $tieneGrupoMadre = $data['tieneGrupoMadre'] === 'si';
    $grupoMadreId = $data['grupoMadreId'] ?? '';

    // Datos del primer reporte
    $tipoActividad = $data['tipoActividad'] ?? 'reunion_cotidiana';
    $fechaActividad = $data['fechaActividad'] ?? date('Y-m-d');
    $asistencia_hom = intval($data['asistencia_hom'] ?? 0);
    $asistencia_muj = intval($data['asistencia_muj'] ?? 0);
    $asistencia_jov = intval($data['asistencia_jov'] ?? 0);
    $asistencia_nin = intval($data['asistencia_nin'] ?? 0);

    // Conectar a BD
    $PSN1 = new DBbase_Sql;

    $generacionNumero = 0;
    $idGrupoMadre = 0;
    $grupoMadre_txt = '';

    // Si tiene grupo madre, obtener información y calcular generación
    if ($tieneGrupoMadre && $grupoMadreId) {
        // Buscar todos los reportes del facilitador para encontrar el grupo madre
        $queryGrupos = "
            SELECT DISTINCT nombreGrupo_txt, plantador, ciudad, barrio, generacionNumero, grupoMadre_txt, direccion
            FROM sat_reportes
            WHERE idUsuario = " . (int)$idFacilitador . "
            ORDER BY generacionNumero DESC, id DESC
        ";

        error_log('DEBUG: Buscando grupo madre con query: ' . $queryGrupos);

        $resultGrupos = $PSN1->query($queryGrupos);
        $grupoMadreEncontrado = false;

        while ($PSN1->next_record()) {
            $ubicacionGrupo = ($PSN1->f('ciudad') ?? '') . ($PSN1->f('barrio') ? ', ' . $PSN1->f('barrio') : '');
            $direccionGrupo = $PSN1->f('direccion') ?? '';
            $md5Test = md5($PSN1->f('nombreGrupo_txt') . '|' . $PSN1->f('plantador') . '|' . $ubicacionGrupo . '|' . ($PSN1->f('grupoMadre_txt') ?? '') . '|' . $direccionGrupo);

            error_log('DEBUG: Comparando hash: ' . substr($md5Test, 0, 8) . ' vs ' . substr($grupoMadreId, 0, 8));

            // Comparar primeros 8 caracteres del hash
            if (substr($md5Test, 0, 8) === substr($grupoMadreId, 0, 8)) {
                $generacionNumero = (int)$PSN1->f('generacionNumero') + 1;
                $grupoMadre_txt = $PSN1->f('nombreGrupo_txt');
                $idGrupoMadre = 0; // Por ahora mantenemos en 0
                $grupoMadreEncontrado = true;
                error_log('DEBUG: Grupo madre encontrado con gen: ' . $generacionNumero);
                break;
            }
        }

        if (!$grupoMadreEncontrado) {
            error_log('ERROR: Grupo madre no encontrado con id: ' . $grupoMadreId);
            throw new Exception('No se encontró el grupo madre seleccionado');
        }

        if ($generacionNumero > 5) {
            throw new Exception('No se puede crear un grupo de generación mayor a 5');
        }
    }

    // Sanitizar strings
    $nombre = addslashes($nombre);
    $descripcion = addslashes($descripcion);
    $ciudad = addslashes($ciudad);
    $barrio = addslashes($barrio);
    $direccion = addslashes($direccion);
    $lider = addslashes($lider);
    $grupoMadre_txt = addslashes($grupoMadre_txt);
    $fechaActividad = addslashes($fechaActividad);

    // Calcular asistencia total
    $asistencia_total = $asistencia_hom + $asistencia_muj + $asistencia_jov + $asistencia_nin;

    // Crear reporte de generación 0 (creación del grupo)
    $hoy = date('Y-m-d');
    $ahora = date('Y-m-d H:i:s');

    $sqlInsert = "INSERT INTO sat_reportes (
        idUsuario,
        inactivo,
        idGrupoMadre,
        generacionNumero,
        plantador,
        fechaReporte,
        fechaInicio,
        sitioReunion,
        grupoMadre_txt,
        nombreGrupo_txt,
        capacitacion_txt,
        barrio,
        direccion,
        ciudad,
        asistencia_total,
        asistencia_hom,
        asistencia_muj,
        asistencia_jov,
        asistencia_nin,
        bautizados,
        discipulado,
        desiciones,
        preparandose,
        bautizadosPeriodo,
        iglesias_reconocidas,
        creacionFecha,
        creacionUsuario,
        modificacionFecha,
        modificacionUsuario,
        ext1,
        ext2,
        mapeo_anho,
        mapeo_cuarto,
        ext3,
        mapeo_fecha,
        mapeo_comprometido,
        mapeo_oracion,
        mapeo_companerismo,
        mapeo_adoracion,
        mapeo_biblia,
        mapeo_evangelizar,
        mapeo_cena,
        mapeo_dar,
        mapeo_bautizar,
        mapeo_trabajadores,
        comentario
    ) VALUES (
        " . (int)$idFacilitador . ",
        0,
        " . (int)$idGrupoMadre . ",
        " . (int)$generacionNumero . ",
        '$lider',
        '$hoy',
        '$fechaActividad',
        '$nombre',
        '$grupoMadre_txt',
        '$nombre',
        '$descripcion',
        '$barrio',
        '$direccion',
        '$ciudad',
        " . (int)$asistencia_total . ",
        " . (int)$asistencia_hom . ",
        " . (int)$asistencia_muj . ",
        " . (int)$asistencia_jov . ",
        " . (int)$asistencia_nin . ",
        0,
        0,
        0,
        0,
        0,
        0,
        '$ahora',
        " . (int)$idFacilitador . ",
        '$hoy',
        " . (int)$idFacilitador . ",
        '', '',
        YEAR(NOW()),
        QUARTER(NOW()),
        '',
        NOW(),
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        0,
        '$descripcion'
    )";

    error_log('DEBUG: INSERT query para nuevo grupo: ' . $sqlInsert);

    // Ejecutar la query usando el método query de DBbase_Sql
    $result = $PSN1->query($sqlInsert);

    if (!$result) {
        error_log('ERROR BD: ' . $PSN1->Error);
        throw new Exception('Error al crear el grupo: ' . $PSN1->Error);
    }

    $nuevoReporteId = $PSN1->ultimoId();

    error_log('DEBUG: Grupo creado exitosamente con ID: ' . $nuevoReporteId);

    echo json_encode([
        'success' => true,
        'message' => 'Grupo creado exitosamente',
        'nuevoGrupoId' => $nuevoReporteId,
        'generacion' => $generacionNumero
    ]);

} catch (Exception $e) {
    error_log('ERROR en crear_grupo.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}
?>
