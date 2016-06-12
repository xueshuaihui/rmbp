$(function () {
    setNewsStatus();

    if($('#accountBasicInfo')[0]){
        initBasicInfo();
        initCropImage();
        initCityInput();
        initHotCity();
        initFieldSelect();
    }

    if($('#accountNews')[0]){
        initNewsPage();
    }

    if($('#accountInvestment')[0]){
        initInvested();
    }
});

function initInvested(){
    cancelOrder();
    refundOrder();
}

function cancelOrder(){
    $('.J-cancel-order').click(function(){
        var id = $(this).data('id');
        $('.J-cancelPay-confirm-btn').data('id',id);
        $('#cancelPayDialog').modal('show');
    });

    $('.J-cancelPay-confirm-btn').click(function(){
        var id = $(this).data('id');
        API.post('order_cancel',{id:id},function(result){
            $('#cancelPayDialog').modal('hide');
            setTimeout(function(){
                $('.J-continue-'+id).css('display','none');
                $('.J-cancel-'+id).replaceWith('<a href="javascript:void(0);" class="btn C-pray-btn C-J-static-waiting-btn">已取消支付</a>');
                alertImg(result.message);
            },500);
        })
    });

    $('.J-cancelPay-cancel-btn').click(function(){
        $('#cancelPayDialog').modal('hide');
    })
}

function refundOrder(){
    $('.J-refund-order').click(function(){
        var id = $(this).data('id');
        $('#myReasonModel').modal('show').find('.C-J-confirm').data('id', id);
    });

    $('.J-refund-reason  .status-flag').click(function(){
        $('.J-refund-reason .status-flag').removeClass('select');
        var is_other = parseInt($(this).data('value')) == 3;
        if(is_other){
            $('.J-other-reason-input').attr('disabled',false).focus();
        }else{
            $('.J-other-reason-input').attr('disabled',true);
        }
        $(this).addClass('select');
    });

    $('#myReasonModel .C-J-confirm').click(function(){
        var refundReason = {
            '0' : '项目风险高，回报低',
            '1' : '投资期限太长',
            '2' : '短期急需资金周转',
            '3' : $('.J-other-reason-input').val()
        };

        var myReasonObj = $('#myReasonModel');
        var id = $(this).data('id');
        var reason = refundReason[myReasonObj.find('.status-flag.select').data('value')];

        if(!reason.length){
            myReasonObj.find('.error-box').html('*请填写您要撤资的原因').css('display','block');
            setTimeout(function(){
                myReasonObj.find('.error-box').css('display','none');
            },1500);
            return;
        }
        myReasonObj.find('.error-box').css('display','none');
        var post = {
            id: id,
            reason: reason
        };

        API.post('order_refund',post, function(result){
            myReasonObj.modal('hide');
            setTimeout(function(){
                alertImg(result.message);
                $('.J-refund-order').replaceWith('<a href="javascript:void(0);" class="btn C-J-static-waiting-btn">已撤资，待审核</a>');
            },500)
        })
    })
}

function initNewsPage(){
    $('.J-unread').click(function(){
        var that = this;
        API.post('message_read', {id: $(that).data('id')}, function (result) {
            if (result.error == 0) {
                $(that).find('.unread').remove();
                $(that).removeClass('unread-li').removeClass('J-unread');
                var preNum = parseInt($('.J-news-num').html());
                var num = preNum - 1;
                if(num){
                    $('.J-news-num').html(num);
                }
                if(!num){
                    $('.J-news-num').css('display','none');
                }

            } else {
                alertImg(result.message);
            }
        });
    });

    $('.J-pop-page').click(function(){
        var text = $(this).text();
        if(text == '跳转'){
            $('.J-pop-to-box').slideDown('fast');
            $('.J-pop-to-box input').focus();
            $(this).text('取消');
        }
       if(text == '取消'){
            $('.J-pop-to-box').slideUp('fast');
            $(this).text('跳转');
           $('.J-pop-to-box input').val('');
        }
    });

    $('.J-page-input-btn').click(function(){
        var page_input = $('.J-page-input').val();
        if(!checkPositiveInt(page_input)){
            alertImg('请输入大于0的页码');
        }
        location.href = '/user/message/page/'+page_input;
    })
}

function initHotCity() {
    var selectIds = $('#city-input').data('ids');
    if(selectIds){
        selectIds = selectIds.toString().split(',');
    }

    API.post('region', {pid: 'hot'}, function (result) {
        if (result.error == 0) {
            var cities = JSON.parse(result.message);
            var tpl_1 = tpl('hotCityModal');
            var tpl_2 = tpl('cityShowModal');

            for (var i = 0; i < cities.length; i++) {
                var tplHtml_1 = tpl_1({name: cities[i].name, code: cities[i].code, id: cities[i].id});
                $('.J-select-city-box .J-city').append(tplHtml_1);

                if(selectIds){
                    for(var j = 0;j<selectIds.length;j++){
                        if(cities[i].id == selectIds[j]){
                            $('.J-select-city-box .J-city input').eq(i).addClass('select');
                            var tplHtml_2 = tpl_2({val: cities[i].name, id: selectIds[j]});
                            $('#city-input').before(tplHtml_2);
                        }
                    }
                    initCityDelete();
                }
            }

            initCitySelect();
        } else {
            alertImg(result.message);
        }
    })
}

function initCityInput() {
    $('#city-input').click(function () {
        $('#citySelectModal').modal('show');
        removeError($(this));
    });

    initCitySelect();

    $('.J-select-city-box .J-confirm').click(function () {
        var has_select_inputs = $('.J-select-city-box .J-city').find('.select');
        $('.J-focus-city').find('.J-city').remove();
        var ids = [];
        for (var i = 0; i < has_select_inputs.length; i++) {
            var val = has_select_inputs.eq(i).val();
            var tplHtml = tpl('cityShowModal', {val: val, id: has_select_inputs.eq(i).data('id')});
            ids.push(has_select_inputs.eq(i).data('id'));
            $('#city-input').before(tplHtml);
        }

        showLoading();
        API.post('user_feed_region', {id: ids.join(',')}, function (result) {
            if (result.error == 0) {
                initCityDelete();
                $('#citySelectModal').modal('hide');
                hideLoading();
            } else {
                hideLoading();
                alertImg(result.message);
            }
        })
    });
}

function initCitySelect() {
    $('.J-select-city-box .J-city input').unbind('click').click(function () {
        var is_selected = $(this).hasClass('select');

        if (is_selected) {
            $(this).removeClass('select');
        }
        if (!is_selected) {
            $(this).addClass('select');
        }
    });
}

function initCityDelete() {
    $('.J-focus-city .close').unbind('click').click(function () {
        var id = $(this).data('id');
        var that = this;
        var post = {
            id: id,
            unfeed: 1,
            type: 'region'
        };
        API.post('user_feed', post, function (result) {
            if (result.error == 0) {
                $(that).parent().remove();
                var city_inputs = $('.J-select-city-box .J-city').find('input');
                for (var i = 0; i < city_inputs.length; i++) {
                    if (city_inputs.eq(i).data('id') == id) {
                        city_inputs.eq(i).removeClass('select');
                    }
                }
            } else {
                alertImg(result.message);
            }
        });

    })
}

function initFieldSelect(){
    $('.J-focus-fields .status-flag').click(function () {
        var is_select = $(this).hasClass('select');
        var unfeed = is_select ? 1 : '';
        var post = {
            id: $(this).data('id'),
            type: 'area',
            unfeed: unfeed
        };
        var that = this;
        removeError($('.J-focus-fields'));
        API.post('user_feed', post, function (result) {
            if (result.error == 0) {
                if (is_select) {
                    $(that).removeClass('select')
                } else {
                    $(that).addClass('select');
                }
            } else {
                alertImg(result.message);
            }
        });
    });
}

function setNewsStatus() {
    $('#accountNews .J-mark-all-news').click(function () {
        var newsUnread = $('.J-unread');
        var ids = [];

        if(!newsUnread[0]){
            alertImg('已全部标记为已读');
            return;
        }

        for (var i = 0; i < newsUnread.length; i++) {
            ids.push(newsUnread.eq(i).data('id'));
        }
        var post = {id: ids.join(',')};

        showLoading();
        API.post('message_read', post, function (result) {
            hideLoading();
            if (result.error == 0) {
                $('#accountNews .unread').remove();
                $('.J-unread').removeClass('unread-li').removeClass('J-unread');
                alertImg('全部标记成功');
            } else {
                alertImg(result.message);
            }
        });

    })
}

function initBasicInfo() {
    initEditBtn();
    initEditCancelBtn();
    initEditSaveBtn();
}

function initCropImage() {
    $('.C-J-upload-logo').unbind('click').click(function () {
        var target = $(this).data('target');
        $('#'+target).click();
    });
}

function initEditBtn() {
    $('.J-is-invested .J-edit-btn').click(function () {
        $('.J-is-invested .show-box').css('display', 'none');
        $('.J-is-invested .edit-box').css('display', 'block');
    })
}

function initEditCancelBtn() {
    $('.J-is-invested .C-J-cancel-btn').click(function () {
        $('.J-is-invested .edit-box').css('display', 'none');
        $('.J-is-invested .show-box').css('display', 'block');
        $("body").animate({scrollTop: 0}, 100);
    })
}

function initEditSaveBtn() {
    $('.J-is-invested .C-J-save-btn').click(function () {
        var investorData = judgeUserEditData();
        if (investorData.status) {
            showLoading();

            API.post('certificate', investorData.data, function (result) {
                if (result.error == 0) {
                    location.reload(true);
                } else {
                    hideLoading();
                    alertImg(result.message);
                }
            })
        }
    })
}

function judgeUserEditData(){
    var realNameObj = $('.J-is-invested .edit-box .J-real-name'),
        cityObj = $('.J-is-invested .edit-box .J-city'),
        companyObj = $('.J-is-invested .edit-box .J-company'),
        positionObj = $('.J-is-invested .edit-box .J-position'),
        focusCityObj = $('.J-is-invested .edit-box .J-focus-city'),
        focusFieldsObj = $('.J-is-invested .edit-box .J-focus-fields');
    var name = realNameObj.val(),
        city = cityObj.val(),
        company = companyObj.val(),
        position = positionObj.val(),
        img_src = $('#userImgPrev img').attr('src'),
        focus_cities = focusCityObj.find('.J-city'),
        focus_fields = focusFieldsObj.find('.select');

    var investorData = {
        'status': false,
        'data': []
    };
    if (!name || !judgeOnlyChineseOrEnlish(realNameObj[0])) {
        setError(realNameObj, '*请填写姓名');
        realNameObj.focus();
        return investorData;
    } else {
        removeError(realNameObj);
    }

    if (!city) {
        setError(cityObj, '*请填写您的所在城市');
        cityObj.focus();
        return investorData;
    } else {
        removeError(cityObj);
    }

    if (!company) {
        setError(companyObj, '*请填写您的公司');
        companyObj.focus();
        return investorData;
    } else {
        removeError(companyObj);
    }

    if (!position) {
        setError(positionObj, '*请填写您的公司');
        positionObj.focus();
        return investorData;
    } else {
        removeError(positionObj);
    }

    if (!focus_cities.length) {
        setError($('#city-input'), '*请选择您关注的城市');
        $('#city-input').focus();
        focusCityObj.find('.error-tip').css({
            "margin-top": $('.J-focus-city').height() - 10
        });
        return investorData;
    } else {
        removeError($('#city-input'));
    }

    if (!focus_fields.length) {
        setError(focusFieldsObj, '*请选择您关注的领域');
        focusFieldsObj.focus();
        focusFieldsObj.parent().find('.error-tip').css({
            "margin-top": focusFieldsObj.height(),
            "margin-left": '80px'
        });
        return investorData;
    } else {
        removeError(focusFieldsObj);
    }

    $('#userAvatarInput').val(img_src);

    var userData = $('#investorEditorBox').serializeArray();
    investorData.data = {};
    for (var i in userData) {
        investorData.data[userData[i].name] = userData[i].value;
    }
    investorData.status = true;
    return investorData;
}


function judgeChangePasswordConfirm(input) {
    var pwd = $('.new-pwd').val();
    var str = input.value;

    if (pwd != str) {
        setError(input, '*两次密码输入不一致');
    } else {
        removeError(input);
    }
}

function cropImg(o) {
    var isIE = navigator.userAgent.indexOf('MSIE') >= 0;
    if (!$(o).val().match(/.jpg|.gif|.png|.bmp/i)) {
        alertImg('图片格式无效！');
        return;
    }
    if (isIE) { //IE浏览器
        $('#myCropHeadIconModel .image-box img').attr('src', o.value);
        $('#myCropHeadIconModel .preview-box img').attr('src', o.value);
    }
    if (!isIE) {
        var file = o.files[0];
        var reader = new FileReader();
        reader.onload = function () {
            var img = new Image();
            img.src = reader.result;
            $('#myCropHeadIconModel .image-box img').attr('src', reader.result);
            $('#myCropHeadIconModel .preview-box img').attr('src', reader.result);
        };
        reader.readAsDataURL(file);
    }

    $('#myCropHeadIconModel').modal('show');
    $(o).replaceWith('<input type="file" style="display:none" onchange="cropImg(this)">');
}

function preview(img, selection) {
    if (!selection.width || !selection.height)
        return;

    var scaleX = 150 / selection.width;
    var scaleY = 150 / selection.height;

    var PrimaryWidth = $('#myCropHeadIconModel .image-box img').width();
    var PrimaryHeight = $('#myCropHeadIconModel .image-box img').height();

    $('#myCropHeadIconModel .preview-box img').css({
        width: Math.round(scaleX * PrimaryWidth),
        height: Math.round(scaleY * PrimaryHeight),
        marginLeft: -Math.round(scaleX * selection.x1),
        marginTop: -Math.round(scaleY * selection.y1)
    });
    localStorage.setItem('cropImgSize', JSON.stringify({
        'x1': selection.x1,
        'y1': selection.y1,
        'w': selection.width,
        'h': selection.height
    }));
}

function update_password(){
    var pwdObj = $('#accountPassword');
    var old_pwd = pwdObj.find('.old-pwd').val(),
        password = pwdObj.find('.new-pwd').val(),
        password_confirm = pwdObj.find('.new-pwd-confirm').val();

    if(!old_pwd){
        setError($('.old-pwd'),'*密码不能为空');
        $('.old-pwd').focus();
        return;
    }else{
        removeError($('.old-pwd'));
    }
    if(!judgePassword(pwdObj.find('.new-pwd')[0])){
        pwdObj.find('.new-pwd').focus();
        return;
    }

    if(password != password_confirm){
        setError(pwdObj.find('.new-pwd-confirm'),'*两次密码输入不一致');
        pwdObj.find('.new-pwd-confirm').focus();
        return;
    }else{
        removeError(pwdObj.find('.new-pwd-confirm'));
    }
    var post = {
        password_new : password,
        password2 : password_confirm,
        password : old_pwd
    };
    showLoading();
    API.post('password', post, function (result) {
        if(result.error == 0){
            hideLoading();
            setTimeout(function(){
                alertImg('密码修改成功，请用新密码重新登录');
                setTimeout(function(){
                    location.href = $('.J-logout').attr('href');
                    showLoading();
                },1000);
            },1000);
        }else{
            hideLoading();
            alertImg(result.message);
        }
    });
}


