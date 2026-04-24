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

function ciclo_build_filtro_sat($idUsuario)
{
    $sqlFiltro = '';

    if ($idUsuario !== '') {
        $sqlFiltro .= " AND sat_reportes.idUsuario = '" . $idUsuario . "'";
    }

    return $sqlFiltro;
}

$PSN = new DBbase_Sql;
$PSN2 = new DBbase_Sql;
$PSN3 = new DBbase_Sql;

if ($_SESSION['perfil'] == 163) {
    $_REQUEST['idUsuario'] = $_SESSION['id'];
}

$buscar_idUsuario = ciclo_req_num('idUsuario');
$sqlFiltroUsuario = ciclo_build_filtro_sat($buscar_idUsuario);

$usuarios = array();
$nombreUsuarioSeleccionado = 'Todos los facilitadores';

$sql = "SELECT id, nombre
        FROM usuario
        WHERE tipo IN (162, 163) AND acceso = 1";

if ($_SESSION['perfil'] == 163) {
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

$totalReportes = 0;
$primerReporte = '';
$ultimoReporte = '';
$totalFacilitadoresConDatos = 0;

$sql = "SELECT
            COUNT(sat_reportes.id) AS totalReportes,
            COUNT(DISTINCT sat_reportes.idUsuario) AS totalFacilitadores,
            MIN(sat_reportes.fechaReporte) AS primerReporte,
            MAX(sat_reportes.fechaReporte) AS ultimoReporte
        FROM sat_reportes
        WHERE 1 " . $sqlFiltroUsuario;

$PSN3->query($sql);
if ($PSN3->next_record()) {
    $totalReportes = (int)$PSN3->f('totalReportes');
    $totalFacilitadoresConDatos = (int)$PSN3->f('totalFacilitadores');
    $primerReporte = $PSN3->f('primerReporte');
    $ultimoReporte = $PSN3->f('ultimoReporte');
}
?>

<style>
    .ciclo-wrap {
        max-width: 1200px;
        margin: 0 auto;
        padding: 20px 15px 35px;
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
        align-items: end;
        gap: 10px;
        flex-wrap: wrap;
    }

    .ciclo-actions .btn {
        min-width: 120px;
    }
</style>

<div class="ciclo-wrap">
    <div class="ciclo-hero">
        <h2>Grafica de ciclo</h2>
        <p>Esta vista queda preparada para construir la nueva grafica sobre <code>sat_reportes</code>. Por ahora el filtro por <code>idUsuario</code> ya aplica tanto al formulario como a las consultas base.</p>
    </div>

    <form action="index.php" method="get" name="formCiclo" class="form-horizontal ciclo-card">
        <input type="hidden" name="doc" value="ciclo" />

        <div class="ciclo-card__head">
            <h3>Filtro inicial</h3>
        </div>

        <div class="ciclo-card__body">
            <div class="form-group">
                <div class="col-sm-5">
                    <strong>Facilitador Satura:</strong>
                    <select name="idUsuario" onchange="this.form.submit()" class="form-control">
                        <?php if ($_SESSION['perfil'] != 163) { ?>
                            <option value="">Ver todos</option>
                        <?php } ?>

                        <?php foreach ($usuarios as $usuario) { ?>
                            <option value="<?=$usuario['id']; ?>" <?php if ((string)$buscar_idUsuario === (string)$usuario['id']) { ?>selected="selected"<?php } ?>>
                                <?=$usuario['nombre']; ?>
                            </option>
                        <?php } ?>
                    </select>
                </div>

                <div class="col-sm-3">
                    <strong>Filtro activo:</strong>
                    <div class="form-control" style="background:#f8fafc;"><?=$nombreUsuarioSeleccionado; ?></div>
                </div>

                <div class="col-sm-4 ciclo-actions">
                    <div>
                        <input type="submit" value="Filtrar" class="btn btn-success" />
                    </div>
                    <?php if ($_SESSION['perfil'] != 163) { ?>
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
            <h3>Validacion del filtro</h3>
        </div>

        <div class="ciclo-card__body">
            <div class="ciclo-grid">
                <div class="ciclo-stat">
                    <span class="ciclo-stat__label">Registros encontrados</span>
                    <span class="ciclo-stat__value"><?=$totalReportes; ?></span>
                </div>

                <div class="ciclo-stat">
                    <span class="ciclo-stat__label">Facilitadores con datos</span>
                    <span class="ciclo-stat__value"><?=$totalFacilitadoresConDatos; ?></span>
                </div>

                <div class="ciclo-stat">
                    <span class="ciclo-stat__label">Primer reporte</span>
                    <span class="ciclo-stat__value"><?=($primerReporte != '' ? $primerReporte : 'Sin datos'); ?></span>
                </div>

                <div class="ciclo-stat">
                    <span class="ciclo-stat__label">Ultimo reporte</span>
                    <span class="ciclo-stat__value"><?=($ultimoReporte != '' ? $ultimoReporte : 'Sin datos'); ?></span>
                </div>
            </div>
        </div>
    </div>

    <div class="ciclo-card">
        <div class="ciclo-card__head">
            <h3>Espacio de la nueva grafica</h3>
        </div>

        <div class="ciclo-card__body">
            <div class="ciclo-placeholder">
                <div>
                    <strong>Filtro por idUsuario listo</strong>
                    <div class="ciclo-muted">
                        El siguiente paso es conectar aqui la nueva grafica usando la variable <code>$sqlFiltroUsuario</code>.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
