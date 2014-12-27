<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

//全局嵌入点类（必须存在）
class plugin_takashiro_autoverify {

}

//脚本嵌入点类
class plugin_takashiro_autoverify_member extends plugin_takashiro_autoverify {

	function register_verify_message($value){
		global $_G;

		$param = &$value['param'];
		if($param[0] == 'register_manual_verify'){
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
				$uid = &$param[2]['uid'];
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

}

?>
