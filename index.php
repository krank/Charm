<?php 

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once 'inc/common.php';

session_start();

// Setup possible/allowed actions
$action = array(	'register' => array('reghandling', 'userhandling'),
					'login' => array('loginhandling', 'userhandling'),
					'logout' => array('loginhandling'),
					'forgot' => array('loginhandling', 'userhandling'),
	
					'listforms' => array('formhandling', 'userhandling'),
					'editform' => array('formhandling'),
					'showform' => array('formhandling'),
					'delform' => array('formhandling'),
	
					'listchars' => array('charhandling', 'userhandling'),
					'editchar' => array('charhandling'),
					'showchar' => array('charhandling'),
					'delchar' => array('charhandling'),
	
					'editarticle' => array('newshandling'),
	
					'showprofile' => array('profilehandling', 'userhandling'),
					'editprofile' => array('profilehandling', 'userhandling'),
					'changepass' => array('profilehandling', 'userhandling'),
					'changepic' => array('profilehandling','userhandling')
					);

if (isset($_GET['do'])) {
	if (array_key_exists($_GET['do'], $action)) {
		
		// Go through the importables
		foreach ($action[$_GET['do']] as $file) {
			include_once("inc/$file.php");
		}
		
		print call_user_func($_GET['do']);
	} else {
		print template("<h2>Ok&auml;nt kommando</h2>");
	}
} else {
	
	// Get first key of the $_GET
	$first = each($_GET);
	
	// If it's an integer, show it as a character
	if (is_numeric($first[0])) {
		print showchar($first[0]);
		
	// if it's the letter f and its value is an integer, show it as a form
	} else if ($first[0] == 'f' && is_numeric($first[1])) {
		print showform($first[1]);
		
	} else {
		print template();
	}
}

?>