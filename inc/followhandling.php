<?php

/* =============================================================================
 * User Interface / externally called functions 
 */

function watch() {
	
	// Must be logged in to watch a user
	if (isset($_SESSION['userid'])) {
		
		$watcher_id = $_SESSION['userid'];
		
		if (isset($_GET['userid'])) {
			$watched_id = $_GET['userid'];
			
			// Check if watcher is already watching the watched
			if (!isWatchedBy($watcher_id, $watched_id)) {
				
				$watched_name = addWatch($watcher_id, $watched_id);
				
				if ($watched_name) {
					return template("<h2>Bevakning aktiverad</h2>
						<p>Du bevakar nu anv&auml;ndaren $watched_name.
						<p><a href=\"?do=showprofile&userid=$watched_id\">Klicka h&auml;r f&ouml;r att komma tillbaks till anv&auml;ndarens profil</a>");
				} else {
					return template("<h2>Fel!</h2><p>Det gick inte att l&auml;gga in en bevakning av den h&auml;r anv&auml;ndaren");
				}
				
			} else {
				return template("<h2>Fel!</h2><p>Du bevakar redan den h&auml;r anv&auml;ndaren.");
			}
		} else {
			return template("<h2>Fel!</h2><p>Du m&aring;ste ange en anv&auml;ndare att bevaka!");
		}
	} else {
		return template("<h2>Fel!</h2><p>Du m&aring;ste vara inloggad f&ouml;r att kunna bevaka anv&auml;ndare.");
	}
}


// Unwatch
/* Clean inputs
 * Execute unwatching
 * Check result... true/false
 * Display output
 */

function unwatch() {
	// Must be logged in to watch a user
	if (isset($_SESSION['userid'])) {
		
		$watcher_id = $_SESSION['userid'];
		
		if (isset($_GET['userid'])) {
			$watched_id = $_GET['userid'];
			
			// Check if the watched is, indeed, actually watched by the watcher
			if (isWatchedBy($watcher_id, $watched_id)) {
				
				$watched_name = delWatch($watcher_id, $watched_id);
				
				if ($watched_name) {
					return template("<h2>Bevakning inaktiverad</h2>
						<p>Du bevakar inte l&auml;ngre anv&auml;ndaren $watched_name.
						<p><a href=\"?do=showprofile&userid=$watched_id\">Klicka h&auml;r f&ouml;r att komma tillbaks till anv&auml;ndarens profil</a>");
				} else {
					return template("<h2>Fel!</h2><p>Det gick inte att ta bort bevakningen av den h&auml;r anv&auml;ndaren. &Auml;r du s&auml;ker p&aring; att du bevakar den?");
				}
				
			} else {
				return template("<h2>Fel!</h2><p>Du bevakar inte den h&auml;r anv&auml;ndaren.");
			}
		} else {
			return template("<h2>Fel!</h2><p>Du m&aring;ste ange en anv&auml;ndare att sluta bevaka!");
		}
	} else {
		return template("<h2>Fel!</h2><p>Du m&aring;ste vara inloggad f&ouml;r att kunna sluta bevaka anv&auml;ndare.");
	}
}


// Listwatched
/* Clean input
 * Get list of id's and names
 * Generate list
 * Display list
 */


/* =============================================================================
 * Internal functions
 */

function addWatch($watcher_id, $watched_id) {
	
	// Make sure we're dealing with integers
	$watched_id = (int)$watched_id;
	$watcher_id = (int)$watcher_id;
	
	// Get the username of the watched
	$watchedname = getuserbyid($watched_id, 'username');
	
	// if the watcher and the watched exist
	if (getuserbyid($watcher_id) && $watchedname) {
		$query = "INSERT INTO watch (watcher_id ,watched_id) VALUES ($watcher_id, $watched_id)";
		$result = makequery($query);
		if ($result) {
			return $watchedname;
		}
	}
	return false;
	
}

function delWatch($watcher_id, $watched_id) {

	// Make sure we're dealing with integers
	$watched_id = (int)$watched_id;
	$watcher_id = (int)$watcher_id;
	
	// Get the username of the watched
	$watchedname = getuserbyid($watched_id, 'username');
	
	// Attempt to remove the watching
	$query = "DELETE FROM watch WHERE watcher_id=$watcher_id AND watched_ID=$watched_id";
	$result = makequery($query);
	if ($result) {
		return $watchedname;
	}
	return false;
}

function isWatchedBy($watcher_id, $watched_id) {
	
	// Make sure we're dealing with integers
	$watched_id = (int)$watched_id;
	$watcher_id = (int)$watcher_id;
	
	// Create and execute query
	$query = "SELECT COUNT(id) AS 'exists' FROM watch WHERE watcher_id=$watcher_id AND watched_id=$watched_id";
	$result = makequery($query);
	$result = mysql_fetch_array($result);
	$result = $result[0];
	
	if ($result == 0) {
		return false;
	} else {
		return true;
	}
}

function getWatchedBy($watcher_id) {
	//SELECT id FROM watch WHERE watcher_id=10
	// MODIFY with a left join and stuff before use
}








// makeList(headers, listitems, currentOffset)
/*   Listitems should give items per page
 * 
 */
?>
