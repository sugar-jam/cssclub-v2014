<?php

if (!defined('IN_DISCUZ')) exit('Access Denied');

if (submitcheck('passsubmit')) {
	$vids = array();
	if(!empty($_GET['verify'])) {
		foreach($_GET['verify'] as $id => $type) {
			$vids[] = $id;
		}

		$verifysetting = $_G['setting']['verify'];
		$verify = $refusal = array();
		foreach(C::t('common_member_verify_info')->fetch_all($vids) as $value) {
			if(in_array($_GET['verify'][$value['vid']], array('refusal', 'validate'))) {
				$fields = dunserialize($value['field']);
				$verifysetting = $_G['setting']['verify'][$value['verifytype']];

				if($_GET['verify'][$value['vid']] == 'refusal') {
					$refusalfields = !empty($_GET['refusal'][$value['vid']]) ? $_GET['refusal'][$value['vid']] : $verifysetting['field'];
					$fieldtitle = $common = '';
					$deleteverifyimg = false;
					foreach($refusalfields as $key => $field) {
						$fieldtitle .= $common.$_G['cache']['profilesetting'][$field]['title'];
						$common = ',';
						if($_G['cache']['profilesetting'][$field]['formtype'] == 'file') {
							$deleteverifyimg = true;
							@unlink(getglobal('setting/attachdir').'./profile/'.$fields[$key]);
							$fields[$field] = '';
						}
					}
					if($deleteverifyimg) {
						C::t('common_member_verify_info')->update($value['vid'], array('field' => serialize($fields)));
					}
					if($value['verifytype']) {
						$verify["verify"]['-1'][] = $value['uid'];
					}
					$verify['flag'][] = $value['vid'];
					$note_values = array(
							'verify' => $vid ? '<a href="home.php?mod=spacecp&ac=profile&op=verify&vid='.$vid.'" target="_blank">'.$verifysetting['title'].'</a>' : '',
							'profile' => $fieldtitle,
							'reason' => $_GET['reason'][$value['vid']],
						);
					$note_lang = 'profile_verify_error';
				} else {
					C::t('common_member_profile')->update(intval($value['uid']), $fields);
					$verify['delete'][] = $value['vid'];
					if($value['verifytype']) {
						$verify["verify"]['1'][] = $value['uid'];
					}
					$note_values = array(
							'verify' => $vid ? '<a href="home.php?mod=spacecp&ac=profile&op=verify&vid='.$vid.'" target="_blank">'.$verifysetting['title'].'</a>' : ''
						);
					$note_lang = 'profile_verify_pass';
				}
				notification_add($value['uid'], 'verify', $note_lang, $note_values, 1);
			}
		}
		if($vid && !empty($verify["verify"])) {
			foreach($verify["verify"] as $flag => $uids) {
				$flag = intval($flag);
				C::t('common_member_verify')->update($uids, array("verify$vid" => $flag));
			}
		}

		if(!empty($verify['delete'])) {
			C::t('common_member_verify_info')->delete($verify['delete']);
		}

		if(!empty($verify['flag'])) {
			C::t('common_member_verify_info')->update($verify['flag'], array('flag' => '-1'));
		}
	}
	if($single && $_GET['frame'] == 'no') {
		echo "<script type=\"text/javascript\">var trObj = parent.$('mod_{$single}_row');trObj.parentNode.removeChild(trObj);</script>";
	} else {
		showmessage('members_verify_succeed', 'action=verify&operation=verify&do='.$vid.'&anchor='.$_GET['anchor'], 'succeed');
	}
}

$anchor = &$_GET['anchor'];
if ($anchor != 'refusal') {
	$anchor = 'authstr';
}

$verifyusers = C::t('common_member_verify_info')->fetch_all_search(null, null, $anchor == 'authstr' ? 0 : -1);

$verifyuids = array();
foreach ($verifyusers as $user) {
	$verifyuids[] = $user['uid'];
}
$profiles = C::t('common_member_profile')->fetch_all($verifyuids, false, 0);

foreach ($verifyusers as $vid => &$user) {
	if (!array_key_exists($user['uid'], $profiles) || ($_G['member']['issbranch'] && $_G['member']['issbranch'] != $profiles[$user['uid']]['issbranch'])) {
		unset($verifyusers[$vid]);
	} else {
		$user = array_merge($user, $profiles[$user['uid']]);
		$user['field'] = dunserialize($user['field']);
		$user['dateline'] = dgmdate($user['dateline'], 'u');
	}
}
unset($user);

loadcache('profilesetting');

?>
