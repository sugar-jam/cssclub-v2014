<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

//@todo: this should be a configuration
$forbidden_usergroups = array(6, 7, 8, 9, 20);
if(in_array($_G['groupid'], $forbidden_usergroups)){
	if(in_array($_G['groupid'], array(6, 7, 8, 9))){
		showmessage('先登录一下呀~', null, array(), array('showmsg' => true, 'login' => 1));
	}else{
		showmessage('抱歉，您没有权限访问该内容哦……');
	}
}

if(!empty($_GET['action'])){
	$action = trim($_GET['action']);
	$module_file = __DIR__.'/'.$action.'.inc.php';
	if(file_exists($module_file)){
		include $module_file;
		exit;
	}
}

$home_member_num = 6;

$member_table = DB::table('common_member');
$member_profile_table = DB::table('common_member_profile');

$condition = "AND m.avatarstatus=1 AND (p.affectivestatus='' OR p.affectivestatus='单身') AND p.realname!='' AND p.awardyear!='' AND p.issbranch!='' ORDER BY RAND() LIMIT $home_member_num";
$home_male_members = DB::fetch_all("SELECT m.*, p.*
	FROM $member_table m
		LEFT JOIN $member_profile_table p ON p.uid=m.uid
	WHERE p.gender=1 $condition");
$home_female_members = DB::fetch_all("SELECT m.*, p.*
	FROM $member_table m
		LEFT JOIN $member_profile_table p ON p.uid=m.uid
	WHERE p.gender=2 $condition");

$home_members = array_merge($home_male_members, $home_female_members);
shuffle($home_members);

$now_m = intval(dgmdate(TIMESTAMP, 'm'));
$now_d = intval(dgmdate(TIMESTAMP, 'd'));
$now_Y = intval(dgmdate(TIMESTAMP, 'Y'));
foreach($home_members as &$m){
	$m['age'] = $now_Y - $m['birthyear'];
	if($now_m < $m['birthmonth'] || ($now_m == $m['birthmonth'] && $now_d < $m['birthdate']))
		$m['age']--;
}
unset($m);

$couple_table = DB::table('takashiro_lovewins_couple');
$couples = DB::fetch_all("SELECT * FROM $couple_table WHERE 1 ORDER BY coinnum DESC LIMIT 6");
if($couples){
	$member_profile_table = C::t('common_member_profile');
	foreach($couples as &$couple){
		$couple['user1'] = $member_profile_table->fetch($couple['uid1']);
		$couple['user2'] = $member_profile_table->fetch($couple['uid2']);
	}
	unset($couple);
}

for($i = count($couples); $i < 6; $i++){
	$couple = array(
		'uid1' => $home_male_members[$i]['uid'],
		'uid2' => $home_female_members[$i]['uid'],
	);

	foreach($couples as $c){
		if(($c['uid1'] == $couple['uid1'] && $c['uid2'] == $couple['uid2']) || ($c['uid1'] == $couple['uid2'] && $c['uid2'] == $couple['uid1']))
			continue 2;
	}

	$couple['user1'] = $home_male_members[$i];
	$couple['user2'] = $home_female_members[$i];
	$couple['coinnum'] = 0;
	$couple['success'] = 0;

	$couples[] = $couple;
}

$navtitle.= '单身交友';
include template('takashiro_lovewins:main');
