<?php

if(!defined('IN_DISCUZ'))
	exit('Access Denied');

$verify_table = DB::table('plugin_member_verify');
$club_member_num = DB::result_first("SELECT COUNT(*) FROM $verify_table");

$profile_table = DB::table('common_member_profile');
$gender_stat = DB::fetch_all("SELECT gender,COUNT(*) AS num FROM $profile_table WHERE gender IN (1,2) GROUP BY gender");
$male_num = $female_num = 0;
foreach($gender_stat as $row){
	if($row['gender'] == 1){
		$male_num = $row['num'];
	}elseif($row['gender'] == 2){
		$female_num = $row['num'];
	}
}
$gender_ratio = $female_num > 0 ? ($male_num / $female_num) : 0;

$education_stat = DB::fetch_all("SELECT education,COUNT(*) AS num FROM $profile_table WHERE education!='' GROUP BY education");
$education_sample_num = 0;
$student_count = 0;
foreach($education_stat as $r){
	$education_sample_num += $r['num'];
	if(in_array($r['education'], array('中学', '本科在读', '硕士在读', '博士在读'))){
		$student_count += $r['num'];
	}
}

$overseas_education_count = DB::result_first("SELECT COUNT(*) FROM $profile_table WHERE field1='有'");
$employed_count = DB::result_first("SELECT COUNT(*) FROM $profile_table WHERE company!=''");

$branch_sample_num = 0;
$branch_stat = DB::fetch_all("SELECT issbranch,COUNT(*) AS num FROM $profile_table WHERE issbranch!='' GROUP BY issbranch");
foreach($branch_stat as $r){
	$branch_sample_num += $r['num'];
}

$city_sample_num = DB::result_first("SELECT COUNT(*) FROM $profile_table WHERE residecity!=''");
$city_stat = DB::fetch_all("SELECT resideprovince,residecity,COUNT(*) AS num FROM $profile_table WHERE residecity!='' GROUP BY resideprovince,residecity ORDER BY num DESC LIMIT 5");

include template('takashiro_issprofile:stat');
