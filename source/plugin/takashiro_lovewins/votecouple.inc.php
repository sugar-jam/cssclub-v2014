<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

$coupleid = isset($_REQUEST['coupleid']) ? intval($_REQUEST['coupleid']) : 0;
if($coupleid <= 0){
	$uid1 = isset($_REQUEST['uid1']) ? intval($_REQUEST['uid1']) : 0;
	$uid2 = isset($_REQUEST['uid2']) ? intval($_REQUEST['uid2']) : 0;
	if($uid1 <= 0 || $uid2 <= 0)
		exit('invalid couple id or user id');

	$couple_table = DB::table('takashiro_lovewins_couple');
	$coupleid = DB::result_first("SELECT id FROM $couple_table WHERE (uid1=$uid1 AND uid2=$uid2) OR (uid1=$uid2 AND uid2=$uid1)");
	if(!$coupleid){
		$couple = array(
			'uid1' => $uid1,
			'uid2' => $uid2,
		);
		DB::insert('takashiro_lovewins_couple', $couple);
		$coupleid = DB::insert_id();
	}
}

$today_offset = dmktime(dgmdate(TIMESTAMP, 'Y-m-d'));

$log_table = DB::table('takashiro_lovewins_couplelog');
$log = DB::fetch_first("SELECT * FROM $log_table WHERE coupleid=$coupleid AND voterid={$_G['uid']} AND dateline>=$today_offset");

if(empty($log)){
	$couple_table = DB::table('takashiro_lovewins_couple');
	DB::query("UPDATE $couple_table SET coinnum=coinnum+1 WHERE coupleid=$coupleid");
	if(DB::affected_rows() > 0){
		$log = array(
			'coupleid' => $coupleid,
			'voterid' => $_G['uid'],
			'dateline' => TIMESTAMP,
		);
		DB::insert('takashiro_lovewins_couplelog', $log);
		echo 1;
	}else{
		echo 0;
	}
}else{
	echo 0;
}
