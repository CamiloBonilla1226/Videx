<?php
/*******************************************
DASHBOARD - NUEVO DISEÑO ESTRATÉGICO
Archivo: graphs_dashboard2.php

KPIs GENERALES (tarjetas resumen)
GRÁFICAS:
  #1  Evolución de Asistencia (LineChart)
  #2  Embudo de Crecimiento Espiritual (BarChart horizontal)
  #3  Impacto por Actividad (ColumnChart)
  #4  Frutos por Generación (ColumnChart)
  #5  Multiplicación de Grupos (ColumnChart)

ID MENU (permiso): 22
*******************************************/

/* =========================
   HELPERS
   ========================= */
function db_first_row(DBbase_Sql $db, $sql){
    $db->query($sql);
    if($db->num_rows() > 0){
        $db->next_record();
        return $db;
    }
    return null;
}
function req_num($key){
    return (isset($_REQUEST[$key]) && soloNumeros($_REQUEST[$key]) != "") ? soloNumeros($_REQUEST[$key]) : "";
}
function req_date_or_default($key, $default){
    if(isset($_REQUEST[$key]) && soloNumeros($_REQUEST[$key]) != ""){
        return eliminarInvalidos($_REQUEST[$key]);
    }
    $_REQUEST[$key] = $default;
    return $default;
}

/* =========================
   DB
   ========================= */
$PSN  = new DBbase_Sql;
$PSN2 = new DBbase_Sql;

/* =========================
   1) AUTORIZACIÓN
   ========================= */
$sql = "SELECT idMenu
        FROM usuarios_menu_graphs
        WHERE idMenu = 22
          AND idUsuario = '".$_SESSION["id"]."'";
$PSN->query($sql);
if($PSN->num_rows() == 0){
    die("NO está autorizado a ver este dashboard.");
}

/* =========================
   2) FILTROS GLOBALES
   ========================= */
$fechaInicial = "2021-02-01";
$fechaFinal   = date("Y-m-d");

if(isset($_SESSION["perfil"]) && $_SESSION["perfil"] == 163){
    $_REQUEST["idUsuario"] = $_SESSION["id"];
}

$fechaInicial     = req_date_or_default("fechaInicial", $fechaInicial);
$fechaFinal       = req_date_or_default("fechaFinal",   $fechaFinal);
$buscar_idUsuario = req_num("idUsuario");
$empresa_paisid   = req_num("empresa_paisid");

// Agrupación temporal para evolución
$agrupar_por = (isset($_REQUEST["agrupar_por"]) && in_array($_REQUEST["agrupar_por"], ["week","month"])) ? $_REQUEST["agrupar_por"] : "month";

// Filtro base
$sqlFiltroBase = "";
if($buscar_idUsuario !== ""){
    $sqlFiltroBase .= " AND sat_reportes.idUsuario = '".$buscar_idUsuario."'";
}
if($fechaInicial !== ""){
    $sqlFiltroBase .= " AND sat_reportes.fechaReporte >= '".$fechaInicial."'";
}
if($fechaFinal !== ""){
    $sqlFiltroBase .= " AND sat_reportes.fechaReporte <= '".$fechaFinal."'";
}
if($empresa_paisid !== ""){
    $sqlFiltroBase .= " AND usuario_empresa.empresa_paisid = '".$empresa_paisid."'";
}

/* =========================
   3) KPIs GENERALES
   (solo reportes: id_grupo > 0)
   ========================= */
$kpi_asistencia  = 0;
$kpi_decisiones  = 0;
$kpi_preparando  = 0;
$kpi_bautizados  = 0;
$kpi_discipulado = 0;
$kpi_grupos      = 0;

$sql = "SELECT
            SUM(sat_reportes.asistencia_total) as asistencia_total,
            SUM(sat_reportes.desiciones)        as decisiones,
            SUM(sat_reportes.preparandose)      as preparandose,
            SUM(sat_reportes.bautizados)        as bautizados,
            SUM(sat_reportes.discipulado)       as discipulado
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE sat_reportes.id_grupo > 0 ".$sqlFiltroBase;

if($row = db_first_row($PSN, $sql)){
    $kpi_asistencia  = (int)$row->f('asistencia_total');
    $kpi_decisiones  = (int)$row->f('decisiones');
    $kpi_preparando  = (int)$row->f('preparandose');
    $kpi_bautizados  = (int)$row->f('bautizados');
    $kpi_discipulado = (int)$row->f('discipulado');
}

// Total de grupos (id_grupo = 0)
$sqlGrupos = "SELECT COUNT(*) as total
              FROM sat_reportes
              LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
              WHERE sat_reportes.id_grupo = 0 ".$sqlFiltroBase;
if($row = db_first_row($PSN, $sqlGrupos)){
    $kpi_grupos = (int)$row->f('total');
}

/* =========================
   4) GRÁFICA #1: EVOLUCIÓN DE ASISTENCIA
   ========================= */
$datosEvolucion = [];
$varErrorEvolucion = 0;

if($agrupar_por === "week"){
    $groupExpr  = "YEARWEEK(sat_reportes.fechaReporte, 1)";
    $labelExpr  = "CONCAT('Sem ', WEEK(sat_reportes.fechaReporte, 1), '/', YEAR(sat_reportes.fechaReporte))";
} else {
    $groupExpr  = "DATE_FORMAT(sat_reportes.fechaReporte, '%Y-%m')";
    $labelExpr  = "DATE_FORMAT(sat_reportes.fechaReporte, '%b %Y')";
}

$sql = "SELECT
            {$labelExpr} as periodo,
            SUM(sat_reportes.asistencia_total) as asistencia
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE sat_reportes.id_grupo > 0 ".$sqlFiltroBase."
        GROUP BY {$groupExpr}
        ORDER BY {$groupExpr} ASC";

$PSN->query($sql);
if($PSN->num_rows() > 0){
    while($PSN->next_record()){
        $datosEvolucion[] = [
            $PSN->f('periodo'),
            (int)$PSN->f('asistencia')
        ];
    }
} else {
    $varErrorEvolucion = 1;
}

/* =========================
   5) GRÁFICA #2: EMBUDO DE CRECIMIENTO
   ========================= */
$varErrorEmbudo = 0;
$datosEmbudo = [];

// Usamos los mismos KPIs ya calculados
if($kpi_asistencia > 0 || $kpi_decisiones > 0 || $kpi_bautizados > 0){
    $datosEmbudo = [
        ["Asistencia",     $kpi_asistencia],
        ["Decisiones",     $kpi_decisiones],
        ["Preparándose",   $kpi_preparando],
        ["Bautizados",     $kpi_bautizados],
        ["Discipulado",    $kpi_discipulado],
    ];
} else {
    $varErrorEmbudo = 1;
}

/* =========================
   6) GRÁFICA #3: IMPACTO POR ACTIVIDAD
   ========================= */
$varErrorActividad = 0;
$datosActividad = [];

$sql = "SELECT
            actividad.nombre_actividad,
            SUM(sat_reportes.asistencia_total) as asistencia
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        INNER JOIN actividad ON actividad.id_actividad = sat_reportes.id_actividad
        WHERE sat_reportes.id_grupo > 0
          AND sat_reportes.id_actividad IN (99, 1, 77, 8)
          ".$sqlFiltroBase."
        GROUP BY sat_reportes.id_actividad, actividad.nombre_actividad
        ORDER BY asistencia DESC";

$PSN->query($sql);
if($PSN->num_rows() > 0){
    while($PSN->next_record()){
        $datosActividad[] = [
            $PSN->f('nombre_actividad'),
            (int)$PSN->f('asistencia')
        ];
    }
} else {
    $varErrorActividad = 1;
}

/* =========================
   7) GRÁFICA #4: FRUTOS POR GENERACIÓN
   ========================= */
$varErrorFrutos = 0;
$datosFrutos = [];

$sql = "SELECT
            sat_reportes.generacionNumero,
            SUM(sat_reportes.bautizados)   as bautizados,
            SUM(sat_reportes.desiciones)   as decisiones,
            SUM(sat_reportes.discipulado)  as discipulado
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE sat_reportes.id_grupo > 0
          AND sat_reportes.generacionNumero NOT IN (77, 8)
          ".$sqlFiltroBase."
        GROUP BY sat_reportes.generacionNumero
        ORDER BY sat_reportes.generacionNumero ASC";

$PSN->query($sql);
if($PSN->num_rows() > 0){
    while($PSN->next_record()){
        $datosFrutos[] = [
            "Gen ".(int)$PSN->f('generacionNumero'),
            (int)$PSN->f('bautizados'),
            (int)$PSN->f('decisiones'),
            (int)$PSN->f('discipulado'),
        ];
    }
} else {
    $varErrorFrutos = 1;
}

/* =========================
   8) GRÁFICA #5: MULTIPLICACIÓN DE GRUPOS
   ========================= */
$varErrorMulti = 0;
$datosMulti = [];

$sql = "SELECT
            sat_reportes.generacionNumero,
            COUNT(*) as total_grupos
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE sat_reportes.id_grupo = 0
          AND sat_reportes.generacionNumero NOT IN (77, 8)
          ".$sqlFiltroBase."
        GROUP BY sat_reportes.generacionNumero
        ORDER BY sat_reportes.generacionNumero ASC";

$PSN->query($sql);
if($PSN->num_rows() > 0){
    while($PSN->next_record()){
        $datosMulti[] = [
            "Gen ".(int)$PSN->f('generacionNumero'),
            (int)$PSN->f('total_grupos')
        ];
    }
} else {
    $varErrorMulti = 1;
}
?>

<style>
/* ===== UI Profesional / Limpia / Entendible ===== */
.db-header{ margin-bottom: 10px; }

.db-card{
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 6px 18px rgba(0,0,0,.06);
  margin-bottom: 18px;
  overflow: hidden;
  transition: all .2s ease-in-out;
}
.db-card:hover{ box-shadow: 0 10px 26px rgba(0,0,0,.08); transform: translateY(-2px); }

.db-card__head{
  padding: 14px 16px;
  border-bottom: 1px solid rgba(0,0,0,.06);
  display:flex;
  align-items:center;
  justify-content:space-between;
  gap: 10px;
  flex-wrap: wrap;
}
.db-card__title{
  margin:0;
  font-size: 13px;
  font-weight: 900;
  letter-spacing: .4px;
  text-transform: uppercase;
}
.db-card__meta{
  display:flex;
  align-items:center;
  gap: 8px;
  flex-wrap: wrap;
  justify-content:flex-end;
}
.db-pill{
  display:inline-block;
  padding: 4px 10px;
  border-radius: 999px;
  background: rgba(2, 117, 216, .10);
  color: #0259a5;
  font-size: 12px;
  font-weight: 900;
}
.db-card__body{ padding: 14px 16px 18px 16px; }

.chart-box{ width:100%; height: 360px; }

.db-summary{
  max-height:46px;
  overflow:auto;
  margin-bottom:8px;
  opacity:.9;
  font-size:12px;
  line-height:1.2;
}

/* KPIs */
.db-kpis{
  display: flex;
  flex-wrap: wrap;
  gap: 12px;
  margin-bottom: 18px;
}
.db-kpi{
  flex: 1;
  min-width: 130px;
  border: 1px solid rgba(0,0,0,.08);
  border-radius: 14px;
  background: #fff;
  box-shadow: 0 6px 18px rgba(0,0,0,.06);
  padding: 14px 16px;
  position: relative;
  overflow: hidden;
  transition: all .2s ease-in-out;
}
.db-kpi:hover{ box-shadow: 0 10px 26px rgba(0,0,0,.08); transform: translateY(-2px); }
.db-kpi__label{
  font-size: 11px;
  font-weight: 700;
  letter-spacing: .5px;
  text-transform: uppercase;
  color: #888;
  margin-bottom: 6px;
}
.db-kpi__value{
  font-size: 26px;
  font-weight: 900;
  color: #1a1a1a;
  line-height: 1;
}

/* Grid gráficas */
.db-grid-2{
  display: flex;
  flex-wrap: wrap;
  gap: 0;
  margin-left: -8px;
  margin-right: -8px;
}
.db-grid-2 > .db-grid-col{
  width: 50%;
  padding-left: 8px;
  padding-right: 8px;
}

/* Responsive */
@media (max-width: 992px){
  .chart-box{ height: 340px; }
}
@media (max-width: 767px){
  .db-card{ border-radius: 12px; }
  .db-card__title{ font-size: 12px; }
  .chart-box{ height: 320px; }
  .db-summary{ max-height:64px; }
  .db-grid-2 > .db-grid-col{ width: 100%; }
  .db-kpi{ min-width: calc(50% - 6px); }

  form.form-horizontal .form-group > [class*="col-"]{
    width: 100% !important;
    max-width: 100% !important;
    padding-left: 0 !important;
    padding-right: 0 !important;
    margin-bottom: 10px;
  }
  form.form-horizontal .form-group strong{
    display:block;
    margin-bottom:4px;
  }
  form.form-horizontal select,
  form.form-horizontal input[type="date"],
  form.form-horizontal input[type="submit"]{
    width:100% !important;
  }
  form.form-horizontal .btn{
    width:100%;
  }
}
</style>

<div class="container">

<form action="index.php" method="get" name="formDashboard" class="form-horizontal">
  <input type="hidden" name="doc" value="graphs_dashboard2" />

  <div class="db-header">
    <h3 class="alert alert-info text-center" style="margin-bottom:10px;">DASHBOARD</h3>
  </div>

  <div class="cont-tit">
    <div class="hr"><hr></div>
    <div class="tit-cen"><h3>FILTROS DE BÚSQUEDA</h3></div>
    <div class="hr"><hr></div>
  </div>

  <div class="form-group">

    <div class="col-sm-4">
      <strong>Facilitador Satura:</strong>
      <select name="idUsuario" onchange="this.form.submit()" class="form-control">
        <?php if($_SESSION["perfil"] != 163){ ?>
          <option value="">Ver todos</option>
        <?php } ?>
        <?php
        $sql = "SELECT id, nombre FROM usuario WHERE tipo IN (162, 163)";
        if($_SESSION["perfil"] == 163){
            $sql .= " AND id = '".$_SESSION["id"]."'";
        }
        $sql .= " ORDER BY nombre ASC";
        $PSN2->query($sql);
        if($PSN2->num_rows() > 0){
            while($PSN2->next_record()){
                $id  = $PSN2->f('id');
                $nom = $PSN2->f('nombre');
                $sel = ($buscar_idUsuario == $id) ? 'selected="selected"' : '';
                echo '<option value="'.$id.'" '.$sel.'>'.$nom.'</option>';
            }
        }
        ?>
      </select>
    </div>

    <?php if($_SESSION["perfil"] != 163){ ?>
    <div class="col-sm-3">
      <strong>Nombre del país:</strong>
      <select name="empresa_paisid" class="form-control" onchange="this.form.submit()">
        <option value="">Sin especificar</option>
        <?php
        $sql = "SELECT id, descripcion FROM categorias WHERE idSec = 37 ORDER BY descripcion ASC";
        $PSN2->query($sql);
        if($PSN2->num_rows() > 0){
            while($PSN2->next_record()){
                $id   = $PSN2->f('id');
                $desc = $PSN2->f('descripcion');
                $sel  = ($empresa_paisid == $id) ? 'selected="selected"' : '';
                echo '<option value="'.$id.'" '.$sel.'>'.$desc.'</option>';
            }
        }
        ?>
      </select>
    </div>
    <?php } ?>

    <div class="col-sm-2">
      <strong>Fecha Inicial:</strong>
      <input type="date" name="fechaInicial" value="<?=$fechaInicial;?>" class="form-control" />
    </div>

    <div class="col-sm-2">
      <strong>Fecha Final:</strong>
      <input type="date" name="fechaFinal" value="<?=$fechaFinal;?>" class="form-control" />
    </div>

    <div class="col-sm-1"><br>
      <input type="submit" value="Filtrar" class="btn btn-success" />
    </div>

  </div>

  <div class="form-group" style="margin-top:8px;">
    <div class="col-sm-12">
      <strong>Agrupar evolución por:</strong>
      <label style="display:inline-flex; align-items:center; gap:8px; margin-left:10px;">
        <?php
          $urlBase = "index.php?doc=graphs_dashboard2";
          if($buscar_idUsuario) $urlBase .= "&idUsuario=".$buscar_idUsuario;
          if($empresa_paisid)   $urlBase .= "&empresa_paisid=".$empresa_paisid;
          $urlBase .= "&fechaInicial=".$fechaInicial."&fechaFinal=".$fechaFinal;
        ?>
        <a href="<?=$urlBase;?>&agrupar_por=month" class="btn btn-xs <?=($agrupar_por=='month'?'btn-primary':'btn-default');?>">Mes</a>
        <a href="<?=$urlBase;?>&agrupar_por=week"  class="btn btn-xs <?=($agrupar_por=='week'?'btn-primary':'btn-default');?>">Semana</a>
      </label>
    </div>
  </div>

</form>

<div class="cont-tit">
  <div class="hr"><hr></div>
  <div class="tit-cen"><h3 class="text-center">RESULTADOS</h3></div>
  <div class="hr"><hr></div>
</div>

<!-- KPIs -->
<div class="db-kpis">
  <div class="db-kpi">
    <div class="db-kpi__label">Asistencia Total</div>
    <div class="db-kpi__value"><?=number_format($kpi_asistencia);?></div>
  </div>
  <div class="db-kpi">
    <div class="db-kpi__label">Decisiones</div>
    <div class="db-kpi__value"><?=number_format($kpi_decisiones);?></div>
  </div>
  <div class="db-kpi">
    <div class="db-kpi__label">Preparándose</div>
    <div class="db-kpi__value"><?=number_format($kpi_preparando);?></div>
  </div>
  <div class="db-kpi">
    <div class="db-kpi__label">Bautizados</div>
    <div class="db-kpi__value"><?=number_format($kpi_bautizados);?></div>
  </div>
  <div class="db-kpi">
    <div class="db-kpi__label">Discipulado</div>
    <div class="db-kpi__value"><?=number_format($kpi_discipulado);?></div>
  </div>
  <div class="db-kpi">
    <div class="db-kpi__label">Total Grupos</div>
    <div class="db-kpi__value"><?=number_format($kpi_grupos);?></div>
  </div>
</div>

<!-- #1 Evolución de Asistencia (ancho completo) -->
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title">Evolución de la Asistencia</h4>
        <div class="db-card__meta">
          <span class="db-pill">por <?=($agrupar_por=='week'?'semana':'mes');?></span>
        </div>
      </div>
      <div class="db-card__body">
        <?php if($varErrorEvolucion == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">No se ha encontrado ningún registro para el rango de fechas seleccionado.</div>
        <?php } else { ?>
          <div id="chart_evolucion" class="chart-box"></div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<!-- #2 Embudo  +  #3 Impacto por Actividad -->
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title">Embudo de Crecimiento Espiritual</h4>
      </div>
      <div class="db-card__body">
        <?php if($varErrorEmbudo == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">No se ha encontrado ningún registro para el rango de fechas seleccionado.</div>
        <?php } else { ?>
          <div id="chart_embudo" class="chart-box"></div>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title">Impacto por Actividad</h4>
      </div>
      <div class="db-card__body">
        <?php if($varErrorActividad == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">No se ha encontrado ningún registro para el rango de fechas seleccionado.</div>
        <?php } else { ?>
          <div id="chart_actividad" class="chart-box"></div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<!-- #4 Frutos por Generación  +  #5 Multiplicación de Grupos -->
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title">Frutos por Generación</h4>
        <div class="db-card__meta">
          <span class="db-pill">Bautizados · Decisiones · Discipulado</span>
        </div>
      </div>
      <div class="db-card__body">
        <?php if($varErrorFrutos == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">No se ha encontrado ningún registro para el rango de fechas seleccionado.</div>
        <?php } else { ?>
          <div id="chart_frutos" class="chart-box"></div>
        <?php } ?>
      </div>
    </div>
  </div>
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title">Multiplicación de Grupos</h4>
        <div class="db-card__meta">
          <span class="db-pill">Total: <?=$kpi_grupos;?></span>
        </div>
      </div>
      <div class="db-card__body">
        <?php if($varErrorMulti == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">No se ha encontrado ningún registro para el rango de fechas seleccionado.</div>
        <?php } else { ?>
          <div id="chart_multi" class="chart-box"></div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

</div><!-- /container -->

<script type="text/javascript">
google.charts.load("current", {packages:["corechart","bar"]});
google.charts.setOnLoadCallback(drawAllCharts);

// Redibuja en resize con debounce
(function(){
  var t = null;
  function redraw(){ clearTimeout(t); t = setTimeout(drawAllCharts, 220); }
  window.addEventListener('resize', redraw);
  window.addEventListener('orientationchange', redraw);
  if('ResizeObserver' in window){
    var ro = new ResizeObserver(redraw);
    ['chart_evolucion','chart_embudo','chart_actividad','chart_frutos','chart_multi'].forEach(function(id){
      var el = document.getElementById(id); if(el) ro.observe(el);
    });
  }
})();

// Tema claro (estilo original)
var DB2_BG        = 'transparent';
var DB2_TEXT      = '#333333';
var DB2_MUTED     = '#888888';
var DB2_GRID      = '#e0e0e0';
var DB2_COLORS    = ['#016601','#00FBFF','#3D38B8','#FC0BFB','#8778ED','#8C448C','#f0a500'];

function baseTextStyle(size){ return { color: DB2_TEXT, fontName:'Arial', fontSize: size||12 }; }
function baseAxisStyle(){
  return {
    textStyle: baseTextStyle(11),
    gridlines: { color: DB2_GRID },
    minorGridlines: { color: 'transparent' },
    baselineColor: DB2_GRID
  };
}

function drawAllCharts(){
  drawEvolucion();
  drawEmbudo();
  drawActividad();
  drawFrutos();
  drawMulti();
}

/* ===== #1 Evolución de Asistencia (LineChart) ===== */
function drawEvolucion(){
  <?php if($varErrorEvolucion == 0): ?>
  var data = google.visualization.arrayToDataTable([
    ['Período','Asistencia'],
    <?php
      $rows=[];
      foreach($datosEvolucion as $r){
        $label = str_replace("'","\\'",$r[0]);
        $rows[] = "['".$label."',".(int)$r[1]."]";
      }
      echo implode(",\n",$rows);
    ?>
  ]);

  var options = {
    backgroundColor: DB2_BG,
    colors: ['#b5f23d'],
    legend: { position:'none' },
    chartArea: { width:'88%', height:'78%', backgroundColor: DB2_BG },
    lineWidth: 3,
    pointSize: 6,
    pointShape: 'circle',
    curveType: 'function',
    hAxis: Object.assign(baseAxisStyle(), { slantedText: true, slantedTextAngle: 35 }),
    vAxis: baseAxisStyle(),
    animation:{ startup:true, duration:1000, easing:'out' },
    tooltip: { textStyle: baseTextStyle(12), showColorCode: true }
  };

  var chart = new google.visualization.LineChart(document.getElementById('chart_evolucion'));
  chart.draw(data, options);
  <?php endif; ?>
}

/* ===== #2 Embudo de Crecimiento (BarChart horizontal) ===== */
function drawEmbudo(){
  <?php if($varErrorEmbudo == 0): ?>
  var data = google.visualization.arrayToDataTable([
    ['Etapa','Personas',{role:'style'},{role:'annotation'}],
    <?php
      $colores = ['#b5f23d','#4ecdc4','#f0c040','#7c6af7','#ff9f43'];
      $rows=[];
      foreach($datosEmbudo as $i=>$r){
        $label = str_replace("'","\\'",$r[0]);
        $val   = (int)$r[1];
        $col   = $colores[$i] ?? '#b5f23d';
        $rows[] = "['".$label."',".$val.",'color:".$col."','".$val."']";
      }
      echo implode(",\n",$rows);
    ?>
  ]);

  var options = {
    backgroundColor: DB2_BG,
    legend: { position:'none' },
    chartArea: { width:'72%', height:'82%', backgroundColor: DB2_BG },
    hAxis: baseAxisStyle(),
    vAxis: Object.assign(baseAxisStyle(),{ textStyle: Object.assign(baseTextStyle(12),{bold:true}) }),
    bars: 'horizontal',
    bar: { groupWidth: '58%' },
    annotations: { textStyle: baseTextStyle(12), alwaysOutside: false },
    animation:{ startup:true, duration:900, easing:'out' },
    tooltip: { textStyle: baseTextStyle(12) }
  };

  var chart = new google.visualization.BarChart(document.getElementById('chart_embudo'));
  chart.draw(data, options);
  <?php endif; ?>
}

/* ===== #3 Impacto por Actividad (ColumnChart) ===== */
function drawActividad(){
  <?php if($varErrorActividad == 0): ?>
  var data = google.visualization.arrayToDataTable([
    ['Actividad','Asistencia',{role:'style'},{role:'annotation'}],
    <?php
      $rows=[];
      foreach($datosActividad as $i=>$r){
        $label = str_replace("'","\\'",$r[0]);
        $val   = (int)$r[1];
        $col   = DB2_COLORS[$i % count(DB2_COLORS)] ?? '#b5f23d';
        // We output JS-side color via style role
        $rows[] = "['".$label."',".$val.",'color:".DB2_COLORS[$i % 7]."','".$val."']";
      }
      echo implode(",\n",$rows);
    ?>
  ]);

  // palette cycle done server-side above
  var colors = <?=json_encode(DB2_COLORS);?>;

  var options = {
    backgroundColor: DB2_BG,
    legend: { position:'none' },
    chartArea: { width:'84%', height:'76%', backgroundColor: DB2_BG },
    hAxis: Object.assign(baseAxisStyle(),{ slantedText: true, slantedTextAngle: 30 }),
    vAxis: baseAxisStyle(),
    bar: { groupWidth: '55%' },
    annotations: { textStyle: baseTextStyle(11) },
    animation:{ startup:true, duration:900, easing:'out' },
    tooltip: { textStyle: baseTextStyle(12) }
  };

  var chart = new google.visualization.ColumnChart(document.getElementById('chart_actividad'));
  chart.draw(data, options);
  <?php endif; ?>
}

/* ===== #4 Frutos por Generación (ColumnChart multi-serie) ===== */
function drawFrutos(){
  <?php if($varErrorFrutos == 0): ?>
  var data = google.visualization.arrayToDataTable([
    ['Generación','Bautizados','Decisiones','Discipulado'],
    <?php
      $rows=[];
      foreach($datosFrutos as $r){
        $label = str_replace("'","\\'",$r[0]);
        $rows[] = "['".$label."',".(int)$r[1].",".(int)$r[2].",".(int)$r[3]."]";
      }
      echo implode(",\n",$rows);
    ?>
  ]);

  var options = {
    backgroundColor: DB2_BG,
    colors: ['#7c6af7','#4ecdc4','#ff9f43'],
    legend: { position:'bottom', textStyle: baseTextStyle(11) },
    chartArea: { width:'84%', height:'70%', backgroundColor: DB2_BG },
    hAxis: baseAxisStyle(),
    vAxis: baseAxisStyle(),
    bar: { groupWidth: '60%' },
    isStacked: false,
    animation:{ startup:true, duration:900, easing:'out' },
    tooltip: { textStyle: baseTextStyle(12) }
  };

  var chart = new google.visualization.ColumnChart(document.getElementById('chart_frutos'));
  chart.draw(data, options);
  <?php endif; ?>
}

/* ===== #5 Multiplicación de Grupos (ColumnChart) ===== */
function drawMulti(){
  <?php if($varErrorMulti == 0): ?>
  var data = google.visualization.arrayToDataTable([
    ['Generación','Grupos',{role:'style'},{role:'annotation'}],
    <?php
      $rows=[];
      $palette=['#b5f23d','#f0c040','#4ecdc4','#ff9f43','#7c6af7','#ff6b6b','#38c9f7'];
      foreach($datosMulti as $i=>$r){
        $label = str_replace("'","\\'",$r[0]);
        $val   = (int)$r[1];
        $col   = $palette[$i % count($palette)];
        $rows[] = "['".$label."',".$val.",'color:".$col."','".$val."']";
      }
      echo implode(",\n",$rows);
    ?>
  ]);

  var options = {
    backgroundColor: DB2_BG,
    legend: { position:'none' },
    chartArea: { width:'84%', height:'72%', backgroundColor: DB2_BG },
    hAxis: baseAxisStyle(),
    vAxis: baseAxisStyle(),
    bar: { groupWidth: '50%' },
    annotations: { textStyle: baseTextStyle(12) },
    animation:{ startup:true, duration:900, easing:'out' },
    tooltip: { textStyle: baseTextStyle(12) }
  };

  var chart = new google.visualization.ColumnChart(document.getElementById('chart_multi'));
  chart.draw(data, options);
  <?php endif; ?>
}
</script>