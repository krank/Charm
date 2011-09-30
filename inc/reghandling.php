<?php

function regform($errors = array()) {
	$post = clean_array($_POST, array('name','mail'));
	$errors = clean_array($errors, array("name","mail","pass"));

	$lines = array(
		array(	"header"	=> "Anv&auml;ndarnamn", 
				"input"		=> $post['name'],
				"maxlen"	=> 64,
				"name"		=> 'name',
				"error"		=> $errors['name']
		),
		array(	"header"	=> "E-mailadress",
				"input"		=> $post['mail'],
				"maxlen"	=> 64,
				"name"		=> 'mail',
				"error"		=> $errors['mail']
		),
		array(	"header"	=> "L&ouml;senord",
				"input"		=> "",
				"maxlen"	=> 64,
				"name"		=> "pass",
				"type"		=> "password"
		),
		array(	"header"	=> "Upprepa l&ouml;senord",
				"input"		=> "",
				"maxlen"	=> 64,
				"name"		=> "pass2",
				"error"		=> $errors['pass'],
				"type"		=> "password"
		)
	);


	$form = makeform("Registrera dig", "?do=register", $lines, "Registrera!");

	return $form;
}

function register() {
	
	$errors = array();
	
	// Kolla om vi redan försöker registrera en användare
	
	if (isset($_POST['name'])) {
		
		
	
		$user = clean_array($_POST, array("name", "pass", "pass2", "mail"));
	
	    // Check the username
	    
	    // -- Check if username contains only alphanumeric
    	$namecorrect = chkusername($user['name']);
	    if ($namecorrect) {
	    	$errors['name'] = $namecorrect;
	    }
	    
	    // Check the passwords
	    
	    $passcorrect = chkpass($user['pass'],$user['pass2']);
	    if ($passcorrect) {
	    	$errors['pass'] = $passcorrect;
	    }

	    // Check the E-mail adress
	    
	    if(!chkmail($user['mail'])) {
	    	$errors['mail'] = 'Du m&aring;ste skriva in en riktig E-mailadress.';
	    }
	    
	    
	    // Om allt stämmer, registrera användaren, returnera "Lyckades, logga in nu"
	    if (sizeof($errors) == 0) {
			insert_user($user['name'],$user['pass'],$user['mail']);
			return template("Du &auml;r nu registrerad med anv&auml;ndarnamnet \"" . $user['name'] . "\". Du kan nu logga in via l&auml;nken till v&auml;nster.");
	    }
	
	}
	
	
	return template(regform($errors));
	
    
}


?>