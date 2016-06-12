$(function () {
    countdown = 60; //再次获取手机验证等待时间

    initPostData();
    initCheckboxClick();
    initFetchCheckCode();
    initTplFunction();

    window.API = (function () {
        var that = this;
        that.post = function (api, post, callback) {
            //add form hash
            post.FORM_HASH = FORM_HASH;
            $.post(DIR + 'vcapi/' + api + '/?ajax=1', post, function (result) {
                var data;
                try {
                    eval('data=' + result);
                } catch (e) {
                }
                callback && callback(data);
            });
        };
        return that;
    })();

    if ($('#user-box').length > 0) {
        $('#user-box').load(DIR + '?c=user-online');
    }

    if ($('.J-search-nav').length > 0) {
       initSearch();
    }

    setImgLazy();

    $('body').css('min-height', $(window).height());

    if($('.J-search-content').length){
        $('.J-search-content').css('max-height', $(window).height() - $('.header').height() * 2);
        console.log($(window).height() - $('.header').height() * 2)
    }

});

function initSearch(){
    $('.J-search-nav li').click(function(){
        var is_active = $(this).hasClass('active');
        var target = $(this).data('target');

        if(is_active){
            $(this).removeClass('active');
            $('.J-search-content[data-id=' + target + ']').slideUp('fast');
            $('.J-fade').slideUp('fast');
        }
        if(!is_active){
            $('.J-fade').slideDown('fast');
            $('.J-search-nav li').removeClass('active');
            $(this).addClass('active');
            $('.J-search-content').slideUp('fast');
            $('.J-search-content[data-id=' + target + ']').slideDown('fast');
        }
    });

    $('.J-region-box a').click(function(){
        var is_select = $(this).hasClass('select');
        if(is_select){
            $(this).removeClass('select');
        }
        if(!is_select){
            $(this).addClass('select');
        }
    });

    $('.J-fade').click(function(){
        hideSearch();
    });

    $('.J-reset-btn').click(function(){
        $(this).parent().parent().find('.select').removeClass('select');
    });

    $('.J-confirm-btn').click(function(){
        var projectListsObj = $('.project-list');
        var verifySelects = $('.J-screening-box .select[data-verify]'),
            regionSelects = $('.J-screening-box .select[data-region]');
        var verifyArr = [],
            regionArr = [];

        for(var i = 0; i < verifySelects.length; i++){
            verifyArr.push(verifySelects.eq(i).data('verify'));
        }
        for(var j = 0; j < regionSelects.length; j++){
            regionArr.push(regionSelects.eq(j).data('region'));
        }

        if(verifyArr.length || regionArr.length){
            projectListsObj.hide();
            for(var k = 0; k < projectListsObj.length; k++){
                if($.inArray(projectListsObj.eq(k).data('verify'),verifyArr) > -1 ||
                    $.inArray(projectListsObj.eq(k).data('region'),regionArr) > -1){
                    projectListsObj.eq(k).show();
                }
            }
        }
        hideSearch();
    });

    $('[data-type="checkbox"]').click(function(){
        var aObj = $(this).find('.select-box');
        var is_select = aObj.hasClass('select');
        if(is_select){
            aObj.removeClass('select');
        }
        if(!is_select){
            aObj.addClass('select');
        }
    });

    $('[data-type="search"]')
        .mousedown(function(){
            $(this).css('background-color','#f3f3f3');
        })
        .mouseup(function(){
            var that = this;
            setTimeout(function(){
                $(that).css('background-color','#fff');
            },1000);
        })
        .click(function(){

        var projectListsObj = $('.project-list');
        var stage = $(this).data('stage'),
            area = $(this).data('areaids'),
            financing = $(this).data('financing'),
            is_default = $(this).data('default');

        $(this).parent().find('li').removeClass('active');
        $(this).addClass('active');
        if(is_default){
            projectListsObj.show();
            hideSearch();
            return;
        }

        projectListsObj.hide();
        if(stage){
            $('.project-list[data-stage=' + stage + ']').show();
        }

        if(area){
            for(var i=0; i<projectListsObj.length; i++){
                var project_areids = projectListsObj.eq(i).data('areids').split(',');
                if($.inArray(area.toString(),project_areids) > -1){
                    projectListsObj.eq(i).show();
                }
            }
        }

        if(financing){
            var financing_array = financing.split('-');
            var min = financing_array[0],
                max = financing_array[1];

            for(var j=0; j<projectListsObj.length; j++){
                var project_min = projectListsObj.eq(j).data('minfinancing'),
                    project_max = projectListsObj.eq(j).data('maxfinancing');

                if(!max.length && (project_min > min || project_max > min )){
                    projectListsObj.eq(j).show();
                }
                if(max.length && (
                        ( project_min > min && project_min < max) ||
                        ( project_max > min && project_max < max)
                    )
                ){
                    projectListsObj.eq(j).show();
                }
            }
        }

        hideSearch();
    });
}

function hideSearch(){
    $('.J-search-nav .active').removeClass('active');
    $('.J-search-content').slideUp('fast');
    $('.J-fade').slideUp('fast');
}

function setImgLazy(){
    $('img[data-src]')
        .each(function() {
            $(this).attr('data-original',$(this).data('src'));
        })
        .lazyload({effect: "fadeIn"});
}

function initTplFunction() {
    window.tpl = (function () {
        var cache = {};
        return function tmpl(str, data) {
            var fn = !/\W/.test(str) ?
                cache[str] = cache[str] ||
                    tpl(document.getElementById(str).innerHTML) :
                new Function("obj",
                    "var p=[],print=function(){p.push.apply(p,arguments);};" +
                    "with(obj){p.push('" +
                    str
                        .replace(/[\r\t\n]/g, " ")
                        .split("<%").join("\t")
                        .replace(/((^|%>)[^\t]*)'/g, "$1\r")
                        .replace(/\t=(.*?)%>/g, "',$1,'")
                        .split("\t").join("');")
                        .split("%>").join("p.push('")
                        .split("\r").join("\\'")
                    + "');}return p.join('');");
            return data ? fn(data) : fn;
        };
    })();
}

function initPostData() {
    $('#register .J-phone-check').click(function () {
        setCountdownTime($(this));
    });

    initPostLoginData();
    initPostRegisterData();
}

function initPostLoginData() {
    $('#login .J-login-submit').click(function () {
        var account = $('#login .J-account').val(),
            password = $('#login .J-password').val();
        //var is_legel_account = checkPhone(account) || checkEmail(account);
        var is_legel_account = account;
        var is_legel_pwd = password.length > 0;
        var dynamic_login = $('#login').find('.checkbox').attr('checked') == 'checked';
        /*if(!is_legel_account){
         setError($('#login .J-account'),'*请输入正确的手机号或者邮箱');
         $('#login .J-account').focus();
         return;
         }*/
        if (!is_legel_pwd) {
            setError($('#login .J-password'), '*请输入密码进行登录');
            $('#login .J-password').focus();
            return;
        }
        if (is_legel_account && is_legel_pwd) {
            $('#login .error-box').css('display', 'none');
            var post = {
                source: account,
                password: password
            };
            API.post('login', post, function (result) {
                if (result.error == 0) {
                    setTimeout(function () {
                        alertInfo('登录成功,即将返回首页.');
                        location.href = '/';
                    }, 500);
                } else {
                    $('#login .error-box').text(result.message).show();
                }
            });
        }
    })
}

function initPostRegisterData() {
    $('.J-register-submit').click(function () {
        var registerData = judgeRegisterData();

        if (registerData.status) {
            registerData.data.type = 'phone';

            API.post('register', registerData.data, function (result) {
                if (result.error == 0) {
                    $('.error-box').css('display', 'none');
                    alertInfo('注册成功,马上跳转到首页');
                    setTimeout(function () {
                        location.href = '/';
                    }, 1000);
                } else {
                    $('.error-box').html(result.message).css('display', 'block');
                }
            });
        }
    })

}

function judgeRegisterData() {
    var registerObj = $('#register'),
        //name = registerObj.find('.J-name').val(),
        password = registerObj.find('.J-password').val(),
        password_confirm = registerObj.find('.J-password-confirm').val(),
        phone = registerObj.find('.J-phone').val(),
        check_code = registerObj.find('.J-check-code').val(),
        is_checkbox = registerObj.find('.checkbox').is(':checked'),
        data = {
            status: false,
            data: {
                //username: name,
                password: password,
                password2: password_confirm,
                type: 'phone',
                regcode: check_code,
                source: phone
            }
        };

    //if (!checkName(name)) {
    //    setError(registerObj.find('.J-name'), '*请输入中文、英文、数字，不允许特殊字符');
    //    registerObj.find('.J-name').focus();
    //    return data;
    //} else {
    //    removeError(registerObj.find('.J-name'));
    //}

    if (!checkPhone(phone)) {
        setError(registerObj.find('.J-phone'), '*请输入正确的手机号码');
        registerObj.find('.J-phone').focus();
        return data;
    } else {
        removeError(registerObj.find('.J-phone'));
    }

    if (!check_code.length) {
        setError(registerObj.find('.J-check-code'), '*请输入手机验证码');
        registerObj.find('.J-check-code').focus();
        return data;
    } else {
        removeError(registerObj.find('.J-check-code'));
    }

    if (!checkPassword(password)) {
        setError(registerObj.find('.J-password'), '*请输入6-16位的密码');
        registerObj.find('.J-password').focus();
        return data;
    } else {
        removeError(registerObj.find('.J-password'));
    }

    if (password != password_confirm) {
        setError(registerObj.find('.J-password-confirm'), '*两次密码输入不一致');
        registerObj.find('.J-password-confirm').focus();
        return data;
    } else {
        removeError(registerObj.find('.J-password-confirm'));
    }

    if (!is_checkbox) {
        registerObj.find('.error-box').html('*请先阅读服务条款').css('display', 'block');
        return data;
    }
    registerObj.find('.error-box').css('display', 'block');
    data.status = true;
    return data;
}

//function judgeRegisterUsername(input) {
//    var username = input.value;
//    if (!username.length) {
//        setError($(input), '*名称不能为空');
//        return false;
//    }
//    if (!checkName(username)) {
//        setError($(input), '*请输入字母、数字、下划线组成，字母开头，4-16位的名称');
//        return false;
//    }
//
//    API.post('username2', {username: username}, function (result) {
//        if (result.error == 1) {
//            setError($(input), '*该用户名已被注册');
//            return false;
//        } else {
//            removeError($(input));
//        }
//    })
//}

function judgeRegisterPhoneNum(input) {
    var phone = input.value;

    if (!phone) {
        setError($(input), '*手机不能为空');
        return false;
    }

    if (!checkPhone(phone)) {
        setError($(input), '*请填写正确的手机号码');
        return false;
    }

    API.post('phone', {phone: phone}, function (result) {
        if (result.error == 1) {
            setError($(input), '*该手机号已被注册');
            return false;
        } else {
            removeError($(input));
        }
    })
}

function initCheckboxClick() {
    $('input[type="checkbox"]').click(function () {
        var is_checked = $(this).attr('checked') == 'checked';
        if (is_checked) {
            $(this).removeAttr('checked');
        }
        if (!is_checked) {
            $(this).attr('checked', 'checked');
            $('.error-box').css('display', 'none');
        }
    })
}

function initFetchCheckCode() {
    $('.J-check-code-btn').click(function () {
        var type = $(this).data('type');
        var page = $(this).data('page');
        var post = {
            type: type,
            source: $('#' + $(this).data('target')).val()
        }, eventObject = this;
        API.post('regcode', post, function (result) {
            if (result.error == 0) {
                if (type == 'phone') {
                    alertInfo('验证码已发送至您的手机，请及时查收。');
                }
                setCountdownTime($(eventObject));
            }
            if (result.error == 1) {
                alertInfo(result.message);
            }
        });
    });
}

function initPageStyle() {
    var screen_width = window.screen.width;
    if (screen_width > 1024) {
        $('.submit-btn-box').css({'left': '50%'}).css({'margin-left': '-512px'});
    }
}

function checkName(str) {
    var reg = /^[a-zA-z\u4e00-\u9fa50-9_]+$/;
    if (str.length) {
        return reg.test(str);
    }
    if (!str.length) {
        return false;
    }
}

function checkPhone(str) {
    var reg = /^1\d{10}$/;
    return reg.test(str);
}

function checkPassword(str) {
    var len = str.length;
    return len > 5;
}

function checkEmail(str) {
    var reg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
    return reg.test(str);
}

function checkLinkUrl(str) {
    var regUrl = /(http\:\/\/)?([\w.]+)(\/[\w- \.\/\?%&=]*)?/gi;
    var result = str.match(regUrl);
    return result != null;
}

function checkProjectName(str) {
    return str.length > 1 && str.length < 41;
}

function checkMoney(str) {
    return !isNaN(str) && str > 0;
}

function checkPositiveInt(str) {
    var reg = /^\+?[1-9][0-9]*$/;
    return reg.test(str);
}

function checkOnlyChineseOrEnlish(str) {
    var reg = /^\+?[a-zA-z\u4e00-\u9fa5]*$/;
    return reg.test(str) && str.length > 0;
}

function judgeOnlyChineseOrEnlish(input) {
    if (!checkOnlyChineseOrEnlish(input.value)) {
        setError($(input), '*请输入英文/中文，不支持特殊字符');
        return;
    } else {
        removeError($(input));
    }
}

function judgeLinkUrl(input) {
    if (checkLinkUrl(input.value)) {
        removeError($(input));
    } else {
        setError($(input), '*请输入正确的链接地址');
    }
}

function judgeMoney(input) {
    if (checkMoney(input.value)) {
        removeError($(input));
    } else {
        setError($(input), '*请输入大于0的金额');
    }
}

function judgePositiveInt(input) {
    if (checkPositiveInt(input.value)) {
        removeError($(input));
    } else {
        setError($(input), '*请输入大于0的整数');
    }
}

function judgeName(input) {
    if (!input.value.length) {
        setError($(input), '*名称不能为空');
        return;
    }
    if (checkName(input.value)) {
        removeError($(input));
    } else {
        setError($(input), '*请输入中文、英文、数字，不允许特殊字符');
    }
}

function judgePassword(input) {
    if (!input.value.length) {
        setError($(input), '*密码不能为空');
        return;
    }
    if (checkPassword(input.value)) {
        removeError($(input));
    } else {
        setError($(input), '*请输入6-16位的密码');
    }
}

function judgePasswordConfirm(input) {
    var pwd = $(input).parent().parent().find('.J-password').val();
    var str = input.value;

    if (pwd != str) {
        setError($(input), '*两次密码输入不一致');
    } else {
        removeError($(input));
    }
}

function judgePhoneNum(input) {
    if (checkPhone(input.value)) {
        removeError($(input));
    } else {
        setError($(input), '*请填写正确的手机号码');
    }
}

function judgeEmail(input) {
    if (!input.value.length) {
        setError($(input), '*邮箱不能为空');
        return;
    }
    if (checkEmail(input.value)) {
        removeError($(input));
    } else {
        setError($(input), '*请填写正确的电子邮箱');
    }
}

function judgeLoginName(input) {
    if (checkPhone(input.value) || checkEmail(input.value)) {
        removeError($(input));
    } else {
        setError($(input), '*请填写正确的手机号码或电子邮箱');
    }
}
function judgeLoginPassword(input) {
    if (input.value.length > 0) {
        removeError($(input));
    } else {
        setError($(input), '*请输入密码');
    }
}

function setError(obj, msg) {
    obj.addClass('error');
    obj.parent().find('.error-tip').remove();
    obj.after('<div class="error-tip">' + msg + '</div>');
}

function removeError(obj) {
    obj.removeClass('error');
    obj.parent().find('.error-tip').remove();
}

function setWarning(obj) {
    obj.addClass('warning');
}

function removeWarning(obj) {
    obj.removeClass('warning');
}

function setCountdownTime(val) {
    if (countdown == 0) {
        val.removeAttr("disabled");
        val.val("获取验证");
        countdown = 60;
        return;
    } else {
        val.attr("disabled", true);
        val.val(countdown + "秒后重试");
        countdown--;
    }
    setTimeout(function () {
        setCountdownTime(val)
    }, 1000)
}

function alertInfo(info) {
    $('.J-alert-info').html(info).css('display', 'block');

    setTimeout(function () {
        $('.J-alert-info').css('display', 'none');
    }, 2500);
}