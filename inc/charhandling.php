<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once 'inc/settings.php';
include_once 'inc/db.php';
include_once 'inc/common.php';

function editchar() {
	// Set defaults
	$tr = array(	"%name_err%"	=> "",
					"%system_err%"	=> "",
					"%desc_err%"	=> "",
	
					"%privchecked%"	=> "checked",
					"%publchecked%"	=> "",

					"%message%"		=> "",
					"%title%"		=> "Redigera rollperson"
	
	);
	
	$emptydatasource = array('formid'=>'','name'=>'', 'system'=>'', 'desc'=>'', 'public'=>'', 'xml'=>'');
	
	if (isset($_SESSION['userid'])) {
		
		// If a saving POST is received...
		// --- Get values from POST, and save them to database ---
		
		if (isset($_POST['name'], $_POST['system'], $_POST['xml'], $_POST['public'], $_POST['charid'])) {

			// Clean the POST
			$datasource = clean_array($_POST, array('charid','name', 'system', 'desc', 'public', 'xml'));

			// Check the POST for errors
			$maxlen = array(	"name" => 64,
									"system" => 64,
									"desc" => 128
							 );
			
			$errors = checkerrors($datasource, $maxlen);

			$tr = array_merge($tr, $errors);

			// If save has been set, save it if the user has ownership

			if (isset($_POST['submit']) && count($errors) == 0) {

				// If formid has been set...
				if ($datasource['charid'] != "") {
					// Overwrite
					
					$charid = savedataset('characters', $datasource['name'],
							$datasource['system'],
							$datasource['public'],
							$datasource['desc'],
							$datasource['xml'], 
							$datasource['charid']);

				} else {
					// Otherwise write as new
					
					$charid = savedataset('characters', $datasource['name'],
						$datasource['system'],
						$datasource['public'],
						$datasource['desc'],
						$datasource['xml']);
				}

				if ($charid) {
					$tr['%message%'] = "Rollpersonen sparades";
				} else {
					$tr['%message%'] = "Du &auml;ger inte den h&auml;r rollpersonen.";
				}
			}

		// If a char has been specified...
		// --- Load the char ---
		} else if (isset($_GET['charid'])) {
			
			// Load the data from the formid, if it exists
			$datasource = getdataset('characters', $_GET['charid']);

			$charid = $_GET['charid'];
			
			if (isset($_GET['makecopy']) && $datasource) {
				$tr['%message%'] = "Rollpersonen har kopierats. Nedan ser du kopian, som kommer att sparas i din lista.";
				$charid = "";
			} else if (isset($_GET['makecopy'])) {
				$tr['%message%'] = "Kopieringen misslyckades - antagligen finns inte originalet i databasen.";
				$datasource = $emptydatasource;
			} else if (!$datasource) {
				$tr['%message%'] = "Rollpersonen finns inte eller &auml;gs av en annan anv&auml;ndare.";
				$datasource = $emptydatasource;
			}
			

		// If a form has been specified...
		// --- Create new char based on form ---
		} else if (isset($_GET['formid'])) {

			$tr['%title%'] = "Skapa ny rollperson";
			
			// Read the form
			$datasource = getdataset('forms', $_GET['formid']);
			
			// Clean the forminfo to prepare for use as new character
			$datasource['charid'] = "";
			$datasource['name'] = "";
			$datasource['desc'] = "";
			$datasource['public'] = 0;
			
			$charid = "";

		// If neither have been specified...
		// --- Display a list of forms to base the char on ---
		} else {
			
		}
	
	} else {
		return template('Du m&aring;ste logga in f&ouml;r att komma &aring;t denna funktion.');
	}
	
	
	if (!isset($datasource)) {
		$datasource = $emptydatasource;
	}
	
	
	foreach (array("name","system","desc") as $v) {
		$tr["%$v"."_str%"] = $datasource[$v];
	}

	$tr["%xml_str%"] = xmltoform($datasource['xml'], true);

	$tr['%charid%'] = $charid;
	
	if ($datasource["public"] == "1") {
		$tr["%privchecked%"]	= "";
		$tr["%publchecked%"]	= "checked";
	}
	
	
	if ($tr['%message%'] == "") {
		$tr['%msgdisplay%'] = 'none';
	} else {
		$tr['%msgdisplay%'] = 'block';
	}
	
	// Return template
	
	$body = file_get_contents("template/charedit_tpl.html");
	$body = strtr($body,$tr);
	return template($body);
	
}

function showchar($charid = false) {
	
	
	if (isset($_GET['charid'])) {
		$charid = $_GET['charid'];
	}
	
	if ($charid) {
		return showdata('characters', 'Visa rollperson', $charid, 
				array("?do=editchar&charid=$charid&makecopy","Kopiera"),
				array("?do=editchar&charid=$charid","Redigera")
				);
	} else {
		return template("Ingen rollperson angiven.");
	}
}

function listchars() {
	if (isset($_GET['userid'])) {
		return datalist('characters', 'Rollpersoner', 'char', '', $_GET['userid']);
	} else {
		return datalist('characters', 'Rollpersoner', 'char', '');
	}
}

function delchar() {
	if (isset($_GET['charid'])) {
		return deldata('characters', 
				'char', $_GET['charid'], 
				"&Auml;r du s&auml;ker p&aring; att du vill ta bort rollpersonen?", 
				"Du kan bara ta bort rollpersoner du &auml;r &auml;gare till.");
	} else {
		return template('Du m&aring;ste logga in f&ouml;r att komma &aring;t denna funktion.');
	}
}

?>
