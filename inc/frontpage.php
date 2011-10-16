<?php

function frontpage() {
	$content = file_get_contents("template/frontpage.html");
	
	$news = news_stream();
	
	$tr = array();
	
	$tr['%newsstream%'] = news_stream();
	
	$content = strtr($content, $tr);
	
	return template($content);
}

?>
