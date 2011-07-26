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


function forgot() {
	
	$tr = array("%forgot_err%" => "");
	
	if (isset($_POST['mail'])) {
		// Check if mail is in database
		$mail = mysql_real_escape_string($_POST['mail']);
		
		$query = "SELECT id FROM users WHERE email = '$mail'";
		$result = makequery($query);
		$row = mysql_fetch_array($result);
		
		if ($row) {
			// If it is, generate new password randomly, and insert it
			
			$randompass = mysql_real_escape_string(makerandompass());
			
			modify_user($row['id'],false,$randompass);
			
			// And then mail it to the adress
			mail($mail, 'Ditt nya lösenord från rollperson.se', "Hej!\nDitt nya lösenord är ". $randompass, 'From: noreply@rollperson.se');

			
		} else {
			return template("<h2>Fel!</h2><p>Mailadressen $mail finns inte i vår databas!</p>");
		}

	}
	
	$body = file_get_contents('template/forgot_tpl.html');
	$body = strtr($body,$tr);
	return template($body);
}



function makerandompass() {
    $password = "";
    $loop = 0;
    while ($loop < 12)
    {
        $randomchar = chr(mt_rand(35, 126));
        if (!strstr($password, $randomchar))
        {
            $password .= $randomchar;
            $loop++;
        }
    }
    return $password;
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