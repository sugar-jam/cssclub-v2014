<?php

require './source/class/class_core.php';

$discuz = C::app();
$discuz->init();

$data = array();

$namelist = file('namelist.csv');
foreach($namelist as $line){
	$line = explode(',', $line);
	$school = trim($line[0]);
	$year = intval($line[1]);
	$name = trim($line[2]);

	$data[$school][$year][] = $name;
}

include_once './tools/hanzi.func.php';

foreach($data as $school => &$years){
	foreach($years as $year => &$namelist){
		usort($namelist, 'compareHanziByStroke');

		$i = 1;
		$verifytable = DB::table('plugin_member_verify');
		$profiletable = DB::table('common_member_profile');
		foreach($namelist as $name){
			$uid = DB::result_first("SELECT uid FROM $profiletable WHERE awardschool='$school' AND awardyear='$year' AND realname='$name'");
			$uid = $uid > 0 ? $uid : 'NULL';

			DB::query("INSERT INTO $verifytable (awardschool, awardyear, subserial, realname, uid) VALUES ('$school', '$year', '$i', '$name', $uid)");
			$i++;
		}
	}
	unset($namelist);
}
unset($years);

?>
