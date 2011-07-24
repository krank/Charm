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
	
}

?>
