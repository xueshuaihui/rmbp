$(function(){

    setBannerSlideBotom();
    setIndexBannerImg();
    setReasonContentStyle();

    $(window).resize(function(){
        setIndexBannerImg();
    });

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

        if($('.content-bottom video')[0]){
            var video = $('.content-bottom video');
            var videoTop = $('.content-bottom').offset().top;
            if( !video.attr('src') && videoTop < (scrollTop+windowHeight) ){
                video.attr('src',video.data('original'));
            }
        }
    });
});

function setBannerSlideBotom(){
    $('.J-banner-bottom-btn').click(function(){
        var bannerHeight = $('.bx-wrapper').height()+22;
        $("body").animate({scrollTop: bannerHeight}, 500);
        $("html").animate({scrollTop: bannerHeight}, 500);
    })
}

function setReasonContentStyle(){
    $('.reasons .reason').hover(function(){
        $(this).find('.desc').animate({bottom:"0"},'normal');
    },function(){
        $(this).find('.desc').animate({bottom:"-140px"},'normal');
    })
}

function setIndexBannerImg(){
    var banner_imgs = $('.J-index-bxslider .banner-img');
    var windowH = window.innerHeight;
    windowH = windowH>500 ? windowH : 500;
    windowH -= 22;

    $('.banner-box').css('height',windowH);
    $('.home-content-1').css('margin-top',windowH);
    $('.bx-viewport').css('height' , windowH);
    banner_imgs.css('height' , windowH);

    for(var i=0; i<banner_imgs.length; i++){
        var src = banner_imgs.eq(i).data('src');
        banner_imgs.eq(i).css({
            'background' : 'url('+src+') no-repeat center center',
            'background-size' : 'cover'});
    }
}