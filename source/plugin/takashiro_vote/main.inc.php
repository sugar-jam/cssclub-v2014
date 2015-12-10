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

$action = &$_GET['action'];

if($action == 'vote' && $_G['uid'] > 0){
	$cid = intval($_GET['cid']);
	if($cid <= 0)
		exit;

	$voterid = $_G['uid'];
	if(!$voterid)
		exit;

	$dateline = TIMESTAMP;
	$deadline = dmktime('2015-12-25');
	if($dateline >= $deadline)
		exit('-1');

	$today_begin = dmktime(dgmdate(TIMESTAMP, 'Y-m-d'));
	$today_end = $today_begin + 24 * 3600;

	$table = DB::table('plugin_vote_log');
	$logs = DB::fetch_all("SELECT * FROM $table WHERE voterid=$voterid AND dateline>=$today_begin AND dateline<$today_end");
	foreach($logs as $log){
		if($log['cid'] == $cid)
			exit('-2');
	}
	if(count($logs) >= 10)
		exit('-3');

	DB::query("INSERT INTO $table (`cid`,`voterid`,`dateline`) VALUES ($cid, $voterid, $dateline)");
	if(DB::affected_rows() > 0){
		$table = DB::table('plugin_vote_candidate');
		DB::query("UPDATE $table SET `selfintrovotenum`=`selfintrovotenum`+1 WHERE `id`=$cid");
		echo 1;
	}else{
		echo 0;
	}
	exit;
}

$root_url = 'plugin.php?id=takashiro_vote:main';
$navtitle.= '2015十佳青年风采展示';

if(empty($_G['cache']['profilesetting'])){
	loadcache('profilesetting');
}

$issbranches = explode("\n", $_G['cache']['profilesetting']['issbranch']['choices']);
array_unshift($issbranches, '');
unset($issbranches[0]);

if(empty($_GET['candidateid'])){
	$condition = array();
	$extra_sql = '';
	$page_url = $root_url;

	if(!empty($_GET['branchid'])){
		$branchid = intval($_GET['branchid']);
		if(!isset($issbranches[$branchid])){
			$issbranch = current($issbranches);
		}else{
			$issbranch = $issbranches[$branchid];
		}
		$condition[] = "`issbranch`='$issbranch'";
		$page_url.= '&branchid='.$branchid;
	}else{
		$branchid = 0;
	}

	if(!empty($_GET['orderby'])){
		$orderby = array('story', 'selfintro', 'souvenir');
		$orderby = in_array($_GET['orderby'], $orderby) ? $_GET['orderby'] : '';
		if($orderby){
			if($orderby == 'total'){
				$extra_sql.= 'ORDER BY `storyvotenum`+`selfintrovotenum`+`souvenirvotenum` DESC';
			}else{
				$extra_sql.= 'ORDER BY `'.$orderby.'votenum` DESC';
			}
			$page_url.= '&orderby='.$orderby;
		}
	}else{
		$orderby = '';
	}

	$condition = $condition ? implode(' AND ', $condition) : '1';
	$table = DB::table('plugin_vote_candidate');

	$limit = 8;
	$page = max(1, intval($_GET['page']));
	$offset = ($page - 1) * $limit;
	$candidatenum = DB::result_first("SELECT COUNT(*) FROM `$table` WHERE $condition");
	$multipage = multi($candidatenum, $limit, $page, $page_url);

	$candidates = DB::fetch_all("SELECT * FROM `$table` WHERE $condition $extra_sql LIMIT $offset,$limit");
	foreach($candidates as &$c){
		foreach(array('selfintro', 'story', 'souvenir') as $f){
			$c[$f] = mb_substr($c[$f], 0, 30, 'utf-8');
		}
	}
	unset($c);

	include template('takashiro_vote:main');
}else{

	$candidateid = intval($_GET['candidateid']);
	$table = DB::table('plugin_vote_candidate');
	$candidate = DB::fetch_first("SELECT * FROM `$table` WHERE `id`='$candidateid'");

	$votelog = array();
	foreach(array('story', 'selfintro', 'souvenir') as $f){
		$candidate[$f] = '<p>'.str_replace("\n", '</p><p>', $candidate[$f]).'</p>';
		$votelog[$f] = array();
	}

	$pre_common_member_profile = DB::table('common_member_profile');
	$pre_plugin_vote_log = DB::table('plugin_vote_log');
	$votelogs = DB::fetch_all("SELECT l.*,p.realname
		FROM $pre_plugin_vote_log l
			LEFT JOIN $pre_common_member_profile p ON p.uid=l.voterid
		WHERE l.cid={$candidate['id']}");
	foreach($votelogs as $l){
		$votelog[$l['votedfield']][] = $l;
	}

	$issbranches = array_flip($issbranches);
	$branchid = $issbranches[$candidate['issbranch']];

	$contenttype = isset($_GET['content']) ? $_GET['content'] : '';
	in_array($contenttype, array('selfintro', 'story', 'souvenir')) || $contenttype = '';

	include template('takashiro_vote:view');
}

?>
