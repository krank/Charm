<?php

// Skapa och returnera en connection
$connection = @mysql_connect($dbserver, $dbuser, $dbpass) or die(mysql_error());
mysql_selectdb($dbname);


function makequery($query) {
	// Gör en query, med felrapportering
	$result = mysql_query($query) or die("<b>A fatal MySQL error occured</b>.\n<br />Query: " . $query . "<br />\nError: (" . mysql_errno() . ") " . mysql_error()); 
	return $result;
}


?>