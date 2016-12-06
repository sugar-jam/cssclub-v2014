<?php

if(!defined('IN_DISCUZ')) exit('access denied');

class CSSClub{
	public static $Data;

	const WebMaster = 0;

	public static function Branch($code, $key = 'school'){
		foreach(self::$Data as $branch){
			if(isset($branch[$key]) && $branch[$key] === $code){
				return $branch;
			}
		}
		return null;
	}

	public static function Admin(){
		global $_G;
		$admin = array();
		if(isfounder()){
			$admin['groupid'] = self::WebMaster;
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

		$branch = self::Branch($admin['issbranch'], 'name');
		$admin['awardschool'] = $branch['school'];

		return $admin;
	}
}

$rows = array(
	array('Z', '浙江大学', '启新团'),
	array('B', '北京大学', '燕新社'),
	array('Q', '清华大学', '京英汇'),
	array('J', '上海交通大学', '新尚海'),
	array('F', '复旦大学', ''),
	array('S', '四川大学', '川行团'),
	array('D', '电子科技大学', '成电菁英'),
	array('X', '西南财经大学', '新财菁'),
	array('N', '西南交通大学', '新唐苑'),
	array('C', '重庆大学', '渝跃团'),
	array('', '新粤社', ''),
	array('', '鸥沃社', ''),
);

CSSClub::$Data = array();
foreach($rows as $row){
	CSSClub::$Data[] = array(
		'code' => $row[0],
		'school' => $row[1],
		'name' => $row[2],
	);
}
