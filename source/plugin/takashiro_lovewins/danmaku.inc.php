<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

if(empty($_REQUEST['targetid']))
	exit('parameter `targetid` missing.');
$targetid = max(1, intval($_REQUEST['targetid']));
$type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);

if($_POST){
	if(empty($_POST['content']))
		exit('parameter `content` missing.');

	$content = dhtmlspecialchars(trim($_POST['content']));

	$danmaku = array(
		'authorid' => $_G['uid'],
		'dateline' => TIMESTAMP,
		'content' => $content,
		'type' => $type,
		'targetid' => $targetid,
	);
	DB::insert('takashiro_lovewins_danmaku', $danmaku);
	exit;
}


$danmaku_table = DB::table('takashiro_lovewins_danmaku');
$danmaku = DB::fetch_all("SELECT * FROM $danmaku_table WHERE type=$type AND targetid=$targetid ORDER BY id DESC LIMIT 10");
echo json_encode($danmaku);
