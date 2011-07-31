<?php 

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once 'inc/userhandling.php';
include_once 'inc/reghandling.php';
include_once 'inc/loginhandling.php';
include_once 'inc/formhandling.php';
include_once 'inc/charhandling.php';
include_once 'inc/profilehandling.php';

session_start();

$action = array(	'register',
					'login',
					'logout',
					'forgot',
	
					'listforms',
					'editform',
					'showform',
					'delform',
	
					'editchar',
					'showchar',
					'listchars',
					'delchar',
	
					'showprofile',
					'editprofile',
					'changepass',
					'changepic'
					);

if (isset($_GET['do'])) {
	if (in_array($_GET['do'], $action)) {
		print call_user_func($_GET['do']);
	} else {
		print template("<h2>Ok&auml;nt kommando</h2>");
	}
} else {
	print template();
}

?>