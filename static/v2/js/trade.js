$(function(){
    $('.status-flag').click(function(){
        var isSelect = $(this).hasClass('select');
        var tradeBtnObj = $('.J-trade-btn');
        if(isSelect){
            $(this).removeClass('select');
            tradeBtnObj.attr('disabled',true)
                .unbind('click');
        }
        if(!isSelect){
            $(this).addClass('select');
            tradeBtnObj.attr('disabled',false)
                .bind('click',function(){
                    $('#trade-form').submit();
                })
        }
    });

    var times = $('.J-time');
    var timeInterval = setInterval(function(){
        for(var i=0 ; i<times.length; i++){
            var dateline = parseInt(times.eq(i).data('dateline'));
            var end_time = new Date(dateline*1000) ;
            var now =  new Date();
            if(end_time > now){
                var relax_time = getRelaxTime(end_time, now);
                times.eq(i).html(relax_time.day + '天' + relax_time.hour +'时' + relax_time.min + '分' + relax_time.second + '秒');
            }
        }
    },900);

    setTotal();

    $('.J-process-link').click(function(){
        var processImgObj = $('.J-process-img');
        var is_show = processImgObj.css('display') == 'block';
        if(is_show){
            processImgObj.slideUp('normal');
            $(this)
                .html('阅读股权转让流程图')
                .removeClass('up');
        }
        if(!is_show){
            processImgObj.slideDown('normal');
            $(this)
                .html('收起股权转让流程图')
                .addClass('up');
        }
    })
});


function getRelaxTime(end, start){
    var totalSecond = (end - start) / 1000;
    var day = parseInt(totalSecond / ( 24 * 3600 ));
    totalSecond -= day * 24 *3600;

    var hour = parseInt(totalSecond / 3600);
    totalSecond -= hour *3600;

    var min = parseInt(totalSecond / 60);
    totalSecond -= min *60;

    var second = parseInt(totalSecond);

    return {day : day, hour : hour, min : min, second : second};
}

function setTotal(){
    var num = parseFloat($('.J-sell-per').data('num')),
        per = parseFloat($('.J-sell-per').val()),
        total = per * num,
        total_capital = digitUppercase(total);
    $('.J-total').val(total);
    $('.J-total-capital').val(total_capital);
}