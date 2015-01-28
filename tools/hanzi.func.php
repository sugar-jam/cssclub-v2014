<?php

function unicode2utf8($unicode){
	if($unicode < 0)
		return '';

	if($unicode <= 0x7F){
		//0xxxxxxx
		return chr($unicode);
	}elseif($unicode <= 0x7FF){
		//110xxxxx 10xxxxxx
		return chr(0xC0 | (0x3F & ($unicode >> 6))).chr(0x80 | (0x3F & $unicode));
	}elseif($unicode <= 0xFFFF){
		//1110xxxx 10xxxxxx 10xxxxxx
		return chr(0xE0 | (0xF & ($unicode >> 12))).chr(0x80 | (0x3F & ($unicode >> 6))).chr(0x80 | (0x3F & $unicode));
	}

	return '';
}

function utf82unicode($utf8){
	$c1 = ord($utf8{0});
	if(($c1 & 0x80) == 0){
		return $c1;
	}

	if(($c1 & 0xE0) == 0xC0){
		$c1 &= 0x1F;
		$c1 <<= 6;
		$c2 = ord($utf8{1}) & 0x3F;
		return $c1 | $c2;
	}

	if(($c1 & 0xF0) == 0xE0){
		$c1 &= 0xF;
		$c1 <<= 12;
		$c2 = ord($utf8{1}) & 0x3F;
		$c2 <<= 6;
		$c3 = ord($utf8{2}) & 0x3F;
		return $c1 | $c2 | $c3;
	}

	return -1;
}

function hanziStrokeOrder(){
	static $stroke_order = array();

	if(empty($stroke_order)){
		$fp = fopen(dirname(__FILE__).'/hanziorder', 'r');

		$i = 0;
		while(!feof($fp)){
			$ch = fread($fp, 3);
			$stroke_order[$ch] = $i;
			$i++;
		}

		fclose($fp);
	}

	return $stroke_order;
}

function compareHanziByStroke($str1, $str2){
	$stroke_order = hanziStrokeOrder();

	$l1 = mb_strlen($str1, 'utf-8');
	$l2 = mb_strlen($str2, 'utf-8');

	$offset = 0;
	$ch1 = $ch2 = '';
	while($ch1 == $ch2 && $offset < $l1 && $offset < $l2){
		$ch1 = trim(mb_substr($str1, $offset, 1, 'utf-8'));
		$ch2 = trim(mb_substr($str2, $offset, 1, 'utf-8'));
		$offset++;
	}

	return $offset >= $l2 || $stroke_order[$ch2] < $stroke_order[$ch1];
}

function compareHanziByPinyin($str1, $str2){
	return iconv('utf-8', 'gbk', $str1) > iconv('utf-8', 'gbk', $str2);
}

?>
