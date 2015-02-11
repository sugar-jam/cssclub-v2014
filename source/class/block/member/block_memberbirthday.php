<?php

/* Powered by Takashiro */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

require_once libfile('block_member', 'class/block/member');

class block_memberbirthday extends block_member {
	function __construct() {
		$this->setting = array(
			'groupid' => array(
				'title' => 'memberlist_groupid',
				'type' => 'mselect',
				'value' => array()
			),
			'gender' => array(
				'title' => 'memberlist_gender',
				'type' => 'mradio',
				'value' => array(
					array('1', 'memberlist_gender_male'),
					array('2', 'memberlist_gender_female'),
					array('', 'memberlist_gender_nolimit'),
				),
				'default' => ''
			),
			'avatarstatus' => array(
				'title' => 'memberlist_avatarstatus',
				'type' => 'radio',
				'default' => ''
			),
			'startrow' => array(
				'title' => 'memberlist_startrow',
				'type' => 'text',
				'default' => 0
			),
		);
	}

	function name() {
		return '生日会员';
	}

	function getdata($style, $parameter) {
		global $_G;

		$parameter = $this->cookparameter($parameter);

		$groupid	= !empty($parameter['groupid']) && !in_array(0, $parameter['groupid']) ? $parameter['groupid'] : array();
		$startrow	= !empty($parameter['startrow']) ? intval($parameter['startrow']) : 0;
		$items		= !empty($parameter['items']) ? intval($parameter['items']) : 10;
		$avatarstatus = !empty($parameter['avatarstatus']) ? 1 : 0;
		$profiles = array();
		$profiles['gender'] = !empty($parameter['gender']) ? intval($parameter['gender']) : 0;

		$bannedids = !empty($parameter['bannedids']) ? explode(',', $parameter['bannedids']) : array();

		$list = $todayposts = array();
		$tables = $wheres = array();
		$olditems = $items;
		$tables[] = DB::table('common_member').' m';
		if($groupid) {
			$wheres[] = 'm.groupid IN ('.dimplode($groupid).')';
		}
		if($bannedids) {
			$wheres[] = 'm.uid NOT IN ('.dimplode($bannedids).')';
		}
		if($avatarstatus) {
			$wheres[] = "m.avatarstatus='1'";
		}

		$tables[] = DB::table('common_member_count').' mc';
		$wheres[] = 'mc.uid=m.uid';

		$tables[] = DB::table('common_member_profile').' mp';
		$wheres[] = 'mp.uid=m.uid';

		foreach($profiles as $key => $value) {
			if($value) {
				$wheres[] = "mp.$key='$value'";
			}
		}

		$wheres[] = '(m.groupid < 4 OR m.groupid > 8)';

		$wheres = array($wheres, $wheres);
		list($today_month, $today_day) = explode('-', dgmdate(TIMESTAMP, 'm-d'));
		$wheres[0][] = "((mp.birthmonth=$today_month AND mp.birthday>=$today_day) OR mp.birthmonth>$today_month)";
		$wheres[1][] = "((mp.birthmonth>0 AND mp.birthmonth<$today_month) OR (mp.birthmonth=$today_month AND mp.birthday<$today_day))";

		$tablesql = implode(',', $tables);

		foreach($wheres as $where){
			$wheresql = implode(' AND ', $where);
			$query = DB::query("SELECT m.*,mc.* FROM $tablesql WHERE $wheresql ORDER BY mp.birthmonth, mp.birthday LIMIT $startrow,$items");
			$resultuids = array();
			while($data = DB::fetch($query)){
				$resultuids[] = intval($data['uid']);
				$list[] = array(
					'id' => $data['uid'],
					'idtype' => 'uid',
					'title' => $data['username'],
					'url' => 'home.php?mod=space&uid='.$data['uid'],
					'pic' => '',
					'picflag' => 0,
					'summary' => '',
					'fields' => array(
						'avatar' => avatar($data['uid'], 'small', true, false, false, $_G['setting']['ucenterurl']),
						'avatar_middle' => avatar($data['uid'], 'middle', true, false, false, $_G['setting']['ucenterurl']),
						'avatar_big' => avatar($data['uid'], 'big', true, false, false, $_G['setting']['ucenterurl']),
						'credits' => $data['credits'],
						'extcredits1' => $data['extcredits1'],
						'extcredits2' => $data['extcredits2'],
						'extcredits3' => $data['extcredits3'],
						'extcredits4' => $data['extcredits4'],
						'extcredits5' => $data['extcredits5'],
						'extcredits6' => $data['extcredits6'],
						'extcredits7' => $data['extcredits7'],
						'extcredits8' => $data['extcredits8'],
						'regdate' => $data['regdate'],
						'posts' => empty($todayposts[$data['uid']]) ? $data['posts'] : $todayposts[$data['uid']],
						'threads' => $data['threads'],
						'digestposts' => $data['digestposts'],
						'reason' => isset($data['reason']) ? $data['reason'] : '',
						'unitprice' => isset($data['unitprice']) ? $data['unitprice'] : '',
						'showcredit' => isset($data['showcredit']) ? $data['showcredit'] : '',
						'shownote' => isset($data['shownote']) ? $data['shownote'] : '',
					),
				);
			}

			$items -= count($resultuids);
			if($items <= 0)
				break;
		}
		if($resultuids) {
			include_once libfile('function/profile');
			$profiles = array();
			$query = DB::query('SELECT * FROM '.DB::table('common_member_profile')." WHERE uid IN (".dimplode($resultuids).")");
			while($data = DB::fetch($query)) {
				$profile = array();
				foreach($data as $fieldid=>$fieldvalue) {
					$fieldvalue = profile_show($fieldid, $data, true);
					if(false !== $fieldvalue) {
						$profile[$fieldid] = $fieldvalue;
					}
				}
				$profiles[$data['uid']] = $profile;
			}
			for($i=0,$L=count($list); $i<$L; $i++) {
				$uid = $list[$i]['id'];
				if($profiles[$uid]) {
					$list[$i]['fields'] = array_merge($list[$i]['fields'], $profiles[$uid]);
				}
			}
		}
		return array('html' => '', 'data' => $list);
	}
}

?>
