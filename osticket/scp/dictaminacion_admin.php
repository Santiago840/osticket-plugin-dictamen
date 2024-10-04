<?php
include('admin.inc.php');

$nav->setTabActive('manage');

require(STAFFINC_DIR . 'header.inc.php');

$esAdmin = false;
global $esAdmin;
if ($thisstaff && $thisstaff->isAdmin()) {
	$esAdmin = true;
}
?>
<script>
function editar(ticket_id) {
    window.location.href = 'asignacion_dictamen.php?id=' + ticket_id + '&idEstado=3';
}
</script>

<h1>Dictaminación</h1>

<table border="1">
    <thead>
        <tr>
            <th>Ticket</th>
            <th>Asignar a </th>
            <th>Estado</th>
            <th>Editar</th>
        </tr>
    </thead>
    <tbody>
        <?php
		$sql = "SELECT ticket_id, number FROM " . $TABLE_PREFIX . "ticket";
		$res = db_query($sql);

		while ($row = db_fetch_array($res)) {
			$ticket_number = $row['number'];
			$ticket_id = $row['ticket_id'];

			echo "<tr>";
			echo "<td>#" . $ticket_number . "</td>";

			$sql_estado = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion WHERE id_ticket=$ticket_id AND id_estado=1 AND (id_valoracion=1 OR id_valoracion=0)";
			$res_estado = db_query($sql_estado);
			$num_diferencia = 0;
			while ($diferencia = db_fetch_array($res_estado)) {
				if ($diferencia['id_valoracion'] == 1) {
					$num_diferencia++;
				}
			}

			//para saber si ya esta asignado
			$sql_asignacion = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket=$ticket_id";
			$res_asignacion = db_query($sql_asignacion);

			$deshabilitar = 'enabled';
			if (db_num_rows($res_asignacion) >= 2) {
				switch (db_num_rows($res_estado)) {
					case 0:
						$idEstado = 1;
						$estadoAsignar = 'Asignado';
						$estadoDictamen = 'Sin Dictaminar';
						break;
					case 1:
						$idEstado = 1;
						$estadoAsignar = 'Asignado';
						$estadoDictamen = 'En proceso';
						break;
					case 2:
						if ($num_diferencia == 1 && db_num_rows($res_asignacion) == 3) {
							$idEstado = 1;
							$estadoAsignar = 'Asignado';
							$estadoDictamen = 'En proceso';
						} elseif ($num_diferencia == 1 && db_num_rows($res_asignacion) == 2) {
							$idEstado = 2;
							$estadoAsignar = 'Asignar(3ro)';
							$estadoDictamen = 'Discrepancia';
							$deshabilitar = 'disabled';
						} elseif ($num_diferencia == 2 || $num_diferencia == 0) {
							$idEstado = 1;
							$estadoAsignar = 'Asignado';
							$estadoDictamen = 'Evaluado';
							$deshabilitar = 'disabled';
						}
						break;
					case 3:
						$idEstado = 1;
						$estadoAsignar = 'Asignado';
						$estadoDictamen = 'Evaluado';
						$deshabilitar = 'disabled';
						break;
				}
			} else {
				$idEstado = 0;
				$estadoAsignar = 'Asignar';
				$estadoDictamen = 'Sin dictaminar';
				$deshabilitar = 'disabled';
			}

			echo "<td><a href='asignacion_dictamen.php?id=" . $ticket_id . "&idEstado=" . $idEstado . "'>" . $estadoAsignar . "</a></td>";
			echo "<td>$estadoDictamen</td>";
			echo "<td><input type='button' value='Editar' onclick='editar(" . $ticket_id . ")' " . $deshabilitar . "></td>";
			echo "</tr>";
		}
		?>
    </tbody>
</table>



<?php
include(STAFFINC_DIR . 'footer.inc.php');
?>