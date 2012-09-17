<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once 'inc/common.php';

include_once 'inc/settings.php';
include_once 'inc/db.php';


$BASELINK = "http://testbed.rollperson.se/";


function rssheader($title, $link, $description) {
	return "<?xml version=\"1.0\" encoding=\"UTF-8\" ?>\n<rss version=\"2.0\">".
			"\n\t<channel>".
			"\n\t\t<title>$title</title>".
			"\n\t\t<link>$link</link>".
			"\n\t\t<description>$description</description>";
}

function rssfooter() {
	return "	</channel>
			</rss>";
}

function rssitem($title, $link, $description, $pubDate, $id) {
	return "\n\t\t<item>".
			"\n\t\t\t<title>$title</title>".
			"\n\t\t\t<description>$description</description>".
			"\n\t\t\t<pubDate>$pubDate</pubDate>".
			"\n\t\t\t<link>$link</link>".
			"\n\t\t\t<guid>$id</guid>".
			"\n\t\t</item>";
}


// NEWS
if (count($_GET) == 0) {
	
	// Do header stuff
	header("Content-Type: application/rss+xml");
	print rssheader('Nyheter', $BASELINK,'Nyheter från rollperson.se');
	
	// Get the news
	$newsdb = makequery('SELECT id, UNIX_TIMESTAMP(date) AS date, title, content FROM `news` LIMIT 0,100');
	
	// Go through the news items
	while($row = mysql_fetch_array($newsdb)) {
		
		// Get or generate the item's data
		$pubDate = date(DATE_RFC822, $row['date']);
		$title = html_entity_decode($row['title']);
		$description = utf8_encode(html_entity_decode($row['content']));
		$id = $row['id'];
		$link = $BASELINK.'?do=article&amp;id='.$id;
		
		// print the item
		print rssitem($title, $link, $description, $pubDate, $id);
		
	}
	
	// Print footer.
	print rssfooter();

// CHARACTERS AND FORMS
} else {
	// Set initial false value of flags
	$useid = false;
	$usechars = false;
	$useforms = false;
	
	// Check the $_GET items for flag changers
	if (isset($_GET['id'])) $useid = " AND users.id = " . $_GET['id'];
	if (isset($_GET['chars'])) $usechars = true;
	if (isset($_GET['forms'])) $useforms = true;
	
	// If any of the flags have been set
	if ($useid || $usechars || $useforms) {
		
		// Create empty query
		$q = "";
		
		// if id has been set, but neither chars or forms, use both
		if (!$usechars && !$useforms && $useid) {
			$usechars = true;
			$useforms = true;
		}
		
		// Characters-part of query
		if ($usechars) {
			$q .= "(SELECT users.username, characters.id, UNIX_TIMESTAMP(characters.changed) AS changed, characters.name, characters.system, characters.description, 'character' AS type ".
					"FROM characters ".
					"JOIN users ON users.id=characters.ownerid ".
					"WHERE characters.public=1 $useid)";
		}
		
		// If forms and chars are set, make sure the two query parts fit snuggly together
		if ($usechars && $useforms) {
			$q .= " UNION ";
		}
		
		// Forms.part of the query
		if ($useforms) {
			$q .= "(SELECT users.username, forms.id, UNIX_TIMESTAMP(forms.changed) AS changed, forms.name, forms.system, forms.description, 'form' AS type ".
					"FROM forms ".
					"JOIN users ON users.id=forms.ownerid ".
					"WHERE forms.public=1 $useid)";
		}
		
		// Ordering and limits-part of the query
		$q .= " ORDER BY changed LIMIT 0,100";
		
		
		// Execute the query
		$result = makequery($q);
		
		// Create empty strings to put items, type description and username in
		$items = "";
		$typestr = "";
		$username = "";

		// Go through the items
		while($row = mysql_fetch_array($result)) {
			// get id and owner's name of current item.
			$username = $row['username'];
			$id = $row['id'];
			
			// Generate a type descriptor if bot types are present
			if ($usechars && $useforms) $typestr = "[".ucfirst($row['type'])."] ";
	
			// Get date of item
			$pubDate = date(DATE_RFC822, $row['changed']);
			
			// Generate title of item
			$title = html_entity_decode($typestr . $row['name'] . " (System: " . $row['system'] . ") skapad av " . $username);
			
			// Get the description of the item
			$description = html_entity_decode($row['description']);
			
			// Generate the item's link
			if ($row['type'] == 'character') {
				$link = $BASELINK.'?do=showchar&amp;charid='.$id;
			} else {
				$link = $BASELINK.'?do=showform&amp;formid='.$id;
			}

			// Generate the item and add it to the string.
			$items .= rssitem($title, $link, $description, $pubDate, $row['type'].$id);
		}
		
		// Generate RSS feed title
		
		$rsstitle = "Rollperson.se: ";

		$rssc = "";
		if ($useforms) $rssc .= "Rollformulär";
		if ($useforms && $usechars) $rssc .= " och ";
		if ($usechars) $rssc .= "Rollpersoner";
		
		$rsstitle .= $rssc;
		
		if ($useid) {
			$rsstitle .= " [".$username."]";
		}
		
		// Generate RSS feed link
		
		if ($useid) {
			$rsslink = $BASELINK.'?do=showprofile&amp;userid='.$_GET['id'];
		} else if (!$usechars) {
			$rsslink = $BASELINK.'?do=listforms';
		} else if (!$useforms) {
			$rsslink = $BASELINK.'?do=listchars';
		} else {
			$rsslink = $BASELINK;
		}
		
		// Generate RSS description
		
		$rssdescription = $rssc . " från Rollperson.se";
		
		header("Content-Type: application/rss+xml");
		print rssheader($rsstitle, $rsslink, $rssdescription);
		print $items;
		print rssfooter();	
		
		
	} else {
		header("Content-Type: application/rss+xml");
		print rssheader("FEL!", $BASELINK, "Något är fel i din RSS-syntax.");
		print rssfooter();
	}
}



?>
