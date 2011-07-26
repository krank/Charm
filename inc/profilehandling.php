<?php

function showprofile() {
	
	$tr = array(	"%charstream%" => "",
					"%formstream%" => "",
					"%toptools%" => "");
	
	// Default; Display Not Logged In.
	
	
	// check if an user-id has been sent by $_GET
	if (isset($_GET['userid'])) {
		$userid = $_GET['userid'];
		$checkpublic = true;
		
	// If it hasn't, and a user is logged in, display the logged-in user
	} elseif (isset($_SESSION['userid'])) {
		$userid = $_SESSION['userid'];
		$checkpublic = false;
	}
	
	// Get the user
	$user = getuserbyid($userid);
		
	// See if it exists and is public

	if ($user) {
		if ($user['public'] || !$checkpublic) {
			// If it does and is, use its values
			$tr['%userid%'] = $userid;
			$tr['%title%'] = "Anv&auml;ndarprofil f&ouml;r ". $user['username'];
			$tr['%name_str%'] = $user['username'];
			$tr['%desc_str%'] = $user['description'];

			if ($user['picture']) {
				$tr['%profileimg%'] = './images/profiles/'.$user['picture'];
			} else {
				$tr['%profileimg%'] = './images/emptyprofile.png';
			}
			
			// Streams: Use the specified user's 10 latest characters and sheets
			foreach (array(	'characters' => array('%charstream%', 'char'),
							'forms' => array('%formstream%', 'form')) as $source => $str) {
				
				// Use different queries depending on whether or not to care about publicness
				if ($checkpublic) {
					$query = "SELECT id, name, system FROM $source WHERE ownerid=$userid AND public=1 ORDER BY changed LIMIT 0,10";
				} else {
					$query = "SELECT id, name, system FROM $source WHERE ownerid=$userid ORDER BY changed LIMIT 0,10";
				}
				
				// Make the query
				$result = makequery($query);
				
				// Divide the $str from the array at the beginning into two different vars
				$repl = $str[0];
				$word = $str[1];
				
				// Begin table
				$tr[$repl] .= "<table>\n";
				
				$tr[$repl] .=	"\t<tr>\n"
									."\t\t<th>Namn</th>\n"
									."\t\t<th>System</th>\n"
								."</tr> \n";
				
				
				$odd = true;
				while($row = mysql_fetch_array($result)) {
					$id = $row['id'];
					$name = $row['name'];
					$system = $row['system'];
					
					if ($odd) {
						$tr[$repl] .= "\t<tr class=\"odd\">\n";
					} else {
						$tr[$repl] .= "\t<tr class=\"even\">\n";
					}
					
					$tr[$repl] .= "\t\t<td><a href=\"./index.php?do=show$word&$word"."id=$id\">$name</a></td><td>$system</td>";
					
					$tr[$repl] .= "\t</tr>\n";
					
					$odd = !$odd;
				}
				
				$tr[$repl] .= "</table>";
				
			}
			
		} else {
			return template('<h2>Fel!</h2><p>Anv&auml;ndarens profil &auml;r markerad som privat.');
		}
	} else {
		return template('<h2>Fel!</h2><p>Anv&auml;ndaren finns inte.');
	}
	
	
	
	$body = file_get_contents("template/profileview_tpl.html");
	$body = strtr($body,$tr);
	return template($body);
	
}

function editprofile() {
	// Check is user is logged in
	if (isset($_SESSION['userid'])) {
		$userid = $_SESSION['userid'];
		$id = $_SESSION['userid'];
		$tr = array();
		$errors = array();
		
		// If it is, get its values
		
		$userdata = getuserbyid($id, "username, email, description");
		$name = $userdata['username'];
		$email = $userdata['email'];
		$desc = $userdata['description'];
		
		$newname = false;
		$newemail = false;
		$newdesc = false;
		
		// See if username has been sent
		if (isset($_POST['name'])) {
			$newname = $_POST['name'];
			
			// If it has, check it
			$currentname = getuserbyid($id, 'username');
			if ($currentname != $newname) {
				$errors["name"] = chkusername($newname);
				
				// If it checks out, insert it into the database
				if (!$errors["name"])	$name = $newname;
				else					$newname = false;
			}
		}
		
		// See is email adress has been sent
		if (isset($_POST['email'])) {
			$newemail = $_POST['email'];
			
			// If it checks out, insert it into the database
			if (chkmail($newemail)) {
				$email = $newemail;
			} else {
				$newemail = false;
				$errors['mail'] = 'Du m&aring;ste skriva in en riktig E-mailadress.';
			}
		}
		
		// See if description has been sent
		if (isset($_POST['desc'])) {
			$newdesc = $_POST['desc'];
			$desc = $newdesc;
		}

		
		// If there are no errors & at least 1 of the above have been sent...
		if ($newname || $newemail && count($errors) == 0) {
			// Modify the user accordingly
			modify_user($id,	$newname,
								false,
								$newemail,
								$newdesc);
		}
		
		
		$errors = clean_array($errors, array("name", "email"));
	
		// Prepare lines-array
		
		$lines = array(
			array(	"header"	=> "Anv&auml;ndarnamn", 
					"input"		=> $name,
					"maxlen"	=> 64,
					"name"		=> 'name',
					"error"		=> $errors["name"]
			),
			array(	"header"	=> "E-mailadress",
					"input"		=> $email,
					"maxlen"	=> 64,
					"name"		=> 'email',
					"error"		=> $errors["email"]
			),
			array(	"header"	=> "Beskrivning",
					"textarea"	=> $desc,
					"maxlen"	=> 1024,
					"name"		=> 'desc'
			)
		);
		
		
		// Insert lines
		$tr['%lines%'] = makelines($lines);

		// Return 
		
		$body = file_get_contents("template/profileedit_tpl.html");
		$body = strtr($body,$tr);
		return template($body);
		
	} else {
		return template("Du &auml;r inte inloggad.");
	}
	
	

}

?>
