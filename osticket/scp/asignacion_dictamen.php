<?php

$sql_prueba = "CREATE TABLE IF NOT EXISTS ost_dictaminacion_asignaciones(
	id_Asignacion INT AUTO_INCREMENT PRIMARY KEY, id_ticket INT NOT NULL, id_staff INT NOT NULL) ENGINE=InnoDB;";

$sql = "DROP TABLE IF EXISTS ost_dictaminacion_asignaciones";



/*$sql = 'CREATE TABLE IF NOT EXISTS '.TABLE_PREFIX."_search (
 *            `object_type` varchar(8) not null,
 *            `object_id` int(11) unsigned not null,
 *            `title` text collate utf8_general_ci,
 *            `content` text collate utf8_general_ci,
 *            primary key `object` (`object_type`, `object_id`),
 *            fulltext key `search` (`title`, `content`)
 *        ) $engine CHARSET=utf8";*/
//$resultado=db_query($sql_prueba);
/*require_once(INCLUDE_DIR.'class.config.php');
 * class Tabla extends Config{
 * static function crearTabla(){
 *	$sql = "SELECT COUNT(*) FROM information_schema.tables WHERE table_schema='information_schema' AND table_name='INNODB_FT_CONFIG'";
 *	$mysql156 = db_result(db_query($sql));
 *
 *	$sql = "SHOW STATUS LIKE 'wsrep_local_state'";
 *	$galera = db_result(db_query($sql));
 *
 *	if($galera && !$mysql156){
 *		throw new Exception('Galera cannot be used with MyISAM');
 *	}
 *	$engine = $galera ? 'InnoDB' : ($mysql156 ? '' : 'MyISAM');
 *
 *
 *	if($engine){
 *			$engine = 'ENGINE=' . $engine;
 *	}
 *
 *	$sql_prueba = 'CREATE TABLE IF NOT EXISTS' . TABLE_PREFIX .  "dictaminacion_asignaciones(
 *	`id_Asignacion` INT AUTO_INCREMENT NOT NULL,
 *	`id_ticket` INT NOT NULL,
 *	`id_staff` INT NOT NULL,
 *	PRIMARY KEY (`id_Asignacion`)
 *	) $engine CHARSET=utf8;";
 *
 *	if(!db_query($sql_prueba)){
 *		return false
 *	}
 *
 *	return true;
 * }
 * }*/



include ('admin.inc.php');

$nav->setTabActive('manage');

require_once(STAFFINC_DIR.'header.inc.php');

//Tabla::crearTabla();

$est = 0;
global $est;

if($_SERVER['REQUEST_METHOD']=='GET'){
	$ticket_id = intval($_GET['id']);
	global $ticket_id;

}

if (isset($_GET['idEstado'])) {
	$estado_id= intval($_GET['idEstado']);
	global $estado_id;
}


$sql = "SELECT * FROM ost_ticket WHERE ticket_id = $ticket_id";
$res = db_query($sql);

if($ticket = db_fetch_array($res)){
	echo "<h3>Asignación del ticket #".$ticket['number']."</h3>";
}


$limite = 2;
global $limite;
if($estado_id == 2){
	$limite = 3;
}

if($_SERVER['REQUEST_METHOD']=='POST'){
	$ticket_id = intval($_POST['ticket_id']);
	$id_estado = intval($_POST['estado_id']);
	$id_anterior = $_POST['anterior'];

	$agentes_seleccionados=$_POST['opciones'];
	$agentes_anteriores = $_POST['anteriores'];
	if($id_estado == 3) {
		foreach($agentes_seleccionados as $opcion) {
			$sql_verificar = "SELECT id_asignacion FROM ost_dictaminacion_asignaciones WHERE id_ticket = $ticket_id AND id_staff = $opcion";
			$res_verificar = db_query($sql_verificar);

			if(db_num_rows($res_verificar) > 0) {
				$fila_asignacion = db_fetch_array($res_verificar);
				$id_asignacion = $fila_asignacion['id_asignacion'];
				$sql_actualizar = "UPDATE ost_dictaminacion_asignaciones SET id_staff = $opcion WHERE id_asignacion = $id_asignacion";
				db_query($sql_actualizar);
				echo "<script>console.log('Asignación actualizada para el agente $opcion');</script>";
			}else {
				$sql_insertar = "INSERT INTO ost_dictaminacion_asignaciones (id_ticket, id_staff) VALUES ($ticket_id, $opcion)";
				db_query($sql_insertar);
				echo "<script>console.log('Nueva asignación guardada para el agente $opcion');</script>";
			}
		}
		foreach($agentes_anteriores as $anterior) {
			if(!in_array($anterior, $agentes_seleccionados)) {
				foreach($id_anterior as $ant) {
					if($ant != $anterior){
						$sql_eliminar = "DELETE FROM ost_dictaminacion_asignaciones WHERE id_ticket = $ticket_id AND id_staff = $anterior";
						db_query($sql_eliminar);
						echo "<script>console.log('Agente $anterior eliminado de las asignaciones');</script>";
					}
				}
			}
		}
	} else{
		foreach($agentes_seleccionados as $opcion) {
			$stmt = db_query("INSERT INTO ost_dictaminacion_asignaciones (id_ticket, id_staff) VALUES ($ticket_id, $opcion)");
			echo "<script>console.log('Agente $opcion asignado');</script>";
		}
	}

}


?>

<script>
var limite = <?php echo $limite;?>;
var esValido =false;
function initializeForm(){
	actualizarValidacion();
	$('input.single-checkbox').off('change').on('change', function(){
		var seleccionadas=$('input.single-checkbox:checked').length;
		if(seleccionadas > limite){
			$(this).prop('checked', false);
		}
		actualizarValidacion();
	});
}

function actualizarValidacion(){
	var seleccionadas = $('input.single-checkbox:checked').length;
	esValido=seleccionadas === limite;
}

function checarFormulario(){
	actualizarValidacion();
	if(esValido){
		document.getElementById('miForm').submit();
	}
}

function cambiarLimite(nuevoLimite){
	limite=nuevoLimite;
	initializeForm();
}

$(document).ready(function(){
	initializeForm();
});


function volver(){
	window.location.href='dictaminacion_admin.php';
}


</script>

<form id="miForm" class="dynamic-form" action="asignacion_dictamen.php?id=<?php echo $ticket_id; ?>&idEstado=1" method="post">
<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
<input type="hidden" name="estado_id" value="<?php echo $estado_id; ?>">
<input type="hidden" name="anterior[]" id="anterior">

<?php    csrf_token(); ?>
<table>

<th>Nombre</th>
<th>Apellido</th>
<th>Username</th>
<th>Asignar</th>

<?php
if($estado_id == 0){
	$sql = 'SELECT staff_id, firstname, lastname, username FROM ost_staff WHERE isactive=1';
	$staffs = db_query($sql);
}else{
	$sql = 'SELECT staff_id, firstname, lastname, username FROM ost_staff';
	$staffs = db_query($sql);
}

while($row = db_fetch_array($staffs)){
	$id_staff = $row['staff_id'];
	$nombre = $row['firstname'];
	$apellido = $row['lastname'];
	$usuario = $row['username'];

	echo "<tr>";
	echo "<td>".$nombre."</td>";
	echo "<td>".$apellido."</td>";
	echo "<td>".$usuario."</td>";

	echo "<td><input class='single-checkbox' type='checkbox' id='$id_staff' name='opciones[]' value='$id_staff'></td>";
	echo "<input type='hidden' id='$usuario' name='anteriores[]'>";

	$sql_estado = "SELECT * FROM ost_dictaminacion WHERE id_ticket = $ticket_id AND id_staff = $id_staff AND id_estado=1";
	$res_estado = db_query($sql_estado);

	$sql_asignacion = "SELECT * FROM ost_dictaminacion_asignaciones WHERE id_ticket = $ticket_id";
	$res_asignacion = db_query($sql_asignacion);

	switch($estado_id){
		case 0:
			echo "<script>
			document.getElementById($id_staff).disabled = false;
			</script>";
			break;
		case 1:
			while($fila_estado = db_fetch_array($res_asignacion)){
				if($fila_estado['id_staff']==$id_staff){
					echo "<script>
					var opcion = document.getElementById($id_staff);
					opcion.checked = true;
					</script>";
				}
			}
			echo "<script>
			var opcion = document.getElementById($id_staff);
			opcion.disabled = true;</script>";
			break;
		case 2:
			while($fila_estado = db_fetch_array($res_asignacion)){
				if($fila_estado['id_staff']==$id_staff){
					echo "<script>
					var opcion = document.getElementById($id_staff);
					opcion.checked = true;
					opcion.disabled = true;
					</script>";
				}


			}
			break;
		case 3:
			$conteo=0;
			global $conteo;
			while($fila_estado = db_fetch_array($res_asignacion)){
				$conteo = $conteo+1;
				if($fila_estado['id_staff']==$id_staff){

					echo "<script>
					var opcion = document.getElementById($id_staff);
					opcion.checked = true;
					var anterior = document.getElementById('$usuario');
					anterior.value = $id_staff;
					</script>";
					if(db_num_rows($res_estado)==1){
						echo "<script>
						opcion.disabled = true;
						var ante = document.getElementById('anterior');
						ante.value = $id_staff;
						</script>";
					}

				}

			}
			if($conteo==3){
				echo "<script>
				cambiarLimite(3);
				</script>";
			}else{
				echo "<script>
				cambiarLimite(2);
				</script>";
			}

			break;
	}

	echo "</tr>";
}
?>

</table>
<?php
if($estado_id == 1){
	echo "<input type='button' value='Volver' onclick='volver()'>";
}elseif($estado_id == 3){
	echo "<input type='button' value='Guardar' onclick='checarFormulario()'>";
	echo "<input type='button' value='Cancelar' onclick='volver()'>";
}else{
	echo "<input type='button' value='Guardar' onclick='checarFormulario()'>
	<input type='button' value='Cancelar' onclick='volver()'>";
}

require_once(STAFFINC_DIR.'footer.inc.php');
?>
