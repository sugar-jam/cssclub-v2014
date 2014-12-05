/* 轮播图 */
var slide = function(){
    var slideWrap = $(".slide"),
        listButtonBox = $('.buttonlist'),
        roll = slideWrap.children(),
        list = roll.children(),
        len = list.length,
        move = list.height(),
        fx = 'easeInSine',
        curIndex = 0,
        timer = null,
        speed = 500,
        auto = 5000;

    var init = function(){
        list.eq(0).css('zIndex' , 2).siblings().hide();
        var s = "";
        for(var i = 0; i < len; i++){
            if(i === curIndex){
                s += '<a  class="active" href="javascript:;"></a>';
            }else{
                s += '<a href="javascript:;"></a>';
            }
        }
        listButtonBox.append($(s));
        listButtonBox.on('mouseenter','a',function(e){
            var index = $(this).index();
            skip(curIndex,index);
        });
        start();

        $('.slide').add(listButtonBox).hover(function(){
            clearInterval(timer);
        },function(){
            start();
        });
       
    }

    var toNext = function(){
      var n = curIndex + 1 == len ? 0 : curIndex + 1;
       skip(curIndex,n);
    }

    var skip = function(pr,ne){
        if(pr == ne) return;
       list.stop(true,true);
       list.eq(pr).css('zIndex',2).stop(true,true).animate({
            opacity : 0
       },speed,fx,function(){
            $(this).css({zIndex : 0 , display : 'none' , opacity : 1});
             list.eq(ne).css('zIndex',2);
       });
       list.eq(ne).css({display : 'block' , zIndex : 1});
       // list.eq(ne).css({display : 'block' , zIndex : 1 , opacity : 0}).stop(true).animate({
       //      opacity : 1
       // },speed,fx,function(){
       //      $(this).css({zIndex : 2});
       // });
       curIndex = ne;
       listButtonBox.children(true).eq(ne).addClass('active').siblings().removeClass('active');
    }

    var start = function(){
        timer = setInterval(function(){
            toNext();
        },auto);
    }

    init();

}


/* 20140723 */

// 遮罩层

var overlay = {

    dom :null,

    show : function(){
        if(!this.dom){
            this.dom = $("#lp-overlay");
        }
        this.dom.css({height : $(document).height(),width:'100%'}).css({display:'block', opacity: 0}).animate({opacity : 0.7},200);
    },
    hide : function(){
        this.dom.animate({
            opacity : 0
        },200,function(){
            $(this).css('display','none');
        });
    }
}

// 视频弹出层

var ag7_showVideo = function(url,width,height){
    var width = width || 860;
    var height = height || 520;
    var videoBox = $('#video-box');
    videoBox.show();
    var l = ($(window).width() - width) / 2;
    var t = ($(window).height() - height) / 2;
    videoBox.css({
        left : l < 0 ? 0 : l,
        top : t < 0 ? 0 : t
    });
    var s = '<embed id="videoPlayer" src="'+url+'" allowFullScreen="true" quality="high" width="'+width+'" height="'+height+'" align="middle" allowScriptAccess="always" type="application/x-shockwave-flash"></embed>';
    overlay.show();
    videoBox.append($(s));
}

var closeVideo = function(){
    var videoBox = $('#video-box');
    videoBox.empty().hide();
    overlay.hide();

}


// 搞机快讯
var refresh = function(){
    var button = $('.topshow .refresh'),
        uls = $('.topshow .right ul'),
        len = uls.length,
        speed = 300,
        fx = "swing",
        lock = 2,
        curIndex = 0;

    uls.filter(":gt(0)").hide();
    var nextIndex = function(){
        if(curIndex + 1 == len){
            return 0;
        }else{
            return curIndex+1;
        }
    }
    var toNext = function(){
        if(lock != 2) return;
        lock = 0;
       uls.eq(nextIndex()).delay(100).fadeIn(600,function(){
            lock++; 
        });
        uls.eq(curIndex).children().each(function(i){

            $(this).delay(i * 50).animate({
                left : - 260,
                opacity : 0
            },speed,fx,function(){
                if(i == 4){
                  $(this).parent().css('display','none').children('li').css({left : 0 , opacity : 1});
                  curIndex = nextIndex()
                  lock++;
                }
            });
        });
    }

    button.click(toNext);
}
$(function(){
    slide();
    refresh();
    $('#lp-overlay').click(function(){
        closeVideo();
    });
});


 
// add by wuyue 20140730 轮播图视频图交互

$(".topshow .slide ul li.video").hover(function(){
    $(this).children(".video-over").css({display:'block',opacity : 0}).animate({
        opacity : 0.7
    },10);
    $(this).children(".video-over").addClass("hov");
    $(this).children(".video-coin").css("display","block").children("i").addClass("ro");
},function(){
    $(this).children(".video-over").animate({opacity : 0},100,function(){
        $(this).css({'display' : 'none'});
    });
    $(this).children(".video-over").removeClass("hov");
    $(this).children(".video-coin").css("display","none").children("i").removeClass("ro");
});

