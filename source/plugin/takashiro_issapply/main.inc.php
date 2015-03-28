<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

if ($_G['uid'] != 1 && $_G['uid'] != 3){
	showmessage('现在不在申请开放时间。');
}

$issapplytable = DB::table('plugin_issapply');
$types = array('scholarship', 'grant');
isset($_GET['type']) || in_array($_GET['type'], $types) || $_GET['type'] = $types[0];
$type = &$_GET['type'];

switch ($type) {
	case 'scholarship':case 'grant':
		$fields = array(
			'university', 'college', 'major', 'fullname', 'studentid', 'uregtime', 'nationality', 'gender',
			'birthday', 'birthorigin', 'politicstatus', 'wechatid', 'mobile', 'qq', 'email', 'annualavg', 'rank',
			'rankbase', 'projectdetails', 'scholarship', 'competition', 'honor', 'otherhonor', 'homedetails',
			'strongpoint', 'careerplan', 'studentworkdetails', 'education',
		);
		break;

	default:
		$fields = array();
}


if(submitcheck('applysubmit')){
	$appliancedata = array();
	foreach($fields as $var){
		isset($_POST[$var]) && $appliancedata[$var] = dhtmlspecialchars(trim($_POST[$var]));
	}

	$appliancedata['education'] = array();
	$education_fields = array('starttime', 'endtime', 'university', 'college', 'major', 'degree');
	$education_count = count($_POST['education_'.$education_fields[0]]);
	for($i = 0; $i < $education_count; $i++){
		$education = array();
		$all_empty = true;
		foreach($education_fields as $var){
			if(isset($_POST['education_'.$var][$i])){
				$education[$var] = dhtmlspecialchars(trim($_POST['education_'.$var][$i]));
				empty($education[$var]) || $all_empty = false;
			}else{
				$education[$var] = '';
			}
		}
		$all_empty || $appliancedata['education'][] = $education;
	}

	$appliancedata = serialize($appliancedata);

	if(isset($_POST['applyid']) && isset($_POST['qcode'])){
		$applyid = intval($_POST['applyid']);
		$qcode = daddslashes(trim($_POST['qcode']));

		$appliance = array(
			'data' => $appliancedata,
		);

		$o = DB::fetch_first("SELECT id,state FROM $issapplytable WHERE `id`=$applyid AND `qcode`='$qcode'");
		if(!$o){
			showmessage('没有查询到您的申请表。');
		}
		if($o['state'] != 0){
			showmessage('您的申请已被处理，无法修改。');
		}

		DB::update('plugin_issapply', $appliance, '`id`='.$applyid);

		$appliance['id'] = $applyid;
		$appliance['qcode'] = $qcode;
	}else{
		$appliance = array(
			'qcode' => random(8),
			'type' => $type,
			'data' => $appliancedata,
		);

		DB::insert('plugin_issapply', $appliance);
		$appliance['id'] = DB::insert_id();
	}

	$link = $_G['siteurl'].'plugin.php?id=takashiro_issapply:main&applyid='.$appliance['id'].'&qcode='.$appliance['qcode'];
	showmessage('您的申请成功提交！请记住此链接查询申请结果：<br /><a href="'.$link.'">'.$link.'</a>');
}

if(isset($_GET['applyid']) && isset($_GET['qcode'])){
	$applyid = intval($_GET['applyid']);
	$qcode = daddslashes(trim($_GET['qcode']));

	$appliance = DB::fetch_first("SELECT state,data FROM $issapplytable WHERE id=$applyid AND qcode='$qcode'");
	if($appliance){
		$appliance['data'] = unserialize($appliance['data']);
	}
}
if(empty($appliance)){
	$appliance = array('state' => 0, 'data' => array());
	foreach($fields as $var){
		$appliance['data'][$var] = '';
	}

	unset($applyid, $qcode);
}
$a = &$appliance['data'];

$navtitle = '申请';
include template('takashiro_issapply:main');

?>
