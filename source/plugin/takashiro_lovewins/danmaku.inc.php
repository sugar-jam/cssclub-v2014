<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

$type = empty($_REQUEST['type']) ? 0 : intval($_REQUEST['type']);
if($type == 1){
	if(empty($_REQUEST['targetid']))
		exit('parameter `targetid` missing.');
	$targetid = max(1, intval($_REQUEST['targetid']));
}else{
	$type = 2;
	if(empty($_REQUEST['uid1']) || empty($_REQUEST['uid2']))
		exit('parameter `uid1` or `uid2` missing.');

	$uid1 = intval($_REQUEST['uid1']);
	$uid2 = intval($_REQUEST['uid2']);

	$couple_table = DB::table('takashiro_lovewins_couple');
	$targetid = DB::result_first("SELECT id FROM $couple_table WHERE (uid1=$uid1 AND uid2=$uid2) OR (uid1=$uid2 AND uid2=$uid1)");
}

if($_POST){
	if(empty($_POST['content']))
		exit('parameter `content` missing.');

	$content = dhtmlspecialchars(trim($_POST['content']));

	if($targetid <= 0 && $type == 2){
		$couple = array(
			'uid1' => $uid1,
			'uid2' => $uid2,
		);
		DB::insert('takashiro_lovewins_couple', $couple);
		$targetid = DB::insert_id();
	}

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
