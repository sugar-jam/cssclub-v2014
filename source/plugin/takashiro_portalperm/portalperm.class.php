<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

class plugin_takashiro_portalperm {

}

class plugin_takashiro_portalperm_portal extends plugin_takashiro_portalperm {

	function view_permcheck() {
		global $_G;

		$aid = empty($_GET['aid']) ? 0 : intval($_GET['aid']);
		$article = C::t('portal_article_title')->fetch($aid);

		//@todo: this should be a configuration
		$forbidden_usergroup_table = array(
			3 => array(6, 7, 8, 9, 20),
		);

		if(isset($forbidden_usergroup_table[$article['catid']])){
			$forbidden_usergroups = $forbidden_usergroup_table[$article['catid']];
			if(in_array($_G['groupid'], $forbidden_usergroups)){
				if(in_array($_G['groupid'], array(6, 7, 8, 9))){
					showmessage('先登录一下呀~', null, array(), array('showmsg' => true, 'login' => 1));
				}else{
					showmessage('抱歉，您没有权限访问该内容哦……');
				}
			}
		}
	}
}

?>
