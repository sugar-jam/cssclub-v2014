<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

class plugin_takashiro_issprofile {
	protected static $SchoolPrefix = array(
		'浙江大学' => 'Z',
		'北京大学' => 'B',
		'清华大学' => 'Q',
		'上海交通大学' => 'J',
		'复旦大学' => 'F',
		'重庆大学' => 'C',
		'四川大学' => 'S',
		'电子科技大学' => 'D',
		'西南财经大学' => 'X',
		'西南交通大学' => 'N',
	);

	function cacheuserstats(){
		global $_G;
		$member = C::t('common_member')->range(0, 1, 'DESC');
		$member = current($member);
		$value = C::t('common_member_verify_info')->fetch_by_uid_verifytype($member['uid'], 0);
		if($value){
			$_G['userstatdata']['newsetuserprofile'] = dunserialize($value['field']);
			$_G['userstatdata']['newsetuserprofile']['uid'] = $member['uid'];
		}else{
			$_G['userstatdata']['newsetuserprofile'] = C::t('common_member_profile')->fetch($member['uid']);
		}
	}

	function _forcelogin(){
		global $_G;

		$forbidden_usergroups = array(6, 7, 8, 9, 20);
		if(in_array($_G['groupid'], $forbidden_usergroups)){
			if(in_array($_G['groupid'], array(6, 7, 8, 9))){
				showmessage('先登录一下呀~', null, array(), array('showmsg' => true, 'login' => 1));
			}else{
				showmessage('抱歉，您没有权限访问该内容哦……');
			}
		}
	}

}

class plugin_takashiro_issprofile_home extends plugin_takashiro_issprofile {

	function space_profile_baseinfo_top(){
		global $_G;

		$uid = !empty($_GET['uid']) ? intval($_GET['uid']) : $_G['uid'];

		if($uid != $_G['uid']){
			$member = array('uid' => $uid);
			space_merge($member, 'field_home');

			$realname_privacy = &$member['privacy']['profile']['realname'];
			$issbranch_privacy = &$member['privacy']['profile']['issbranch'];

			//仅注册用户可见
			if(($realname_privacy == 2 || $issbranch_privacy == 2) && empty($_G['uid'])){
				return '';
			}
			//仅好友可见
			if($realname_privacy == 1 || $issbranch_privacy == 1){
				$friends = C::t('home_friend')->fetch_all_by_uid_fuid($uid, $_G['uid']);
				if(empty($friends[0])){
					return '';
				}
			}
			//保密
			if(($realname_privacy == 3 || $issbranch_privacy == 3)){
				return '';
			}
		}

		$verify_table = DB::TABLE('plugin_member_verify');
		$profile_table = DB::TABLE('common_member_profile');
		$info = DB::fetch_first("SELECT v.*,p.`realname`,p.`issbranch` FROM `{$verify_table}` v
			LEFT JOIN `{$profile_table}` p ON p.uid=v.uid
			WHERE p.`uid`=$uid");
		$hid = self::$SchoolPrefix[$info['awardschool']].substr($info['awardyear'], -2, 2).$info['subserial'].'-'.$info['realname'].'-'.$info['issbranch'];
		return '<ul class="pf_l cl"><li><em>团内编号</em>'.$hid.'</li></ul>';
	}

	static protected $HiddenProfile = array(
		18 => array('awardschool', 'awardyear', 'issbranch'),
		21 => array('awardschool', 'awardyear', 'issbranch'),
		22 => array('awardschool', 'awardyear'),
	);

	function spacecp_profile_groupspecified_output($output){
		global $_G, $profilegroup;

		if(!isset(self::$HiddenProfile[$_G['groupid']]))
			return;

		$hidden_profile = &self::$HiddenProfile[$_G['groupid']];
		foreach($hidden_profile as $field){
			unset($GLOBALS['settings'][$field]);
		}
	}

}

class plugin_takashiro_issprofile_member extends plugin_takashiro_issprofile {

	function connect_verify_message($args){
		if($args['param'][0] == 'register_manual_verify'){
			$this->_verify_member();
		}
	}

	function register_verify_message($args){
		if($args['param'][0] == 'register_manual_verify'){
			$this->_verify_member();
		}
	}

	function connect_verify(){
		$this->_check_duplicated_account();
	}

	function register_verify(){
		$this->_check_duplicated_account();
	}

	function _check_duplicated_account(){
		if($_POST){
			if(empty($_POST['realname']))
				showmessage('请填写真实姓名。');

			if(empty($_POST['awardyear']))
				showmessage('请选择获奖年份。');

			if(empty($_POST['awardschool']))
				showmessage('请选择获奖所在学校。');

			$user = array(
				'realname' => trim($_POST['realname']),
				'awardyear' => intval($_POST['awardyear']),
				'awardschool' => trim($_POST['awardschool']),
			);

			$tablename = DB::table('plugin_member_verify');
			$info = DB::fetch_first("SELECT `id`,`uid`
				FROM `$tablename`
				WHERE `realname`='{$user['realname']}'
					AND `awardyear`='{$user['awardyear']}'
					AND `awardschool`='{$user['awardschool']}'");

			if($info['uid'] > 0){
				$profile = C::t('common_member')->fetch($info['uid']);
				if($profile){
					showmessage('您已注册账号 '.$profile['username'].' ，请通过该账号登录。');
				}else{
					showmessage('系统异常。');
				}
			}
		}
	}

	function _verify_member(){
		global $_G;

		$user = array(
			'realname' => trim($_POST['realname']),
			'awardyear' => intval($_POST['awardyear']),
			'awardschool' => trim($_POST['awardschool']),
		);

		$tablename = DB::table('plugin_member_verify');
		$info = DB::fetch_first("SELECT `id`
			FROM `$tablename`
			WHERE `realname`='{$user['realname']}'
				AND `awardyear`='{$user['awardyear']}'
				AND `awardschool`='{$user['awardschool']}'
				AND `uid` IS NULL");

		if($info){
			$uid = $_G['uid'];
			if(!empty($uid)){
				DB::query("UPDATE `$tablename` SET `uid`='$uid' WHERE `id`='$info[id]'");

				$validateuids = array($uid);
				C::t('common_member')->update($validateuids, array('adminid' => 0, 'groupid' => $_G['setting']['newusergroupid'], 'freeze' => 0));
				C::t('common_member_validate')->delete($validateuids);
				notification_add($uid, 'mod_member', 'member_moderate_validate_no_remark');

				$common_member_verify_info = DB::table('common_member_verify_info');
				$vid = DB::result_first("SELECT `vid` FROM `$common_member_verify_info` WHERE `uid`='$uid'");

				$verify = $refusal = array();
				$value = C::t('common_member_verify_info')->fetch($vid);
				$fields = dunserialize($value['field']);
				$verifysetting = $_G['setting']['verify'][$value['verifytype']];

				C::t('common_member_profile')->update(intval($value['uid']), $fields);
				$verify['delete'][] = $value['vid'];
				if($value['verifytype']) {
					$verify["verify"]['1'][] = $value['uid'];
				}
				$note_values = array('verify' => '');

				notification_add($value['uid'], 'verify', 'profile_verify_pass', $note_values, 1);

				if(!empty($verify['delete'])) {
					C::t('common_member_verify_info')->delete($verify['delete']);
				}

				if(!empty($verify['flag'])) {
					C::t('common_member_verify_info')->update($verify['flag'], array('flag' => '-1'));
				}
			}
		}
	}

}

class plugin_takashiro_issprofile_forum extends plugin_takashiro_issprofile{

	function forumdisplay_realname_output($output){
		global $_G;
		$uids = array();
		$usernames = array();
		foreach($_G['forum_threadlist'] as $thread){
			$uids[] = $thread['authorid'];
			empty($thread['lastposter']) || $usernames[] = $thread['lastposter'];
		}
		$usernames = implode('\',\'', $usernames);

		$username2realname = array();
		$common_member = DB::table('common_member');
		$common_member_profile = DB::table('common_member_profile');
		$query = DB::query("SELECT m.username,p.realname FROM `$common_member` m LEFT JOIN `$common_member_profile` p ON p.uid=m.uid WHERE m.username IN ('$usernames')");
		while($node = DB::fetch($query)){
			empty($node['realname']) || $username2realname[$node['username']] = $node['realname'];
		}

		$profiles = C::t('common_member_profile')->fetch_all($uids);
		foreach ($_G['forum_threadlist'] as &$thread) {
			$thread['authorprofile'] = $profiles[$thread['authorid']];
			if(!empty($thread['lastposter']) && array_key_exists($thread['lastposter'], $username2realname)){
				$thread['lastposter'] = $username2realname[$thread['lastposter']];
			}
		}
		unset($thread);
	}

	function index_forcelogin(){
		$this->_forcelogin();
	}

	function index_realname_output($output){
		global $whosonline;
		if(empty($whosonline))
			return;

		$uids = array();
		foreach($whosonline as $member){
			empty($member['uid']) || $uids[] = $member['uid'];
		}

		$profiles = C::t('common_member_profile')->fetch_all($uids);
		foreach($whosonline as &$member){
			if(!empty($profiles[$member['uid']]['realname'])){
				$member['username'] = $profiles[$member['uid']]['realname'];
			}
		}
	}

}

?>
