<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

$condition = array("m.avatarstatus=1 AND (p.affectivestatus='' OR p.affectivestatus='单身') AND p.awardyear!='' AND p.issbranch!=''");

if(!empty($_REQUEST['keyword'])){
	$keyword = trim($_REQUEST['keyword']);
	$keyword = daddslashes($keyword);
	$condition[] = "p.realname LIKE '%$keyword%'";
}

if(!empty($_REQUEST['gender'])){
	$gender = intval($_REQUEST['gender']);
	if($gender >= 0 && $gender <= 2)
		$condition[] = 'p.gender='.$gender;
}

$condition = implode(' AND ', $condition);
if(!empty($_REQUEST['randnum'])){
	$randnum = intval($_REQUEST['randnum']);
	if($randnum > 0){
		$condition.= ' ORDER BY rand() LIMIT '.$randnum;
	}
}

$member_table = DB::table('common_member');
$profile_table = DB::table('common_member_profile');
$profiles = DB::fetch_all("SELECT p.* FROM
	$member_table m
		LEFT JOIN $profile_table p ON p.uid=m.uid
	WHERE $condition");

$now_m = intval(dgmdate(TIMESTAMP, 'm'));
$now_d = intval(dgmdate(TIMESTAMP, 'd'));
$now_Y = intval(dgmdate(TIMESTAMP, 'Y'));
foreach($profiles as &$profile){
	$profile['avatar'] = avatar($profile['uid'], 'big');

	if($profile['birthyear']){
		$profile['age'] = $now_Y - $profile['birthyear'];
		if($now_m < $profile['birthmonth'] || ($now_m == $profile['birthmonth'] && $now_d < $profile['birthdate']))
			$profile['age']--;
	}else{
		$profile['age'] = '?';
	}
}
unset($profile);

echo json_encode($profiles);
