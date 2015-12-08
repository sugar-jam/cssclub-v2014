(function($){
	var root_url = 'plugin.php?id=takashiro_lovewins:main';

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

		$.post(root_url + '&action=love', {toid : uid}, function(response){
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


	var user_item = $('#userlist').children().eq(0).clone();
	$('#search_user_form').on('submit', function(e){
		e.preventDefault();

		var input = $(this).find('input[name="search_user_keyword"]');
		var keyword = input.val();
		input.val('');

		$.post(root_url + '&action=search', {keyword : keyword}, function(users){
			var listbox = $('#userlist');

			if(users.length > 0){
				listbox.html('');

				for(var i = 0; i < users.length; i++){
					var user = users[i];

					var item = user_item.clone();
					var avatar_link = item.find('.user .avatar a');
					avatar_link.html(user.avatar);
					avatar_link.attr('href', 'home.php?mod=space&uid=' + user.uid);

					item.find('.realname').html(user.realname);
					item.find('.issbranch').html(user.issbranch);
					item.find('.briefintro').html(user.age + '岁 ' + user.constellation);
					item.find('a.sendpm_button').attr('href', 'home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_' + user.uid + '&touid=' + user.uid + '&pmid=0&daterange=2');
					item.data('uid', user.uid);

					listbox.append(item);
				}
			}else{
				alert('您要找的人不属于我们伟大的单身汪星球。');
			}
		}, 'json');
	});
})(jQuery);
