<?php

include_once 'inc/userhandling.php';
include_once 'inc/mailvalidate.php';


function regform($errors = array()) {
	// Display registration form, incl. template
	$body = file_get_contents('template/register_tpl.html');

	
	$tr = array(	'%name_err%' => '',
					'%name_str%' => '', 
					'%mail_err%' => '',
					'%mail_str%' => '', 
					'%pass_err%' => '');
	
	if (sizeof($errors) > 0) {
		
		if (isset($_POST['name'])) {
			$tr['%name_str%'] = $_POST['name'];
		}
		
		if (isset($_POST['mail'])) {
			$tr['%mail_str%'] = $_POST['mail'];
		}
		
		if (isset($errors['name'])) {
			$tr['%name_err%'] = $errors['name'];
		}
	
		if (isset($errors['mail'])) {
			$tr['%mail_err%'] = $errors['mail'];
		}
		
		if (isset($errors['pass'])) {
			$tr['%pass_err%'] = $errors['pass']; 
		}
	}
	
	

	
	
	$body = strtr($body,$tr);
	
	
	return template($body);
}


function register() {
	
	// Kolla om vi redan försöker registrera en användare
	
	if (isset($_POST['name'])) {
		
		$result = array();
	
		$user = $_POST;
		// Skapa en pålitlig användar-array
	    foreach(array("name", "pass", "pass2", "mail") as $s) {
	        if (!isset($user[$s])) $user[$s] = "";
	    }
	
	    // Check the username
	    
	    // -- Check if username contains only alphanumeric
    	$namecorrect = chkusername($user['name']);
	    if ($namecorrect) {
	    	$result['name'] = $namecorrect;
	    }
	    
	    // Check the passwords
	    
	    $passcorrect = chkpass($user['pass'],$user['pass2']);
	    if ($passcorrect) {
	    	$result['pass'] = $passcorrect;
	    }
	    

	    // Check the E-mail adress
	    
	    if(!chkmail($user['mail'])) {
	    	$result['mail'] = 'Du m&aring;ste skriva in en riktig E-mailadress.';
	    }
	    
	    
	    // Om allt stämmer, registrera användaren, returnera "Lyckades, logga in nu"
	    if (sizeof($result) == 0) {
			insert_user($user['name'],$user['pass'],$user['mail']);
			return template("Du &auml;r nu registrerad med anv&auml;ndarnamnet \"" . $user['name'] . "\". Du kan nu logga in via l&auml;nken till v&auml;nster.");
	    	
	    // Om det inte stämmer, returnera regform, med fel
	    } else {
	    	return regform($result);
	    }
	    
	    
	    

    
	} else {
		return regform();
	}
    
}


?>