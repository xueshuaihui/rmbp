$(function () {
    initAccountEvents();

    var href = location.href;
    if(href.match('index')){
        $('.J-center-li a').click();
    }
});

function initAccountEvents(){
    if (accountEvents.length > 0) {
        for (var i in accountEvents) {
            try {
                accountEvents[i]();
            } catch (e) {
                console.log('accountEvents[' + i + ']', e);
            }
        }
    }
}

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
        var selectField = $('.J-focus-fields .select');
        var is_select = $(this).hasClass('select');
        if(!is_select && selectField.length > 4){
            alertImg('不要贪心，您关注的够多了');
            return;
        }
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
        var newNum = parseInt($('.J-news-num').html());
        showLoading();
        API.post('message_read', post, function (result) {
            hideLoading();
            if (result.error == 0) {
                $('#accountNews .unread').remove();
                $('.J-unread').removeClass('unread-li').removeClass('J-unread');
                if(newNum > ids.length){
                    $('.J-news-num').html(newNum - ids.length);
                }else{
                    $('.J-news-num').hide();
                }
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
    $('.J-upload-logo').unbind('click').click(function () {
        var target = $(this).data('target');
        $('#'+target).click();
    });
}

function initEditBtn() {
    $('.J-is-invested .J-edit-btn').unbind('click').click(function () {
        $('.J-is-invested .show-box').css('display', 'none');
        $('.J-is-invested .edit-box').css('display', 'block');

    })
}

function initEditCancelBtn() {
    $('.J-is-invested .C-J-cancel-btn').unbind('click').click(function () {
        $('.J-is-invested .edit-box').css('display', 'none');
        $('.J-is-invested .show-box').css('display', 'block');
        $("body").animate({scrollTop: 0}, 100);
    })
}

function initEditSaveBtn() {
    $('.J-is-invested .C-J-save-btn').unbind('click').click(function () {
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
        cityObj = $('.J-is-invested .edit-box .J-region'),
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

$.validator.addMethod("phone",function(value,element,params){
    var phoneReg = /^1[3|4|5|7|8]\d{9}$/;
    return phoneReg.test(value);
},"请输入正确的手机号码");
$.validator.addMethod("passwordR",function(value,element,params){
    return value.length > 5 && value.length < 17;
},"请输入6-16位的密码");

jQuery.extend(jQuery.validator.messages, {
    required: "不能为空",
    email: "请输入正确格式的电子邮件",
    equalTo :"两次密码输入不一致"
});

var security = {
    init : function(){
        this.setLevelStatus();
        this.initEvent();
        initFetchCheckCode2();
        this.validate();
    },

    validate : function(){
        $('.J-email-edit-box form').validate({
            rules: {
                "email": {
                    required: true,
                    email: true
                },
                "regcode": {
                    required: true
                }
            }
        });

        $('.J-phone-edit-box form').validate({
            rules: {
                "phone":{
                    required: true,
                    phone: true
                },
                "regcode": {
                    required: true
                }
            }
        });

        $('.J-password-edit-box form').validate({
            rules: {
                "old-password":{
                    required: true
                },
                "password": {
                    required: true,
                    passwordR: true
                },
                "password-confirm":{
                    required: true,
                    equalTo: '.J-password-form .J-pwd'
                }
            }
        });
    },

    initEvent : function(){
        var self = this;

        initPwdCapsLock();

        $('.J-fix-btn').click(function(){
            var type = $(this).data('type'),
                status = $(this).data('status'),
                source = $(this).data('source'),
                sourceStatus = 'display:none',
                sourceName = '',
                sourceShow = '';
            var securityModalObj = $('#securityModel');

            if(type == 'password'){
                $('.J-' + type + '-edit-box').slideDown('fast')
                    .find('.J-pwd-old').focus();

                $(this).hide();
            }else{
                if(status == 'y'){
                    sourceStatus = 'display:block';
                    sourceName = type == 'phone' ? '手机' : '邮箱';
                    //sourceShow = type == 'phone' ? self.formatePhone(source.toString()) : self.formateEmail(source);
                    sourceShow = type == 'phone' ? $('.J-phone-show').html() : $('.J-email-show').html();
                }
                var data = {
                    sourceName : sourceName,
                    sourceShow : sourceShow,
                    source : source,
                    type : type,
                    sourceStatus : sourceStatus
                };
                var securityCheckTpl = tpl('securityCheckTpl',data);
                securityModalObj.find('.modal-content').html(securityCheckTpl);
                securityModalObj
                    .unbind('shown.bs.modal')
                    .on('shown.bs.modal',function(){
                        self.initSecurityModalEvent();
                        var isHasCodeCheck = $(this).find('.account-box').css('display') == 'block';
                        if(isHasCodeCheck){
                            $(this).find('.J-check-code-btn').click();
                            $(this).find('.J-check-code').focus();
                        }else{
                            $(this).find('.J-pwd').focus();
                        }
                    })
                    .modal('show');
            }
        });

        $('.J-security .J-cancel-btn').click(function(){
            var type = $(this).data('type');
            $('.J-' + type + '-edit-box').slideUp('fast');
            $('.J-fix-btn[data-type=' + type + ']').show();
        });

        $('.J-security .J-confirm-btn').click(function(){
            var type = $(this).data('type');
            $('.J-'+type+'-form')
                .submit(function(){
                    return false;
                })
                .submit();

            if($('.J-'+type+'-form input.error').length){
                return;
            }

            if(type == 'email'){
                self.fixEmail();
            }

            if(type == 'phone'){
                self.fixPhone();
            }

            if(type == 'password'){
                self.fixPassword();
            }
        });

        $('.J-security .J-pwd-old').on('change', function(){
            var that = this;
            var post = {
                password : $(this).val()
            };
            API.post('is_password', post, function (result) {
                if (result.error == 0) {
                    $(that).parent()
                        .removeClass('unpass')
                        .addClass('pass');
                }else{
                    $(that).parent()
                        .removeClass('pass')
                        .addClass('unpass');
                    $(that).parent().find('.warning').html('密码错误').show();
                    setTimeout(function(){
                        $(that).parent().find('.warning').hide();
                    },2000)
                }
            });
        });

        $('.J-security input').bind('keypress', function (event) {
            if (event.keyCode == "13") {
                $('.J-security input').parent().parent().find('.J-confirm-btn').click();
            }
        });
    },

    setLevelStatus : function(){
        var level_1 = $('.level-1');
        var levelDescObj = $('.J-level-desc'),
            levelLineObj = $('.J-level-line');
        if(level_1.length == 2){
            levelDescObj.html('中');
            levelLineObj
                .css('width', '50%')
                .addClass('level-0');
        }
        if(level_1.length == 3){
            levelDescObj.html('高');
            levelLineObj
                .css('width', '100%')
                .removeClass('level-0');
        }
    },

    //formatePhone : function(str){
    //    var newStr = [];
    //    for(var i = 0; i<str.length;i++){
    //        if(i<3 || i>6){
    //            newStr.push(str[i]);
    //        }else{
    //            newStr.push('*');
    //        }
    //    }
    //
    //    return newStr.join('');
    //},
    //
    //formateEmail : function(str){
    //    var newStr = [];
    //    var strArr = str.split('@');
    //    for(var i = 0; i<strArr[0].length;i++){
    //        if(i<2){
    //            newStr.push(str[i]);
    //        }else{
    //            newStr.push('*');
    //        }
    //    }
    //
    //    return newStr.join('') + '@' + strArr[1];
    //},

    initSecurityModalEvent : function(){
        initFetchCheckCode2();
        initPwdCapsLock();
        $('#securityCheck').unbind('validate').validate({
            rules: {
                "loginPassword": {
                    required: true
                },
                "regcode": {
                    required: true
                }
            }
        });

        var securityModalObj = $('#securityModel');
        securityModalObj.find('.J-confirm').unbind('click').click(function(){
            var type = $(this).data('type'),
                formObj = $('#securityCheck');
            formObj.submit(function(){
                return false;
            }).submit();

            var isError = formObj.find('input.error').length;
            if(isError){
                return;
            }
            if(!isError){
                var password = securityModalObj.find('.J-pwd').val(),
                    regcode = securityModalObj.find('.J-check-code').val();
                var post = {
                    type: type,
                    password: password,
                    regcode: regcode
                };
                API.post('is_password', post, function (result) {
                    if (result.error == 0) {
                        var message = result.message;
                        if(type == 'email'){
                            $('.J-email-form').find('.J-md5-pass').val(message);
                        }else if(type == 'phone'){
                            $('.J-phone-form').find('.J-md5-pass').val(message);
                        }
                        securityModalObj.modal('hide');
                        $('.J-'+type+'-edit-box').slideDown('fast');
                    }else{
                        securityModalObj.find('.error-box').text(result.message).show();
                        setTimeout(function(){
                            securityModalObj.find('.error-box').hide();
                        },2500)
                    }
                });
            }
        });

        securityModalObj.find('input').bind('keypress', function (event) {
            if (event.keyCode == "13") {
                securityModalObj.find('.J-confirm').click();
            }
        });
    },

    fixEmail : function(){
        var emailObj = $('.J-email-form');
        var post = {
            email : emailObj.find('#securityEmailInput').val(),
            regcode : emailObj.find('.J-check-code').val(),
            md5_pass : emailObj.find('.J-md5-pass').val(),
        };
        API.post('update_email', post, function (result) {
            if(result.error == 0){
                setTimeout(function(){
                    alertImg('邮箱已修改成功，请重新登录');
                    setTimeout(function(){
                        location.href = '/user/login/';
                    },1000);
                },1000);
            }else{
                emailObj.find('.error-box').html(result.message).show();
                setTimeout(function(){
                    emailObj.find('.error-box').hide();
                },2500)
            }
        });
    },

    fixPhone : function(){
        var phoneObj = $('.J-phone-form');
        var post = {
            phone : phoneObj.find('#securityPhoneInput').val(),
            regcode : phoneObj.find('.J-check-code').val(),
            md5_pass : phoneObj.find('.J-md5-pass').val(),
        };
        API.post('update_phone', post, function (result) {
            if(result.error == 0){
                alertImg('手机已修改成功，请重新登录');
                setTimeout(function(){
                    location.href = '/user/login/';
                },2500);
            }else{
                phoneObj.find('.error-box').html(result.message).show();
                setTimeout(function(){
                    phoneObj.find('.error-box').hide();
                },2500)
            }
        });
    },

    fixPassword : function(){
        var passwordObj = $('.J-password-form');
        var post = {
            password_new : passwordObj.find('.J-pwd').val(),
            password2 : passwordObj.find('.J-pwd-confirm').val(),
            password : passwordObj.find('.J-pwd-old').val()
        };
        API.post('password', post, function (result) {
            if(result.error == 0){
                alertImg('密码修改成功，请用新密码重新登录');
                setTimeout(function(){
                    location.href = '/user/login/';
                },2500);
            }else{
                passwordObj.find('.error-box').html(result.message).show();
                setTimeout(function(){
                    passwordObj.find('.error-box').hide();
                },2500)
            }
        });
    },

    checkPwdMode: function(o){
        var pwd = $(o).val();
        var levelBoxObj = $('.J-pwd-level-box');
        if(pwd.length < 6 || pwd.length > 16){
            levelBoxObj
                .hide();
            return;
        }
        var level = check_strength(pwd);
        var content = "";
        switch (level) {
            case 0:
                content = "弱";
                break;
            case 1:
                content = "中";
                break;
            case 2:
                content = "高";
                break;
        }
        levelBoxObj
            .show()
            .find('.J-level-desc').html(content);
        levelBoxObj.find('.level')
            .removeClass('level-0')
            .removeClass('level-1')
            .removeClass('level-2')
            .addClass('level-' + level);

    }
};

function char_type(char)
{
    if (char >=48 && char <= 57)
    {
        // 数字
        return 1;
    }
    if (char >=65 && char <= 90)
    {
        // 大写字母
        return 2;
    }
    if (char >= 97 && char <= 122)
    {
        // 小写字母
        return 4;
    }
    // 其余都是特殊字符
    return 8;
}
// 计算二进制中1出现的次数
function bit_count(n)
{
    var c = 0;
    while (n > 0)
    {
        if ((n & 1) == 1)
        {
            ++c;
        }
        n = n >> 1;
    }
    return c;
}
// 检测用户提供的字符串包含几种类型（数字、大写字母、小写字母、特殊字符）
function total_mode(str) {
    var len = str.length;
    var mode = 0;
    for (var i = 0; i < len; i++) {
        mode = mode | char_type(str[i].charCodeAt());
    };
    return bit_count(mode);
}
// 测试用户提供的密码强度
// 返回值 -- 弱：0，中：1，高：2
function check_strength(str) {
    var len = str.length;
    var mode = total_mode(str);
    if (mode == 1)
    {
        return 0;
    }
    if (mode == 2 || (len > 6 && len <= 8))
    {
        return 1;
    }
    if (len > 8 && (mode == 3 || mode == 4))
    {
        return 2;
    }
    return 0;
    // 字符串类型1，属于弱强度密码
    // 字符串类型2 或 密码长度大于6且小于8，属于中强度密码
    // 字符串类型3或4 且 密码长度大于8，属于高强度密码
}

function initFetchCheckCode2() {
    $('.J-check-code-btn').unbind('click').click(function () {
        var type = $(this).data('type');
        var page = $(this).data('page');

        var post = {
            type: type,
            source: page=='securityModel' ? '' : $('#' + $(this).data('target')).val()
        }, eventObject = this;

        API.post('account_safecode', post, function (result) {
            if (result.error == 0) {
                if (type == 'phone') {
                    showAlertInfo('验证码已发送至您的手机，请及时查收。', page=='securityModel' ? $('#securityModel .J-alert') : '');
                }
                if (type == 'email') {
                    showAlertInfo('验证码已发送至您的邮箱，请及时查收。');
                }
                $('.error-box').css('display', 'none');
                settime($(eventObject));
            }
            if (result.error == 1) {
                if (page == 'phone-register') {
                    $('#phone').find('.error-box').html(result.message).css('display', 'block');
                    return;
                }
                if (page == 'email-register') {
                    $('#email').find('.error-box').html(result.message).css('display', 'block');
                    return;
                }
                if (page == 'securityModel') {
                    $('#securityModel').find('.error-box').html(result.message).show();
                    setTimeout(function(){
                        $('#securityModel').find('.error-box').hide();
                    },2500);
                    return;
                }
                alertImg(result.message);
            }
        });
    });
}

