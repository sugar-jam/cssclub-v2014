<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

class plugin_takashiro_autoverify {
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

}

class plugin_takashiro_autoverify_home extends plugin_takashiro_autoverify {

	function space_profile_baseinfo_top(){
		global $_G;

		$uid = intval($_GET['uid']);

		if($uid != $_G['uid']){
			$member = array('uid' => $uid);
			space_merge($member, 'field_home');

			$realname_privacy = &$member['privacy']['profile']['realname'];
			$field4_privacy = &$member['privacy']['profile']['field4'];

			//仅注册用户可见
			if(($realname_privacy == 2 || $field4_privacy == 2) && empty($_G['uid'])){
				return '';
			}
			//仅好友可见
			if($realname_privacy == 1 || $field4_privacy == 1){
				$friends = C::t('home_friend')->fetch_all_by_uid_fuid($uid, $_G['uid']);
				if(empty($friends[0])){
					return '';
				}
			}
			//保密
			if(($realname_privacy == 3 || $field4_privacy == 3)){
				return '';
			}
		}

		$verify_table = DB::TABLE('plugin_member_verify');
		$profile_table = DB::TABLE('common_member_profile');
		$info = DB::fetch_first("SELECT v.*,p.`realname`,p.`field4` FROM `{$verify_table}` v
			LEFT JOIN `{$profile_table}` p ON p.uid=v.uid
			WHERE p.`uid`=$uid");
		$hid = self::$SchoolPrefix[$info['awardschool']].substr($info['awardyear'], -2, 2).$info['subserial'].'-'.$info['realname'].'-'.$info['field4'];
		return '<ul class="pf_l cl"><li><em>团内编号</em>'.$hid.'</li></ul>';
	}

}

class plugin_takashiro_autoverify_member extends plugin_takashiro_autoverify {

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

	function _verify_member(){
		global $_G;

		$user = array(
			'realname' => $_POST['realname'],
			'awardyear' => $_POST['field1'],
			'awardschool' => $_POST['field3'],
		);

		$tablename = DB::table('plugin_member_verify');
		$info = DB::fetch_first("SELECT `id`
			FROM `$tablename`
			WHERE `realname`='$user[realname]'
				AND `awardyear`='$user[awardyear]'
				AND `awardschool`='$user[awardschool]'
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
				$note_lang = 'profile_verify_pass';

				notification_add($value['uid'], 'verify', $note_lang, $note_values, 1);

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

?>
