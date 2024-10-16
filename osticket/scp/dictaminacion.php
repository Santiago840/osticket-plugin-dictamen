<?php
$TABLE_PREFIX = "ostck_";
/* $sql = "CREATE TABLE IF NOT EXISTS ost_dictaminacion_respuestas(id_respuesta INT AUTO_INCREMENT PRIMARY KEY, id_staff INT NOT NULL, id_ticket INT NOT NULL, pregunta TEXT, pregunta_label text, respuesta TEXT) ENGINE=InnoDB";
 */
include('staff.inc.php');

$nav->setTabActive('dictaminacions');
require_once(STAFFINC_DIR . 'header.inc.php');

$agent_id = $thisstaff->getId();
global $agent_id;

$sql = "SELECT t.ticket_id, t.number FROM " . $TABLE_PREFIX . "ticket t WHERE t.ticket_id IN (SELECT da.id_ticket FROM "  . $TABLE_PREFIX . "dictaminacion_asignaciones da WHERE da.id_staff ='$agent_id')";
$res = db_query($sql);
$sql_opcionesAsignadas = db_query("SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_opciones");

$form_titulo = 'Dictaminación';
$sql_idForm = "SELECT * FROM " . $TABLE_PREFIX . "form WHERE title = '$form_titulo'";
$res_formulario = db_query($sql_idForm);

$error = '';
$ir_formulario = false;
//verificar si hay registros
if (db_num_rows($sql_opcionesAsignadas) == 0) {
    $error = 'Parece que el administrador no ha configurado el formulario de dictaminación correctamente. Verifique que haya seleccionado las opciones de la valoración global';
}

if (db_num_rows($res_formulario) == 0) {
    $error = 'Parece que el administrador no ha configurado el formulario de dictaminación correctamente. Verifique que esté nombrado como Dictaminación';
} elseif (db_num_rows($res_formulario) > 2) {
    $error = 'Parece que el administrador no ha configurado el formulario de dictaminación correctamente. Verifique que no haya duplicidad en el nombre de Dictaminación';
}

if (db_num_rows($sql_opcionesAsignadas) > 0 || db_num_rows($res_formulario) == 1) {
    $ir_formulario = true;
} else {
    $ir_formulario = false;
}



?>

<script src="jspdf.umd.min.js"></script>
<script src="jspdf.plugin.autotable.min.js"></script>
<script>
    function mostrarAlerta(error) {
        alert(error);
        window.location.href = 'dictaminacion.php';
    }

    async function generarPdf(preguntas_json, ticket_number) {
    let preguntas = preguntas_json;
    const {
        jsPDF
    } = window.jspdf;

    const doc = new jsPDF({
        orientation: 'landscape',
        unit: 'mm',
        format: 'a4'
    });
    doc.setFontSize(20);

    const pageWidth = doc.internal.pageSize.getWidth();
    const pageHeight = doc.internal.pageSize.getHeight();

    const titulo1 = "BENEMÉRITA ESCUELA NORMAL VERACRUZANA ENRIQUE C. RÉBSAMEN.";
    const titulo2 = "OFICINA DE INNOVACIÓN EDUCATIVA";

    const titulo1Width = doc.getTextWidth(titulo1);
    const titulo2Width = doc.getTextWidth(titulo2);

    const titulo1X = (pageWidth - titulo1Width) / 2;
    const titulo2X = (pageWidth - titulo2Width) / 2;

    doc.text(titulo1, titulo1X, 20);
    doc.text(titulo2, titulo2X, 30);

    doc.setFontSize(18);
    const nombreTicket = "Evaluación del ticket #" + ticket_number;

    doc.text(nombreTicket, 15, 45);

    const columns = ["ASPECTO A EVALUAR", "RESPUESTA"];

    const numFilas = preguntas.length;
    const rows = preguntas.map(pregunta => [pregunta.pregunta_label, pregunta.respuesta]);

    // Crear una copia de la tabla sin la penúltima fila
    const rowsCopy = [...rows];
    rowsCopy.splice(numFilas - 2, 1); // Eliminar la penúltima fila

    if (rows[numFilas - 1][0] == 'Valoración Global') {
        rows[numFilas - 1] = [rows[numFilas - 1][0], rows[numFilas - 1][1]];
    }

    // Generar la tabla de respuestas
    doc.autoTable({
        head: [columns],
        body: rowsCopy,
        margin: {
            top: 50
        },
        styles: {
            fontSize: 16,
            cellPadding: 5,
            textColor: [0, 0, 0],
            lineWidth: 0.75,
            lineColor: [0, 0, 0]
        },
        headStyles: {
            halign: 'center',
        }
    });

    // Salto de página para la última pregunta
    doc.addPage();  // Añade una nueva página

    doc.setFontSize(18);

    if (rows[numFilas - 1]) {
        const textoUltima = `${rows[numFilas - 2][0]}\n${rows[numFilas - 2][1]}`;
        const marginLeft = 15;
        const marginTop = 20; // Margen superior en la nueva página
        const textWidth = pageWidth - (2 * marginLeft);

        const textLines = doc.splitTextToSize(textoUltima, textWidth);

        let posicionY = marginTop;
        textLines.forEach(line => {
            if (posicionY + 10 > pageHeight) {
                doc.addPage();
                posicionY = 10;
            }
            doc.text(line, marginLeft, posicionY);
            posicionY += 10;
        });
    }

    doc.save('Ticket_No.' + ticket_number + '_evaluación.pdf');
}
</script>

<h1>Dictaminación</h1>


<?php
if (db_num_rows($res) > 0) {
?>
    <table border="1">
        <thead>
            <tr>
                <th>Ticket</th>
                <th>Estado</th>
                <th>Exportar</th>
            </tr>
        </thead>
        <tbody>
            <?php
            while ($row = db_fetch_array($res)) {
                $ticket_number = $row['number'];
                $ticket_id = $row['ticket_id'];

                $preguntas = [];

                $sql_form = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion_respuestas WHERE id_ticket=$ticket_id AND id_staff = $agent_id ORDER BY id_respuesta";
                $result_form = db_query($sql_form);

                while ($fila_preguntas = db_fetch_array($result_form)) {
                    $preguntas[] = $fila_preguntas;
                }
                $preguntas_json = json_encode($preguntas);

                $sql_estado = "SELECT * FROM " . $TABLE_PREFIX . "dictaminacion WHERE id_staff = $agent_id AND id_ticket = $ticket_id AND id_estado=1";
                $estado = db_query($sql_estado);
                echo "<tr>";
                if ($ir_formulario) {
                    echo "<td><a href='formulario_dictamen.php?id=" . $ticket_id . "'>#" . $ticket_number . "</a></td>";
                } else {
                    echo "<td><span style='color: blue; text-decoration: underline; cursor: pointer;' onclick='mostrarAlerta(\"$error\")'>#$ticket_number</span></td>";
                }
                if (db_num_rows($estado) == 1) {
                    echo "<td>Evaluado</td>";
                    echo "<td><input type='button' value='PDF' onclick='generarPdf($preguntas_json, $ticket_number)'></td>";
                } elseif (db_num_rows($estado) == 0) {
                    echo "<td>Pendiente</td>";
                    echo "<td><input type='button' value='PDF' onclick='generarPdf($preguntas_json, $ticket_number)' disabled></td>";
                }
                echo "</tr>";
            }
            ?>
        </tbody>
    </table>
<?php
} else {
    echo "<p>No tiene tickets asignados por el momento.</p>";
}

include(STAFFINC_DIR . 'footer.inc.php');
?>