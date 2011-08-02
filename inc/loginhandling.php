<?php

function forgot() {
	
	$error = "";
	$message = false;
	
	if (isset($_POST['mail'])) {
		// Check if mail is in database
		$mail = mysql_real_escape_string($_POST['mail']);
		
		$query = "SELECT id FROM users WHERE email = '$mail'";
		$result = makequery($query);
		$row = mysql_fetch_array($result);
		
		if ($row) {
			// If it is, generate new password randomly, and insert it
			
			$randompass = mysql_real_escape_string(makerandompass());
			
			modify_user($row['id'], false, $randompass);
			
			// And then mail it to the adress
			mail($mail, 'Ditt nya l&ouml;senord från rollperson.se', "Hej!\nDitt nya lösenord är ". $randompass, 'From: noreply@rollperson.se');

			$message = "Ditt nya l&ouml;senord har skickats! Om du inte f&aring;r det, kolla bland skr&auml;pposten.";
			
		} else {
			$error = "Mailadressen $mail finns inte i v&aring;r databas!";
		}

	}
	
	$lines = array(
		array(	"header"	=> "E-mailadress", 
				"input"		=> "",
				"maxlen"	=> 1024,
				"name"		=> 'mail',
				"error"		=> $error
		)
	);
	
	$form = makeform("Gl&ouml;mt l&ouml;senordet", "?do=forgot", $lines, "Skicka", $message, "Skriv in den mailadress du registrerade dig med nedan, så genererar vi ett nytt lösenord och mailar det till dig.");
	return template($form);
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
	
	// Check if name & pass have been sent by POST
	if (isset($_POST['username']) && isset($_POST['pass'])) {
		
		// If they have, reduce them to 64 characters each
		$pass = substr($_POST['pass'],0,64);
		$name = substr($_POST['username'],0,64);
		
		// Try to get userdata based on username & password
		$userdata = get_user($name, $pass);
		
		// If the user exists
		if ($userdata) {
			// Set session variables
			$_SESSION['username'] = $userdata['username'];
			$_SESSION['userid'] = $userdata['id'];
			$_SESSION['level'] = $userdata['level'];
			
			// Return login confirmation
			return template("Du &auml;r nu inloggad.");
		} else {
			// Return login form with error
			$error = "Felaktig anv&auml;ndare eller l&ouml;senord.";
		}
	} else {
		// If they haven't, set name & error to blank
		$name = "";
		$error = "";
	}
	
	// Prepare line arrays
	$lines = array(
		array(	"header"	=> "Anv&auml;ndarnamn", 
				"input"		=> $name,
				"maxlen"	=> 64,
				"name"		=> 'username'
		),
		array(	"header"	=> "L&ouml;senord",
				"input"		=> "",
				"maxlen"	=> 64,
				"name"		=> "pass",
				"type"		=> "password",
				"error"		=> $error
		),
		array(	
				"text"		=> "<a href=\"?do=forgot\">Gl&ouml;mt l&ouml;senordet?</a>"
		)
	);
	
	// Create the form, and return it.
	$form = makeform("Logga in", "?do=login", $lines, "Logga in");
	return template($form);
}

function logout() {
	$_SESSION['userid'] = null;
	session_destroy();
	return template("Du &auml;r nu utloggad.");
}


?>