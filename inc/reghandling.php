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
		$post = clean_array($_POST, array('name','mail'));
		$errors = clean_array($errors, array("name","mail","pass"));
		
		$tr['%name_str%'] = $_POST['name'];
		$tr['%mail_str%'] = $_POST['mail'];
		
		$tr['%name_err%'] = $errors['name'];
		$tr['%mail_err%'] = $errors['mail'];
		$tr['%pass_err%'] = $errors['pass']; 

	}
	
	
	
	$lines = array(
		array(	"header"	=> "Anv&auml;ndarnamn", 
				"input"		=> $tr['%name_str%'],
				"maxlen"	=> 64,
				"name"		=> 'name',
				"error"		=> $tr['%name_err%']
		),
		array(	"header"	=> "E-mailadress",
				"input"		=> $tr['%mail_str%'],
				"maxlen"	=> 64,
				"name"		=> 'mail',
				"error"		=> $tr['%mail_err%']
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
				"error"		=> $tr['%pass_err%'],
				"type"		=> "password"
		)
		
	);
	
	$tr['%lines%'] = makelines($lines);

	
	
	$body = strtr($body,$tr);
	
	
	return template($body);
}


function register() {
	
	// Kolla om vi redan försöker registrera en användare
	
	if (isset($_POST['name'])) {
		
		$result = array();
	
		$user = clean_array($_POST, array("name", "pass", "pass2", "mail"));
	
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