<?php
$tabla_dictaminacion = "CREATE TABLE IF NOT EXISTS ost_dictaminacion(
    id_dictaminacion INT AUTO_INCREMENT PRIMARY KEY,
    id_staff INT NOT NULL,
    id_ticket INT NOT NULL,
    id_estado INT NOT NULL,
    id_valoracion INT NOT NULL
) ENGINE=InnoDB";

include ('staff.inc.php');

$nav->setTabActive('dictaminacions');
require_once(STAFFINC_DIR.'header.inc.php');

$estatus = false;
$staff_id = $thisstaff->getId();

$form_titulo = 'dictaminacion';
$sql_idForm = "SELECT id FROM ost_form WHERE title = '$form_titulo'";
$res_formulario = db_query($sql_idForm);

if($id_form = db_fetch_array($res_formulario)){
	$idForm = $id_form['id'];

	$sql_form = "SELECT * FROM ost_form_field WHERE form_id=$idForm ORDER BY sort";
	$preguntas = db_query($sql_form);

$preguntas_labels = [];
while ($fila = db_fetch_array($preguntas)) {
    $preguntas_labels[$fila['name']] = $fila['label'];
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $ticket_id = intval($_POST['ticket_id']);
    // Verifica si ya existe un registro en ost_dictaminacion para el ticket y el staff
    $check_dictaminacion = db_query("SELECT * FROM ost_dictaminacion WHERE id_ticket = $ticket_id AND id_staff = $staff_id");
    if (db_num_rows($check_dictaminacion) == 0) {
        foreach ($_POST as $key => $value) {
            $pregunta_id = htmlspecialchars($key);
            $respuesta = htmlspecialchars($value);
            if ($respuesta == 'Correcto') {
                    $valoracion = 1;
            } elseif($respuesta == 'Incorrecto') {
                    $valoracion = 0; 
            }
            if ($pregunta_id != 'ticket_id' && $pregunta_id != '__CSRFToken__') {
                $pregunta_label = isset($preguntas_labels[$pregunta_id]) ? $preguntas_labels[$pregunta_id] : '';

                $stmt_respuesta = db_query("INSERT INTO ost_dictaminacion_respuestas (id_staff, id_ticket, pregunta, pregunta_label, respuesta) VALUES ($staff_id, $ticket_id, '$pregunta_id', '$pregunta_label', '$respuesta')");
            }
        }

        $stmt_estado = db_query("INSERT INTO ost_dictaminacion(id_staff, id_ticket, id_estado, id_valoracion) VALUES ($staff_id, $ticket_id, 1, $valoracion)");
    }
}

if (isset($_GET['id'])) {
    $ticket_id = intval($_GET['id']);
}

$sql = "SELECT * FROM ost_ticket WHERE ticket_id = $ticket_id";
$res = db_query($sql);

if ($ticket = db_fetch_array($res)) {
    echo "<h3>Dictaminación del ticket #".$ticket['number']."</h3>";
} else {
    echo "<p>No se encontraron detalles para este ticket.</p>";
}

$sql_estado = "SELECT * FROM ost_dictaminacion WHERE id_ticket=$ticket_id AND id_staff=$staff_id AND id_estado=1";
$estado = db_query($sql_estado);
$estatus = db_num_rows($estado) == 1;

}else{
	echo "<script>alert('Parece que su formulario de dictaminación está nombrado incorrectamente. Verifique que esté nombrado como Dictaminación o dictaminación');
	window.location.href = 'dictaminacion.php';
	</script>";
}

?>

<style>
    .dynamic-form .form-group {
        margin-bottom: 15px;
    }

    .dynamic-form label {
        display: block;
        margin-bottom: 5px;
        font-weight: bold;
    }
</style>

<script>
function volver(){
    window.location.href='dictaminacion.php';
}

function irConfiguracion(){
	window.location.href='configuracion_dictamen.php';
}
/*function confirmarEnvio() {
    return confirm("¿Está seguro de guardar? No se podrán efectuar cambios una vez realizada la operación.");
}*/

</script>

<form class="dynamic-form" method="post">
    <input type="hidden" name="ticket_id" value="<?php echo $ticket_id; ?>">
    <?php 
    csrf_token(); 
    // Rehacer la consulta para obtener las preguntas
    $preguntas = db_query($sql_form);
    echo "<table>";
    while ($fila = db_fetch_array($preguntas)) {
        $pregunta = $fila['label'];
        $pregunta_nombre = $fila['name'];

        echo "<tr>";
        echo "<td><label for=".$pregunta_nombre.">".$pregunta."</label></td>";
        
        if ($fila['type'] == 'list-2') {
            echo "<td>";
            echo "<select id=".$pregunta_nombre." name=".$pregunta_nombre.">";
            $list_id = 2;
            $sql_listas = "SELECT * FROM ost_list_items WHERE list_id = $list_id";
            $opciones = db_query($sql_listas);    

            while ($row = db_fetch_array($opciones)) {
                $opcion = $row['value'];
                $opcion_val = $row['extra'] ? $row['extra'] : $row['value'];
                echo "<option value=".$opcion.">".$opcion_val."</option>";
            }

            if ($estatus) {
                $sql_opciones = "SELECT respuesta FROM ost_dictaminacion_respuestas WHERE id_ticket=$ticket_id AND id_staff=$staff_id AND pregunta='$pregunta_nombre'";
                $opcion_seleccionada = db_query($sql_opciones);
                if ($resultante = db_fetch_array($opcion_seleccionada)) {
                    $claro = $resultante['respuesta'];
                    echo "<script>
                    document.getElementById('$pregunta_nombre').value = '$claro';
                    document.getElementById('$pregunta_nombre').disabled = true;
                    </script>";
                }
            }
        
            echo "</select></br></br>";
        }elseif($fila['type'] == 'memo'){
        	echo "<td>";
        	echo "<textarea id=".$pregunta_nombre." name=".$pregunta_nombre." rows ='10' cols='50'></textarea>";	
        	
        	if ($estatus) {
                $sql_opciones = "SELECT respuesta FROM ost_dictaminacion_respuestas WHERE id_ticket=$ticket_id AND id_staff=$staff_id AND pregunta='$pregunta_nombre'";
                $opcion_seleccionada = db_query($sql_opciones);
                if ($resultante = db_fetch_array($opcion_seleccionada)) {
                    $claro = htmlspecialchars($resultante['respuesta'], ENT_QUOTES, 'UTF-8');
                    echo "<script>
                    var textarea = document.getElementById('$pregunta_nombre');
                        textarea.value = ".json_encode($claro).";
                        textarea.disabled = true;
                    </script>";
                }
            }
        	
        }elseif($fila['type'] == 'list-3'){
        	echo "<td>";
            echo "<select id=".$pregunta_nombre." name=".$pregunta_nombre.">";
            $list_id = 3;
            $sql_listas = "SELECT * FROM ost_list_items WHERE list_id = $list_id";
            $opciones = db_query($sql_listas);    

            while ($row = db_fetch_array($opciones)) {
                $opcion = $row['value'];
                $opcion_val = $row['extra'] ? $row['extra'] : $row['value'];
                echo "<option value=".$opcion.">".$opcion_val."</option>";
            }

            if ($estatus) {
                $sql_opciones = "SELECT respuesta FROM ost_dictaminacion_respuestas WHERE id_ticket=$ticket_id AND id_staff=$staff_id AND pregunta='$pregunta_nombre'";
                $opcion_seleccionada = db_query($sql_opciones);
                if ($resultante = db_fetch_array($opcion_seleccionada)) {
                    $claro = $resultante['respuesta'];
                    echo "<script>
                    document.getElementById('$pregunta_nombre').value = '$claro';
                    document.getElementById('$pregunta_nombre').disabled = true;
                    </script>";
                }
            }
        
            echo "</select></br></br>";
        }

        echo "</td>";
        echo "</tr>";
    }    
    
    echo "</table>";
    
    if ($estatus) { ?>
        <input type="button" value="Volver" onclick="volver()">
    <?php } else { ?>
        <input type="submit" value="Guardar">
        <input type="button" value="Cancelar" onclick="volver()">
    <?php } ?>
</form>

<?php
require_once(STAFFINC_DIR.'footer.inc.php');
?>

