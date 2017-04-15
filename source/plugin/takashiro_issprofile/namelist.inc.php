<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$members = DB::fetch_all("SELECT p.uid,p.realname,p.awardschool,p.awardyear FROM pre_common_member_profile p LEFT JOIN pre_common_member m ON m.uid=p.uid WHERE m.groupid NOT IN (8,19) AND realname!='' AND awardschool!='' AND awardyear!=''");

include template('takashiro_issprofile:member_list');
