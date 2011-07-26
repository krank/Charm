<?php

include_once 'inc/settings.php';
include_once 'inc/db.php';


function insert_user($username, $password, $email, $id=False){
	// Insert a user into the database
	makequery("INSERT INTO users(username, password, email) VALUES ('$username', md5('$password'), '$email')");
}

function modify_user($id, $username=false, $password=false, $email=false, $desc=false, $public=false, $level=false, $picture=false){
	// Modify a user in the database
	
	$values = array();
	$cols = array();
	$upd = "";
	
	if ($username) {
		$upd .= " username='$username'";
	}
	if ($password) {
		$password = md5($password);
		$upd .= ", password='$password'";
	}
	if ($email) {
		$upd .= ", email='$email'";
	}
	if ($desc) {
		$upd .= ", description='$desc'";
	}
	if ($public) {
		$upd .= ", public='$public'";
	}
	
	$query = "UPDATE users SET $upd WHERE id=$id";
	
	makequery($query);
}

function del_user($id) {
	// Deletes a user from the database
}


function get_user($username, $password) {
	
	// Prepare the username and password
	$username = mysql_real_escape_string ($username);
	$password = md5($password);
	
	// Get results
	$result = makequery("SELECT id, username FROM users WHERE username='$username' AND password='$password'");
	
	$row = mysql_fetch_array($result);
	
	if ($row) {
		return $row;
	} else {
		return False;
	}
	
}

function getuserbyid($id, $field = "*") {
	$id = intval($id);
	
	$result = makequery("SELECT $field FROM users WHERE id=$id");
	$row = mysql_fetch_array($result);
	
	if ($row) {
		if ($field != "*") {
			if (count($row) > 2) {
				return $row;
			} else {
				return $row[$field];
			}
		} else {
			return $row;
		}
	} else {
		return false;
	}
}

function user_exists($username) {
	// Check if user exists in the database

	// Clean the username string
	$username = mysql_real_escape_string ($username);

	// Search for the username, coun the number of times it appears

	$result = mysql_query("SELECT COUNT(*) FROM users WHERE username='$username'");
	
	// Get the first row of results
	$row = mysql_fetch_array($result);
	
	// If the username appears more than 0 times, the user already exists
	if ($row["COUNT(*)"] > 0) return True;
	else return False;
}




function chkusername($username) {
    // -- Check if username contains only alphanumeric
    if (!preg_match('/^\w+$/',$username)) {
    	return 'Anv&auml;ndarnamnet f&aring;r bara best&aring; av siffror, bokst&auml;ver och understreck.';

    // -- Check if username is between 3 and 64 characters in length
    } else if (!in_array(strlen($username), range(3,64))) {
    	return 'Anv&auml;ndarnamnet m&aring;ste vara mellan 3 och 64 tecken l&aring;ngt';

    // -- Check if username aleady exists in the database
    } else if (user_exists($username)) {
    	return 'Det finns redan en anv&auml;ndare med det namnet!';
    } else {
    	return False;
    }
}




function chkpass($pass, $pass2) {
	    // -- Check if the passwords match
	    if ($pass != $pass2) {
	    	return "L&ouml;senorden matchade inte";
	    	
	    // -- Check if the password has the correct length
	    } else if (!in_array(strlen($pass), range(5,64))) {
	    	return 'L&ouml;senordet m&aring;ste vara mellan 5 och 64 tecken l&aring;ngt.';
	    } else {
	    	return False;
	    }
}

function chkmail($email) {
	$isValid = true;
	$atIndex = strrpos($email, "@");
	if (is_bool($atIndex) && !$atIndex) {
		$isValid = false;
	} else {
		$domain = substr($email, $atIndex+1);
		$local = substr($email, 0, $atIndex);
		$localLen = strlen($local);
		$domainLen = strlen($domain);
		if ($localLen < 1 || $localLen > 64) {
			// local part length exceeded
			$isValid = false;
		} else if ($domainLen < 1 || $domainLen > 255) {
			// domain part length exceeded
			$isValid = false;
		} else if ($local[0] == '.' || $local[$localLen-1] == '.') {
			// local part starts or ends with '.'
			$isValid = false;
		} else if (preg_match('/\\.\\./', $local)) {
			// local part has two consecutive dots
			$isValid = false;
		} else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain)) {
			// character not valid in domain part
			$isValid = false;
		} else if (preg_match('/\\.\\./', $domain)) {
			// domain part has two consecutive dots
			$isValid = false;
		} else if (!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
		str_replace("\\\\","",$local))) {
			// character not valid in local part unless
			// local part is quoted
			if (!preg_match('/^"(\\\\"|[^"])+"$/',
				str_replace("\\\\","",$local)))
			{
				$isValid = false;
			}
		}
		if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A"))) {
			// domain not found in DNS
			$isValid = false;
		}
	}
	return $isValid;
}

?>