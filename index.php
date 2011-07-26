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

if (isset($_GET['do'])) {
	if ($_GET['do'] == 'register') {
		print register();
		
	} else if ($_GET['do'] == 'login') {
		print login();
		
	} else if ($_GET['do'] == 'logout') {
		print logout();
		
	} else if ($_GET['do'] == 'forgot') {
		print forgot();
		
	} else if ($_GET['do'] == 'listforms') {
		if (isset($_GET['userid'])) {
			print formlist($_GET['userid']);
		} else {
			print formlist();
		}
	} else if ($_GET['do'] == 'editform') {
		print editform();
	} else if ($_GET['do'] == 'showform') {
		print showform();
	} else if ($_GET['do'] == 'delform') {
		print delform();
	} else if ($_GET['do'] == 'editchar') {
		print editchar();
	} else if ($_GET['do'] == 'showchar') {
		print showchar();
	} else if ($_GET['do'] == 'listchars') {
		print listchar();
	} else if ($_GET['do'] == 'delchar') {
		print delchar();
	} else if ($_GET['do'] == 'showprofile') {
		print showprofile();
	} else {
		print template("<h2>Ok&auml;nt kommando</h2>");
	}
} else {
	print template();
}

?>