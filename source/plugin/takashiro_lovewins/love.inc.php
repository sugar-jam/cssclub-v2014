<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

$toid = isset($_REQUEST['toid']) ? intval($_REQUEST['toid']) : 0;
if($toid <= 0)
	exit('invalid toid');

$love = array(
	'fromid' => $_G['uid'],
	'toid' => $_G['toid'],
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

		$subject = '原来TA也喜欢你！';
		$message = "{$touser['realname']}也喜欢你！快去找TA聊聊吧~ <a href=\"home.php?mod=spacecp&amp;ac=pm&amp;op=showmsg&amp;handlekey=showmsg_{$touser['uid']}&amp;touid={$touser['uid']}&amp;pmid=0&amp;daterange=2\" onclick=\"showWindow('showMsgBox', this.href, 'get', 0)\">[开始聊天]</a>";
		sendpm($fromuser['uid'], $subject, $message);

		$subject = 'TA也喜欢你！';
		$message = "{$fromuser['realname']}也喜欢你！快去找TA聊聊吧~ <a href=\"home.php?mod=spacecp&amp;ac=pm&amp;op=showmsg&amp;handlekey=showmsg_{$fromuser['uid']}&amp;touid={$fromuser['uid']}&amp;pmid=0&amp;daterange=2\" onclick=\"showWindow('showMsgBox', this.href, 'get', 0)\">[开始聊天]</a>";
		sendpm($touser['uid'], $subject, $message);

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

		echo 1;
		exit;
	}
}

echo 0;
