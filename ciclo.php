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
              AND nombreGrupo_txt IN (
                    SELECT nombreGrupo_txt
                    FROM sat_reportes
                    WHERE idUsuario = '" . $buscar_idUsuario . "'
                      AND nombreGrupo_txt IS NOT NULL
                      AND TRIM(nombreGrupo_txt) <> ''
                    GROUP BY nombreGrupo_txt
                    HAVING COUNT(*) > 1
              )
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

$totalReportes = 0;
$primerReporte = '';
$ultimoReporte = '';
$totalFacilitadoresConDatos = 0;

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
?>

<style>
    .ciclo-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 15px 35px;
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

    .ciclo-muted {
        color: #607284;
        font-size: 14px;
    }

    .ciclo-placeholder {
        min-height: 220px;
        display: flex;
        align-items: center;
        justify-content: center;
        text-align: center;
        border: 2px dashed #c8d6e5;
        border-radius: 14px;
        background: linear-gradient(180deg, #fbfdff 0%, #f3f8fc 100%);
        padding: 24px;
    }

    .ciclo-placeholder strong {
        display: block;
        margin-bottom: 10px;
        color: #213547;
        font-size: 20px;
    }

    .ciclo-actions {
        display: flex;
        align-items: flex-end;
        gap: 10px;
        flex-wrap: wrap;
        flex: 0 1 auto;
        padding-top: 24px;
    }

    .ciclo-actions .btn {
        min-width: 120px;
    }

    @media (max-width: 767px) {
        .ciclo-actions {
            padding-top: 0;
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
            <h3>Espacio de la nueva grafica</h3>
        </div>

        <div class="ciclo-card__body">
            <div class="ciclo-placeholder">
                <div>
                    <?php if ($requiereSeleccionFacilitador) { ?>
                        <strong>Seleccion de facilitador requerida</strong>
                        <div class="ciclo-muted">
                            La nueva grafica se habilitara cuando el admin elija un facilitador especifico.
                        </div>
                    <?php } else { ?>
                        <strong>Filtros listos</strong>
                        <div class="ciclo-muted">
                            El siguiente paso es conectar aqui la nueva grafica usando <code>$sqlFiltroUsuario</code> y <code>$sqlFiltroGrupo</code>.
                        </div>
                    <?php } ?>
                </div>
            </div>
        </div>
    </div>
</div>
