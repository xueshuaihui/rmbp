$(function () {
    initProject();

});

function initProject(){
    $('.J-weixin').click(function(){
        var imgObj = $(this).find('img');
        var isShow = imgObj.css('display') != 'none';
        if(isShow){
            imgObj.css('display','none');
        }
        if(!isShow){
            imgObj.css('display','inline');
        }
    });

    $('.J-invest-btn').click(function(){
        var post = {
            project_id : $('.J-project').data('target')
        };
        API.post('judge_meeted', post, function (result) {
            if (result.error == 0) {
                $('.J-project').hide();
                $('.J-send-modal').show();
            } else {
                alertInfo(result.message);
            }
        });
    });

    $('.J-cancel-btn').click(function(){
        $('.J-send-modal').hide();
        $('.J-project').show();
    });

    $('.J-confirm-btn').click(function(){
        var val = $('.J-send-modal textarea').val();
        var is_more = $('.J-send-modal').find('.warning').length;
        var post = {
            project_id : $('.J-project').data('target'),
            message : val
        };
        if(val.length){
            if(is_more){
                alertInfo('内容不能超过' + $('.J-max-input').text() + '字符');
                return;
            }
            API.post('project_meet', post, function (result) {
                if (result.error == 0) {
                    alertInfo('意向申请提交成功！');
                    setTimeout(function(){
                        $('.J-send-modal').hide();
                        $('.J-project').show();
                        $('.J-send-modal textarea').val('');
                    },2500)
                } else {
                    alertInfo(result.message);
                }
            });
        }

        if(!val.length){
            alertInfo('投资意向不能为空');
        }
    });


}

function checkWordNum(obj) {
    var maxLength = $(obj).data("max");
    var input = $(obj).val();
    var bytesCount = input.toString().length;

    //var bytesCount = 0;
    //for (var i = 0; i < input.length; i++) {
    //    var c = input.charAt(i);
    //    if (/^[\u0000-\u00ff]$/.test(c)) { //匹配双字节
    //        bytesCount += 1;
    //    } else {
    //        bytesCount += 2;
    //    }
    //}

    $(obj).parent().find('.J-had-input').html(bytesCount);
    $(obj).parent().find('.J-max-input').html(maxLength);

    if (bytesCount > maxLength) {
        $(obj).parent().find('.J-had-input').addClass('warning');
    } else {
        $(obj).parent().find('.J-had-input').removeClass('warning')
    }
}