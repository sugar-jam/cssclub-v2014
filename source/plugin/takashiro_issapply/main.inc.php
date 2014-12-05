<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

if (!$_G['uid']) {
	showmessage('亲想申请什么都要先登录一下呀~', null, array(), array('showmsg' => true, 'login' => 1));
}

if ($_G['adminid'] != 1){
	showmessage('现在不在申请开放时间。');
}

require_once libfile('function/profile');
require_once libfile('function/space');

$space = array('uid' => $_G['uid']);
space_merge($space, 'profile');

$navtitle = '游学申请';
include template('takashiro_issapply:main');

?>
