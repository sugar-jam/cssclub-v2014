<?php

/**
 *      [Discuz!] (C)2001-2099 Comsenz Inc.
 *      This is NOT a freeware, use is subject to license terms
 *
 *      $Id: task_profile.php 24704 2011-10-08 10:19:11Z zhangguosheng $
 */

if(!defined('IN_DISCUZ')) {
	exit('Access Denied');
}

class task_profile {

	var $version = '1.0';
	var $name = 'profile_name';
	var $description = 'profile_desc';
	var $copyright = '<a href="http://www.comsenz.com" target="_blank">Comsenz Inc.</a>';
	var $icon = '';
	var $period = '';
	var $periodtype = 0;
	var $conditions = array();

	function csc($task = array()) {
		global $_G;

		$data = $this->checkfield();
		if(!$data[0]) {
			return true;
		}
		return array('csc' => $data[1], 'remaintime' => 0);
	}

	function view() {
		$data = $this->checkfield();
		return lang('task/profile', 'profile_view', array('profiles' => implode(', ', $data[0])));
	}

	function checkfield() {
		global $_G;

		$unrequired_fields = array(
			'birthprovince', 'birthcity', 'birthdist', 'birthcommunity',
			'resideprovince', 'residecity', 'residedist', 'residecommunity',
			'company', 'occupation', 'position',
			'birthyear', 'birthmonth', 'zodiac', 'constellation'
		);

		loadcache('profilesetting');
		$fields = array();
		foreach($_G['cache']['profilesetting'] as $fieldid => $fieldsetting){
			if($fieldsetting['available'] && !in_array($fieldid, $unrequired_fields)){
				$fields[] = $fieldid;
			}
		}

		$fieldsnew = array();
		foreach($fields as $v) {
			$fieldsnew[$v] = $_G['cache']['profilesetting'][$v]['title'];
		}
		if($fieldsnew) {
			space_merge($_G['member'], 'profile');
			$none = array();
			foreach($_G['member'] as $k => $v) {
				if(in_array($k, $fields, true) && !trim($v)) {
					$none[] = $fieldsnew[$k];
				}
			}
			$all = count($fields);
			$csc = intval(($all - count($none)) / $all * 100);
			return array($none, $csc);
		} else {
			return true;
		}
	}

}

?>