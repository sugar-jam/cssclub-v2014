<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

$toid = isset($_REQUEST['toid']) ? intval($_REQUEST['toid']) : 0;
if($toid <= 0 || $toid == $_G['uid'])
	exit('invalid toid');

$love = array(
	'fromid' => $_G['uid'],
	'toid' => $toid,
	'dateline' => TIMESTAMP,
);
$sql = DB::implode($love);
$love_table = DB::table('takashiro_lovewins_love');
DB::query("INSERT IGNORE INTO $love_table SET $sql");

if(DB::affected_rows() > 0){
	$response = DB::fetch_first("SELECT id FROM $love_table WHERE fromid=$toid AND toid={$_G['uid']}");
	if($response){
		$fromuser = C::t('common_member_profile')->fetch($_G['uid']);
		$touser = C::t('common_member_profile')->fetch($toid);

		$message = '我也好宣你哦！我们交往吧！';
		sendpm($touser['uid'], $message, $message, $fromuser['uid']);

		$message = '你造吗……其实……我宣你嗯久了！';
		sendpm($fromuser['uid'], $message, $message, $touser['uid']);

		$couple_table = DB::table('takashiro_lovewins_couple');
		DB::query("UPDATE $couple_table SET success=1 WHERE (uid1={$fromuser['uid']} AND uid2={$touser['uid']}) OR (uid1={$touser['uid']} AND uid2={$fromuser['uid']})");

		if(DB::affected_rows() <= 0){
			$couple = array(
				'uid1' => $fromuser['uid'],
				'uid2' => $touser['uid'],
				'success' => 1,
			);
			$sql = DB::implode($couple);
			DB::query("INSERT IGNORE INTO $couple_table SET $sql");
		}

		echo 2;
		exit;
	}

	echo 1;
	exit;
}

echo 0;
