<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$verify_table = DB::table('plugin_member_verify');
$members = DB::fetch_all("SELECT * FROM $verify_table WHERE uid IS NOT NULL");

include template('takashiro_issprofile:member_list');
