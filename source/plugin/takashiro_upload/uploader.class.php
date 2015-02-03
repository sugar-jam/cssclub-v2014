<?php

if(!defined('IN_DISCUZ')) exit('Access Denied');

class plugin_takashiro_upload{
	public function _post_image_btn_extra(){
		return '<li id="e_btn_tupload"><a href="javascript:switchImagebutton(\'tupload\');" hidefocus="true">超大图片</a></li>';
	}


	public function _post_image_tab_extra(){
		global $_G;

		$fid = isset($_GET['fid']) ? intval($_GET['fid']) : 0;

		require_once libfile('function/upload');
		$swfconfig = getuploadconfig($_G['uid'], $fid);

		ob_start();
		include template('takashiro_upload:uploader');
		$template = ob_get_contents();
		ob_clean();
		return $template;
	}
}

class plugin_takashiro_upload_forum extends plugin_takashiro_upload{

	public function post_image_btn_extra(){
		return parent::_post_image_btn_extra();
	}

	public function post_image_tab_extra(){
		return parent::_post_image_tab_extra();
	}

}

?>
