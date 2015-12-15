<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

$coupleid = isset($_REQUEST['coupleid']) ? intval($_REQUEST['coupleid']) : 0;
if($coupleid <= 0){
	$uid1 = isset($_REQUEST['uid1']) ? intval($_REQUEST['uid1']) : 0;
	$uid2 = isset($_REQUEST['uid2']) ? intval($_REQUEST['uid2']) : 0;
	if($uid1 <= 0 || $uid2 <= 0)
		exit('invalid couple id or user id');
