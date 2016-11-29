<?php
/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: admincp_verify.php 33455 2013-06-19 03:52:01Z andyzheng $
 */

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}
cpheader();

$root_url = 'plugins&operation='.$operation.'&do='.$do;

$anchor = in_array($_GET['anchor'], array('base', 'edit', 'verify', 'verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7', 'authstr', 'refusal', 'pass')) ? $_GET['anchor'] : 'base';
$current = array($anchor => 1);

loadcache('profilesetting');
$vid = 0;
$anchor = in_array($_GET['anchor'], array('authstr', 'refusal', 'pass', 'add')) ? $_GET['anchor'] : 'authstr';
$current = array($anchor => 1);

if($anchor != 'pass') {
	$_GET['verifytype'] = $vid;
} else {
	$_GET['verify'.$vid] = 1;
	$_GET['orderby'] = 'uid';
}

$admin = array();
if(isfounder()){
	$admin['groupid'] = 0;
}else{
	$cpgroup = C::t('common_admincp_member')->fetch($_G['uid']);
	if($cpgroup){
		$admin['groupid'] = $cpgroup['cpgroupid'];
	}else{
		$admin['groupid'] = null;
	}
}

$common_member_profile = DB::table('common_member_profile');
$admin['issbranch'] = DB::result_first("SELECT issbranch FROM $common_member_profile WHERE uid={$_G['uid']}");

require_once DISCUZ_ROOT.'source/plugin/takashiro_issprofile/cssclub.class.php';
$branch = CSSClub::Branch($issbranch, 'name');
$admin['awardschool'] = $branch['school'];
unset($branch);

require_once libfile('function/profile');
if(!submitcheck('verifysubmit', true)) {
?>
<iframe id="frame_profile" name="frame_profile" style="display: none"></iframe>
<script>
function singleverify(vid) {
	var formobj = $('cpform');
	var oldaction = formobj.action;
	formobj.action = oldaction+'&frame=no&singleverify='+vid;
	formobj.target = "frame_profile";
	formobj.submit();
	formobj.action = oldaction;
	formobj.target = "";
}
</script>
<?php
	if($anchor == 'refusal') {
		$_GET['flag'] = -1;
	} elseif ($anchor == 'authstr') {
		$_GET['flag'] = 0;
	}
	$intkeys = array('uid', 'verifytype', 'flag', 'verify1', 'verify2', 'verify3', 'verify4', 'verify5', 'verify6', 'verify7');
	$strkeys = array();
	$randkeys = array();
	$likekeys = array('username');
	$results = getwheres($intkeys, $strkeys, $randkeys, $likekeys, 'v.');
	foreach($likekeys as $k) {
		$_GET[$k] = dhtmlspecialchars($_GET[$k]);
	}

	$orders = getorders(array('dateline', 'uid'), 'dateline', 'v.');
	$ordersql = $orders['sql'];

	$orders = in_array($_G['orderby'], array('dateline', 'uid')) ? $_G['orderby'] : 'dateline';

	showformheader($root_url.'&anchor='.$anchor);
	echo "<script>disallowfloat = '{$_G[setting][disallowfloat]}';</script><input type=\"hidden\" name=\"verifysubmit\" value=\"trun\" />";
	showtableheader('members_verify_manage', 'fixpadding');

	$cssarr = array('width="90"', 'width="120"', 'width="120"', '');
	$titlearr = array($lang['members_verify_username'], $lang['members_verify_type'], $lang['members_verify_dateline'], $lang['members_verify_info']);
	showtablerow('class="header"', $cssarr, $titlearr);
	$count = C::t('common_member_verify_info')->count_by_search($_GET['uid'], $vid, $_GET['flag'], $_GET['username'], strtotime($_GET['dateline1']), strtotime($_GET['dateline2']));

	if($count) {
		$verifyusers = C::t('common_member_verify_info')->fetch_all_search(null, $vid, $_GET['flag'], $_GET['username'], strtotime($_GET['dateline1']), strtotime($_GET['dateline2']), $orders, 0, 0, 'desc');
		$verifyuids = array();
		foreach($verifyusers as $u){
			$verifyuids[] = $u['uid'];
		}
		$members = C::t('common_member')->fetch_all($verifyuids, false, 0);
		$profiles = C::t('common_member_profile')->fetch_all($verifyuids, false, 0);

		foreach($verifyusers as $vid => $value) {
			$uid = $value['uid'];
			if(empty($members[$uid]) || empty($profiles[$uid])){
				continue;
			}
			if($members[$uid]['groupid'] == 8){
				continue;
			}

			$value['username'] = '<a href="home.php?mod=space&uid='.$value['uid'].'&do=profile" target="_blank">'.avatar($value['uid'], "small").'<br/>'.$value['username'].'</a>';
			$fields = dunserialize($value['field']);
			if($admin['groupid'] !== 0){
				if(isset($fields['issbranch'])){
					if($fields['issbranch'] !== $admin['issbranch']){
						continue;
					}
				}elseif(isset($fields['awardschool'])){
					if($fields['awardschool'] !== $admin['awardschool']){
						continue;
					}
				}else{
					$profile = $profiles[$uid];
					if(isset($profile['issbranch'])){
						if($profile['issbranch'] !== $profile['issbranch']){
							continue;
						}
					}elseif(isset($fields['awardschool'])){
						if($profile['awardschool'] !== $profile['awardschool']){
							continue;
						}
					}else{
						continue;
					}
				}
			}

			$verifytype = $value['verifytype'] ? $_G['setting']['verify'][$value['verifytype']]['title'] : $lang['members_verify_profile'];
			$fieldstr = '<table width="96%">';
			$i = 0;
			$fieldstr .= '<tr>'.($anchor == 'authstr' ? '<td width="26">'.$lang[members_verify_refusal].'</td>' : '').'<td width="100">'.$lang['members_verify_fieldid'].'</td><td>'.$lang['members_verify_newvalue'].'</td></tr><tbody id="verifyitem_'.$value[vid].'">';
			$i++;
			foreach($fields as $key => $field) {
				if(in_array($key, array('constellation', 'zodiac', 'birthyear', 'birthmonth', 'birthprovince', 'birthdist', 'birthcommunity', 'resideprovince', 'residedist', 'residecommunity'))) {
					continue;
				}
				if($_G['cache']['profilesetting'][$key]['formtype'] == 'file') {
					if($field) {
						$field = '<a href="'.(getglobal('setting/attachurl').'./profile/'.$field).'" target="_blank"><img src="'.(getglobal('setting/attachurl').'./profile/'.$field).'" class="verifyimg" /></a>';
					} else {
						$field = cplang('members_verify_pic_removed');
					}
				} elseif(in_array($key, array('gender', 'birthday', 'birthcity', 'residecity'))) {
					$field = profile_show($key, $fields);
				}
				$fieldstr .= '<tr>'.($anchor == 'authstr' ? '<td><input type="checkbox" name="refusal['.$value['vid'].']['.$key.']" value="'.$key.'" onclick="$(\'refusal'.$value['vid'].'\').click();" /></td>' : '').'<td>'.$_G['cache']['profilesetting'][$key]['title'].':</td><td>'.$field.'</td></tr>';
				$i++;
			}
			$opstr = "";

			if($anchor == 'authstr') {
				$opstr .= "<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"validate\" onclick=\"mod_setbg($value[vid], 'validate');showreason($value[vid], 0);\">$lang[validate]</label>&nbsp;<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"refusal\" id=\"refusal$value[vid]\" onclick=\"mod_setbg($value[vid], 'refusal');showreason($value[vid], 1);\">$lang[members_verify_refusal]</label>";
			} elseif ($anchor == 'refusal') {
				$opstr .= "<label><input class=\"radio\" type=\"radio\" name=\"verify[$value[vid]]\" value=\"validate\" onclick=\"mod_setbg($value[vid], 'validate');\">$lang[validate]</label>";
			}

			$fieldstr .= "</tbody><tr><td colspan=\"5\">$opstr &nbsp;<span id=\"reason_$value[vid]\" style=\"display: none;\">$lang[moderate_reasonpm]&nbsp; <input type=\"text\" class=\"txt\" name=\"reason[$value[vid]]\" style=\"margin: 0px;\"></span>&nbsp;<input type=\"button\" value=\"$lang[moderate]\" name=\"singleverifysubmit\" class=\"btn\" onclick=\"singleverify($value[vid]);\"></td></tr></table>";

			$valuearr = array($value['username'], $verifytype, dgmdate($value['dateline'], 'dt'), $fieldstr);
			showtablerow("id=\"mod_$value[vid]_row\" verifyid=\"$value[vid]\"", $cssarr, $valuearr);
		}
	} else {
		showtablerow('', 'colspan="'.count($cssarr).'"', '<strong>'.cplang('moderate_nodata').'</strong>');
	}

	showtablefooter();
	showformfooter();

} else {

	$vids = array();
	$single = intval($_GET['singleverify']);
	$verifyflag = empty($_GET['verify']) ? false : true;
	if($verifyflag) {
		if($single) {
			$_GET['verify'] = array($single => $_GET['verify'][$single]);
		}
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
		cpmsg('members_verify_succeed', 'action=verify&operation=verify&do='.$vid.'&anchor='.$_GET['anchor'], 'succeed');
	}
}

function getverifyicon($iconkey = 'iconnew', $vid = 1, $extstr = 'verify_icon') {
	global $_G, $_FILES;

	if($_FILES[$iconkey]) {
		$data = array('extid' => "$vid");
		$iconnew = upload_icon_banner($data, $_FILES[$iconkey], $extstr);
	} else {
		$iconnew = $_GET[''.$iconkey];
	}
	return $iconnew;
}

function delverifyicon($icon) {
	global $_G;

	$valueparse = parse_url($icon);
	if(!isset($valueparse['host']) && preg_match('/^'.preg_quote($_G['setting']['attachurl'].'common/', '/').'/', $icon)) {
		@unlink($icon);
	}
	return '';
}
