<?php

function loginform($error = False) {
	
	// Display registration form, incl. template
	$body = file_get_contents('template/login_tpl.html');

	$tr = array();
	
	if (isset($_POST['name'])) {
		$tr['%name_str%'] = $_POST['name'];
	} else {
		$tr['%name_str%'] = "";
	}
	
	if ($error) {
		$tr['%login_err%'] = "Felaktiga inloggningsuppgifter!";	
	} else {
		$tr['%login_err%'] = "";		
	}
	
	

	
	
	$body = strtr($body,$tr);
	
	
	return template($body);
}


function login() {
	
	if (isset($_POST['name']) && isset($_POST['pass'])) {
		$pass = substr($_POST['pass'],0,64);
		$name = substr($_POST['name'],0,64);
		
		$userdata = get_user($name, $pass);
		
		if ($userdata) {
			$_SESSION['username'] = $userdata['username'];
			$_SESSION['userid'] = $userdata['id'];
			return template("Du &auml;r nu inloggad.");
		} else {
			return template("Could not find user!");
		}
		
		return template();
	}
	
	return loginform();
}

function logout() {
	$_SESSION['userid'] = null;
	session_destroy();
	return template("Du &auml;r nu utloggad.");
}


?>