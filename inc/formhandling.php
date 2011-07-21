<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once 'inc/settings.php';
include_once 'inc/db.php';
include_once 'inc/common.php';

function formlist($user_id=False) {
	if (isset($_GET['userid'])) {
		return datalist('forms', 'Rollformul&auml;r', 'form', array('./index.php?do=editform','Nytt rollformulär'), $_GET['userid']);
	} else {
		return datalist('forms', 'Rollformul&auml;r', 'form');
	}
}

function delform() {
	if (isset($_GET['formid'])) {
		return deldata('forms', 
				'form', $_GET['formid'], 
				"&Auml;r du s&auml;ker p&aring; att du vill ta bort formul&auml;ret?", 
				"Du kan bara ta bort formul&auml;r du &auml;r &auml;gare till.");
	} else {
		return template('Du m&aring;ste logga in f&ouml;r att komma &aring;t denna funktion.');
	}	
}

function showform() {
	
	if (isset($_GET['formid'])) {
		$formid = $_GET['formid'];
		return showdata('forms', 'Visa rollformulär', $formid, 
				array("./index.php?do=editform&formid=$formid&makecopy","Kopiera"),
				array("./index.php?do=editform&formid=$formid","Redigera"),
				array("./index.php?do=editchar&formid=$formid","Skapa rollperson")
				);
	} else {
		return template("Ingen rollperson angiven.");
	}
	
}

function editform() {
	// Load template
	
	$body = file_get_contents("template/formedit_tpl.html");
	
	
	// Set defaults
	$tr = array(	"%name_err%"	=> "",
					"%system_err%"	=> "",
					"%desc_err%"	=> "",
	
					"%privchecked%"	=> "checked",
					"%publchecked%"	=> "",

					"%message%"		=> "",
					"%title%"		=> "Redigera rollformul&auml;r"
	
	);
	
	$emptydatasource = array('formid'=>'','name'=>'', 'system'=>'', 'desc'=>'', 'public'=>'', 'xml'=>'');

	if (isset($_SESSION['userid'])) {
		// If $_POST-data exists, assume we are loading from it
		if (isset($_POST['name'], $_POST['system'], $_POST['xml'], $_POST['formid'], $_POST['public'])) {

			// Clean the POST
			$datasource = getcleanpost(array('formid','name', 'system', 'desc', 'public', 'xml'));

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
				if ($datasource['formid'] != "") {
					// Overwrite

					$formid = savedataset('forms', 
							$datasource['name'],
							$datasource['system'],
							$datasource['public'],
							$datasource['desc'],
							$datasource['xml'], 
							$datasource['formid']);

				} else {
					// Otherwise write as new

					$formid = savedataset('forms', 
						$datasource['name'],
						$datasource['system'],
						$datasource['public'],
						$datasource['desc'],
						$datasource['xml']);
				}

				if ($formid) {
					$tr['%message%'] = "Rollformul&auml;ret sparades";
				} else {
					$tr['%message%'] = "Du har inte r&auml;ttigheter att spara det h&auml;r formul&auml;ret.";
				}
		}




		// If no $_POST, but a formid has been sent by GET
		} else if (isset($_GET['formid'])) {

			// Load the data from the formid, if it exists
			$datasource = getdataset('forms', $_GET['formid']);

			if (isset($_GET['makecopy']) && $datasource) {
				$tr['%message%'] = "Formul&auml;ret har kopierats. Nedan ser du kopian, som kommer att sparas i din lista.";
				$datasource['formid'] = "";
			} else if (isset($_GET['makecopy'])) {
				$tr['%message%'] = "Kopieringen misslyckades - antagligen finns inte originalet i databasen.";
				$datasource = $emptydatasource;
			} else if (!$datasource) {
				$tr['%message%'] = "Formul&auml;ret finns inte eller &auml;gs av en annan anv&auml;ndare.";
				$datasource = $emptydatasource;
			}

		} else {
			// If neither $_POST nor $_GET has been properly set, assume "new" sheet.
			$datasource = $emptydatasource;

			$tr['%title%'] = 'Nytt rollformul&auml;r';
		}
	} else {
		
		$datasource = $emptydatasource;
		$tr['%title%'] = 'FEL!';
		$tr['%message%'] = "Du &auml;r inte inloggad och kan d&auml;rf&ouml;r inte redigera formul&auml;r";
	}

	// Populate the translation matrix
	
	foreach (array("name","system","desc") as $v) {
		$tr["%$v"."_str%"] = $datasource[$v];
	}

	$tr["%xml_str%"] = xmltoform($datasource['xml']);

	$tr['%formid%'] = $datasource['formid'];
	
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
	
	$body = strtr($body,$tr);
	return template($body);
}

?>