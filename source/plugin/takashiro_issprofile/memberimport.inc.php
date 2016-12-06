<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$table = DB::table('plugin_member_verify');
$mod_action = 'plugins&operation=config&do='.$do.'&identifier=takashiro_issprofile&pmod=memberimport';
$mod_url = 'action='.$mod_action;

require_once dirname(__FILE__).'/cssclub.class.php';
$admin = CSSClub::Admin();

if(submitcheck('importsubmit')){
	if(isset($_FILES['namelist'])){
		$rows = file($_FILES['namelist']['tmp_name']);
		if(empty($rows)){
			cpmsg('文件内容无法读取。', $mod_url, 'error');
		}

		$titles = explode(',', array_shift($rows));
		if(empty($rows)){
			cpmsg('表格内容为空，导入失败。', $mod_url, 'error');
		}

		if($titles[0] != '序号'){
			if($titles[0]{0} == '\xEF' && $titles[0]{1} == '\xBB' && $titles[0]{2} == '\xBF'){
				$titles[0] = substr($titles[0], 3);
			}else{
				$titles[0] = iconv('gbk', 'utf-8', $titles[0]);
				if($titles[0] == '序号'){
					foreach($rows as &$row){
						$row = iconv('gbk', 'utf-8', $row);
					}
					unset($row);
				}else{
					cpmsg('无法识别该文件编码。', $mod_url, 'error');
				}
			}
		}

		showformheader($mod_action);
		showtableheader('请确认获奖名单', 'fixpadding');
		showsubtitle(array('序号', '真实姓名', '获奖年份', '学校'));
		foreach($rows as $row){
			$cells = explode(',', $row);
			if(count($cells) < 3){
				continue;
			}

			//1,方云哲,2014,浙江大学
			$cells[0] = intval($cells[0]);
			$cells[1] = daddslashes(dhtmlspecialchars(trim($cells[1], "　\0\t\r\n\x0B ")));
			$cells[2] = intval($cells[2]);
			if(empty($cells[3]) || $admin['groupid'] !== CSSClub::WebMaster){
				$cells[3] = $admin['awardschool'];
			}else{
				$cells[3] = daddslashes(dhtmlspecialchars(trim($cells[3], "　\0\t\r\n\x0B ")));
				if(!CSSClub::Branch($cells[3], 'school')){
					$cells[3] = $admin['awardschool'];
				}
			}

			showtablerow('', array(), $cells);
		}
		echo '<textarea style="display:none" name="csv">'.implode("\n", $rows).'</textarea>';
		showsubmit('importsubmit');
		showtablefooter();
		showformfooter();
		exit;

	}elseif(isset($_POST['csv'])){
		$rows = implode($_POST['csv']);
		foreach($rows as $row){
			if(count($row) < 4){
				continue;
			}
			$serial = intval($row[0]);
			$realname = dhtmlspecialchars(daddslashes(trim($row[1])));
			$awardyear = intval($row[2]);
			$awardschool = dhtmlspecialchars(daddslashes(trim($row[3])));

			$table = DB::table('plugin_member_verify');
			DB::query("INSERT IGNORE INTO $table (`subserial`,`realname`,`awardyear`,`awardschool`) VALUES ('$serial', '$realname', '$awardyear', '$awardschool')");
		}
		cpmsg('成功导入名单。', $mod_url, 'succeed');

	}else{
		cpmsg('请上传一个csv文件。', $mod_url, 'error');
	}
}

showformheader($mod_action, 'enctype');
showtableheader('批量导入');
showsetting('上传获奖名单csv文件', 'namelist', '', 'file');
showsubmit('importsubmit', 'submit', '', '<a href="source/plugin/takashiro_issprofile/template/namelist.csv">下载名单csv模板</a>');
showtablefooter();
showformfooter();

$limit = 60;
$offset = ($page - 1) * $limit;

$condition = array();

if($admin['groupid'] !== CSSClub::WebMaster){
	$condition[] = 'awardschool=\''.daddslashes($admin['awardschool']).'\'';
}

$condition = $condition ? '('.implode(')AND (', $condition).')' : '1';
$query = DB::query("SELECT id,awardschool,awardyear,realname,subserial,uid FROM $table WHERE $condition LIMIT $offset,$limit");

showtableheader('用户列表', 'fixpadding');
showsubtitle(array('VID', '获奖学校', '获奖年份', '真实姓名', '序号', 'UID'));
while($row = DB::fetch($query, MYSQLI_NUM)){
	if(!empty($row[5])){
		$row[5] = '<a href="home.php?mod=space&do=profile&uid='.$row[5].'" target="_blank">'.$row[5].'</a>';
	}
	showtablerow('', array(), $row);
}
showtablefooter();

$totalnum = DB::result_first("SELECT COUNT(*) FROM $table WHERE $condition");
echo multi($totalnum, $limit, $page, ADMINSCRIPT.'?'.$mod_url);
