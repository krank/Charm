<?php

ini_set('display_errors',1);
error_reporting(E_ALL);

include_once 'inc/settings.php';
include_once 'inc/db.php';

function editarticle() {
	$errors = array();
	$message = "";
	
	if (isset($_SESSION['userid']) && isset($_SESSION['level'])) if ($_SESSION['level'] > 0) {
	
	// If an article ID was specified in the URL...
	if (isset($_GET['id'])) {
		// ...and that article doesn't exist, tell that in the message. And show empty values.
		$cleanpost = array('title' => '', 'content' => '', 'id' => '');
		
		// ...and that article exists, load it
		
	// If it was not, get stuff from POST
	} else {
		$cleanpost = clean_array($_POST, array('title','content','id'));
		
		// If stuff in POST exists...
		if (isset($_POST['submit'])) {
			
			// (Check for errors)
			
			if ($cleanpost['title'] == "") $errors['title'] = 'Du har inte skrivit någon rubrik!';
			
			if ($cleanpost['content'] == "") $errors['content'] = 'Du har inte skrivit någon text!';
			
			if (count($errors) == 0) {
				$title = strip_tags($cleanpost['title']);
				$content = htmlentities($cleanpost['content'], ENT_QUOTES, "UTF-8");
				$id = $cleanpost['id'];
				
				// If POST includes an ID, insert article as that ID
				if ($_POST['id'] != "") {
					makequery("UPDATE news SET title='$title', content='$content' WHERE id=$id");

				// if it does not, insert new article
				} else {
					makequery("INSERT INTO news (title, content) VALUES ('$title','$content')");
					$cleanpost['id'] = mysql_insert_id();
				}
				
				$message = "Nyheten sparades med id ".$cleanpost['id'].".";
				
			} else {
				$message = "Fel upptäcktes, och nyheten sparades inte.";
			}
			
			
		}
		
	}

	// Create the article editing interface
	
	$errors = clean_array($errors, array("title", "content"));
	
	$lines = array(
		array(	"header"	=> "Rubrik", 
				"input"		=> $cleanpost['title'],
				"maxlen"	=> 64,
				"name"		=> 'title',
				"error"		=> $errors['title']
		),
		array(	"header"	=> "Text",
				"textarea"	=> $cleanpost['content'],
				"maxlen"	=> 1024,
				"name"		=> 'content',
				"error"		=> $errors['content']
		),
		array(	"input"		=> $cleanpost['id'],
				"type"		=> "hidden",
				"name"		=> "id"
		)
	);
	
	$body = makeform("Skriv nyhet", "?do=editarticle", $lines, "Spara artikeln", $message);
	
	return template($body);
	
	}
	
	return template("Du har inte tillräckliga rättigheter för att göra det här.");
}

function news_stream($include_text=false, $maxitems=10) {
    
    
    if ($include_text) {
        $andtext = ", text";
    } else {
        $andtext = "";
    }
	
	
    $query = "SELECT id, title, $andtext DATE_FORMAT( date, '%d/%m %Y' ) AS date FROM news ORDER BY date LIMIT 0,10";
    $result = makequery($query);
    
    $output = "";
	
	while($row = mysql_fetch_array($result)) {
		$output .= "<tr><td><a href=\"?do=article&id={$row['id']}\">{$row['title']}</a></td><td>{$row['date']}</td>";
	}
    
	return $output;
}


function article() {
	if (isset($_GET['id'])) {
		$id = $_GET['id'];
		$tr = array('%title%' => 'Article does not exist', '%date%' => '', '%content%' => 'Please check your URL');
		
		$query = "SELECT title, content, DATE_FORMAT( date, '%d/%m %Y' ) AS date FROM news WHERE id=$id";
		
		$result = makequery($query);
		
		$article = mysql_fetch_array($result);
		
		if ($article) {
			$tr['%title%'] = $article['title'];
			$tr['%date%'] = $article['date'];
			$tr['%content%'] = nl2br($article['content']);
		}
		
		$content = file_get_contents('./template/articleview_tpl.html');
		$content = strtr($content, $tr);
		
		return template($content);
		
	} else {
		return template('<h2>Ingen artikel specifierad</h2>');
	}
}

?>
