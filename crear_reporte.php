<?php
/**
 * crear_reporte.php
 * Crea un reporte ligado a un grupo existente en sat_reportes.
 */

header('Content-Type: application/json; charset=utf-8');
ini_set('display_errors', 1);
ini_set('log_errors', 1);
error_reporting(E_ALL);

try {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    include_once('funciones.php');
    include_once('config.php');

    if (!isset($_SESSION['id'])) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'No autorizado']);
        exit();
    }

    $data = json_decode(file_get_contents('php://input'), true);
    error_log('DEBUG: crear_reporte.php - datos recibidos: ' . json_encode($data));

    if (!is_array($data)) {
        throw new Exception('Datos invalidos para crear el reporte');
    }

    $idFacilitador = (int)$_SESSION['id'];
    $idGrupo = (int)($data['idGrupo'] ?? 0);

    if ($idGrupo <= 0) {
        throw new Exception('Debe seleccionar un grupo valido');
    }

    $tipoActividad = (string)($data['tipoActividad'] ?? '');
    $actividades = array(
        'evangelismo' => 77,
        'bautizo' => 99,
        'gran_celebracion' => 8,
        'reunion_cotidiana' => 1
    );

    if (!isset($actividades[$tipoActividad])) {
        throw new Exception('Tipo de actividad no valido');
    }

    $idActividad = (int)$actividades[$tipoActividad];
    $fechaActividad = trim((string)($data['fechaActividad'] ?? ''));

    if ($fechaActividad === '') {
        throw new Exception('La fecha de la actividad es obligatoria');
    }

    $asistencia_hom = intval($data['asistencia_hom'] ?? 0);
    $asistencia_muj = intval($data['asistencia_muj'] ?? 0);
    $asistencia_jov = intval($data['asistencia_jov'] ?? 0);
    $asistencia_nin = intval($data['asistencia_nin'] ?? 0);
    $asistencia_total = $asistencia_hom + $asistencia_muj + $asistencia_jov + $asistencia_nin;

    $bautizados = intval($data['bautizados'] ?? 0);
    $desiciones = intval($data['desiciones'] ?? 0);
    $comentario = addslashes((string)($data['comentario'] ?? ''));

    $mapeo_oracion = intval($data['mapeo_oracion'] ?? 0);
    $mapeo_companerismo = intval($data['mapeo_companerismo'] ?? 0);
    $mapeo_adoracion = intval($data['mapeo_adoracion'] ?? 0);
    $mapeo_biblia = intval($data['mapeo_biblia'] ?? 0);
    $mapeo_evangelizar = intval($data['mapeo_evangelizar'] ?? 0);
    $mapeo_cena = intval($data['mapeo_cena'] ?? 0);
    $mapeo_dar = intval($data['mapeo_dar'] ?? 0);
    $mapeo_bautizar = intval($data['mapeo_bautizar'] ?? 0);
    $mapeo_trabajadores = intval($data['mapeo_trabajadores'] ?? 0);

    if ($idActividad !== 1) {
        $mapeo_oracion = 0;
        $mapeo_companerismo = 0;
        $mapeo_adoracion = 0;
        $mapeo_biblia = 0;
        $mapeo_evangelizar = 0;
        $mapeo_cena = 0;
        $mapeo_dar = 0;
        $mapeo_bautizar = 0;
        $mapeo_trabajadores = 0;
    }

    $PSN1 = new DBbase_Sql;

    $sqlGrupo = "
        SELECT
            id,
            idGrupoMadre,
            generacionNumero,
            plantador,
            sitioReunion,
            grupoMadre_txt,
            nombreGrupo_txt,
            capacitacion_txt,
            barrio,
            direccion,
            ciudad
        FROM sat_reportes
        WHERE id = " . (int)$idGrupo . "
          AND idUsuario = " . (int)$idFacilitador . "
          AND (id_grupo IS NULL OR id_grupo = 0)
        LIMIT 1
    ";

    error_log('DEBUG: Buscando grupo para reporte: ' . $sqlGrupo);
    $PSN1->query($sqlGrupo);

    if (!$PSN1->next_record()) {
        throw new Exception('No se encontro el grupo seleccionado');
    }

    $idGrupoMadre = (int)$PSN1->f('idGrupoMadre');
    $generacionNumero = (int)$PSN1->f('generacionNumero');
    $plantador = addslashes((string)$PSN1->f('plantador'));
    $sitioReunion = addslashes((string)$PSN1->f('sitioReunion'));
    $grupoMadre_txt = addslashes((string)$PSN1->f('grupoMadre_txt'));
    $nombreGrupo_txt = addslashes((string)$PSN1->f('nombreGrupo_txt'));
    $capacitacion_txt = addslashes((string)$PSN1->f('capacitacion_txt'));
    $barrio = addslashes((string)$PSN1->f('barrio'));
    $direccion = addslashes((string)$PSN1->f('direccion'));
    $ciudad = addslashes((string)$PSN1->f('ciudad'));
    $fechaActividad = addslashes($fechaActividad);

    $hoy = date('Y-m-d');
    $ahora = date('Y-m-d H:i:s');
    $bautizadosPeriodo = ($idActividad === 99) ? $bautizados : 0;

    $sqlInsert = "INSERT INTO sat_reportes (
        idUsuario,
        id_grupo,
        id_actividad,
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
        " . (int)$idGrupo . ",
        " . (int)$idActividad . ",
        0,
        " . (int)$idGrupoMadre . ",
        " . (int)$generacionNumero . ",
        '$plantador',
        '$hoy',
        '$fechaActividad',
        '$sitioReunion',
        '$grupoMadre_txt',
        '$nombreGrupo_txt',
        '$capacitacion_txt',
        '$barrio',
        '$direccion',
        '$ciudad',
        " . (int)$asistencia_total . ",
        " . (int)$asistencia_hom . ",
        " . (int)$asistencia_muj . ",
        " . (int)$asistencia_jov . ",
        " . (int)$asistencia_nin . ",
        " . (int)$bautizados . ",
        0,
        " . (int)$desiciones . ",
        0,
        " . (int)$bautizadosPeriodo . ",
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
        " . (int)$mapeo_oracion . ",
        " . (int)$mapeo_companerismo . ",
        " . (int)$mapeo_adoracion . ",
        " . (int)$mapeo_biblia . ",
        " . (int)$mapeo_evangelizar . ",
        " . (int)$mapeo_cena . ",
        " . (int)$mapeo_dar . ",
        " . (int)$mapeo_bautizar . ",
        " . (int)$mapeo_trabajadores . ",
        '$comentario'
    )";

    error_log('DEBUG: INSERT query para nuevo reporte: ' . $sqlInsert);
    $result = $PSN1->query($sqlInsert);

    if (!$result) {
        error_log('ERROR BD: ' . $PSN1->Error);
        throw new Exception('Error al crear el reporte: ' . $PSN1->Error);
    }

    $nuevoReporteId = $PSN1->ultimoId();

    echo json_encode(array(
        'success' => true,
        'message' => 'Reporte creado exitosamente',
        'nuevoReporteId' => $nuevoReporteId,
        'idGrupo' => $idGrupo,
        'idActividad' => $idActividad
    ));
} catch (Exception $e) {
    error_log('ERROR en crear_reporte.php: ' . $e->getMessage());
    http_response_code(500);
    echo json_encode(array(
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ));
}
?>
