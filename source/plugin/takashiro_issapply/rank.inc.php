<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

$member_table = DB::table('common_member');
$member_profile_table = DB::table('common_member_profile');
$forum_thread_table = DB::table('forum_thread');
$forum_post_table = DB::table('forum_post');
$portal_article_title_table = DB::table('portal_article_title');
$plugin_member_verify_table = DB::table('plugin_member_verify');
$forum_moderator_table = DB::table('forum_moderator');

//Profile completion ratio
$unrequired_fields = array(
	'birthprovince', 'birthcity', 'birthdist', 'birthcommunity',
	'resideprovince', 'residecity', 'residedist', 'residecommunity',
	'company', 'occupation', 'position',
	'birthyear', 'birthmonth', 'zodiac', 'constellation',
	'passport', 'site',
);

loadcache('profilesetting');
$required_fields = array();
foreach($_G['cache']['profilesetting'] as $fieldid => $fieldsetting){
	if($fieldsetting['available'] && !in_array($fieldid, $unrequired_fields)){
		$required_fields[] = $fieldid;
	}
}
$field_base = count($required_fields) + 2;

$query = DB::query("SELECT m.*,p.*
	FROM $member_table m
		LEFT JOIN $member_profile_table p ON p.uid=m.uid
	WHERE m.groupid NOT IN (18,21,4,5,6,7,8)");
$members = array();
while($member = DB::fetch($query)){
	if(empty($member['realname'])){
		continue;
	}

	$i = 0;
	foreach($required_fields as $var){
		empty($member[$var]) || $i++;
	}
	$member['emailstatus'] && $i++;
	$member['avatarstatus'] && $i++;
	$members[$member['uid']] = array(
		'uid' => $member['uid'],
		'realname' => $member['realname'],
		'issbranch' => $member['issbranch'],
		'awardyear' => $member['awardyear'],
		'profilecpratio' => $i,
		'threadnum' => 0,
		'replynum' => 0,
		'activedays' => array(),
	);
}

//Posts and active Days
$query = DB::query("SELECT authorid,dateline FROM $forum_thread_table WHERE 1");
$posts = array();
while($post = DB::fetch($query)){
	if(empty($members[$post['authorid']]))
		continue;

	$members[$post['authorid']]['threadnum']++;

	$date = dgmdate($post['dateline'], 'Ymd');
	$members[$post['authorid']]['activedays'][$date] = true;
}

$query = DB::query("SELECT authorid,dateline FROM $forum_post_table WHERE 1");
$posts = array();
while($post = DB::fetch($query)){
	if(empty($members[$post['authorid']]))
		continue;

	$members[$post['authorid']]['replynum']++;

	$date = dgmdate($post['dateline'], 'Ymd');
	$members[$post['authorid']]['activedays'][$date] = true;
}

$sample = current($members);
$sample['activedays'] = count($sample['activedays']);
$min = $max = $sample;

foreach($members as &$member){
	$member['replynum'] -= $member['threadnum'];
	$member['activedays'] = count($member['activedays']);

	foreach(array('profilecpratio', 'threadnum', 'replynum', 'activedays') as $field){
		$min[$field] = min($min[$field], $member[$field]);
		$max[$field] = max($max[$field], $member[$field]);
	}
}
unset($member);

$min['profilecpratio'] = 0;
$range = array();
foreach(array('profilecpratio', 'threadnum', 'replynum', 'activedays') as $field){
	$range[$field] = $max[$field] - $min[$field];
}

$ratio = array(
	'profilecpratio' => 0.3,
	'activedays' => 0.35,
	'threadnum' => 0.2,
	'replynum' => 0.15,
);

$totalscore_sum = 0;
foreach($members as &$member){
	$member['score'] = array(
		'total' => 0,
	);
	foreach(array('profilecpratio', 'threadnum', 'replynum', 'activedays') as $field){
		$member['score'][$field] = ($member[$field] - $min[$field]) / $range[$field];
		$member['score']['total'] += $ratio[$field] * $member['score'][$field];
	}
	$totalscore_sum += $member['score']['total'];
}
unset($member);

foreach($members as $sample)
	break;
$min['total'] = $max['total'] = $sample['score']['total'];

foreach($members as $member){
	$min['total'] = min($min['total'], $member['score']['total']);
	$max['total'] = max($max['total'], $member['score']['total']);
}

$range['total'] = $max['total'] - $min['total'];
foreach($members as &$member){
	$member['score']['total'] = $member['score']['total'] / $range['total'] * 4;
}
unset($member);

//Articles
$query = DB::query("SELECT uid,COUNT(*) AS articlenum FROM $portal_article_title_table WHERE 1 GROUP BY uid");
while($a = DB::fetch($query)){
	if(!isset($members[$a['uid']]))
		continue;
	$ms = &$members[$a['uid']]['score'];
	$ms['article'] = min(1, $a['articlenum'] * 0.35);
	$ms['total'] = min(4, $ms['total'] + $ms['article']);
	unset($ms);
}

//版主加分
$forums = array();
$query = DB::query("SELECT fid,tid,replies FROM $forum_thread_table WHERE 1");
while($f = DB::fetch($query)){
	if(!isset($forums[$f['fid']])){
		$forums[$f['fid']] = array();
	}
	$forums[$f['fid']]['tidnum']++;
	$forums[$f['fid']]['replies'] += $f['replies'];
}

foreach($forums as &$f){
	$fmin = $fmax = $f;
	$f['replies'] /= $f['fidnum'];
}
unset($f);

foreach($forums as $f){
	$fmin['tidnum'] = min($fmin['tidnum'], $f['tidnum']);
	$fmax['tidnum'] = max($fmax['tidnum'], $f['tidnum']);

	$fmin['replies'] = min($fmin['replies'], $f['replies']);
	$fmax['replies'] = max($fmax['replies'], $f['replies']);
}

$query = DB::query("SELECT fid,uid FROM $forum_moderator_table WHERE 1");
while($m = DB::fetch($query)){
	$f = $forums[$m['fid']];
	if(empty($members[$m['uid']]))
		continue;
	$m = &$members[$m['uid']];
	$m['score']['moderator'] += ($f['tidnum'] - $fmin['tidnum']) / $fmax['tidnum'] * 0.5 + ($f['replies'] - $fmin['replies']) / $fmax['replies'] * 0.5;
	$m['score']['total'] = min(4, $m['score']['total'] + $m['score']['moderator']);
	unset($m);
}

//Advice
$members[154]['score']['advice'] = 0.2;
$members[154]['score']['total'] = min(4, $members[154]['score']['advice'] + $members[154]['score']['total']);

//Sorting members
function member_compare($m1, $m2){
	if($m1['issbranch'] != $m2['issbranch'])
		return $m1['issbranch'] > $m2['issbranch'];
	if($m1['awardyear'] != $m2['awardyear'])
		return $m1['awardyear'] > $m2['awardyear'];
	return iconv('UTF-8', 'GBK', $m1['realname']) > iconv('UTF-8', 'GBK', $m2['realname']);
}

usort($members, 'member_compare');

include template('takashiro_issapply:rank');

?>
