<?php
/*******************************************
DASHBOARD 3 - 5 VISUALES
Archivo: graphs_dashboard3.php

#1 (DOBLE, juntos): GRAN CELEBRACIÓN (gen=8)
   - ColumnChart: Personas alcanzadas vs Actividades
   - Donut: Distribución H/M/J/N

#2: GRUPOS POR GENERACIÓN (excluye 0,77,8) - Donut

#3: PORCENTAJES DE EFECTIVIDAD (excluye 0 y 77)
   - Filtros extra SOLO aquí: meta1 y meta2

#4: ASISTENCIA MENSUAL X FACILITADOR (excluye 0,77,8)
   - Requiere facilitador seleccionado (idUsuario)
*******************************************/

/* =========================
   HELPERS
   ========================= */
function obtenerPorcentajeSeguro($cantidad, $total){
    $total = (float)$total;
    if($total <= 0) return 0;
    $porcentaje = ((float)$cantidad * 100) / $total;
    return round($porcentaje, 2);
}
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
function req_int_or_default($key, $default){
    if(isset($_REQUEST[$key]) && eliminarInvalidos($_REQUEST[$key]) != ""){
        return (int)eliminarInvalidos($_REQUEST[$key]);
    }
    $_REQUEST[$key] = $default;
    return (int)$default;
}
function req_date_or_default($key, $default){
    if(isset($_REQUEST[$key]) && soloNumeros($_REQUEST[$key]) != ""){
        return eliminarInvalidos($_REQUEST[$key]);
    }
    $_REQUEST[$key] = $default;
    return $default;
}
function build_filtros_sat($idUsuario, $fechaInicial, $fechaFinal, $paisId){
    $base = "";

    if($idUsuario !== ""){
        $base .= " AND sat_reportes.idUsuario = '".$idUsuario."'";
    }
    if($fechaInicial !== ""){
        $base .= " AND sat_reportes.fechaReporte >= '".$fechaInicial."'";
    }
    if($fechaFinal !== ""){
        $base .= " AND sat_reportes.fechaReporte <= '".$fechaFinal."'";
    }
    if($paisId !== ""){
        $base .= " AND usuario_empresa.empresa_paisid = '".$paisId."'";
    }

    $excl = $base;
    $excl .= " AND sat_reportes.generacionNumero != 0";
    $excl .= " AND sat_reportes.generacionNumero != 77";
    $excl .= " AND sat_reportes.generacionNumero != 8";

    return [$excl, $base];
}
function g3_label($k){
    switch((int)$k){
        case 1: return "Evangelismo (real)";
        case 2: return "Discipulado";
        case 3: return "Decisiones para Cristo";
        case 4: return "Bautizos";
        case 5: return "IPG Gen 1";
        case 6: return "IPG Gen 2";
        case 7: return "IPG Gen 3";
        case 8: return "Asistencia";
        default: return "Meta";
    }
}
function g3_pick_val($k, $ev_real, $dis, $dec, $bau, $ig1, $ig2, $ig3, $asis){
    switch((int)$k){
        case 1: return (int)$ev_real;
        case 2: return (int)$dis;
        case 3: return (int)$dec;
        case 4: return (int)$bau;
        case 5: return (int)$ig1;
        case 6: return (int)$ig2;
        case 7: return (int)$ig3;
        case 8: return (int)$asis;
        default: return 0;
    }
}

/* =========================
   DB
   ========================= */
$mesesNom = array("No", "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre");
$PSN  = new DBbase_Sql;
$PSN2 = new DBbase_Sql;

/* =========================
   AUTORIZACIÓN (1, 2 o 11)
   ========================= */
$sql = "SELECT idMenu
        FROM usuarios_menu_graphs
        WHERE idMenu = 23
          AND idUsuario = '".$_SESSION["id"]."'";
$PSN->query($sql);
if($PSN->num_rows() == 0){
    die("NO está autorizado a ver este dashboard.");
}

/* =========================
   FILTROS GLOBALES
   ========================= */
$fechaInicial = "2021-02-01";
$fechaFinal   = date("Y-m-d");

if(isset($_SESSION["perfil"]) && $_SESSION["perfil"] == 163){
    $_REQUEST["idUsuario"] = $_SESSION["id"];
}

$fechaInicial = req_date_or_default("fechaInicial", $fechaInicial);
$fechaFinal   = req_date_or_default("fechaFinal",   $fechaFinal);

$buscar_idUsuario = req_num("idUsuario");
$empresa_paisid   = req_num("empresa_paisid");

list($sqlFiltroExcl, $sqlFiltroBase) = build_filtros_sat($buscar_idUsuario, $fechaInicial, $fechaFinal, $empresa_paisid);

/* =========================
   FILTROS EXTRA SOLO G3
   ========================= */
$meta1 = req_int_or_default("meta1", 1);
$meta2 = req_int_or_default("meta2", 2);
if($meta1 < 1 || $meta1 > 8) $meta1 = 1;
if($meta2 < 1 || $meta2 > 8) $meta2 = 2;

/* =========================
   #1 (DOBLE): GRAN CELEBRACIÓN (gen=8, filtro BASE)
   ========================= */
$varErrorGc = 0;
$nombreGc = "GRAN CELEBRACIÓN";

$gc_asistencia = 0;
$gc_conteo = 0;
$gc_hom = 0;
$gc_muj = 0;
$gc_jov = 0;
$gc_nin = 0;

$datosGcCol = [];
$datosGcPie = [];

$sql = "SELECT
            COUNT(sat_reportes.id) as conteo,
            SUM(sat_reportes.asistencia_total) as asistencia_total,
            SUM(sat_reportes.asistencia_hom) as asistencia_hom,
            SUM(sat_reportes.asistencia_muj) as asistencia_muj,
            SUM(sat_reportes.asistencia_jov) as asistencia_jov,
            SUM(sat_reportes.asistencia_nin) as asistencia_nin
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE sat_reportes.generacionNumero = 8 ".$sqlFiltroBase;

if($row = db_first_row($PSN, $sql)){
    $gc_asistencia = (int)$row->f('asistencia_total');
    $gc_conteo     = (int)$row->f('conteo');
    $gc_hom = (int)$row->f('asistencia_hom');
    $gc_muj = (int)$row->f('asistencia_muj');
    $gc_jov = (int)$row->f('asistencia_jov');
    $gc_nin = (int)$row->f('asistencia_nin');

    if($gc_conteo <= 0 && $gc_asistencia <= 0){
        $varErrorGc = 1;
    } else {
        $datosGcCol[] = ["Personas alcanzadas", $gc_asistencia];
        $datosGcCol[] = ["Actividades", $gc_conteo];

        $datosGcPie[] = ["Hombres", $gc_hom];
        $datosGcPie[] = ["Mujeres", $gc_muj];
        $datosGcPie[] = ["Jóvenes", $gc_jov];
        $datosGcPie[] = ["Niños",   $gc_nin];
    }
} else {
    $varErrorGc = 1;
}

/* =========================
   #2: GRUPOS POR GENERACIÓN (filtro EXCL)
   ========================= */
$varErrorG2 = 0;
$nombreG2 = "GRUPOS POR GENERACIÓN";
$totalG2 = 0;
$textoG2 = "";
$datosG2 = [];

$sql = "SELECT
            sat_reportes.generacionNumero,
            COUNT(sat_reportes.id) AS conteo
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE 1 ".$sqlFiltroExcl."
        GROUP BY sat_reportes.generacionNumero
        ORDER BY sat_reportes.generacionNumero ASC";

$PSN->query($sql);
if($PSN->num_rows() > 0){
    while($PSN->next_record()){
        $gen = (int)$PSN->f('generacionNumero');
        $cnt = (int)$PSN->f('conteo');

        $textoG2 .= "| GENERACIÓN ".$gen.": ".$cnt." ";
        $datosG2[] = ["GENERACIÓN ".$gen, $cnt];
        $totalG2 += $cnt;
    }
    if($totalG2 <= 0) $varErrorG2 = 1;
} else {
    $varErrorG2 = 1;
}

/* =========================
   #3: PORCENTAJES DE EFECTIVIDAD (meta1/meta2 SOLO aquí)
   ========================= */
$varErrorG3 = 0;
$nombreG3 = "PORCENTAJES DE EFECTIVIDAD";
$nombre_actual_g3 = "";

$anho_actual = date("Y", strtotime($fechaInicial));

$sqlFiltroG3_limpio = $sqlFiltroBase; // permite gen 77 para evangelismo real
$sqlFiltroG3        = $sqlFiltroBase." AND sat_reportes.generacionNumero != 0 AND sat_reportes.generacionNumero != 77";

$idUsuarioMetas = 0;
if($buscar_idUsuario !== "") $idUsuarioMetas = (int)$buscar_idUsuario;
if(isset($_SESSION["perfil"]) && $_SESSION["perfil"] == 163) $idUsuarioMetas = (int)$_SESSION["id"];

if($idUsuarioMetas == 0){
    $nombreG3 .= " (SATURA NACIONES)";
} else {
    $sql = "SELECT nombre FROM usuario WHERE id = '".$idUsuarioMetas."' LIMIT 1";
    if($row = db_first_row($PSN, $sql)){
        $nombre_actual_g3 = " ".$row->f('nombre');
    }
}

$satura_evangelismo = 0;
$satura_discipulado = 0;
$satura_bautizos = 0;

$sql = "SELECT
            SUM(sat_reportes.asistencia_total) as evangelismo,
            SUM(sat_reportes.discipulado) as discipulado,
            SUM(sat_reportes.bautizadosPeriodo) as bautizos
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE 1 ".$sqlFiltroG3;

if($row = db_first_row($PSN, $sql)){
    $satura_evangelismo = (int)$row->f('evangelismo');
    $satura_discipulado = (int)$row->f('discipulado');
    $satura_bautizos    = (int)$row->f('bautizos');
} else {
    $varErrorG3 = 1;
}

$satura_iglesias  = 0;
$satura_iglesias2 = 0;
$satura_iglesias3 = 0;

$sqlIglesias = "SELECT COUNT(sat_reportes.id) as iglesias
                FROM sat_reportes
                LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
                WHERE 1 ".$sqlFiltroG3;

if($varErrorG3 == 0){
    if($row = db_first_row($PSN, $sqlIglesias." AND sat_reportes.generacionNumero = 1")) $satura_iglesias = (int)$row->f('iglesias');
    if($row = db_first_row($PSN, $sqlIglesias." AND sat_reportes.generacionNumero = 2")) $satura_iglesias2 = (int)$row->f('iglesias');
    if($row = db_first_row($PSN, $sqlIglesias." AND sat_reportes.generacionNumero = 3")) $satura_iglesias3 = (int)$row->f('iglesias');
}

$satura_evangelismo_real = 0;
$sql = "SELECT SUM(sat_reportes.asistencia_total) as evangelismo
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE sat_reportes.generacionNumero = 77 ".$sqlFiltroG3_limpio;
if($varErrorG3 == 0){
    if($row = db_first_row($PSN, $sql)) $satura_evangelismo_real = (int)$row->f('evangelismo');
}

$desiciones = 0;
$sql = "SELECT SUM(sat_reportes.desiciones) as desiciones
        FROM sat_reportes
        LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
        WHERE 1 ".$sqlFiltroG3;
if($varErrorG3 == 0){
    if($row = db_first_row($PSN, $sql)) $desiciones = (int)$row->f('desiciones');
}

$g3_meta1_valor = 0;
$g3_rows = [];

if($varErrorG3 == 0){
    $g3_meta1_valor = g3_pick_val($meta1, $satura_evangelismo_real, $satura_discipulado, $desiciones, $satura_bautizos, $satura_iglesias, $satura_iglesias2, $satura_iglesias3, $satura_evangelismo);
    $g3_rows[] = [g3_label($meta1), $g3_meta1_valor];

    $val2 = g3_pick_val($meta2, $satura_evangelismo_real, $satura_discipulado, $desiciones, $satura_bautizos, $satura_iglesias, $satura_iglesias2, $satura_iglesias3, $satura_evangelismo);
    $pct2 = obtenerPorcentajeSeguro($val2, max(1, $g3_meta1_valor));
    $g3_rows[] = [$pct2."% ".g3_label($meta2), $val2];

    if($g3_meta1_valor <= 0 && $val2 <= 0) $varErrorG3 = 1;
}

/* =========================
   #4: ASISTENCIA MENSUAL X FACILITADOR (filtro EXCL)
   - REQUIERE idUsuario (facilitador)
   - Serie única: SUM(asistencia_total) por mes
   ========================= */
$varErrorG4 = 0;
$nombreG4 = "ASISTENCIA MENSUAL X FACILITADOR";
$totalG4 = 0;
$datosG4 = []; // [ ['Mes', valor], ... ]

if($buscar_idUsuario == ""){
    $varErrorG4 = 2; // estado: falta facilitador
} else {
    $sql = "SELECT
                YEAR(sat_reportes.fechaReporte) as anho,
                MONTH(sat_reportes.fechaReporte) as mes,
                SUM(sat_reportes.asistencia_total) as total_mes
            FROM sat_reportes
            LEFT JOIN usuario_empresa ON usuario_empresa.idUsuario = sat_reportes.idUsuario
            WHERE 1 ".$sqlFiltroExcl."
            GROUP BY anho, mes
            ORDER BY anho ASC, mes ASC";

    $PSN->query($sql);
    if($PSN->num_rows() > 0){
        while($PSN->next_record()){
            $anho = (int)$PSN->f('anho');
            $mes  = (int)$PSN->f('mes');
            $val  = (int)$PSN->f('total_mes');

            $labelMes = $mesesNom[$mes]." ".$anho;
            $datosG4[] = [$labelMes, $val];
            $totalG4 += $val;
        }
        if($totalG4 <= 0) $varErrorG4 = 1;
    } else {
        $varErrorG4 = 1;
    }
}
?>

<style>
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

.duo-grid{ display:flex; flex-wrap:wrap; margin-left:-8px; margin-right:-8px; }
.duo-col{ padding-left:8px; padding-right:8px; width:50%; }
.duo-title{
  margin: 0 0 8px 0;
  font-weight: 900;
  font-size: 12px;
  text-transform: uppercase;
  letter-spacing: .3px;
}
.chart-duo{ height: 360px; }

@media (max-width: 992px){
  .chart-box{ height: 340px; }
  .chart-duo{ height: 340px; }
}
@media (max-width: 767px){
  .db-card{ border-radius: 12px; }
  .db-card__title{ font-size: 12px; }
  .chart-box{ height: 320px; }
  .chart-duo{ height: 320px; }
  .duo-col{ width:100%; }
  .duo-col + .duo-col{ margin-top: 14px; }

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
  form.form-horizontal .btn{ width:100%; }
}
</style>

<div class="container">

<form action="index.php" method="get" name="formDashboard3" class="form-horizontal">
  <input type="hidden" name="doc" value="graphs_dashboard3" />

  <div class="db-header">
    <h3 class="alert alert-info text-center" style="margin-bottom:10px;">DASHBOARD 3</h3>
  </div>

  <div class="cont-tit">
    <div class="hr"><hr></div>
    <div class="tit-cen"><h3>FILTROS DE BÚSQUEDA</h3></div>
    <div class="hr"><hr></div>
  </div>

  <div class="form-group">
    <div class="col-sm-3">
      <strong>Facilitador Satura:</strong>
      <select name="idUsuario" onchange="this.form.submit()" class="form-control">
        <?php if($_SESSION["perfil"] != 163){ ?>
          <option value="">Ver todos</option>
        <?php } ?>
        <?php
        $sql = "SELECT id, nombre
                FROM usuario
                WHERE tipo IN (162, 163) ";
        if($_SESSION["perfil"] == 163){
          $sql .= " AND id = '".$_SESSION["id"]."' ";
        }
        $sql .= " ORDER BY nombre asc";
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
      <small style="display:block; margin-top:6px; opacity:.75;">
        * Para la gráfica de <b>Asistencia mensual</b> debes seleccionar un facilitador.
      </small>
    </div>

    <?php if($_SESSION["perfil"] != 163){ ?>
    <div class="col-sm-3">
      <strong>Nombre del país:</strong>
      <select name="empresa_paisid" class="form-control" onchange="this.form.submit()">
        <option value="">Sin especificar</option>
        <?php
        $sql = "SELECT id, descripcion
                FROM categorias
                WHERE idSec = 37
                ORDER BY descripcion asc";
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
      <input type="date" name="fechaInicial" id="fechaInicial" value="<?=$fechaInicial;?>" class="form-control" />
    </div>

    <div class="col-sm-2">
      <strong>Fecha Final:</strong>
      <input type="date" name="fechaFinal" id="fechaFinal" value="<?=$fechaFinal;?>" class="form-control" />
    </div>

    <div class="col-sm-2">
      <strong>Meta 1 / Meta 2 (solo Efectividad):</strong>
      <div style="display:flex; gap:8px;">
        <select name="meta1" class="form-control" onchange="this.form.submit()">
          <?php for($i=1;$i<=8;$i++){ $sel = ($meta1 == $i) ? 'selected="selected"' : ''; ?>
            <option value="<?=$i;?>" <?=$sel;?>><?=g3_label($i);?></option>
          <?php } ?>
        </select>
        <select name="meta2" class="form-control" onchange="this.form.submit()">
          <?php for($i=1;$i<=8;$i++){ $sel = ($meta2 == $i) ? 'selected="selected"' : ''; ?>
            <option value="<?=$i;?>" <?=$sel;?>><?=g3_label($i);?></option>
          <?php } ?>
        </select>
      </div>
    </div>

    <div class="col-sm-12" style="margin-top:10px;">
      <input type="submit" value="Filtrar" class="btn btn-success" />
    </div>
  </div>
</form>

<div class="cont-tit">
  <div class="hr"><hr></div>
  <div class="tit-cen"><h3 class="text-center">RESULTADOS</h3></div>
  <div class="hr"><hr></div>
</div>

<!-- #1 BLOQUE DOBLE -->
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title"><?=$nombreGc;?></h4>
        <div class="db-card__meta">
          <?php if($varErrorGc == 0){ ?>
            <span class="db-pill">Personas: <?=$gc_asistencia;?></span>
            <span class="db-pill">Actividades: <?=$gc_conteo;?></span>
          <?php } ?>
        </div>
      </div>
      <div class="db-card__body">
        <?php if($varErrorGc == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">
            No se ha encontrado ningún registro para el rango de fechas seleccionado.
          </div>
        <?php } else { ?>
          <div class="duo-grid">
            <div class="duo-col">
              <div class="duo-title">Total (Personas vs Actividades)</div>
              <div id="chart_gc_col" class="chart-box chart-duo"></div>
            </div>
            <div class="duo-col">
              <div class="duo-title">Distribución (H/M/J/N)</div>
              <div id="chart_gc_pie" class="chart-box chart-duo"></div>
            </div>
          </div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<!-- FILA: #2 y #3 -->
<div class="row">
  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title"><?=$nombreG2;?></h4>
        <div class="db-card__meta">
          <?php if($varErrorG2 == 0){ ?><span class="db-pill">Total: <?=$totalG2;?></span><?php } ?>
        </div>
      </div>
      <div class="db-card__body">
        <?php if($varErrorG2 == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">
            No se ha encontrado ningún registro para el rango de fechas seleccionado.
          </div>
        <?php } else { ?>
          <div style="margin-bottom:8px; opacity:.9; font-size:12px; line-height:1.2;"><?=$textoG2;?></div>
          <div id="chart_g2" class="chart-box"></div>
        <?php } ?>
      </div>
    </div>
  </div>

  <div class="col-lg-6 col-md-6 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title"><?=$nombreG3.$nombre_actual_g3;?></h4>
        <div class="db-card__meta">
          <?php if($varErrorG3 == 0){ ?>
            <span class="db-pill"><?=g3_label($meta1);?>: <?=$g3_meta1_valor;?></span>
          <?php } ?>
        </div>
      </div>
      <div class="db-card__body">
        <?php if($varErrorG3 == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">
            No se ha encontrado ningún registro o no hay base para calcular.
          </div>
        <?php } else { ?>
          <div style="margin-bottom:8px; opacity:.9; font-size:12px;">
            Comparación: <b><?=g3_label($meta2);?></b> como porcentaje relativo a <b><?=g3_label($meta1);?></b>.
          </div>
          <div id="chart_g3" class="chart-box"></div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

<!-- #4 FULL WIDTH -->
<div class="row">
  <div class="col-lg-12 col-md-12 col-sm-12">
    <div class="db-card">
      <div class="db-card__head">
        <h4 class="db-card__title"><?=$nombreG4;?></h4>
        <div class="db-card__meta">
          <?php if($varErrorG4 == 0){ ?><span class="db-pill">Total: <?=$totalG4;?></span><?php } ?>
        </div>
      </div>
      <div class="db-card__body">
        <?php if($varErrorG4 == 2){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">
            Debes seleccionar un facilitador para ver esta gráfica.
          </div>
        <?php } else if($varErrorG4 == 1){ ?>
          <div class="alert alert-warning text-center" style="margin-bottom:0;">
            No se ha encontrado ningún registro para el rango de fechas seleccionado.
          </div>
        <?php } else { ?>
          <div style="margin-bottom:8px; opacity:.9; font-size:12px;">
            Muestra la <b>asistencia total</b> por mes para el facilitador seleccionado.
          </div>
          <div id="chart_g4" class="chart-box"></div>
        <?php } ?>
      </div>
    </div>
  </div>
</div>

</div><!-- /container -->

<script type="text/javascript">
google.charts.load("current", {packages:["corechart"]});
google.charts.setOnLoadCallback(drawAllCharts);

(function(){
  var resizeTimer = null;
  window.addEventListener('resize', function(){
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(drawAllCharts, 220);
  });
  window.addEventListener('orientationchange', function(){
    clearTimeout(resizeTimer);
    resizeTimer = setTimeout(drawAllCharts, 220);
  });
})();

(function(){
  if('ResizeObserver' in window){
    var ro = new ResizeObserver(function(){
      if(window.__dbRoTimer) clearTimeout(window.__dbRoTimer);
      window.__dbRoTimer = setTimeout(drawAllCharts, 220);
    });
    ['chart_gc_col','chart_gc_pie','chart_g2','chart_g3','chart_g4'].forEach(function(id){
      var el = document.getElementById(id);
      if(el) ro.observe(el);
    });
  }
})();

function drawAllCharts(){
  drawGranCelebracion();
  drawG2();
  drawG3();
  drawG4();
}

/* ===== #1 BLOQUE DOBLE ===== */
function drawGranCelebracion(){
  <?php if($varErrorGc == 0){ ?>
  var dataCol = google.visualization.arrayToDataTable([
    ['Tipo', 'Cantidad', { role:'annotation' }],
    <?php
      $rows = [];
      foreach($datosGcCol as $r){
        $label = str_replace("'", "\\'", $r[0]);
        $val = (int)$r[1];
        $rows[] = "['".$label."', ".$val.", '".number_format($val, 0, ",", ".")."']";
      }
      echo implode(",\n    ", $rows);
    ?>
  ]);

  var elCol = document.getElementById('chart_gc_col');
  var w = (elCol && elCol.clientWidth) ? elCol.clientWidth : 700;
  var isMobile = (w <= 480);

  var optionsCol = {
    animation:{ startup:true, duration:1200, easing:'out' },
    legend: { position: 'none' },
    bar: { groupWidth: "70%" },
    chartArea: isMobile ? { width:'84%', height:'74%' } : { width:'80%', height:'78%' },
    vAxis: { title: 'Cantidad', minValue: 0 },
    hAxis: { textStyle: { fontSize: isMobile ? 10 : 12 } },
    annotations: { textStyle: { fontSize: isMobile ? 10 : 12 } }
  };

  new google.visualization.ColumnChart(elCol).draw(dataCol, optionsCol);

  var dataPie = google.visualization.arrayToDataTable([
    ['Tipo', 'Cantidad'],
    <?php
      $rows = [];
      foreach($datosGcPie as $r){
        $label = str_replace("'", "\\'", $r[0]);
        $rows[] = "['".$label."', ".(int)$r[1]."]";
      }
      echo implode(",\n    ", $rows);
    ?>
  ]);

  var optionsPie = {
    pieHole: 0.45,
    sliceVisibilityThreshold: 0,
    legend: { position: 'bottom' },
    chartArea: { width: '94%', height: '80%' },
    tooltip: { text: 'both' }
  };

  new google.visualization.PieChart(document.getElementById('chart_gc_pie')).draw(dataPie, optionsPie);
  <?php } ?>
}

/* ===== #2 ===== */
function drawG2(){
  <?php if($varErrorG2 == 0){ ?>
  var data = google.visualization.arrayToDataTable([
    ['Generación', 'Grupos'],
    <?php
      $rows = [];
      foreach($datosG2 as $r){
        $label = str_replace("'", "\\'", $r[0]);
        $rows[] = "['".$label."', ".(int)$r[1]."]";
      }
      echo implode(",\n    ", $rows);
    ?>
  ]);

  var el = document.getElementById('chart_g2');
  if(!el) return;

  var w = el.clientWidth || 600;
  var isMobile = (w <= 480);

  var options = {
    pieHole: 0.45,
    sliceVisibilityThreshold: 0,
    legend: { position: 'bottom', textStyle: { fontSize: isMobile ? 10 : 12 } },
    chartArea: isMobile ? { width:'96%', height:'78%' } : { width:'94%', height:'80%' },
    tooltip: { text: 'both' }
  };

  new google.visualization.PieChart(el).draw(data, options);
  <?php } ?>
}

/* ===== #3 ===== */
function drawG3(){
  <?php if($varErrorG3 == 0){ ?>
  var data = google.visualization.arrayToDataTable([
    ['Nombre', 'Valor', {role:'annotation'}],
    <?php
      $rows = [];
      foreach($g3_rows as $r){
        $label = str_replace("'", "\\'", $r[0]);
        $val = (int)$r[1];
        $rows[] = "['".$label."', ".$val.", '".number_format($val, 0, ",", ".")."']";
      }
      echo implode(",\n    ", $rows);
    ?>
  ]);

  var el = document.getElementById('chart_g3');
  if(!el) return;

  var w = el.clientWidth || 600;
  var isMobile = (w <= 480);

  var options = {
    animation:{ startup:true, duration:1200, easing:'out' },
    legend: { position: 'none' },
    bar: { groupWidth: isMobile ? "70%" : "62%" },
    chartArea: isMobile ? { width:'86%', height:'74%' } : { width:'82%', height:'78%' },
    vAxis: { title: 'Valor', minValue: 0 },
    hAxis: { textStyle: { fontSize: isMobile ? 10 : 12 } },
    annotations: { textStyle: { fontSize: isMobile ? 10 : 12 } }
  };

  new google.visualization.ColumnChart(el).draw(data, options);
  <?php } ?>
}

/* ===== #4 ===== */
function drawG4(){
  <?php if($varErrorG4 == 0){ ?>
  var data = google.visualization.arrayToDataTable([
    ['Mes', 'Asistencia'],
    <?php
      $rows = [];
      foreach($datosG4 as $r){
        $label = str_replace("'", "\\'", $r[0]);
        $rows[] = "['".$label."', ".(int)$r[1]."]";
      }
      echo implode(",\n    ", $rows);
    ?>
  ]);

  var el = document.getElementById('chart_g4');
  if(!el) return;

  var w = el.clientWidth || 700;
  var isMobile = (w <= 480);

  var options = {
    animation:{ startup:true, duration:1200, easing:'out' },
    curveType: 'none',
    legend: { position: 'bottom', textStyle: { fontSize: isMobile ? 10 : 12 } },
    chartArea: isMobile ? { width:'92%', height:'70%' } : { width:'90%', height:'74%' },
    hAxis: { textStyle: { fontSize: isMobile ? 10 : 12 }, slantedText: true, slantedTextAngle: isMobile ? 45 : 30 },
    vAxis: { minValue: 0 }
  };

  new google.visualization.LineChart(el).draw(data, options);
  <?php } ?>
}
</script>