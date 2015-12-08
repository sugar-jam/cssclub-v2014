(function($){
	$('.userlist').on('click', '.col > ul.operation > li > .love_button', function(e){
		var button = $(e.target);
		var li = button.parent();
		var operation_ul = li.parent();
		var col = operation_ul.parent();
		var uid = col.data('uid');

		if(uid == discuz_uid){
			alert('身为一只单身汪，这么自恋下去真的没问题吗？……');
			return;
		}

		$.post('plugin.php?id=takashiro_lovewins:main&action=love', {toid : uid}, function(response){
			var response = parseInt(response, 10);
			if(response === 2){
				alert('TA也喜欢你！快勾搭一下吧！');
			}else if(response === 1){
				alert('成功向对方表白，等待TA的回应是不是也很令人激动呢！~');
			}else if(response === 0){
				alert('好了好了我知道了，你表白过了的。年轻人要有耐心~');
			}else{
				alert('网络君觉得身体有点不太好……让他瘫痪一会儿……');
			}
		}, 'text');
	});
})(jQuery);
