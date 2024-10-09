<?php
$sql= "CREATE TABLE IF NOT EXISTS ";

$sql_prueba = "CREATE TABLE IF NOT EXISTS ost_dictaminacion_listas_positivas(id_positiva INT AUTO_INCREMENT PRIMARY KEY,
	id_Asignacion INT AUTO_INCREMENT PRIMARY KEY, id_ticket INT NOT NULL, id_staff INT NOT NULL) ENGINE=InnoDB;";


include ('admin.inc.php');

$nav->setTabActive('manage');

require(STAFFINC_DIR.'header.inc.php');



?>

<script>
	var conteo=2;
	
	function agregarOpcion(){
		
		if(conteo < 6){
			conteo++;
			var op='op'+conteo;
			var label = document.createElement('label');
			label.textContent = 'Opción '+conteo+': ';
			label.htmlFor = op;
			label.id='lb'+op;
		
			var input = document.createElement('input');
			input.type='text';
			input.id=op;
		
			var espacio = document.createElement('br');
			espacio.id='b'+op;
		
			var contenedor = document.getElementById('formulario');
			var lastElement = contenedor.lastElementChild;
			contenedor.insertBefore(input, lastElement.nextSibling);
			contenedor.insertBefore(label, lastElement.nextSibling);
			contenedor.insertBefore(espacio, lastElement.nextSibling);
		}
	}
	
	function eliminarOpcion(){
		if(conteo > 2){
			var op='op'+conteo;
			var espacio = document.getElementById('b'+op);
			var elementoInput = document.getElementById(op);
			var elementoLabel = document.getElementById('lb'+op);
			
			elementoLabel.remove();	
			elementoInput.remove();
			espacio.remove();
			/*var ultimoInput = ultimoElemento.lastElementChild.previousElementSibling;
			var ultimoLabel = ultimoInput.previousElementSibling;
			
			contenedor.removeChild(utlimoLabel);
			contenedor.removeChild(ultimoInput);*/
			conteo--;
		}
	}
	
	function volver(){
		window.location.href='dictaminacion_admin.php';
	}
</script>

<h1>Configuración</h1>
<p>Este apartado deberá crear las opciones correspondientes para la valoración general de la dictaminación y cuáles deberán ser las que son positivas o negativas para el dictamen.</p>
<div id="formulario-1">
<form id="confgForm" class="dynamic-form" action="configuracion_dictamen.php" method="post">
	<div id="formulario">
		<label for="lista" class="titulo">Nombre de la lista:<label>
		</br>
		<input type="text" id="lista">
		<br></br>
		<label for="op1" class="titulo">Opción 1:</label>
		<input type="text" id="op1">
		<br>
		<label for="op2" class="titulo">Opción 2:</label>
		<input type="text" id="op2">
	</div>
	<br></br>
	<input type = "button" value="Agregar Opción" onclick="agregarOpcion()">
	<input type = "button" value="Eliminar Opción" onclick="eliminarOpcion()">
	<br></br>
	<input type="button" value="Guardar">
	<input type="button" value="Cancelar" onclick="volver()">
</form>
</div>

<?php
include(STAFFINC_DIR.'footer.inc.php');
?>


