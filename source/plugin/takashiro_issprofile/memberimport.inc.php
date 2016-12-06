<?php

if(!defined('IN_DISCUZ') || !defined('IN_ADMINCP')) {
	exit('Access Denied');
}

$table = DB::table('plugin_member_verify');
$mod_action = 'plugins&operation=config&identifier=takashiro_issprofile&pmod=memberimport';
$mod_url = 'action='.$mod_action;

if(submitcheck('importsubmit')){
	if(isset($_FILES['namelist'])){
		$rows = file($_FILES['namelist']['tmp_name']);
		foreach($rows as $row){
			//1,方云哲,2014,浙江大学
			$cells = explode(',', $row);
			if(count($cells) < 4){
				continue;
			}
			foreach($cells as &$cell){
				$cell = addslashes(htmlspecialchars(trim($cell)));
			}
			unset($cell);

			DB::query("INSERT INTO $table (`subserial`,`realname`,`awardyear`,`awardschool`) VALUES ('{$cells[0]}', '$cells[1]', '$cells[2]', '$cells[3]')");
		}
		cpmsg('成功导入。', $mod_url, 'succeed');
	}else{
		cpmsg('请上传一个csv文件。', $mod_url, 'error');
	}
}

showformheader($mod_action, 'enctype');
showtableheader('批量导入');
showsetting('获奖名单csv文件', 'namelist', '', $type = 'file');
showsubmit('importsubmit');
showtablefooter();
showformfooter();

$limit = 60;
$offset = ($page - 1) * $limit;

$condition = array();

require_once dirname(__FILE__).'/cssclub.class.php';
$admin = CSSClub::Admin();
if($admin['groupid'] !== CSSClub::WebMaster){
	$condition[] = 'awardschool=\''.daddslashes($admin['awardschool']).'\'';
}

$condition = $condition ? '('.implode(')AND (', $condition).')' : '1';
$query = DB::query("SELECT id,awardschool,awardyear,realname,subserial,uid FROM $table WHERE $condition LIMIT $offset,$limit");

showtableheader('用户列表', 'fixpadding');
showsubtitle(array('VID', '获奖学校', '获奖年份', '真实姓名', '序号', 'UID'));
while($row = DB::fetch($query, MYSQL_NUM)){
	$row[4] = !empty($row[4]) ? $row[4] : '<div class="input_editor" data-verify-id="'.$row[0].'" data-uid="'.$row[5].'"></div>';
	showtablerow('', array(), $row);
}
showtablefooter();

$totalnum = DB::result_first("SELECT COUNT(*) FROM $table WHERE $condition");
echo multi($totalnum, $limit, $page, ADMINSCRIPT.'?'.$mod_url);
?>
<script>
var editors = document.getElementsByTagName('div');
var edit_url = '<?php echo ADMINSCRIPT.'?'.$mod_url?>';
for(var i = 0; i < editors.length; i++){
	var editor = editors[i];
	if(editor.className.indexOf('input_editor') == -1){
		continue;
	}

	editor.ondblclick = function(){
		var input = document.createElement('input');
		input.type = 'text';
		input.onblur = function(){
			var id = this.dataset.verifyId;
			var uid = this.dataset.uid;
			var value = this.value;
			this.parentNode.innerHTML = value;

			ajaxget(edit_url + '&verifyid=' + id + '&uid=' + uid);
		};
		this.innerHTML = '';
		this.appendChild(input);
	};
}
</script>
