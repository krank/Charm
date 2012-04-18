<?php

function clean_array($source, $items) {
	
	$safeitems = array();
	
	// Clean the POST values
	foreach ($items as $item) {
		// If the item has been set in $_POST
		if (isset($source[$item])) {
			// Insert it into the safe array
			$safeitems[$item] = $source[$item];
		} else {
			$safeitems[$item] = "";
		}
	}

	return $safeitems;
}

function checkerrors($values, $maxlengths) {
	
	$tr = array();
	// Go through the array of values to be checked
	foreach ($maxlengths as $value => $maxlength) {

		// Check the emptiness of the values (excepting desc)
		if ($values[$value] == "" && $value != "desc") {
			$tr["%$value"."_err%"] = "F&aring;r inte l&auml;mnas tomt";
		}

		// Check the length of the values
		if (strlen($values[$value]) > $maxlength) {
			$tr["%$value"."_err%"] = "Du f&aring;r inte skriva fler &auml;n ".$maxlength." tecken h&auml;r.";
		}
	}
	
	return $tr;
	
}

function xmltoform($xmldata, $editing=false) {
	
	$formhtml = "";
	
	$dom = new domDocument();
	
	if ($xmldata) {
		$dom->loadXML($xmldata);
	
	
		$root = $dom->documentElement;
		$groups = $root->getElementsByTagName('group');


		foreach($groups as $group){
			$title = $group->getAttribute('title');
			$gid = $group->getAttribute('id');

			$formhtml .= "<div id=\"$gid\" class=\"group\">\n"
							."<div class=\"groupheader\"><h2>$title</h2></div>\n";

			
			$formhtml .= "\n\t<table class=\"rows\">\n";
			
			
			$rows = $group->getElementsByTagName('row');

			foreach ($rows as $row) {
				$rid = $row->getAttribute('id');
				$formhtml	.= "\t<tr class=\"row\" id=\"$rid\">\n";

				$fields = $row->getElementsByTagName('field');
				
				foreach ($fields as $field) {
					$type = trim($field->getAttribute('type'));
					$value = $field->nodeValue;
					$fid = $field->getAttribute('id');

					$formhtml	.= "\t\t<td class=\"inner field $type\" id=\"$fid\">$value</td>\n";
				}

				$formhtml .= "\t</tr>\n";
			}

			$formhtml .= "\n\t</table>\n";


			$formhtml .= "</div>";


		}
	}
	
	
	return $formhtml;
}

function getdataset($source, $id) {
	
	$datasource = array();
	
	// Get result
	$result = makequery("SELECT ownerid, name, system, description, data AS xml, public FROM $source WHERE id='$id'");
	
	$row = mysql_fetch_array($result);
	
	if (!$row) {
		$row = array("ownerid"=>"", "name"=>"", "system"=>"", "description"=>"", "public"=>0);
	}
	
	// Load the form

	if (isset($_SESSION['userid'])) {
		$loggedinuser = $_SESSION['userid'];
	} else {
		$loggedinuser = "";
	}


	// Only load owned or public forms
	if (($row['ownerid'] == $loggedinuser) || $row['public'] == 1) {

		$datasource['ownerid'] = $row['ownerid'];
		$datasource['name'] = $row['name'];
		$datasource['system'] = $row['system'];
		$datasource['desc'] = $row['description'];
		$datasource['public'] = $row['public'];
		$datasource['formid'] = $id;
		$datasource['charid'] = $id;


		// Decompress the XML
		$datasource['xml'] = gzuncompress($row['xml']);

		return $datasource;

	} else {
		return false;
	}
}

function savedataset($destination, $name, $system, $public, $description, $xmldata, $id=False) {
	
	// Clean the data
	$name = mysql_real_escape_string($name);
	$system = mysql_real_escape_string($system);
	$public = mysql_real_escape_string($public);
	$description = mysql_real_escape_string($description);
	$xmldata = mysql_real_escape_string(gzcompress($xmldata));
	$id = intval($id);
	
	// If id is false, it's a new dataset. Insert it.
	if (!$id) {
		
		// If, of course, the user is logged in.
		if (isset($_SESSION['userid'])){
			
			$owner = $_SESSION['userid'];
			makequery("INSERT INTO $destination (ownerid, name, system, public, description, data) VALUES ($owner,'$name','$system','$public','$description', '$xmldata')");
	
			// Return the id of the dataset that was just inserted
			return mysql_insert_id();
		
		// Otherwise return false
		} else {
			return false; // No id was set, user not logged in. Don't save.
		}
			
	// If there's a non-false id, try to save the form (overwrite)
	} else {
		
		// Only save if the currently logged in user owns the form
		if (getowner($destination, $id) == $_SESSION['userid']) {
			
			makequery("UPDATE $destination SET 
							name='$name', 
							system='$system', 
							public='$public', 
							description='$description', 
							data='$xmldata' 
							WHERE id=$id
						");
			// Return the ID that was just inserted - it hasn't changed
			return $id;
		} else {
			return False; // id was set, but logged in user wasn't the owner
		}
	
	}
	
}

function getowner($source, $dataid) {
	// Get results
	
	$result = makequery("SELECT ownerid FROM $source WHERE id='$dataid'");
	
	$row = mysql_fetch_array($result);
	
	if ($row) {
		return $row['ownerid'];
	} else {
		return False;
	}
}

function showdata($source, $title, $id, $copyurl, $editurl=False, $makecharurl=False) {

	// Set defaults
	$tr = array("%title%"		=> $title,
				"%name_str%"	=> "",
				"%system_str%"	=> "",
				"%desc_str%"	=> "",
				"%xml_str%"	=> "",
				"%toptools%" => "");
	
	
	// Get the form
	$datasource = getdataset($source, $id);

	// If the form exists
	if ($datasource) {

		// Fill basic values

		foreach (array("name","system","desc") as $v) {
			$tr["%$v"."_str%"] = $datasource[$v];
		}

		// Generate HTML from XML
		$tr["%xml_str%"] = xmltoform($datasource['xml']);


		
		// If a user is actually logged in...
		if (isset($_SESSION['userid'])) {
			
			// If a Create Character url has been passed...
			if ($makecharurl) {
				$tr['%toptools%'] .= "<a href=\"".$makecharurl[0]."\" class=\"char button\">".$makecharurl[1]."</a>";
			}
			
			// Add the Copy button
			$tr['%toptools%'] .= "<a href=\"".$copyurl[0]."\" class=\"copy button\">".$copyurl[1]."</a>";
			
			// If the logged-in user owns the form...
			if ($editurl && ($datasource['ownerid'] == $_SESSION['userid'])) {
				$tr['%toptools%'] .= "<a href=\"".$editurl[0]."\" class=\"edit button\">".$editurl[1]."</a>";
			}
			
			

		}


		// Get and populate body
		$body = file_get_contents("template/dataview_tpl.html");
		$body = strtr($body,$tr);
		
	} else {
		$body = "Ok&auml;nd data";
	}

	// Return translated result

	return template($body);
	
}


function datalist($source, $title, $type, $newurl=False, $userid=False) {
	
	// Clean the user id
	$userid = intval($userid);
	
	// Default offset & maxitems
	$maxitems=15;
	$offset=0;
	
	// Get total number of items in list
	if ($userid) {
		$countquery = "SELECT COUNT(*) as num FROM $source WHERE ownerid=$userid";
		$douser = "&userid=$userid"; // Save for later: Pager links should include user's id
	} else {
		$countquery = "SELECT COUNT(*) as num FROM $source WHERE public=1";
		$douser = ""; // Save for later: Pager links should not include user's id
	}
	
	$result = makequery($countquery);
	$n = mysql_fetch_array($result);
	$num_items = $n['num'];
	
	
	// if offset has been specified
	if (isset($_GET['offset'])) {
		
		// Clean it up
		$offset = intval($_GET['offset']);
		
		// Set offset to last page, if it's higher
		$offset = min($num_items-($num_items % $maxitems), $offset);
	}
	
	
	
	// Prepare the translation array
	$tr = array('%toptools%' => "");
	
	
	$loggedinuser = False;
	
	// If the userid has been set, only show that user's forms	
	if ($userid) {
		// Use the user's name as title
		$tr["%title%"] = $title . " - " . getuserbyid($userid, 'username');
		
		// Don't include the Creator/Owner table heading
		$tr['%creator%'] = "";
		
		// Set the query to get all the user's forms
		$query = "SELECT id, name, system, description, changed 
					FROM $source 
					WHERE ownerid=$userid 
					ORDER BY changed DESC LIMIT $offset,$maxitems";
		
		// Save for later: The query to get the total number of forms for the user
		
		$countquery = "SELECT COUNT(*) as num FROM $source WHERE ownerid=$userid";
		
		// Check if viewing the forms of the currently logged-in user
		
		if (isset($_SESSION['userid'])) {
			if ($_SESSION['userid'] == $userid) {
				
				if ($newurl) {
					
					// Add the "new form" tool
					$tr['%toptools%'] = "<a href=\"".$newurl[0]."\" class=\"add button\">".$newurl[1]."</a>";
				}

				$loggedinuser = True; // Save to variable
				
			}
		}
		
		
	// If no user has been specified, show all datas in the system
	} else {
		
		$tr["%title%"] = $title;
		
		// Use the "Owner" table heading
		$tr['%creator%'] = "<th>&Auml;gare</th>";
		
		// Get all forms
		$query = "SELECT 
					$source.id, 
					$source.name, 
					$source.system, 
					$source.description, 
					$source.changed, 
					users.id AS userid,
					users.username AS owner,
					users.public AS public
				FROM $source,users 
				WHERE $source.public=1 AND $source.ownerid = users.id 
				ORDER BY changed DESC LIMIT $offset,$maxitems";
	}
	
	
	// Get the results
	$result = makequery($query);

	$tr['%tablecontents%'] = makerows($result, $type, $userid, $loggedinuser);
	
	if ($tr['%tablecontents%'] != "") {
		$tr['%pager%'] = makepager($type, $douser, $offset, $maxitems, $num_items);
	} else {
		$tr['%pager%'] = "";
	}
	
	$body = file_get_contents('template/datalist_tpl.html');
	$body = strtr($body,$tr);
	return template($body);

}

function makepager($type, $do_user, $current_offset, $max_items, $num_items) {
	
	// Create the pager

	$pager_span = 3;
	$pager = "";
	
	// The "back" arrow
	$pager .= "<a href=\"?do=list$type"."s$do_user&offset=".max(0,($current_offset-$max_items))."\">&lt;&lt;</a>";
	
	// Calculate the current page
	$current_page = ($current_offset / $max_items)+1;
	
	// Go through the valid pages; use ceil() to round up
	for ($p=1; $p<=ceil($num_items/$max_items); $p++) {
		// If it's the current page, show an ineffectual link
		if ($p == $current_page) {
			$pager .= "<a class=\"current\" href=\"#\"/>$p</a>";
			
		// if it's a page inside the pager's span (current page and x pages up/down), 
		// show a link
		} else if ($p > $current_page-$pager_span && $p < $current_page+$pager_span) {
			$o = ($p-1)*$max_items;
			$pager .= "<a href=\"?do=list$type"."s$do_user&offset=$o\">$p</a>";
			
		// if it's any of the two pages signaling the end of the pager's span, display ellipsis
		} else if ($p == $current_page+$pager_span || $p == $current_page-$pager_span) {
			$pager .= "&#0133;";
		}
	}
	
	// Only offsets resulting in valid pages should be available as Nexts.
	
	if ($current_offset+$max_items >= $num_items) {
		$next_offset = $current_offset;
	} else {
		$next_offset = $current_offset+$max_items;
	}
	
	// The "Next" arrow
	$pager .="<a href=\"?do=list$type"."s$do_user&offset=$next_offset\">&gt;&gt;</a>";
	
	return $pager;
}

function makerows($result, $type, $userid, $loggedinuser) {
	
	// Set defaults
	$rows = "";
	$odd = True; // First row is always odd
	
	// Go through the returned rows
	while($row = mysql_fetch_array($result)) {
		
		// Mark every other row
		if ($odd) {
			$rows .= "<tr class=\"odd\">\n";
		} else {
			$rows .= "<tr class=\"even\">\n";
		}
		
		
		// Set the name and id
		$name = $row['name'];
		$id = $row['id'];
		
		// Create the name cell
		$rows .= "\t<td><a href=\"?do=show$type&$type"."id=$id\">$name</td>\n";
		
		// Create the system cell
		$rows .= "\t<td>".$row['system']."</td>\n";
		
		// Create the changed-date cell
		$rows .= "\t<td>".$row['changed']."</td>\n";
		
		// If user hasn't been specified...
		if (!$userid) {
			// Create the owner username cell
			
			
			
			if ($row['public'] == 1) {
				$rows .= "\t<td><a href=\"?do=showprofile&userid={$row['userid']}\">{$row['owner']}</a></td>\n";
			} else {
				$rows .= "\t<td>".$row['owner']."</td>\n";
			}
			
			
			
			// Begin the tools cell
			$rows .= "\t<td>";
			
		// Otherwise, if userid is specified and a user is logged in...
		} else if (isset($_SESSION['userid'])) {
			
			$rows .= "\t<td>";
			
			// And the logged-in user and the userid are one and the same...
			if ($_SESSION['userid'] == $userid) {
				// Show a Delete button
				$rows .= "<a class=\"single button del\" title=\"Ta bort\" href=\"?do=del$type&$type"."id=$id\"></a>";
				
				// Show an Edit button
				$rows .= "\t<a class=\"single button edit\" title=\"Redigera\" href=\"?do=edit$type&$type"."id=$id\"></a>";
				
			}
		}
		
		if ($type == 'form') {
			$rows .= "<a class=\"single button char\" title=\"Skapa rollperson\" href=\"?do=editchar&formid=$id\"></a>";
		}
		
		// Show a Copy button
		$rows .= "<a class=\"single button copy\" title=\"Kopiera\" href=\"?do=edit$type&$type"."id=$id&makecopy\"></a>";
		
		$rows .= "</td>";
		
		// Finish off the rows
		$rows .= "</tr>\n";
		
		// Make so the next row is inverted
		$odd = !$odd;
	}
	
	return $rows;
}


function deldata($source, $type, $id, $confirmationtext, $sorrytext) {
	
	// Prepare the translation array
	$tr = array();

	// Clean the id
	$id = intval($id);
	
	// Get the owner of the form
	$ownerid = getowner($source, $id);

	// If the logged-in user is the owner of the form...
	if ($ownerid == $_SESSION['userid']) {

		// ...and the deletion has been confirmed...

		if (isset($_GET['confirmed'])) {
			// if it has, remove the form

			makequery("DELETE FROM $source WHERE id = $id");

			header("Location: /index.php?do=list$type"."s&userid=$ownerid");

		} else {
			// Otherwise, se the messagebox as a confirmation dialog

			$body = messagebox(array(
				"header"		=> "&Auml;r du s&auml;ker?",
				"content"		=> $confirmationtext,
				"leftbutton"	=> "<a class=\"button cross\" href=\"?do=list$type"."s&userid=$ownerid\">Nej</a>",
				"rightbutton"	=> "<a class=\"button check\" href=\"?do=del$type&confirmed&$type"."id=$id\">Ja</a>"
			));
		}

	} else {
		// If the user is not the owner, display an error 
		$body = messagebox(array(
			"header"		=> "Fel!",
			"content"		=> $sorrytext,
			"leftbutton"	=> "",
			"rightbutton"	=> "<a class=\"button cross\" href=\"?do=list$type"."s&userid={$_SESSION['userid']}\">OK</a>"
		));
	}

	return template($body);
	
}

function messagebox($pieces) {
	
	$tr['%messageheader%'] = $pieces["header"];
	$tr['%messagetext%'] = $pieces["content"];
	$tr['%leftbutton%'] = $pieces["leftbutton"];
	$tr['%rightbutton%'] = $pieces["rightbutton"];
	
	
	$body = file_get_contents('template/message_tpl.html');
	$body = strtr($body,$tr);
	
	return $body;
}


// --- Template stuff

function makemenu($list) {
	$mnu = "<ul class=\"menu\">\n";
	
	// Clean the level
	if (isset($_SESSION['level'])) {
		$lvl = $_SESSION['level'];
	} else {
		$lvl = -200;
	}
    
	//$mnu .= $lvl;
	
	// Go through the menu
	foreach ($list as $key => $content) {
            
		// Name the two parts for clarity
		$value = $content[0];
		$permission = $content[1];

		if ($permission === '*'  // Is the menu item available for everyone?
				|| ($permission >= 0 && $lvl >= $permission) // Is the menu item available to logged-in users of the current user's level?
				|| ($permission == -1 && $lvl == -200) // Is the menu item available specivivally when not logged in?
		){
			// If the item hasn't got a value, it's a divider
			if ($value != '') {
					$mnu .= "\t<li><a href=\"?do=$value\">$key</a></li>\n";
			} else { 
					$mnu .= "</ul><hr><ul class=\"menu\">";
			}
		}
	}
        
	return $mnu . "</ul>"; 
}

function template($content="") {
	$base = file_get_contents("./template/template.html");
	
	$tr = array();
	
	// Set the head replacement
	$tr['%head%'] = "";
	
	// Prepare the content
	$split_content = preg_split("/\|---/",$content);
	if (sizeof($split_content) > 1) {
		$content = $split_content[1];
		$tr['%head%'] = $split_content[0];
	}
	
	// Set the content replacement
	$tr['%content%'] = $content;

	// Clean up the session
	$sess = clean_array($_SESSION, array('userid','level'));

	// Create the menu as a multidimensional array
	$menu = array(
		'Alla rollpersoner' => array('listchars', '*'),
		'Alla rollformul&auml;r' => array('listforms', '*'),
		
		'cmd1' => array('', 0),
		'Dina rollpersoner' => array('listchars&userid='.$sess['userid'], 0),
		'Dina rollformul&auml;r' => array('listforms&userid='.$sess['userid'], 0),
		'Din profil' => array('showprofile',0),
		'Logga ut' => array('logout',0),
		
		'cmd2' => array('',1),
		'Skapa nyhet' => array('editarticle',1),
		'Hantera nyheter' => array('listarticles',1),
		
		'cmd3' => array('', -1),
		'Logga in' => array('login',-1),
		'Registrera dig' => array('register',-1)

	);

	$tr['%menu%'] = makemenu($menu);
	
	
	// get the replacement for the logged-in string
	
	if (isset($_SESSION['userid'])) {
		$tr['%loggedinstr%'] = "Inloggad som " . $_SESSION['username'];
	} else {
		$tr['%loggedinstr%'] = "Inte inloggad";
	}
	
	
	$output = strtr($base, $tr);
	
	return $output;
	
}


function makelines($lines) {
	$out = "";
	
	foreach ($lines as $line) {
		$out .= "\n<div class=\"formline\">\n";
		
		
		if (isset($line['header'])) {
			$out .= "\t<span class=\"formheader\">".$line['header']."</span><br />\n";
		}
		
		if (isset($line['input'])) {
			$text = $line['input'];
			
			if (isset($line['maxlength']))   $maxlength = $line['maxlen'];
			else $maxlength = "";
			
			$name = $line['name'];
			
			if (isset($line['type']))    $type = $line['type'];
			else $type = "text";
			
			$out .= "\t<div class=\"formtext\">"
						."<input type=\"$type\" name=\"$name\" value=\"$text\" maxlength=\"$maxlength\">"
					."</div>\n";
		} else if (isset($line['textarea'])) {
			$text = $line['textarea'];
			$maxlength = $line['maxlen'];
			$name = $line['name'];
			
			if (isset($line['class'])) {
				$class = " class=\"{$line['class']}\"";
			} else {
				$class="";
			}
			
			$out .= "\t<div class=\"formtext\">"
						."<textarea name=\"$name\" $class maxlength=\"$maxlength\">$text</textarea>"
					."</div>\n";
		} else if (isset($line['text'])) {
			$out .= "\t<div class=\"formtext\">".$line['text']."</div>\n";
		} else if (isset($line['checked'])) {
			$name = $line['name'];
			$selected = $line['selected'];
			$values = $line['values'];
			
			$i = 0;
			foreach ($line['options'] as $text) {
				if ($i == $selected) {
					$checked = "checked";
				} else {
					$checked = "";
				}
				
				$out .= "<input type=\"radio\" name=\"$name\" value=\"{$values[$i]}\" $checked />$text";
				$i++;
			}
		}
		
		if (isset($line['error'])) {
			$out .= "\t<div class=\"error\">".$line['error']."</div>\n";
		}
		
		$out .= "</div>\n";
	}
	
	
	return $out;
}


function makeform($title, $action, $lines, $submit, $message=false, $instructions=false) {
	$out = "<h2>$title</h2>\n";
	
	// If a message has been sent, insert it
	if ($message) $out .= "<div id=\"message\">$message</div>\n";
	
	if ($instructions) $out .= "<p>$instructions</p>";
	
	$out .= "<form action=\"$action\" method=\"post\" enctype=\"multipart/form-data\">\n"
				."\t".makelines($lines)."\n"
				."\t<input type=\"submit\" value=\"$submit\" name=\"submit\">\n"
			."</form>";
	
	return $out;
}

?>
