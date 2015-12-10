<?php

if(!defined('IN_DISCUZ'))
	exit('Access Denied');


if($_G['uid'] != 2 && $_G['uid'] != 3)
	exit('permission denied');

$verify_table = DB::table('plugin_member_verify');
$unregistered_members = DB::fetch_all("SELECT * FROM $verify_table WHERE uid IS NULL");

$branches = DB::fetch_all("SELECT awardschool,COUNT(uid) AS registered, COUNT(*) AS total FROM $verify_table WHERE 1 GROUP BY awardschool");
$total = array('awardschool' => '总计', 'registered' => 0, 'unregistered' => 0, 'total' => 0);
foreach($branches as &$b){
	$b['unregistered'] = $b['total'] - $b['registered'];
	$total['registered'] += $b['registered'];
	$total['total'] += $b['total'];
}
unset($b);
$total['unregistered'] = $total['total'] - $total['registered'];
$branches[] = $total;

include template('takashiro_issprofile:unregistered_member');
