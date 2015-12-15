(function($){
	var root_url = 'plugin.php?id=takashiro_lovewins:main';

	function fill_user_dammaku(){
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

			fill_user_dammaku();
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

		item.data('uid1', user1.uid1);
		item.data('uid2', user2.uid2);

		var avatars = item.find('.avatar');
		var avatar1_link = avatars.eq(0).children();
		avatar1_link.html(user1.avatar);
		avatar1_link.attr('href', 'home.php?mod=space&uid=' + user1.uid);
		var avatar2_link = avatars.eq(1).children();
		avatar2_link.attr('href', 'home.php?mod=space&uid=' + user2.uid);
		avatar2_link.html(user2.avatar);

		var branch = item.find('.issbranch');
		branch.eq(0).html(user1.issbranch);
		branch.eq(1).html(user2.issbranch);

		var realname = item.find('.realname');
		realname.eq(0).html(user1.realname);
		realname.eq(1).html(user2.realname);

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
					alert('您已经支持过他们啦！');
				}
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

	fill_user_dammaku();

	$('.userlist').on('keypress', 'input.danmaku', function(e){
		if(e.keyCode != 13)
			return;

		var input = $(e.target);
		var col = input.parent();
		var uid = col.data('uid');

		$.post(root_url + '&action=danmaku&type=1&targetid=' + uid, {'content' : input.val()}, function(){
			var area = col.find('.avatar');
			area.danmaku('add', input.val());
			input.val('');
		});
	});

	$('.couplelist .couple').each(function(){
		$(this).danmaku('config', {
			'texts' : ['haha', 'test', 'lulala']
		});
	});

	$('.couplelist').on('keypress', 'input.danmaku', function(e){
		if(e.keyCode != 13)
			return;

		var input = $(e.target);
		var col = input.parent().parent();

		var content = input.val();
		input.val('');
		var uid1 = col.data('uid1');
		var uid2 = col.data('uid2');

		$.post(root_url + '&action=danmaku&targetid=0', {'uid1': uid1, 'uid2': uid2, 'content': content}, function(){
			//add into the window
		});
	});

})(jQuery);
