(function($){
	var root_url = 'plugin.php?id=takashiro_lovewins:main';

	function fill_user_danmaku(){
		$('.userlist .avatar').each(function(){
			var avatar_area = $(this);
			var col = avatar_area.parent().parent();
			var uid = col.data('uid');
			$.post(root_url + '&action=danmaku&type=1&targetid=' + uid, {}, function(danmaku){
				var contents = [];
				for(var i = 0; i < danmaku.length; i++){
					contents.push(danmaku[i].content);
				}

				avatar_area.danmaku('config', {
					'texts' : contents
				});
			}, 'json');
		});
	}

	function fill_couple_danmaku(){
		$('.couplelist .couple').each(function(){
			var area = $(this);
			var col = area.parent();
			var uid1 = col.data('uid1');
			var uid2 = col.data('uid2');

			$.post(root_url + '&action=danmaku&type=2&uid1=' + uid1 + '&uid2=' + uid2, {}, function(danmaku){
				var contents = [];
				for(var i = 0; i < danmaku.length; i++){
					contents.push(danmaku[i].content);
				}

				area.danmaku('config', {
					'texts' : contents
				});
			}, 'json');
		});
	}

	$('.userlist').on('click', '.col > ul.operation > li > .love_button', function(e){
		var button = $(this);
		var li = button.parent();
		var operation_ul = li.parent();
		var col = operation_ul.parent();
		var uid = col.data('uid');

		if(uid == discuz_uid){
			alert('身为一只单身汪，这么自恋下去真的没问题吗？……');
			return;
		}

		if(!confirm('真的要表白吗？看准了哦，到时候赖账说什么“不小心点错了”我可不负责！'))
			return;

		$.post(root_url + '&action=love', {'toid' : uid}, function(response){
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

	function fill_userlist(users){
		var listbox = $('#userlist');

		if(users.length > 0){
			listbox.html('');

			for(var i = 0; i < users.length; i++){
				var user = users[i];

				var item = user_item.clone();
				var avatar = item.find('.user .avatar');
				avatar.html(user.avatar);

				var realname = item.find('.realname');
				realname.html(user.realname);
				realname.attr('href', 'home.php?mod=space&do=profile&uid=' + user.uid);

				item.find('.issbranch').html(user.issbranch);
				item.find('.briefintro').html(user.age + '岁 ' + user.constellation);
				item.find('a.sendpm_button').attr('href', 'home.php?mod=spacecp&ac=pm&op=showmsg&handlekey=showmsg_' + user.uid + '&touid=' + user.uid + '&pmid=0&daterange=2');
				item.data('uid', user.uid);

				listbox.append(item);
			}

			fill_user_danmaku();
		}else{
			alert('您要找的人不属于我们伟大的单身汪星球。');
		}
	}

	var user_item = $('#userlist').children().eq(0).clone();
	$('#search_user_form').on('submit', function(e){
		e.preventDefault();

		var input = $(this).find('input[name="search_user_keyword"]');
		var keyword = input.val();
		if(keyword == '')
			return;

		input.val('');

		$.post(root_url + '&action=search', {'keyword' : keyword}, fill_userlist, 'json');
	});

	$('#refresh_userlist_button').on('click', function(){
		$.post(root_url + '&action=search', {'randnum' : 12}, fill_userlist, 'json');
	});

	var couple_item = $('#couple_candidate_box').children().eq(0).clone();

	function add_couple(user1, user2){
		var item = couple_item.clone();

		item.data('uid1', user1.uid);
		item.data('uid2', user2.uid);

		var avatars = item.find('.avatar');
		avatars.eq(0).html(user1.avatar);
		avatars.eq(1).html(user2.avatar);

		var branch = item.find('.issbranch');
		branch.eq(0).html(user1.issbranch);
		branch.eq(1).html(user2.issbranch);

		var realname = item.find('.realname');
		realname.eq(0).html(user1.realname);
		realname.eq(0).attr('href', 'home.php?mod=space&do=profile&uid=' + user1.uid);
		realname.eq(1).html(user2.realname);
		realname.eq(1).attr('href', 'home.php?mod=space&do=profile&uid=' + user2.uid);

		var coinnum = item.find('.coinnum .value');
		coinnum.html('');
		$.post(root_url + '&action=votecouple&queryonly=1', {'uid1' : user1.uid, 'uid2' : user2.uid}, function(couple){
			coinnum.html(couple.coinnum);
		}, 'json');

		$('#couple_candidate_box').append(item);
	}

	function generate_couple(users){
		if(users.length < 2)
			return;

		if(users[0].length == 1 && users[1].length == 1){
			$.post(root_url + '&action=votecouple', {'uid1' : users[0][0].uid, 'uid2' : users[1][0].uid}, function(response){
				var response = parseInt(response, 10);
				if(response == 1){
					alert('成功支持这对CP！');
				}else{
					alert('您今天已经支持过他们啦！每天只能顶1次~');
				}
				fill_couple_danmaku();
			}, 'text');
		}

		$('#couple_candidate_box').html('');
		for(var i = 0; i < users[0].length; i++){
			for(var j = 0; j < users[1].length; j++){
				add_couple(users[0][i], users[1][j]);
			}
		}
	}

	$('#search_couple_form').on('submit', function(e){
		e.preventDefault();

		var users = [];
		var inputs = [$('#couple1'), $('#couple2')];

		if(inputs[0].val() == inputs[1].val()){
			if(!confirm('你确定要' + inputs[0].val() + '自攻自受吗？'))
				return;
		}

		for(var i = 0; i < 2; i++){
			var keyword = inputs[i].val();
			if(keyword == '')
				return;
			inputs[i].val('');

			var index = i;
			$.post(root_url + '&action=search', {'keyword' : keyword}, function(data){
				if(data.length > 0){
					users.push(data);
					generate_couple(users);
				}else{
					alert('单身汪星球查无此人，真的有这个单身汪嘛？');
				}
			}, 'json');
		}
	});

	$('#refresh_couplelist_button').on('click', function(){
		var users = [];
		var randnum = 6;

		function generate_couple(){
			if(users.length < 2)
				return;

			$('#couple_candidate_box').html('');
			for(var i = 0; i < randnum; i++){
				add_couple(users[0][i], users[1][i]);
			}

			fill_couple_danmaku();
		}

		for(var i = 1; i <= 2; i++){
			$.post(root_url + '&action=search', {'randnum' : randnum, 'gender' : i}, function(data){
				if(data.length > 0){
					users.push(data);
					generate_couple();
				}else{
					alert('单身汪星球查无此人，真的有这个单身汪嘛？');
				}
			}, 'json');
		}
	});

	fill_user_danmaku();

	$('.userlist').on('keypress', 'input.danmaku', function(e){
		if(e.keyCode != 13)
			return;

		var input = $(e.target);
		var col = input.parent();
		var uid = col.data('uid');

		var content = input.val();
		input.val('');
		$.post(root_url + '&action=danmaku&type=1&targetid=' + uid, {'content' : content}, function(){
			var area = col.find('.avatar');
			area.danmaku('add', content);
		});
	});

	fill_couple_danmaku();

	$('.couplelist').on('keypress', 'input.danmaku', function(e){
		if(e.keyCode != 13)
			return;

		var input = $(e.target);
		var couple_area = input.parent();
		var col = couple_area.parent();

		var content = input.val();
		input.val('');
		var uid1 = col.data('uid1');
		var uid2 = col.data('uid2');

		$.post(root_url + '&action=danmaku&targetid=0', {'uid1': uid1, 'uid2': uid2, 'content': content}, function(){
			couple_area.danmaku('add', content);
		});
	});

	$('.couplelist').on('click', '.coinnum .value, .namecard .glyphicon-heart', function(){
		var col = $(this).parent().parent().parent();
		var coinnum = col.find('.coinnum .value');
		var uid1 = col.data('uid1');
		var uid2 = col.data('uid2');
		$.post(root_url + '&action=votecouple', {'uid1' : uid1, 'uid2' : uid2}, function(result){
			var result = parseInt(result, 10);
			if(result == 1){
				coinnum.html(parseInt(coinnum.html(), 10) + 1);
				alert('成功祝福这对CP！');
			}else{
				alert('今天他们已经收到您的祝福啦！不如明天也来？');
			}
		}, 'text');
	});

})(jQuery);
