<?php

set_time_limit(0);

$urls = file('urls.txt');

foreach($urls as $url){
	$content = file_get_contents($url);

	preg_match('/id\=\"activity\-name\"\>(.*?)\<\/h2\>/', $content, $matches);
	$title = str_replace('ISS创享俱乐部|', '', $matches[1]);

	$dir = iconv('UTF-8', 'GBK', $title);
	mkdir($dir);

	preg_match('/\<em id\=\"post-date\"(?:.*?)\>(\d{4}\-\d{1,2}\-\d{1,2})\<\/em\>/', $content, $matches);
	$date = $matches[1];


	preg_match('/\<div class\=\"rich_media_content\" id\=\"js_content\"\>(.*?)\<\/div\>/', $content, $matches);
	$content = $matches[1];
	$content = preg_replace('/\<p.*?\>(.*?)\<\/p\>/', '<p>\\1</p>', $content);
	$content = preg_replace('/\<span.*?\>(.*?)\<\/span\>/', '\\1', $content);
	$content = str_replace('<p></p>', '', $content);

	preg_match_all('/<img.*?data\-src\=\"(.*?)\".*?\/\>/', $content, $matches);
	$images = $matches[1];

	$i = 0;
	foreach($images as $imageurl){
		$image_content = file_get_contents($imageurl);
		$fp = fopen($dir.'/'.$i.'.jpg', 'wb');
		fwrite($fp, $image_content);
		fclose($fp);
		$i++;
	}


	$content = preg_replace('/<img.*?data\-src\=\"(.*?)\".*?\/\>/is', '[img]\\1[/img]', $content);
	$content = '<div style="text-indent:2em;">'.$content.'</div>';

	$text = "Title: $title\r\nDate: $date\r\nContent: $content";
	$fp = fopen($dir.'/text.txt', 'wt');
	fwrite($fp, $text);
	fclose($fp);
}

?>
