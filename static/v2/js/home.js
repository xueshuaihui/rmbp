$(function(){
    $(window).scroll(function(){
        var lists = $('.J-project-lists li');
        var canvas = $('.J-project-lists canvas');
        var scrollTop = $(document).scrollTop();
        var windowHeight = $(window).height();

        if(lists.length > canvas.length){
            for(var i = 0; i < lists.length; i++){
                var list = lists.eq(i);
                var listTop = list.find('.J-status-line').offset().top;
                var listWindowTop = listTop - scrollTop + 178;

                if(!list.find('.J-status-line canvas').length && listWindowTop < windowHeight && listWindowTop > 0){
                    var lineNum = parseInt(list.find('.J-project-list').data('status-line'))/100 ;
                    var flag = list.find('.J-project-list').data('status-flag');
                    setProjectLineCircle(list.find('.J-status-line'),lineNum,flag,'#e1e1e1');
                }
            }
        }
    });

    feed.init();

    var href = location.href;
    if(href.match('index')){
        $('.J-tab-box li:first-child a').click();
    }

});

var feed = {
    init : function(){
        var self = this;
        setFeedStatus();
        $('.J-feed-btn')
            .hover(function(){
                $(this).addClass('pulse');
            }, function(){
                $(this).removeClass('pulse');
            })
            .click(function(){
                var fansNumObj = $('.J-fans-num');
                var isFeed = $(this).hasClass('feeded');
                var pre_fans_num = parseInt(fansNumObj.html());
                var unfeed = isFeed ? 1 : '';
                var post = {
                    id : $('.J-user').data('target'),
                    type : 'user',
                    unfeed : unfeed
                };
                var that = this;
                $(that).removeClass('pulse');
                API.post('user_feed2', post, function (result) {
                    if (result.error == 0) {
                        if(isFeed){
                            $(that).removeClass('feeded')
                                .addClass('pulse');
                            $(that).find('span').html('关注');
                            fansNumObj.html(pre_fans_num - 1);

                            if(pre_fans_num == 1){
                                $('.J-focus-num-btn').attr('data-ajax-pop','1');
                                self.initModalData();
                            }
                        }else{
                            $(that).addClass('feeded pulse');
                            $(that).find('span').html('已关注');
                            fansNumObj.html(pre_fans_num + 1);
                        }
                    } else {
                        alertImg(result.message);
                    }
                });
            });

        $('.J-focus-num-btn').click(function(e){
            var feednum = parseInt($(this).find('.num').html());
            if(feednum){
                var url = $(this).data('href');
                url += url.search(/\?/) > -1 ? '&ajax=1' : '?ajax=1';
                $('.J-personal-modal .modal-dialog').load(url,function(){
                    self.initCancelFeedBtn();
                    self.initModalPage();
                    $('.J-personal-modal').modal('show');
                });
                e.stopPropagation();
                return false;

            }
        });
    },

    initModalPage: function(){
        var self = this;
        $('.J-personal-modal .pages-box a').on('click', function (e) {
            var url = $(this).attr('href');
            if(url != 'javascript:void(0);'){
                url += url.search(/\?/) > -1 ? '&ajax=1' : '?ajax=1';
                $('.J-personal-modal .modal-dialog').load(url, function(){
                    self.initCancelFeedBtn();
                    self.initModalPage();
                    $('.J-personal-modal').modal('show');
                });
                e.stopPropagation();
                return false;
            }
        });
    },

    initCancelFeedBtn: function(){
        var self = this;
        $('.J-personal-modal-focus-btn').unbind('click').click(function(){
            var feedNumObj = $('.J-feed-num');
            var unfeed = parseInt( $(this).data('unfeed') );
            var target = $(this).data('target');
            var post = {
                id : target,
                type : 'user',
                unfeed : unfeed
            };
            var feedNum = parseInt(feedNumObj.html());
            var that = this;
            API.post('user_feed2', post, function (result) {
                if (result.error == 0) {
                    if(unfeed){
                        feedNumObj.html(feedNum - 1);
                        showAlertInfo('成功取消关注');
                        $(that).replaceWith('<a href="javascript:void(0);" class="btn btn1 J-personal-modal-focus-btn" data-unfeed="'+0+'" data-target="'+target+'">关注</a>')
                    }
                    if(!unfeed){
                        feedNumObj.html(feedNum + 1);
                        showAlertInfo('成功关注');
                        $(that).replaceWith('<a href="javascript:void(0);" class="btn btn1 J-personal-modal-focus-btn" data-unfeed="'+1+'" data-target="'+target+'">取消关注</a>')
                    }
                    self.initCancelFeedBtn();
                } else {
                    showAlertInfo(result.message);
                }
            });
        })
    }
};

function setFeedStatus(){
    var user_id = $('.J-user').data('target');
    var feedBtnObj = $('.J-feed-btn');
    API.post('feed_user', {id: user_id}, function (result) {
        if (result.error == 0) {
            feedBtnObj.addClass('feeded pulse');
            feedBtnObj.find('span').html('已关注');
        } else {
            feedBtnObj.removeClass('feeded pulse');
            feedBtnObj.find('span').html('关注');
        }
    });
}