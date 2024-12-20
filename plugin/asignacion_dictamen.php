<?php

include('admin.inc.php');
$TABLE_PREFIX = $GLOBALS['mi_prefijo_global'];

$nav->setTabActive('manage');

require_once(STAFFINC_DIR . 'header.inc.php');

if ($GLOBALS['esta_activado']) {
	//Tabla::crearTabla();
	$est = 0;
	global $est;

	$prueba = 0;
	global $prueba;

	if ($_SERVER['REQUEST_METHOD'] == 'GET') {
		$ticket_id = intval($_GET['id']);
		global $ticket_id;
	}

	if (isset($_GET['edicion'])) {
		$edicion = intval($_GET['edicion']);
		global $edicion;
	}

	if (isset($_GET['idEstado'])) {
		$estado_id = intval($_GET['idEstado']);
		global $estado_id;
	}

	$sql = "SELECT * FROM " . $TABLE_PREFIX . "ticket WHERE ticket_id = $ticket_id";
	$res = db_query($sql);

	if ($ticket = db_fetch_array($res)) {
		echo "<h3>Asignación del ticket #" . $ticket['number'] . "</h3>";
	}

	$limite = 2;
	global $limite;
	if ($estado_id == 2) {
		$limite = 3;
	}

	if ($_SERVER['REQUEST_METHOD'] == 'POST') {
		//print_r($_POST);
		$pruebas = intval($_POST['prueba']);
		$ticket_id = intval($_POST['ticket_id']);
		$id_estado = intval($_POST['estado_id']);
		$id_anterior = $_POST['anterior'];

		$agentes_seleccionados = $_POST['opciones'];
		$agentes_anteriores = $_POST['anteriores'];
		if ($id_estado == 3) {

			if ($pruebas == 1) {
				// Eliminar el último registro de la base de datos para el ticket actual
				$sql_eliminar_ultimo = "DELETE FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket = $ticket_id ORDER BY id_asignacion DESC LIMIT 1";
				db_query($sql_eliminar_ultimo);
				echo "<script>console.log('Última asignación eliminada');</script>";

				// Actualizar o insertar agentes seleccionados
				foreach ($agentes_seleccionados as $opcion) {
					$sql_verificar = "SELECT id_asignacion FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket = $ticket_id AND id_staff = $opcion";
					$res_verificar = db_query($sql_verificar);

					if (db_num_rows($res_verificar) > 0) {
						// Actualizar asignación si ya existe
						$fila_asignacion = db_fetch_array($res_verificar);
						$id_asignacion = $fila_asignacion['id_asignacion'];
						$sql_actualizar = "UPDATE " . $TABLE_PREFIX . "dictaminacion_asignaciones SET id_staff = $opcion WHERE id_asignacion = $id_asignacion";
						db_query($sql_actualizar);
						echo "<script>console.log('Asignación actualizada para el agente $opcion');</script>";
					} else {
						// Insertar nueva asignación
						$sql_insertar = "INSERT INTO " . $TABLE_PREFIX . "dictaminacion_asignaciones (id_ticket, id_staff) VALUES ($ticket_id, $opcion)";
						db_query($sql_insertar);
						echo "<script>console.log('Nueva asignación guardada para el agente $opcion');</script>";
					}
				}
			} elseif ($pruebas == 0) {
				foreach ($agentes_seleccionados as $opcion) {
					$sql_verificar = "SELECT id_asignacion FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket = $ticket_id AND id_staff = $opcion";
					$res_verificar = db_query($sql_verificar);

					if (db_num_rows($res_verificar) > 0) {
						$fila_asignacion = db_fetch_array($res_verificar);
						$id_asignacion = $fila_asignacion['id_asignacion'];
						$sql_actualizar = "UPDATE " . $TABLE_PREFIX . "dictaminacion_asignaciones SET id_staff = $opcion WHERE id_asignacion = $id_asignacion";
						db_query($sql_actualizar);
						echo "<script>console.log('Asignación actualizada para el agente $opcion');</script>";
					} else {
						$sql_insertar = "INSERT INTO " . $TABLE_PREFIX . "dictaminacion_asignaciones (id_ticket, id_staff) VALUES ($ticket_id, $opcion)";
						db_query($sql_insertar);
						echo "<script>console.log('Nueva asignación guardada para el agente $opcion');</script>";
					}
				}
				foreach ($agentes_anteriores as $anterior) {
					if (!in_array($anterior, $agentes_seleccionados)) {
						foreach ($id_anterior as $ant) {
							if ($ant != $anterior) {
								$sql_eliminar = "DELETE FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket = $ticket_id AND id_staff = $anterior";
								db_query($sql_eliminar);
								echo "<script>console.log('Agente $anterior eliminado de las asignaciones');</script>";
							}
						}
					}
				}
			}
		} else {
			foreach ($agentes_seleccionados as $opcion) {
				$stmt = db_query("INSERT INTO " . $TABLE_PREFIX . "dictaminacion_asignaciones (id_ticket, id_staff) VALUES ($ticket_id, $opcion)");
				echo "<script>console.log('Agente $opcion asignado');</script>";
			}
		}
	}

?>
	<style>
		/* Ajuste general de la tabla */
		table {
			margin: 0 auto;
			/* Centramos la tabla */
			border-collapse: collapse;
			/* Eliminamos espacios entre celdas */
			width: 80%;
		}

		th,
		td {
			padding: 10px;
			/* Espaciado interno */
			text-align: center;
			border: 1px solid lightsalmon;
			/* Bordes suaves */
		}

		th {
			background-color: orangered;
			/* Color de fondo de encabezados */
			font-weight: bold;
			color: white;
		}

		/* Ajuste para los botones */
		input[type="button"],
		input[type="submit"] {
			background-color: orangered;
			/* Color verde */
			color: white;
			/* Texto en blanco */
			padding: 8px 16px;
			/* Tamaño moderado */
			font-size: 14px;
			/* Texto más pequeño */
			border: none;
			border-radius: 4px;
			cursor: pointer;
			/* Icono de mano para interacción */
			margin-left: 10px;
			/* Espacio entre botones */
		}

		input[type="button"]:hover,
		input[type="submit"]:hover {
			background-color: orange;
			/* Efecto hover */
		}

		/* Alineación de botones a la derecha */
		form {
			text-align: right;
			/* Alinea los botones a la derecha */
			margin-top: 20px;
			/* Espacio superior */
		}
	</style>


	<script>
		var limite = <?php echo $limite; ?>;
		var esValido = false;

		function irEditar(ticket_id, staff) {
			var url = 'dictaminacion_editar.php?id=' + ticket_id + '&staff=' + staff;
			window.location.href = url;
		}

		function initializeForm() {
			actualizarValidacion();
			$('input.single-checkbox').off('change').on('change', function() {
				var seleccionadas = $('input.single-checkbox:checked').length;
				if (seleccionadas > limite) {
					$(this).prop('checked', false);
				}
				actualizarValidacion();
			});
		}

		function actualizarValidacion() {
			var seleccionadas = $('input.single-checkbox:checked').length;
			esValido = seleccionadas === limite;
		}

		function checarFormulario() {
			actualizarValidacion();
			if (esValido) {
				document.getElementById('miForm').submit();
			}
		}

		function cambiarLimite(nuevoLimite) {
			limite = nuevoLimite;
			initializeForm();
		}

		$(document).ready(function() {
			initializeForm();
		});


		function volver() {
			window.location.href = 'dictaminacion_admin.php';
		}
	</script>

	<form id="miForm" class="dynamic-form" action="asignacion_dictamen.php?id=<?php echo $ticket_id; ?>&idEstado=1" method="post">
		<input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
		<input type="hidden" name="estado_id" value="<?php echo $estado_id; ?>">
		<input type="hidden" name="anterior[]" id="anterior">

		<?php csrf_token();
		$prueba = 2 ?>
		<table>

			<th>NOMBRE(S)</th>
			<th>APELLIDOS</th>
			<th>USUARIO</th>
			<th>ASIGNAR</th>

			<?php
			if ($edicion == 1) {
				echo "<th>EDITAR</th>";
			}
			if ($estado_id == 0) {
				$sql = "SELECT staff_id, firstname, lastname, username FROM " . $TABLE_PREFIX . "staff WHERE isactive=1";
				$staffs = db_query($sql);
			} else if ($estado_id == 1) {
				$sql = "SELECT DISTINCT s.staff_id, s.firstname, s.lastname, s.username 
				FROM " . $TABLE_PREFIX . "staff AS s 
				JOIN " . $TABLE_PREFIX . "dictaminacion_asignaciones AS a ON s.staff_id = a.id_staff";
				$staffs = db_query($sql);
			} else {
				$sql_verificacion = "SELECT s.staff_id, s.firstname, s.lastname, s.username 
                     FROM " . $TABLE_PREFIX . "staff AS s
                     JOIN " . $TABLE_PREFIX . "dictaminacion_asignaciones AS a ON s.staff_id = a.id_staff 
                     WHERE s.isactive = 0";

				// Ejecuta la consulta y almacena el resultado
				$result = db_query($sql_verificacion);

				// Verifica si hay al menos una fila en el resultado
				if (db_num_rows($result) >= 1) {
					$sql = "SELECT staff_id, firstname, lastname, username FROM " . $TABLE_PREFIX . "staff";
					$staffs = db_query($sql);
				} else {
					// Si no hay resultados en la primera consulta, ejecuta la consulta alternativa
					$sql = "SELECT staff_id, firstname, lastname, username 
					FROM " . $TABLE_PREFIX . "staff 
					WHERE isactive = 1";
					$staffs = db_query($sql);
				}
			}

			while ($row = db_fetch_array($staffs)) {
				$id_staff = $row['staff_id'];
				$nombre = $row['firstname'];
				$apellido = $row['lastname'];
				$usuario = $row['username'];

				echo "<tr>";
				echo "<td>" . $nombre . "</td>";
				echo "<td>" . $apellido . "</td>";
				echo "<td>" . $usuario . "</td>";

				echo "<td><input class='single-checkbox' type='checkbox' id='$id_staff' name='opciones[]' value='$id_staff'></td>";

				echo "<input type='hidden' id='$usuario' name='anteriores[]'>";

				$sql_estado = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion WHERE id_ticket = $ticket_id AND id_staff = $id_staff AND id_estado=1";
				$res_estado = db_query($sql_estado);

				$sql_asignacion = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_asignaciones WHERE id_ticket = $ticket_id";
				$res_asignacion = db_query($sql_asignacion);

				switch ($estado_id) {
					case 0:
						echo "<script>
			document.getElementById($id_staff).disabled = false;
			</script>";
						break;
					case 1:
						while ($fila_estado = db_fetch_array($res_asignacion)) {
							if ($fila_estado['id_staff'] == $id_staff) {
								if ($edicion == 1) {
									echo "<td><input type='button' id='s$id_staff' value='EDITAR' onclick='irEditar($ticket_id, $id_staff)'>";
								}
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
						while ($fila_estado = db_fetch_array($res_asignacion)) {

							if ($fila_estado['id_staff'] == $id_staff) {
								if ($edicion == 1) {
									echo "<td><input type='button' id='s$id_staff' value='EDITAR' onclick='irEditar($ticket_id, $id_staff)'>";
								}
								echo "<script>
					var opcion = document.getElementById($id_staff);
					opcion.checked = true;
					opcion.disabled = true;
					</script>";
							}
						}
						break;
					case 3:
						$conteo = 0;
						global $conteo;
						while ($fila_estado = db_fetch_array($res_asignacion)) {
							$conteo = $conteo + 1;
							if ($fila_estado['id_staff'] == $id_staff) {


								echo "<script>
					var opcion = document.getElementById($id_staff);
					opcion.checked = true;
					var anterior = document.getElementById('$usuario');
					anterior.value = $id_staff;
					</script>";
								if (db_num_rows($res_estado) == 1) {
									echo "<script>
						opcion.disabled = true;
						var ante = document.getElementById('anterior');
						ante.value = $id_staff;
						</script>";
								}
							}
						}
						if ($conteo == 3) {
							$prueba = 1;
							echo "<script>
						cambiarLimite(3);
				</script>";
						} else {
							$prueba = 0;
							echo "<script>
				cambiarLimite(2);
				</script>";
						}

						break;
				}

				echo "</tr>";
			}
			?>
			<input type="hidden" name="prueba" value="<?php echo $prueba; ?>">
		</table>
		<br>
	<?php
	if ($estado_id == 1) {
		echo "<input type='button' value='VOLVER' onclick='volver()'>";
	} elseif ($estado_id == 3) {
		echo "<input type='button' value='GUARDAR' onclick='checarFormulario()'>";
		echo "<input type='button' value='CANCELAR' onclick='volver()'>";
	} else {
		echo "<input type='button' value='GUARDAR' onclick='checarFormulario()'>
	<input type='button' value='CANCELAR' onclick='volver()'>";
	}

	echo "</form>";
} else {
	echo "Verifique que su plugin Dictaminación Plugin se encuentre activado.";
}
require_once(STAFFINC_DIR . 'footer.inc.php');

	?>