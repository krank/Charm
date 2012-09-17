<?php

function showprofile() {
	
	$tr = array(	"%charstream%" => "",
					"%formstream%" => "",
					"%toptools%" => "");

	// check if an user-id has been sent by $_GET
	if (isset($_GET['userid'])) {
		$userid = $_GET['userid'];
		$checkpublic = true;

		
		// Check if a user is logged in.
		if (isset($_SESSION['userid'])) {
			
			// Get watchlist stuff now that we know it'll be needed
			include_once('inc/followhandling.php');
			
			$tr['%toptools%'] = "<a class=\"rss button\" href=\"./rss.php?id=$userid\">Följ anv&auml;ndaren via RSS</a>";
			
			// Check if the user is watched by the logged-in user
			if (isWatchedBy($_SESSION['userid'], $userid)) {
				// Display a "unwatch"-button
				$tr['%toptools%'] .= "\n<a href=\"?do=unwatch&userid=".$userid."\" class=\"button\">Sluta bevaka den här anv&auml;ndaren</a>";
			} else {

				$tr['%toptools%'] .= "\n<a href=\"?do=watch&userid=".$userid."\" class=\"button\">Bevaka den här anv&auml;ndaren</a>";
			}
			
			
		}


 
		
	// If it hasn't, and a user is logged in, display the logged-in user
	} elseif (isset($_SESSION['userid'])) {
		$userid = $_SESSION['userid'];
		$checkpublic = false;
		
		// If the id belongs to the logged-in user
		if ($userid == $_SESSION['userid']) {
			// Display various user-centric stuff
			$tr['%toptools%'] =	 "\n<a class=\"rss button\" href=\"./rss.php?id=$userid\">RSS-flöde</a>"
								."<a href=\"?do=changepass\" class=\"edit button\">&Auml;ndra l&ouml;senord</a>"
								."\n<a href=\"?do=editprofile\" class=\"edit button\">Redigera profil</a>"
								."\n<a href=\"?do=changepic\" class=\"edit button\">&Auml;ndra profilbild</a>";
		} 

	} else {
		$userid = false;
	}
	
	// Get the user
	$user = getuserbyid($userid);
		
	// See if it exists and is public
	if (!$user) return template('<h2>Fel!</h2><p>Anv&auml;ndaren finns inte.');
	
	if (!$user['public'] && $checkpublic) return template('<h2>Fel!</h2><p>Anv&auml;ndarens profil &auml;r markerad som privat.');
		

	// Set basic info
	$tr['%userid%'] = $userid;
	$tr['%title%'] = "Anv&auml;ndarprofil f&ouml;r ". $user['username'];
	$tr['%name_str%'] = $user['username'];
	$tr['%desc_str%'] = $user['description'];

	$tr['%profileimg%'] = clean_profile_image($user['picture']);

	
	
	// Streams: Use the specified user's 10 latest characters and sheets
	foreach (array(	'characters' => array('%charstream%', 'char'),
					'forms' => array('%formstream%', 'form')) as $source => $str) {

		// Divide the $str from the array at the beginning into two different vars
		$repl = $str[0];
		$word = $str[1];

		// Begin table
		$tr[$repl] = make_datastream($source, $userid, $word, !$checkpublic);
	}
	
	
	$body = file_get_contents("template/profileview_tpl.html");
	$body = strtr($body,$tr);
	return template($body);
	
}

function make_datastream($source, $userid, $word, $nonpublic) {
	
	// Check wether to include nonpublic items
	if (!$nonpublic) $publ = "AND public=1";
	else $publ = "";
	
	// Create and execute query
	$query = "SELECT id, name, system FROM $source WHERE ownerid=$userid $publ ORDER BY changed LIMIT 0,10";
	$result = makequery($query);
	
	// Begin table
	$out = "<table>\n";

	$out .=	"\t<tr>\n"
				."\t\t<th>Namn</th>\n"
				."\t\t<th>System</th>\n"
			."</tr> \n";
	
	// Set initial oddness
	$odd = true;
	
	// Go through the results
	while($row = mysql_fetch_array($result)) {
		
		// Odd or even row
		if ($odd)	$out .= "\t<tr class=\"odd\">\n";
		else		$out .= "\t<tr class=\"even\">\n";
		
		// Cells
		$out .= "\t\t<td><a href=\"?do=show$word&$word"."id={$row['id']}\">{$row['name']}</a></td><td>{$row['system']}</td>";
		$out .= "\t</tr>\n";
		
		// Reverse oddness
		$odd = !$odd;
	}

	$out .= "</table>";
	
	return $out;
}

function editprofile() {
	// Check is user is logged in
	if (isset($_SESSION['userid'])) {
		$id = $_SESSION['userid'];
		$errors = array();
		$message = false;
		
		// If it is, get its values
		
		$userdata = getuserbyid($id, "username, email, description, public");
		$username = $userdata['username'];
		$email = $userdata['email'];
		$desc = $userdata['description'];
		$public = $userdata['public'];
		
		$newname = false;
		$newemail = false;
		$newdesc = false;
		
		// See if description has been sent

		
		// See if stuff has been sent
		if (isset($_POST['username'], $_POST['email'], $_POST['desc'], $_POST['public'])) {
			
			// Check the new name
			$errors["username"] = chkusername($_POST['username']);
			if (!$errors["username"] || $username == $_POST['username']) {
				$username = $_POST['username'];
				unset ($errors["username"]);
			}

			
			// Check the new mail
			if (chkmail($_POST['email'])) $email = $_POST['email'];
			else $errors['mail'] = 'Du m&aring;ste skriva in en riktig E-mailadress.';
			
			// Check the new description
			if (sizeof($_POST['desc']) <= 128) {
				$desc = $_POST['desc'];
			} else $errors['desc'] = 'Du f&aring;r max anv&auml;nda 128 tecken!';
			
			if ($_POST['public'] == 0 || $_POST['public'] == 1) {
				$public = $_POST['public'];
			}
			
			// If there are no errors
			if (count($errors) == 0) {

				// Modify the user accordingly
				modify_user($id,	$username,
									false,
									$email,
									$desc,
									$public);

				$message = "Profilen uppdaterades utan problem";
			}
		}

		
	
		
		$errors = clean_array($errors, array("username", "email", "desc"));
	
		// Prepare lines-array
		
		$lines = array(
			array(	"header"	=> "Anv&auml;ndarnamn", 
					"input"		=> $username,
					"maxlen"	=> 64,
					"name"		=> 'username',
					"error"		=> $errors['username']
			),
			array(	"header"	=> "E-mailadress",
					"input"		=> $email,
					"maxlen"	=> 64,
					"name"		=> 'email',
					"error"		=> $errors['email']
			),
			array(	"header"	=> "Beskrivning",
					"textarea"	=> $desc,
					"maxlen"	=> 1024,
					"name"		=> 'desc',
					"error"		=> $errors['desc']
			),
			array(	"header"	=> "Tillg&auml;nglighet",
					"checked"	=> true,
					"selected"	=> $public,
					"values"	=> array(0,1),
					"options"	=> array("Privat", "Visas f&ouml;r andra"),
					"name"		=> "public"
				)
		);

		// Return 
		
		$form = makeform("Redigera din profil", "?do=editprofile", $lines, "Spara &auml;ndringar", $message);

		return template($form);
		
	} else {
		return template("Du &auml;r inte inloggad.");
	}
	
	

}

function changepass() {
	
	if (isset($_SESSION['userid'])) {
	
		$errors = array();
		$message = "";

		if (isset($_POST['oldpass'], $_POST['pass'], $_POST['pass2'])) {
			// get old password
			$oldpass = getuserbyid($_SESSION['userid'], 'password');

			// If they don't match, throw an error
			if ($oldpass != md5($_POST['oldpass'])) {
				$errors['oldpass'] = "Felaktigt l&ouml;senord";
			}

			// If the new passwords aren't OK, throw an error
			$matcherr = chkpass($_POST['pass'], $_POST['pass2']);
			if ($matcherr) {
				$errors['pass'] = $matcherr;
			}

			// If there are no errors at this point, change the password
			if (count($errors) == 0) {
				$message = "L&ouml;senordet &auml;ndrades.";
				
				modify_user($_SESSION['userid'], false,$_POST['pass']);
				
			} else {
				$message = "L&ouml;senordet &auml;ndrades inte.";
			}

		}

		$errors = clean_array($errors, array('oldpass', 'pass'));

		$lines = array(
			array(	"header"	=> "Ditt nuvarande l&ouml;senord", 
					"input"		=> "",
					"maxlen"	=> 64,
					"name"		=> 'oldpass',
					"type"		=> "password",
					"error"		=> $errors['oldpass']
			),
			array(	"header"	=> "Ditt nya l&ouml;senord",
					"input"		=> "",
					"maxlen"	=> 64,
					"name"		=> "pass",
					"type"		=> "password"
			),
			array(	"header"	=> "Upprepa ditt nya l&ouml;senord",
					"input"		=> "",
					"maxlen"	=> 64,
					"name"		=> "pass2",
					"type"		=> "password",
					"error"		=> $errors['pass']
			)
		);

		$form = makeform("&Auml;ndra l&ouml;senord", "?do=changepass", $lines, "&Auml;ndra", $message);

		return template($form);
	}
}

function changepic() {

	if (isset($_SESSION['userid'])) {

		$error = "";
		$message = "";

		// Preliminary, get the user's image from the database

		$image = getuserbyid($_SESSION['userid'], "picture");
		$image = clean_profile_image($image);
		
		// Check if a file has been sent
		if (isset($_FILES['image']['name'])) {
			// Attempt to save the image
			$outimage = "./images/profiles/{$_SESSION['userid']}.jpg";
			$error = save_profile_image("./images/profiles/{$_SESSION['userid']}.jpg");
			if (!$error) {
				$image = $outimage;
			}
		}


		$lines = array(
			array(	"header"	=> "Nuvarande profilbild", 
					"text"		=> "<img src=\"$image\">"
			),
			array(	"header"	=> "", 
					"input"		=> "204800",
					"maxlen"	=> "",
					"name"		=> "MAX_FILE_SIZE",
					"type"		=> "hidden"
			),
			array(	"header"	=> "V&auml;lj en bild att ladda upp (jpg, png, gif, max 200kb).", 
					"input"		=> "",
					"maxlen"	=> "",
					"name"		=> "image",
					"type"		=> "file",
					"error"		=> $error
			)
		);

		$form = makeform("&Auml;ndra profilbild","?do=changepic", $lines, "Ladda upp");

		return template($form);
		
	}

}


function save_profile_image($outputfile) {
	
	$targetwidth = 128;
	$targetheight = 128;
	
	if ($_FILES['image']['error'] === UPLOAD_ERR_FORM_SIZE
			|| $_FILES['image']['error'] === UPLOAD_ERR_INI_SIZE) {
		
		return "Filen du f&ouml;rs&ouml;kte ladda upp var f&ouml;r stor!";
		
	} else if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {

		$filename = $_FILES['image']['tmp_name'];

		$info = getimagesize($filename);

		if (!$info) {
			return "Ok&auml;nt filformat";
		} else {
			$width = $info[0];
			$height = $info[1];
			$type = $info[2];

			// Load image

			if (		$type == IMAGETYPE_JPEG ) {
				$image = imagecreatefromjpeg($filename);

			} else if(	$type == IMAGETYPE_GIF ) {
				$image = imagecreatefromgif($filename);

			} else if(	$type == IMAGETYPE_PNG ) {
				$image = imagecreatefrompng($filename);

			} else {
				return "Du m&aring;ste v&auml;lja en fil i n&aring;got av formaten jpg, png eller gif.";
			}
			
			
			// If either h/w are above 128, or if at least one is below...
			if ($height > $targetheight || $width > $targetwidth 
					|| ($width < $targetwidth && $height < $targetheight)) {

				// ratio (the height's ratio compared to the with)

				$ratio = $width / $height;
				
				// Calculate new height/width

				if ($width > $height) {
					$newwidth = $targetwidth;
					$newheight = $targetwidth/$ratio;
				} else {
					$newheight = $targetheight;
					$newwidth = $targetheight*$ratio;
				}

				// Create new image
				$newimage = imagecreatetruecolor(128, 128);
				
				// Fill the new image with white (16777215 pregenerated by imagecolorallocate)
				imagefilledrectangle($newimage, 0, 0, 128, 128, 16777215 );
				
				// Copy the old contents to it, resized
				$dst_x = ($targetwidth/2) - ($newwidth/2);
				$dst_y = ($targetheight/2) - ($newheight/2);
				
				imagecopyresampled($newimage, $image, $dst_x, $dst_y, 0, 0, $newwidth, $newheight, $width, $height);

				// Copy the new image to the old
				$image = $newimage;
			}
			
			// Save the file to the correct place
			imagejpeg( $image, $outputfile, 90 );

			// Destroy the image, freeing up memory
			imagedestroy($image);
			
			// Set the user's image in the database to its path
			modify_user($_SESSION['userid'], false, false, false, false, false, false, $outputfile);

			return false;
		}
	}
}


?>
