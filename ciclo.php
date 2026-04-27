<?php
/**
 * ciclo.php
 * Base inicial para la nueva grafica del ciclo.
 * En esta primera etapa se deja funcionando el filtro por idUsuario.
 */

if (!isset($_SESSION['id']) || $_SESSION['id'] == '') {
    echo "No autorizado";
    exit();
}

function ciclo_req_num($key)
{
    return (isset($_REQUEST[$key]) && soloNumeros($_REQUEST[$key]) != '') ? soloNumeros($_REQUEST[$key]) : '';
}

function ciclo_req_text($key)
{
    return (isset($_REQUEST[$key]) && trim($_REQUEST[$key]) != '') ? trim(eliminarInvalidos($_REQUEST[$key])) : '';
}

function ciclo_build_filtro_sat($idUsuario)
{
    $sqlFiltro = '';

    if ($idUsuario !== '') {
        $sqlFiltro .= " AND sat_reportes.idUsuario = '" . $idUsuario . "'";
    }

    return $sqlFiltro;
}

function ciclo_build_filtro_grupo($nombreGrupo)
{
    $sqlFiltro = '';

    if ($nombreGrupo !== '') {
        $sqlFiltro .= " AND sat_reportes.nombreGrupo_txt = '" . $nombreGrupo . "'";
    }

    return $sqlFiltro;
}

$PSN = new DBbase_Sql;
$PSN2 = new DBbase_Sql;
$PSN3 = new DBbase_Sql;
$PSN4 = new DBbase_Sql;
$esFacilitador = ($_SESSION['perfil'] == 163);

if ($esFacilitador) {
    $_REQUEST['idUsuario'] = $_SESSION['id'];
}

$buscar_idUsuario = ciclo_req_num('idUsuario');
$buscar_nombreGrupo = ciclo_req_text('nombreGrupo_txt');

$usuarios = array();
$gruposIpg = array();
$nombreUsuarioSeleccionado = 'Seleccione un facilitador';

$sql = "SELECT id, nombre
        FROM usuario
        WHERE tipo IN (162, 163) AND acceso = 1";

if ($esFacilitador) {
    $sql .= " AND id = '" . $_SESSION['id'] . "'";
}

$sql .= " ORDER BY nombre ASC";

$PSN2->query($sql);
if ($PSN2->num_rows() > 0) {
    while ($PSN2->next_record()) {
        $idUsuario = $PSN2->f('id');
        $nombreUsuario = $PSN2->f('nombre');

        $usuarios[] = array(
            'id' => $idUsuario,
            'nombre' => $nombreUsuario,
        );

        if ((string)$buscar_idUsuario === (string)$idUsuario) {
            $nombreUsuarioSeleccionado = $nombreUsuario;
        }
    }
}

$requiereSeleccionFacilitador = (!$esFacilitador && $buscar_idUsuario === '');
$grupoSeleccionadoValido = false;

if (!$requiereSeleccionFacilitador) {
    $sql = "SELECT DISTINCT nombreGrupo_txt
FROM sat_reportes
WHERE idUsuario = '" . $buscar_idUsuario . "'
AND nombreGrupo_txt IS NOT NULL
AND TRIM(nombreGrupo_txt) <> ''
ORDER BY nombreGrupo_txt ASC";
    $PSN3->query($sql);
    if ($PSN3->num_rows() > 0) {
        while ($PSN3->next_record()) {
            $nombreGrupo = trim($PSN3->f('nombreGrupo_txt'));
            $gruposIpg[] = $nombreGrupo;

            if ($buscar_nombreGrupo === $nombreGrupo) {
                $grupoSeleccionadoValido = true;
            }
        }
    }
}

if (!$grupoSeleccionadoValido) {
    $buscar_nombreGrupo = '';
}

$sqlFiltroUsuario = ciclo_build_filtro_sat($buscar_idUsuario);
$sqlFiltroGrupo = ciclo_build_filtro_grupo($buscar_nombreGrupo);
$grupoSeleccionado = ($buscar_nombreGrupo !== '');
$estadoMultiplicar = false;
$estadoEncontrarPersonasPaz = false;
$estadoPrepararseOrar = false;
$imgMultiplicarAzul = 'multiplicar_azul.png';
$imgMultiplicarGris = 'multiplicar_gris.png';
$imgEncontrarAzul = 'encontrar_a.png';
$imgEncontrarGris = 'encontrar_g.png';
$imgEncontrarNaranja = 'encontrar_n.png';

$totalReportes = 0;
$primerReporte = '';
$ultimoReporte = '';
$totalFacilitadoresConDatos = 0;

if (!$requiereSeleccionFacilitador && $grupoSeleccionado) {
    $sql = "SELECT COUNT(sat_reportes.id) AS conteo
            FROM sat_reportes
            WHERE 1 ".$sqlFiltroUsuario.$sqlFiltroGrupo."
              AND sat_reportes.generacionNumero IN (1, 2, 3, 4, 5)
              AND sat_reportes.plantador IS NOT NULL
              AND TRIM(sat_reportes.plantador) <> ''";

    $PSN4->query($sql);
    if ($PSN4->next_record()) {
        $estadoMultiplicar = ((int)$PSN4->f('conteo') > 0);
    }

    $sql = "SELECT
                SUM(CASE
                        WHEN sat_reportes.plantador IS NOT NULL
                         AND TRIM(sat_reportes.plantador) <> ''
                        THEN 1 ELSE 0
                    END) AS conteoPlantador,
                SUM(CASE
                        WHEN sat_reportes.generacionNumero = 0
                         AND sat_reportes.fechaInicio IS NOT NULL
                         AND sat_reportes.fechaInicio <> ''
                         AND sat_reportes.fechaInicio <> '0000-00-00'
                        THEN 1 ELSE 0
                    END) AS conteoFechaInicio
            FROM sat_reportes
            WHERE 1 ".$sqlFiltroUsuario.$sqlFiltroGrupo;

    $PSN4->query($sql);
    if ($PSN4->next_record()) {
        $estadoEncontrarPersonasPaz = ((int)$PSN4->f('conteoPlantador') > 0);
        $estadoPrepararseOrar = ((int)$PSN4->f('conteoFechaInicio') > 0);
    }
}

if (!$requiereSeleccionFacilitador) {
    $sql = "SELECT
                COUNT(sat_reportes.id) AS totalReportes,
                COUNT(DISTINCT sat_reportes.idUsuario) AS totalFacilitadores,
                MIN(sat_reportes.fechaReporte) AS primerReporte,
                MAX(sat_reportes.fechaReporte) AS ultimoReporte
            FROM sat_reportes
            WHERE 1 " . $sqlFiltroUsuario . $sqlFiltroGrupo;

    $PSN3->query($sql);
    if ($PSN3->next_record()) {
        $totalReportes = (int)$PSN3->f('totalReportes');
        $totalFacilitadoresConDatos = (int)$PSN3->f('totalFacilitadores');
        $primerReporte = $PSN3->f('primerReporte');
        $ultimoReporte = $PSN3->f('ultimoReporte');
    }
}

$estadoSegmentoMultiplicar = $estadoMultiplicar ? 'active' : 'disabled';
$estadoAccionMultiplicar = $estadoMultiplicar ? 'active' : 'disabled';

$estadoSegmentoEncontrar = 'disabled';
$estadoAccionPrepararse = 'disabled';
$estadoAccionPersonasPaz = 'disabled';

if ($estadoPrepararseOrar && $estadoEncontrarPersonasPaz) {
    $estadoSegmentoEncontrar = 'active';
    $estadoAccionPrepararse = 'active';
    $estadoAccionPersonasPaz = 'active';
} elseif ($estadoPrepararseOrar || $estadoEncontrarPersonasPaz) {
    $estadoSegmentoEncontrar = 'partial';
    $estadoAccionPrepararse = $estadoPrepararseOrar ? 'active' : 'warning';
    $estadoAccionPersonasPaz = $estadoEncontrarPersonasPaz ? 'active' : 'warning';
}
?>

<style>
    .ciclo-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 15px 35px;
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    .ciclo-hero {
        background: linear-gradient(135deg, #f5f8fb 0%, #eef3f8 100%);
        border: 1px solid #d9e3ee;
        border-radius: 14px;
        padding: 24px;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(33, 53, 71, 0.08);
    }

    .ciclo-hero h2 {
        margin: 0 0 8px;
        color: #213547;
        font-size: 28px;
        font-weight: 700;
    }

    .ciclo-hero p {
        margin: 0;
        color: #556677;
        font-size: 15px;
        line-height: 1.6;
    }

    .ciclo-card {
        background: #ffffff;
        border: 1px solid #e3e8ef;
        border-radius: 14px;
        box-shadow: 0 10px 25px rgba(15, 23, 42, 0.06);
        overflow: hidden;
    }

    .ciclo-card + .ciclo-card {
        margin-top: 22px;
    }

    .ciclo-card__head {
        padding: 18px 22px;
        background: #f8fafc;
        border-bottom: 1px solid #e3e8ef;
    }

    .ciclo-card__head h3 {
        margin: 0;
        color: #1f2d3d;
        font-size: 18px;
        font-weight: 700;
    }

    .ciclo-card__body {
        padding: 22px;
    }

    .ciclo-filter-row {
        display: flex;
        align-items: flex-end;
        gap: 16px;
        flex-wrap: wrap;
    }

    .ciclo-filter-field {
        flex: 1 1 280px;
    }

    .ciclo-filter-field strong {
        display: block;
        margin-bottom: 8px;
        color: #1f2d3d;
    }

    .ciclo-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
        gap: 16px;
    }

    .ciclo-stat {
        background: #f8fbff;
        border: 1px solid #dce7f2;
        border-radius: 12px;
        padding: 18px;
    }

    .ciclo-stat__label {
        display: block;
        margin-bottom: 8px;
        color: #607284;
        font-size: 12px;
        font-weight: 700;
        letter-spacing: 0.04em;
        text-transform: uppercase;
    }

    .ciclo-stat__value {
        color: #1f2d3d;
        font-size: 24px;
        font-weight: 700;
        line-height: 1.2;
    }

    .ciclo-muted {
        color: #607284;
        font-size: 14px;
    }

    .ciclo-visual {
        background: radial-gradient(circle at top, #f6f9ff 0%, #eef4ff 42%, #f9fbff 100%);
        border: 1px solid #d9e4f2;
        border-radius: 22px;
        padding: 34px 18px 18px;
        overflow: visible;
    }

    .ciclo-preload {
        position: absolute;
        width: 0;
        height: 0;
        overflow: hidden;
        opacity: 0;
        pointer-events: none;
    }

    .ciclo-chart-stage {
        position: relative;
        width: min(100%, 840px);
        margin: 0 auto;
        aspect-ratio: 1 / 1;
        isolation: isolate;
        overflow: visible;
    }

    .ciclo-chart-stage svg {
        width: 100%;
        height: 100%;
        display: block;
        transform: translateZ(0);
    }

    .ciclo-node-layer {
        position: absolute;
        inset: 0;
        overflow: visible;
        pointer-events: none;
        transform: translateZ(0);
    }

    .ciclo-segment .ciclo-ring {
        fill: #2f4ea2;
        stroke: #ffffff;
        stroke-width: 4;
        transition: transform 0.25s ease;
        transform-origin: 380px 380px;
        will-change: transform;
    }

    .ciclo-segment .ciclo-region {
        fill: #ffffff;
        stroke: #2f4ea2;
        stroke-width: 2.5;
        transition: transform 0.25s ease, filter 0.25s ease;
        transform-origin: 380px 380px;
        will-change: transform;
    }

    .ciclo-segment .ciclo-hit {
        fill: transparent;
        cursor: pointer;
    }

    .ciclo-segment .ciclo-label {
        fill: #ffffff;
        font-size: 27px;
        font-weight: 800;
        letter-spacing: 1.4px;
        pointer-events: none;
    }

    .ciclo-segment.is-active .ciclo-region {
        transform: scale(1.015);
        filter: drop-shadow(0 14px 20px rgba(47, 78, 162, 0.14));
    }

    .ciclo-segment.is-active .ciclo-ring {
        transform: scale(1.015);
    }

    .ciclo-segment.is-disabled .ciclo-ring,
    .ciclo-segment.is-disabled.is-active .ciclo-ring {
        fill: #b8bfcb;
    }

    .ciclo-segment.is-disabled .ciclo-region,
    .ciclo-segment.is-disabled.is-active .ciclo-region {
        fill: #fbfcfe;
        stroke: #aab2c0;
        filter: none;
    }

    .ciclo-segment.is-partial .ciclo-ring,
    .ciclo-segment.is-partial.is-active .ciclo-ring {
        fill: #f39b2f;
    }

    .ciclo-segment.is-partial .ciclo-region,
    .ciclo-segment.is-partial.is-active .ciclo-region {
        fill: #fff8ef;
        stroke: #f39b2f;
        filter: none;
    }

    .ciclo-heart {
        filter: drop-shadow(0 10px 18px rgba(173, 16, 35, 0.22));
        pointer-events: none;
    }

    .ciclo-heart text {
        fill: #ffffff;
        font-size: 25px;
        font-weight: 800;
    }

    .ciclo-node {
        position: absolute;
        --ciclo-node-shift-y: -50%;
        transform: translate(-50%, var(--ciclo-node-shift-y));
        width: 120px;
        padding: 2px 2px;
        border: 0;
        background: transparent;
        color: #2f4ea2;
        text-align: center;
        cursor: pointer;
        pointer-events: auto;
        transition: transform 0.2s ease, opacity 0.2s ease, color 0.2s ease;
        will-change: transform;
    }

    .ciclo-node:focus {
        outline: none;
    }

    .ciclo-node__icon {
        width: 62px;
        height: 62px;
        margin: 0 auto 4px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 0;
        background: transparent;
        box-shadow: none;
        transition: transform 0.25s ease;
        will-change: transform;
    }

    .ciclo-node__icon svg {
        width: 50px;
        height: 50px;
        display: block;
    }

    .ciclo-node__icon img {
        position: absolute;
        inset: 0;
        margin: auto;
        width: 100%;
        max-width: 108px;
        height: auto;
        max-height: 108px;
        display: block;
        object-fit: contain;
        transform: translateY(-34px);
        pointer-events: none;
    }

    .ciclo-node--image {
        width: clamp(74px, 15%, 108px);
    }

    .ciclo-node--image .ciclo-node__icon {
        width: 100%;
        height: auto;
    }

    .ciclo-node--image .ciclo-node__icon img {
        transform: translateY(-24px);
    }

    .ciclo-node--image .ciclo-node__text {
        margin-top: 34px;
    }

    .ciclo-node--multiplicar-training .ciclo-node__text,
    .ciclo-node--encontrar-pray .ciclo-node__text {
        margin-top: 40px;
    }

    .ciclo-node--encontrar-pray .ciclo-node__icon img {
        transform: translate(4px, -16px);
    }

    .ciclo-node--encontrar-pray {
        width: 104px !important;
    }

    .ciclo-node__text {
        display: block;
        font-size: 11px;
        line-height: 1.18;
        font-weight: 500;
        text-shadow: 0 1px 0 rgba(255, 255, 255, 0.85);
        -webkit-text-size-adjust: 100%;
        text-size-adjust: 100%;
    }

    .ciclo-node__text span {
        display: block;
        white-space: nowrap;
    }

    .ciclo-node.is-active {
        transform: translate(-50%, var(--ciclo-node-shift-y)) scale(1.06);
    }

    .ciclo-node.is-disabled,
    .ciclo-node.is-disabled.is-active {
        color: #98a2b3;
    }

    .ciclo-node.is-active .ciclo-node__icon {
        transform: translateY(-1px);
    }

    .ciclo-node.is-disabled .ciclo-node__icon,
    .ciclo-node.is-disabled.is-active .ciclo-node__icon {
        opacity: 0.68;
        transform: none;
    }

    .ciclo-node.is-disabled .ciclo-node__text,
    .ciclo-node.is-disabled.is-active .ciclo-node__text {
        color: #98a2b3;
        text-shadow: none;
    }

    .ciclo-node.is-warning,
    .ciclo-node.is-warning.is-active {
        color: #f39b2f;
    }

    .ciclo-node.is-warning .ciclo-node__text,
    .ciclo-node.is-warning.is-active .ciclo-node__text {
        color: #f39b2f;
        text-shadow: none;
    }

    .ciclo-actions {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        flex: 0 1 auto;
        padding-top: 25px;
    }

    .ciclo-actions .btn {
        min-width: 120px;
    }

    .ciclo-help {
        margin-top: 16px;
        text-align: center;
        color: #5f7086;
        font-size: 14px;
        line-height: 1.5;
    }

    @media (max-width: 767px) {
        .ciclo-card__body {
            padding: 16px;
        }

        .ciclo-visual {
            padding: 28px 6px 10px;
        }

        .ciclo-node-layer {
            transform: scale(0.7);
            transform-origin: center center;
        }

        .ciclo-actions {
            padding-top: 0;
        }

        .ciclo-segment .ciclo-label {
            font-size: 17px;
            letter-spacing: 0.55px;
        }

        .ciclo-node {
            --ciclo-node-shift-y: -78%;
            width: 92px !important;
            padding: 0;
        }

        .ciclo-node__icon {
            width: 34px;
            height: 34px;
            margin-bottom: 2px;
        }

        .ciclo-node__icon svg {
            width: 24px;
            height: 24px;
        }

        .ciclo-node__icon img {
            max-width: 46px;
            max-height: 46px;
            transform: translateY(-9px);
        }

    .ciclo-node--image .ciclo-node__icon img {
        transform: translateY(-8px);
    }

    .ciclo-node--encontrar-pray .ciclo-node__icon img {
        transform: translate(4px, -6px);
    }

        .ciclo-node--image {
            width: 64px !important;
        }

        .ciclo-node--image .ciclo-node__text {
            margin-top: 16px;
        }

        .ciclo-node--multiplicar-training .ciclo-node__text,
        .ciclo-node--encontrar-pray .ciclo-node__text {
            margin-top: 20px;
        }

        .ciclo-node__text {
            font-size: 6px;
            line-height: 1.08;
            letter-spacing: -0.01em;
        }

        .ciclo-node__text span {
            white-space: normal;
            overflow-wrap: anywhere;
        }

        .ciclo-heart text {
            font-size: 16px;
        }

        .ciclo-help {
            font-size: 12px;
            margin-top: 10px;
        }
    }

    @media (max-width: 480px) {
        .ciclo-card__body {
            padding: 12px;
        }

        .ciclo-visual {
            padding: 26px 0 6px;
        }

        .ciclo-node-layer {
            transform: scale(0.62);
        }

        .ciclo-segment .ciclo-label {
            font-size: 15px;
            letter-spacing: 0.35px;
        }

        .ciclo-node {
            --ciclo-node-shift-y: -88%;
            width: 82px !important;
        }

        .ciclo-node__icon {
            width: 30px;
            height: 30px;
        }

        .ciclo-node__icon svg {
            width: 21px;
            height: 21px;
        }

        .ciclo-node__icon img {
            max-width: 40px;
            max-height: 40px;
            transform: translateY(-7px);
        }

        .ciclo-node--image .ciclo-node__icon img {
            transform: translateY(-6px);
        }

        .ciclo-node--encontrar-pray .ciclo-node__icon img {
            transform: translate(3px, -4px);
        }

        .ciclo-node--image {
            width: 56px !important;
        }

        .ciclo-node--image .ciclo-node__text {
            margin-top: 12px;
        }

        .ciclo-node--multiplicar-training .ciclo-node__text,
        .ciclo-node--encontrar-pray .ciclo-node__text {
            margin-top: 16px;
        }

        .ciclo-node__text {
            font-size: 5px;
            line-height: 1.05;
        }

        .ciclo-heart text {
            font-size: 14px;
        }
    }
</style>

<div class="ciclo-wrap">
  

    <form action="index.php" method="get" name="formCiclo" class="form-horizontal ciclo-card">
        <input type="hidden" name="doc" value="ciclo" />

        <div class="ciclo-card__head">
            <h3>Filtro inicial</h3>
        </div>

        <div class="ciclo-card__body">
            <div class="ciclo-filter-row">
                <div class="ciclo-filter-field">
                    <strong>Facilitador Satura:</strong>
                    <?php if ($esFacilitador) { ?>
                        <input type="hidden" name="idUsuario" value="<?=$buscar_idUsuario; ?>" />
                        <div class="form-control" style="background:#f8fafc;"><?=$nombreUsuarioSeleccionado; ?></div>
                    <?php } else { ?>
                        <select name="idUsuario" onchange="document.getElementById('nombreGrupo_txt').value=''; this.form.submit()" class="form-control">
                            <option value="">Seleccione un facilitador</option>
                            <?php foreach ($usuarios as $usuario) { ?>
                                <option value="<?=$usuario['id']; ?>" <?php if ((string)$buscar_idUsuario === (string)$usuario['id']) { ?>selected="selected"<?php } ?>>
                                    <?=$usuario['nombre']; ?>
                                </option>
                            <?php } ?>
                        </select>
                    <?php } ?>
                </div>

                <div class="ciclo-filter-field">
                    <strong>Grupo IPG:</strong>
                    <select name="nombreGrupo_txt" id="nombreGrupo_txt" onchange="this.form.submit()" class="form-control" <?php if ($requiereSeleccionFacilitador) { ?>disabled="disabled"<?php } ?>>
                        <?php if ($requiereSeleccionFacilitador) { ?>
                            <option value="">Primero seleccione un facilitador</option>
                        <?php } else { ?>
                            <option value="">Seleccione un grupo</option>
                            <?php foreach ($gruposIpg as $nombreGrupo) { ?>
                                <option value="<?=htmlspecialchars($nombreGrupo, ENT_QUOTES, 'UTF-8'); ?>" <?php if ($buscar_nombreGrupo === $nombreGrupo) { ?>selected="selected"<?php } ?>>
                                    <?=htmlspecialchars($nombreGrupo, ENT_QUOTES, 'UTF-8'); ?>
                                </option>
                            <?php } ?>
                        <?php } ?>
                    </select>
                </div>

                <div class="ciclo-actions">
                    <div>
                        <input type="submit" value="Filtrar" class="btn btn-success" />
                    </div>
                    <?php if (!$esFacilitador) { ?>
                        <div>
                            <a href="index.php?doc=ciclo" class="btn btn-default">Limpiar</a>
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </form>

    

    <div class="ciclo-card">
        <div class="ciclo-card__head">
            <h3>Ciclo Interactivo</h3>
        </div>

        <div class="ciclo-card__body">
            <div class="ciclo-visual">
                <div class="ciclo-preload" aria-hidden="true">
                    <img src="<?=$imgMultiplicarAzul; ?>" alt="" loading="eager" decoding="sync" fetchpriority="high" />
                    <img src="<?=$imgMultiplicarGris; ?>" alt="" loading="eager" decoding="sync" fetchpriority="high" />
                    <img src="<?=$imgEncontrarAzul; ?>" alt="" loading="eager" decoding="sync" fetchpriority="high" />
                    <img src="<?=$imgEncontrarGris; ?>" alt="" loading="eager" decoding="sync" fetchpriority="high" />
                    <img src="<?=$imgEncontrarNaranja; ?>" alt="" loading="eager" decoding="sync" fetchpriority="high" />
                </div>
                <div class="ciclo-chart-stage" id="cicloChartStage">
                    <svg id="cicloChartSvg" viewBox="0 0 760 760" aria-label="Ciclo de multiplicacion"></svg>
                    <div class="ciclo-node-layer" id="cicloNodeLayer"></div>
                </div>
                <div class="ciclo-help">
                    Pasa el cursor o haz clic sobre cada porción para resaltar la etapa y sus acciones.
                </div>
            </div>
        </div>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    var svg = document.getElementById('cicloChartSvg');
    var nodeLayer = document.getElementById('cicloNodeLayer');
    var stage = document.getElementById('cicloChartStage');
    var segmentStatus = <?=json_encode(array(
        'multiplicar' => $estadoSegmentoMultiplicar,
        'encontrar' => $estadoSegmentoEncontrar,
    ));?>;
    var actionStatus = <?=json_encode(array(
        'multiplicar_training' => $estadoAccionMultiplicar,
        'encontrar_pray' => $estadoAccionPrepararse,
        'encontrar_search' => $estadoAccionPersonasPaz,
    ));?>;
    var actionImages = {
        multiplicar_training: {
            active: <?=json_encode($imgMultiplicarAzul); ?>,
            disabled: <?=json_encode($imgMultiplicarGris); ?>
        },
        encontrar_pray: {
            active: <?=json_encode($imgEncontrarAzul); ?>,
            disabled: <?=json_encode($imgEncontrarGris); ?>,
            warning: <?=json_encode($imgEncontrarNaranja); ?>
        }
    };

    if (!svg || !nodeLayer || !stage) {
        return;
    }

    var center = 380;
    var outerRadius = 352;
    var ringInnerRadius = 300;
    var regionRadius = 300;
    var labelRadius = 326;

    var icons = {
        training: '' +
            '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.7" stroke-linecap="round" stroke-linejoin="round">' +
                '<circle cx="32" cy="12" r="5" fill="currentColor" stroke="none"></circle>' +
                '<circle cx="18" cy="31" r="4" fill="currentColor" stroke="none"></circle>' +
                '<circle cx="46" cy="31" r="4" fill="currentColor" stroke="none"></circle>' +
                '<path d="M32 18v10"></path>' +
                '<path d="M32 28l-10 6"></path>' +
                '<path d="M32 28l10 6"></path>' +
                '<path d="M18 37l-6 5"></path>' +
                '<path d="M46 37l6 5"></path>' +
                '<path d="M27 45h10"></path>' +
                '<path d="M20 23l4 2"></path>' +
                '<path d="M40 25l4-2"></path>' +
                '<path d="M24 24l4-6"></path>' +
                '<path d="M40 18l4 6"></path>' +
                '<path d="M23 54c2-4 6-6 9-6s7 2 9 6"></path>' +
            '</svg>',
        pray: '' +
            '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">' +
                '<circle cx="35" cy="12" r="5" fill="currentColor" stroke="none"></circle>' +
                '<path d="M35 18c0 8 0 10-5 15"></path>' +
                '<path d="M30 33l-8 6"></path>' +
                '<path d="M30 33l8 7"></path>' +
                '<path d="M22 39c2 8 5 12 10 12"></path>' +
                '<path d="M40 40v11"></path>' +
                '<path d="M31 52h12"></path>' +
            '</svg>',
        search: '' +
            '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.7" stroke-linecap="round" stroke-linejoin="round">' +
                '<circle cx="26" cy="28" r="17"></circle>' +
                '<path d="M38 40l13 13"></path>' +
                '<circle cx="26" cy="22" r="4" fill="currentColor" stroke="none"></circle>' +
                '<path d="M26 27v13"></path>' +
                '<path d="M18 39c3-5 6-7 8-7s5 2 8 7"></path>' +
            '</svg>',
        share: '' +
            '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.7" stroke-linecap="round" stroke-linejoin="round">' +
                '<circle cx="20" cy="27" r="4" fill="currentColor" stroke="none"></circle>' +
                '<circle cx="44" cy="27" r="4" fill="currentColor" stroke="none"></circle>' +
                '<path d="M14 39c3-4 5-6 6-6s3 2 6 6"></path>' +
                '<path d="M38 39c3-4 5-6 6-6s3 2 6 6"></path>' +
                '<path d="M30 21c4 1 7 4 8 8"></path>' +
                '<path d="M32 16c7 2 12 8 13 15"></path>' +
                '<path d="M28 26c2 0 4 1 5 3"></path>' +
            '</svg>',
        book: '' +
            '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="M19 12h18c5 0 8 3 8 8v30H27c-5 0-8-3-8-8z"></path>' +
                '<path d="M19 12c-4 1-6 4-6 8v27c0 4 3 7 7 7h25"></path>' +
                '<path d="M30 20v14"></path>' +
                '<path d="M24 27h12"></path>' +
            '</svg>',
        water: '' +
            '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.8" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="M8 24c4 0 4-4 8-4s4 4 8 4 4-4 8-4 4 4 8 4 4-4 8-4 4 4 8 4"></path>' +
                '<path d="M8 34c4 0 4-4 8-4s4 4 8 4 4-4 8-4 4 4 8 4 4-4 8-4 4 4 8 4"></path>' +
                '<path d="M8 44c4 0 4-4 8-4s4 4 8 4 4-4 8-4 4 4 8 4 4-4 8-4 4 4 8 4"></path>' +
            '</svg>',
        church: '' +
            '<svg viewBox="0 0 64 64" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">' +
                '<path d="M17 43c6 0 7-5 13-5s7 5 13 5"></path>' +
                '<path d="M20 29c4 0 5-3 9-3s5 3 9 3"></path>' +
                '<path d="M43 18c0-3 3-6 7-6s7 3 7 6c0 6-5 10-7 16-2-6-7-10-7-16z"></path>' +
                '<path d="M47 18h6"></path>' +
                '<path d="M50 15v6"></path>' +
            '</svg>'
    };

    var segments = [
        {
            id: 'multiplicar',
            title: 'MULTIPLICAR',
            share: 1,
            actions: [
                {
                    actionId: 'multiplicar_training',
                    icon: 'training',
                    lines: ['Inscriba y entrene', 'a los obreros'],
                    angle: 0,
                    radius: 170,
                    width: 136
                }
            ]
        },
        {
            id: 'encontrar',
            title: 'ENCONTRAR',
            share: 2,
            actions: [
                {
                    actionId: 'encontrar_pray',
                    icon: 'pray',
                    lines: ['Prepárese', 'y ore'],
                    angle: 58,
                    radius: 168,
                    width: 110
                },
                {
                    actionId: 'encontrar_search',
                    icon: 'search',
                    lines: ['Encuentre', 'personas de paz'],
                    angle: 102,
                    radius: 188,
                    width: 122
                }
            ]
        },
        {
            id: 'discipular',
            title: 'DISCIPULAR',
            share: 3,
            actions: [
                {
                    icon: 'share',
                    lines: ['Comparta', 'las buenas nuevas'],
                    angle: 135,
                    radius: 194,
                    width: 126
                },
                {
                    icon: 'book',
                    lines: ['Enseñe a obedecer', 'a Jesús'],
                    angle: 192,
                    radius: 160,
                    width: 126
                },
                {
                    icon: 'water',
                    lines: ['Bautice a', 'los nuevos creyentes'],
                    angle: 244,
                    radius: 194,
                    width: 126
                }
            ]
        },
        {
            id: 'establecer',
            title: 'ESTABLECER',
            share: 1,
            actions: [
                {
                    icon: 'church',
                    lines: ['Establezca', 'la iglesia'],
                    angle: 308,
                    radius: 178,
                    width: 116
                }
            ]
        }
    ];

    var totalShare = 0;
    for (var i = 0; i < segments.length; i++) {
        totalShare += segments[i].share;
    }

    var cursor = -(360 * segments[0].share / totalShare) / 2;

    function pointAt(angle, radius) {
        var radians = angle * Math.PI / 180;
        return {
            x: center + radius * Math.sin(radians),
            y: center - radius * Math.cos(radians)
        };
    }

    function angleSpan(startAngle, endAngle) {
        return (endAngle - startAngle + 360) % 360;
    }

    function wedgePath(radius, startAngle, endAngle) {
        var start = pointAt(startAngle, radius);
        var end = pointAt(endAngle, radius);
        var largeArc = angleSpan(startAngle, endAngle) > 180 ? 1 : 0;
        return 'M ' + center + ' ' + center +
            ' L ' + start.x + ' ' + start.y +
            ' A ' + radius + ' ' + radius + ' 0 ' + largeArc + ' 1 ' + end.x + ' ' + end.y +
            ' Z';
    }

    function ringPath(innerRadius, outerRadiusValue, startAngle, endAngle) {
        var outerStart = pointAt(startAngle, outerRadiusValue);
        var outerEnd = pointAt(endAngle, outerRadiusValue);
        var innerEnd = pointAt(endAngle, innerRadius);
        var innerStart = pointAt(startAngle, innerRadius);
        var largeArc = angleSpan(startAngle, endAngle) > 180 ? 1 : 0;

        return 'M ' + outerStart.x + ' ' + outerStart.y +
            ' A ' + outerRadiusValue + ' ' + outerRadiusValue + ' 0 ' + largeArc + ' 1 ' + outerEnd.x + ' ' + outerEnd.y +
            ' L ' + innerEnd.x + ' ' + innerEnd.y +
            ' A ' + innerRadius + ' ' + innerRadius + ' 0 ' + largeArc + ' 0 ' + innerStart.x + ' ' + innerStart.y +
            ' Z';
    }

    function arcPath(radius, startAngle, endAngle, clockwise) {
        var start = pointAt(startAngle, radius);
        var end = pointAt(endAngle, radius);
        var span = clockwise ? angleSpan(startAngle, endAngle) : angleSpan(endAngle, startAngle);
        var largeArc = span > 180 ? 1 : 0;
        var sweep = clockwise ? 1 : 0;
        return 'M ' + start.x + ' ' + start.y +
            ' A ' + radius + ' ' + radius + ' 0 ' + largeArc + ' ' + sweep + ' ' + end.x + ' ' + end.y;
    }

    function escapeHtml(value) {
        return value
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    var svgMarkup = '' +
        '<circle cx="' + center + '" cy="' + center + '" r="' + (ringInnerRadius - 2) + '" fill="#ffffff"></circle>';

    for (var s = 0; s < segments.length; s++) {
        var segment = segments[s];
        var segmentAngle = 360 * segment.share / totalShare;
        segment.startAngle = cursor;
        segment.endAngle = cursor + segmentAngle;
        segment.middleAngle = segment.startAngle + (segmentAngle / 2);
        cursor = segment.endAngle;

        var labelPadding = Math.min(10, segmentAngle * 0.12);
        var labelStartAngle = segment.startAngle + labelPadding;
        var labelEndAngle = segment.endAngle - labelPadding;
        var labelPath;
        if (segment.id === 'discipular') {
            labelPath = arcPath(labelRadius, labelEndAngle, labelStartAngle, 0);
        } else {
            labelPath = arcPath(labelRadius, labelStartAngle, labelEndAngle, 1);
        }

        var segmentStateClass = '';
        if (segmentStatus[segment.id] === 'disabled') {
            segmentStateClass = ' is-disabled';
        } else if (segmentStatus[segment.id] === 'partial') {
            segmentStateClass = ' is-partial';
        }

        svgMarkup += '' +
            '<g class="ciclo-segment' + segmentStateClass + '" data-segment="' + segment.id + '">' +
                '<path class="ciclo-ring" d="' + ringPath(ringInnerRadius, outerRadius, segment.startAngle, segment.endAngle) + '"></path>' +
                '<path class="ciclo-region" d="' + wedgePath(regionRadius, segment.startAngle, segment.endAngle) + '"></path>' +
                '<path class="ciclo-hit" d="' + wedgePath(outerRadius, segment.startAngle, segment.endAngle) + '"></path>' +
                '<path id="ciclo-label-' + segment.id + '" d="' + labelPath + '" fill="none"></path>' +
                '<text class="ciclo-label">' +
                    '<textPath href="#ciclo-label-' + segment.id + '" startOffset="50%" text-anchor="middle">' + segment.title + '</textPath>' +
                '</text>' +
            '</g>';
    }

    svgMarkup += '' +
        '<g class="ciclo-heart" transform="translate(380 380) scale(0.42) translate(-380 -340)">' +
            '<path d="M380 460 C309 414 252 364 252 297 C252 253 286 222 329 222 C355 222 373 234 380 253 C387 234 405 222 431 222 C474 222 508 253 508 297 C508 364 451 414 380 460 Z" fill="#d51f2d"></path>' +
            '<text x="380" y="342" text-anchor="middle" dominant-baseline="middle">Amor</text>' +
        '</g>';

    svg.innerHTML = svgMarkup;
    nodeLayer.innerHTML = '';

    for (var a = 0; a < segments.length; a++) {
        var currentSegment = segments[a];

        for (var n = 0; n < currentSegment.actions.length; n++) {
            var action = currentSegment.actions[n];
            var position = pointAt(action.angle, action.radius);
            var hasStateImage = !!actionImages[action.actionId];
            var currentActionStatus = actionStatus[action.actionId] || 'active';
            var button = document.createElement('button');
            button.type = 'button';
            button.className = 'ciclo-node' + (hasStateImage ? ' ciclo-node--image' : '');
            if (action.actionId) {
                button.className += ' ciclo-node--' + action.actionId.replace(/_/g, '-');
            }
            if (currentActionStatus === 'disabled') {
                button.className += ' is-disabled';
            } else if (currentActionStatus === 'warning') {
                button.className += ' is-warning';
            }
            button.setAttribute('data-segment', currentSegment.id);
            button.style.left = (position.x / 760 * 100) + '%';
            button.style.top = (position.y / 760 * 100) + '%';
            if (!hasStateImage) {
                button.style.width = (action.width || 148) + 'px';
            }
            button.setAttribute('aria-label', action.lines.join(' '));

            var textMarkup = '';
            for (var l = 0; l < action.lines.length; l++) {
                textMarkup += '<span>' + escapeHtml(action.lines[l]) + '</span>';
            }

            var iconMarkup = icons[action.icon];
            if (actionImages[action.actionId]) {
                var imageSet = actionImages[action.actionId];
                var imagePath = imageSet[currentActionStatus] || imageSet.active;

                if (imagePath) {
                    iconMarkup = '<img src="' + imagePath + '" alt="' + escapeHtml(action.lines.join(' ')) + '" loading="eager" decoding="sync" fetchpriority="high" onerror="this.style.display=\'none\'">';
                }
            }

            button.innerHTML = '' +
                '<span class="ciclo-node__icon">' + iconMarkup + '</span>' +
                '<span class="ciclo-node__text">' + textMarkup + '</span>';

            nodeLayer.appendChild(button);
        }
    }

    var svgSegments = svg.querySelectorAll('.ciclo-segment');
    var nodes = nodeLayer.querySelectorAll('.ciclo-node');
    var pinnedSegment = null;
    var hoveredSegment = null;
    var activeSegment = null;
    var interactiveSelector = '.ciclo-segment, .ciclo-node';

    function applyActiveSegment(segmentId) {
        if (activeSegment === segmentId) {
            return;
        }

        activeSegment = segmentId;

        for (var iSeg = 0; iSeg < svgSegments.length; iSeg++) {
            svgSegments[iSeg].classList.toggle('is-active', svgSegments[iSeg].getAttribute('data-segment') === segmentId);
        }

        for (var iNode = 0; iNode < nodes.length; iNode++) {
            nodes[iNode].classList.toggle('is-active', nodes[iNode].getAttribute('data-segment') === segmentId);
        }
    }

    function getSegmentIdFromTarget(target) {
        if (!target || typeof target.closest !== 'function') {
            return null;
        }

        var interactiveTarget = target.closest(interactiveSelector);
        if (!interactiveTarget || !stage.contains(interactiveTarget)) {
            return null;
        }

        return interactiveTarget.getAttribute('data-segment');
    }

    function refreshActiveSegment() {
        applyActiveSegment(hoveredSegment || pinnedSegment);
    }

    function updateHoveredSegment(target) {
        var nextHoveredSegment = getSegmentIdFromTarget(target);
        if (hoveredSegment === nextHoveredSegment) {
            return;
        }

        hoveredSegment = nextHoveredSegment;
        refreshActiveSegment();
    }

    stage.addEventListener('pointermove', function (event) {
        updateHoveredSegment(event.target);
    });

    stage.addEventListener('pointerleave', function () {
        hoveredSegment = null;
        refreshActiveSegment();
    });

    stage.addEventListener('click', function (event) {
        var segmentId = getSegmentIdFromTarget(event.target);

        if (!segmentId) {
            if (pinnedSegment !== null) {
                pinnedSegment = null;
                refreshActiveSegment();
            }
            return;
        }

        pinnedSegment = (pinnedSegment === segmentId) ? null : segmentId;
        hoveredSegment = segmentId;
        refreshActiveSegment();
    });

    stage.addEventListener('keydown', function (event) {
        if (event.key === 'Escape' && pinnedSegment !== null) {
            pinnedSegment = null;
            refreshActiveSegment();
        }
    });

    nodeLayer.addEventListener('focusin', function (event) {
        updateHoveredSegment(event.target);
    });

    nodeLayer.addEventListener('focusout', function (event) {
        if (nodeLayer.contains(event.relatedTarget)) {
            return;
        }

        hoveredSegment = null;
        refreshActiveSegment();
    });

    applyActiveSegment(null);
});
</script>
