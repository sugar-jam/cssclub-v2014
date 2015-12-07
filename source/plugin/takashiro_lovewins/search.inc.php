<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

if(!isset($_REQUEST['keyword']))
	exit('require parameter keyword');

$keyword = trim($_REQUEST['keyword']);
$keyword = daddslashes($keyword);

$profile_table = DB::table('common_member_profile');
$profiles = DB::fetch_all("SELECT * FROM $profile_table WHERE realname='$keyword'");

echo json_encode($profiles);
