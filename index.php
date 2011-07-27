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

$action = array(
	'register' => 'register',
	'login' => 'login',
	'logout' => 'logout',
	'forgot' => 'forgot',
	
	'listforms' => 'listforms',
	'editform' => 'editform',
	'showform' => 'showform',
	'delform' => 'delform',
	
	'editchar' => 'editchar',
	'showchar' => 'showchar',
	'listchars' => 'listchars',
	'delchar' => 'delchar',
	
	'showprofile' => 'showprofile',
	'editprofile' => 'editprofile',
	'changepass' => 'changepass'
);

if (isset($_GET['do'])) {
	if (array_key_exists($_GET['do'], $action)) {
		print call_user_func($action[$_GET['do']]);
	} else {
		print template("<h2>Ok&auml;nt kommando</h2>");
	}
} else {
	print template();
}

?>