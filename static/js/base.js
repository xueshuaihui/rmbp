$(document).ready(function () {
    countdown = 60; //再次获取手机验证等待时间
    countdownTime = 30; //跳转等待时间
    toggleB2T();
    $(window).scroll(function () {
        toggleB2T();
    });

    window.API = (function () {
        var that = this;
        that.post = function (api, post, callback) {
            //add form hash
            post.FORM_HASH = FORM_HASH;
            $.post(DIR + 'api/' + api + '/?ajax=1', post, function (result) {
                var data;
                try {
                    eval('data=' + result);
                } catch (e) {
                }
                callback && callback(data);
            });
        };
        that.ajax = function (obj, callback) {
            $(document).on('click', 'a[data-ajax]', function (e) {
                e.stopPropagation();
                var url = $(this).attr('href');
                url += url.search(/\?/) > -1 ? '&ajax=1' : '?ajax=1';

                var linkType = $(this).data('type');
                var linkHash = $(this).data('id') ? $(this).data('id') : escape(url);
                var linkClass = $(this).data('class') ? $(this).data('class') : 'tab-content';
                if (linkType && linkClass) {
                    var target = $('#' + $(this).data('target'));
                    obj = target.find('.' + linkClass + '[data-id="' + linkHash + '"]');
                    target.find('.' + linkClass).hide();
                    if (obj.length) {
                        obj.show();
                        return false;
                    }
                    obj = $('<div />').addClass(linkClass).attr('data-id', linkHash);
                    obj.appendTo(target);
                }

                obj.load(url, callback);
                return false;
            });
        };

        that.ajaxPop = function (obj, callback) {
            $(document).on('click', 'a[data-ajax-pop]', function (e) {
                var url = $(this).attr('href');
                url += url.search(/\?/) > -1 ? '&ajax=1' : '?ajax=1';
                obj.load(url, callback);
                e.stopPropagation();
                return false;
            });
        };

        that.postForm = function (form) {
            var url = $(form).attr('action'),
                target = $(form).data('target'),
                data = $(form).serializeArray(),
                post = {};
            for (var i in data) {
                post[data[i].name] = data[i].value;
            }
            url += url.search(/\?/) > -1 ? '&ajax=1' : '?ajax=1';
            $.post(url, post, function (response) {
                $(target).html(response);
            });
            try {
                window.event.stopPropagation();
            } catch (e) {
            }
            return false;
        };

        return that;
    })();

    /**
     * show dialog
     * @param options
     * @returns {boolean}
     */
    window.dialog = function (options) {
        if (options == 'hide' || options == 'close') {
            $('#modalDialog').modal('hide');
            return;
        }
        // when itts a element
        if (typeof options == 'object') {
            var href = $(options).attr('href');
            if (href) {
                options['url'] = href;
            }
        }
        if (options['html']) {
            $('#modalDialog .modal-content').html(options['html']);
            $('#modalDialog').modal('show');
        } else {
            options['url'] += options['url'].search(/\?/) == -1 ? '?' : '&';
            options['url'] += 'ajax=1';
            $('#modalDialog .modal-content').load(options['url'], function () {
                $('#modalDialog').modal('show');
            });
        }
        return false;
    };

    setContainerHeight();
    initBackToTop();
    initHeaderMenuLink();

    //初始化页面checkbox点击后的状态
    setInputCheckboxStatus();

    //取消截图时，清除截图蒙版
    setCancelCropImgPage();

    //初始化tpl方法
    initTplFunction();

    $(window).resize(function () {
        setContainerHeight();
    });

    setInvestPointLink();
    setImgLazy();

    // init animate
    (function () {
        $(document).on('mouseover', '.animated[data-mouseover]', function () {
            console.log('over');
            $(this).addClass($(this).data('mouseover'));
        })
            .on('mouseout', '.animated[data-mouseover]', function () {
                console.log('out');
                $(this).removeClass($(this).data('mouseover'));
            });
    })();
    //
    if (globalEvents.length > 0) {
        for (var i in globalEvents) {
            try {
                globalEvents[i]();
            } catch (e) {
                console.log('globalEventError[' + i + ']', e);
            }
        }
    }
});

function setImgLazy() {
    $('img[data-src]')
        .each(function () {
            $(this).attr('data-original', $(this).data('src'));
        })
        .lazyload({effect: "fadeIn"});
}

function initFooterPosition() {
    var bodyHeight = document.body.clientHeight,
        screenHeight = window.innerHeight;
    if (bodyHeight < screenHeight) {
        $('footer').addClass('bottom_0');
    } else {
        $('footer').removeClass('bottom_0');
    }
}

function setInvestPointLink() {
    $('.J-investor-certification-link').click(function () {
        var isauth = parseInt($('.J-logined').data('isauth'));
        if (isauth == 2) {
            alertImg('您已认证成功。')
        }
        if (isauth != 2) {
            location.href = $(this).data('href');
        }
    })
}

function digitUppercase(n) {
    var fraction = ['角', '分'];
    var digit = [
        '零', '壹', '贰', '叁', '肆',
        '伍', '陆', '柒', '捌', '玖'
    ];
    var unit = [
        ['元', '万', '亿'],
        ['', '拾', '佰', '仟']
    ];
    var head = n < 0 ? '欠' : '';
    n = Math.abs(n);
    var s = '';
    for (var i = 0; i < fraction.length; i++) {
        s += (digit[Math.floor(n * 10 * Math.pow(10, i)) % 10] + fraction[i]).replace(/零./, '');
    }
    s = s || '整';
    n = Math.floor(n);
    for (var i = 0; i < unit[0].length && n > 0; i++) {
        var p = '';
        for (var j = 0; j < unit[1].length && n > 0; j++) {
            p = digit[n % 10] + unit[1][j] + p;
            n = Math.floor(n / 10);
        }
        s = p.replace(/(零.)*零$/, '').replace(/^$/, '零') + unit[0][i] + s;
    }
    return head + s.replace(/(零.)*零元/, '元')
            .replace(/(零.)+/g, '零')
            .replace(/^整$/, '零元整');
};


function initHeaderMenuLink() {
    if ($('#user-panel').length > 0) {
        $('#user-panel').load(DIR + '?c=user-online', function () {
        });
    }
}

function initFetchCheckCode() {
    $('.J-check-code-btn').unbind('click').click(function () {
        var type = $(this).data('type');
        var page = $(this).data('page');
        var post = {
            type: type,
            source: $('#' + $(this).data('target')).val()
        }, eventObject = this;

        API.post('regcode', post, function (result) {
            if (result.error == 0) {
                if (type == 'phone') {
                    showAlertInfo('验证码已发送至您的手机，请及时查收。');
                }
                if (type == 'email') {
                    showAlertInfo('验证码已发送至您的邮箱，请及时查收，并返回填写以完成注册。');
                }
                $('.error-box').css('display', 'none');
                settime($(eventObject));
                hideLoading();
            }
            if (result.error == 1) {
                hideLoading();
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
                    setTimeout(function () {
                        $('#securityModel').find('.error-box').hide();
                    }, 2500);
                    return;
                }
                alertImg(result.message);
            }
        });
    });
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

function setInputCheckboxStatus() {
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

function setCancelCropImgPage() {
    $('.C-J-crop-modal .C-J-cancel').click(function () {
        $('.imgareaselect-outer').css('display', 'none');
        $('.imgareaselect-selection').parent().css('display', 'none');
    });
}

function initBackToTop() {
    $("#back2top a").click(function () {
        scrollToTop();
    });
}

function scrollToTop(){
    if ($("html").scrollTop())
        $("html").animate({scrollTop: 0}, 500);
    else
        $("body").animate({scrollTop: 0}, 500);
}

function setContainerHeight() {
    $('body > .container').css('min-height', $(window).height() - 283);
    $('body > .full-container').css('min-height', $(window).height() - 197);
    $('body > .container-box').css('min-height', $(window).height() - 283);
    $('body > .form-box').css('min-height', $(window).height() - 283);
    $('body > .account-box').css('min-height', $(window).height() - 283);
    $('body > .help-box').css('min-height', $(window).height() - 283);
    $('body > .J-detail-content').css('min-height', $(window).height() - 661);
}

function initTabClick() {
    $('.J-tab-box li a').click(function () {
        $('.J-tab-box li').removeClass('active');
        $(this).parent().addClass('active');
    })
}

function initForgotPassword() {

    $('.J-forgot-check').click(function () {
        var post = {
                source: $('#' + $(this).data('target')).val()
            },
            eventObject = this;
        showLoading();
        API.post('forgotcode', post, function (result) {
            if (result.error == 0) {
                showAlertInfo('验证码已发送，请及时查收。');
                $('.error-box').css('display', 'none');
                settime($(eventObject));
                hideLoading();
            }
            if (result.error == 1) {
                hideLoading();
                $('.J-forgotPasswordModal .error-box').html(result.message).css('display', 'block');
            }
        });
    });


    $('.J-forgotPasswordModal .J-reset-pwd-btn').click(function () {
        var accountObj = $('.J-account-input');
        var check_code = $('.J-reset-code').val();
        var post = {
            source: accountObj.val(),
            code: check_code
        };

        if (!judgePhoneOrEmail(accountObj[0])) {
            return;
        }
        if (!check_code) {
            $(this).prev().html('*请填写验证码').css('display', 'block');
            return;
        }

        API.post('checkcode', post, function (result) {
            if (result.error == 0) {
                $('.forgot-password-box').find('.error-box').css('display', 'none');
                //重置密码
                $('.J-forgotPasswordModal').hide();
                $('.J-resetPasswordModal').show();
                hideLoading();
            }
            if (result.error == 1) {
                hideLoading();
                $('.J-forgotPasswordModal .error-box').html(result.message).css('display', 'block');
            }
        });

    });

    $('.J-resetPasswordModal .J-reset-pwd-submit').click(function () {
        var account = $('.J-account-input').val();
        var regCode = $('.J-reset-code').val();
        var password = $('.J-resetPasswordModal .J-pwd').val();
        var password_confirm = $('.J-pwd-confirm').val();
        var post = {
            source: account,
            code: regCode,
            password: password,
            password2: password_confirm
        };

        if (!checkPassword(password)) {
            setError('.J-pwd', '*请输入6-16位的密码');
            $('.J-pwd').focus();
            return;
        }
        if (password != password_confirm) {
            setError('.J-resetPasswordModal .J-pwd-confirm', '*两次密码输入不一致');
            $('.J-resetPasswordModal .J-pwd-confirm').focus();
            return;
        }

        API.post('forgot', post, function (result) {
            if (result.error == 0) {
                $('.reset-password-box').find('.error-box').css('display', 'none');
                //重置密码成功
                $('.J-resetPasswordModal').hide();
                $('.J-resetPasswordSuccessModal').show();
                setTimeDown($('.J-resetPasswordSuccessModal .J-time-box'), '/');
            }
            if (result.error == 1) {
                $('.reset-password-box').find('.error-box').html(result.message).css('display', 'block');
            }
        });
    });
}
function setTimeDown(obj, href) {
    countdownTime = 5;
    setTimeout(function () {
        setResetPasswordTime(obj, href);
    }, 100)
}

function setResetPasswordTime(obj, href) {
    countdownTime -= 1;
    obj.html(countdownTime);
    if (countdownTime == 0) {
        location.href = href;
        return;
    }
    //每秒执行一次,setResetPasswordTime()
    setTimeout(function () {
        setResetPasswordTime(obj, href);
    }, 1000);
}

function toggleB2T() {
    if ($("html").scrollTop() > 800 | $("body").scrollTop() > 800) {
        $("#back2top").show();
    }
    else
        $("#back2top").hide();
}

function post_data(e) {
    var ID = $(e).parent().parent().attr('id');
    if (ID == 'phone') {
        postPhoneRegistData();
    }
    if (ID == 'email') {
        postEmailRegisterData();
    }
    if (ID == 'login') {
        postLoginData();
    }
}
function postPhoneRegistData() {
    var phoneObj = $('#phone');
    var data = judgeRegisterData('phone');

    if (data.status) {
        showLoading();
        //var post = data.data;
        API.post('register', data.data, function (result) {
            if (result.error == 0) {
                phoneObj.find('.error-box').text('注册成功');
                setTimeout(function () {
                    location.reload();
                }, 1000);
            } else {
                phoneObj.find('.error-box').text(result.message).show();
            }
            hideLoading();
        });
    }
}

function postEmailRegisterData() {
    var data = judgeRegisterData('email');

    if (data.status) {
        showLoading();
        // build post param
        var post = data.data;

        API.post('register', post, function (result) {
            if (result.error == 0) {
                $('#email').find('.error-box').text('注册成功');
                setTimeout(function () {
                    location.reload();
                }, 1000);
            } else {
                $('#email').find('.error-box').text(result.message).show();
            }
            hideLoading();
        });
    }
}

function judgeRegisterData(id) {
    var obj = $('#' + id);
    var
    //name = obj.find('.J-name').val(),
        password = obj.find('.J-pwd').val(),
        password_confirm = obj.find('.J-pwd-confir').val(),
        phone = obj.find('.J-phone').val(),
        email = obj.find('.J-email').val(),
        check_code = obj.find('.J-check-code').val(),
        is_checkbox = obj.find('.checkbox').is(':checked'),
        data = {
            status: false,
            data: {}
        };

    //if (!checkName(name)) {
    //    setObjError(obj.find('.J-name'), '*请输入字母、数字、下划线组成，字母开头，4-16位的名称')
    //    obj.find('.J-name').focus();
    //    return data;
    //} else {
    //    data.data['username'] = name;
    //    removeError('#' + id + ' .J-name');
    //}

    if (id == 'phone') {
        if (!checkPhone(phone)) {
            setObjError(obj.find('.J-phone'), '*请填写正确的手机号码');
            obj.find('.J-phone').focus();
            return data;
        } else {
            data.data['source'] = phone;
            removeError('#' + id + ' .J-phone');
        }
    }

    if (id == 'email') {
        if (!checkEmail(email)) {
            setObjError(obj.find('.J-email'), '*请填写正确的电子邮箱');
            obj.find('.J-email').focus();
            return data;
        } else {
            data.data['source'] = email;
            removeError('#' + id + ' .J-email');
        }
    }

    if (!check_code.length) {
        setObjError(obj.find('.J-check-code'), '*请填写验证码');
        obj.find('.J-check-code').focus();
        return data;
    } else {
        data.data['regcode'] = check_code;
        removeError('#' + id + ' .J-check-code');
    }

    if (!checkPassword(password)) {
        setObjError(obj.find('.J-pwd'), '*请输入6-16位的密码');
        obj.find('.J-pwd').focus();
        return data;
    } else {
        data.data['password'] = password;
        removeError('#' + id + ' .J-pwd');
    }

    if (password != password_confirm) {
        setObjError(obj.find('.J-pwd-confir'), '*两次密码输入不一致');
        obj.find('.J-pwd-confir').focus();
        return data;
    } else {
        data.data['password2'] = password_confirm;
        removeError('#' + id + ' .J-pwd-confir');
    }

    if (!is_checkbox) {
        $('#' + id + ' .error-box').html('请先阅读服务条款').css('display', 'block');
        return data;
    } else {
        $('#' + id + ' .error-box').css('display', 'none');
    }
    data.data['type'] = id;
    data.status = true;

    return data;
}

function judgeRegisterUsername(input) {
    var username = input.value;
    if (!username.length) {
        setError(input, '*名称不能为空');
        return false;
    }
    if (!checkName(username)) {
        setError(input, '*请输入字母、数字、下划线组成，字母开头，4-16位的名称');
        return false;
    }

    API.post('username2', {username: username}, function (result) {
        if (result.error == 1) {
            setError(input, '*该用户名已被注册');
            return false;
        } else {
            removeError(input);
        }
    })
}

function judgeRegisterPhoneNum(input) {
    var phone = input.value;

    if (!phone) {
        setError(input, '*手机不能为空');
        return false;
    }

    if (!checkPhone(phone)) {
        setError(input, '*请填写正确的手机号码');
        return false;
    }

    API.post('phone', {phone: phone}, function (result) {
        if (result.error == 1) {
            setError(input, '*该手机号已被注册');
            return false;
        } else {
            removeError(input);
        }
    })
}

function judgeRegisterEmail(input) {
    var email = input.value;
    if (!email) {
        setError(input, '*邮箱不能为空');
        return false;
    }

    if (!checkEmail(email)) {
        setError(input, '*请填写正确的电子邮箱');
        return false;
    }

    API.post('email', {email: email}, function (result) {
        if (result.error == 1) {
            setError(input, '*该邮箱已被注册');
            return false;
        } else {
            removeError(input);
        }
    })
}

function postLoginData() {
    var loginObj = $('#login');
    var account = loginObj.find('.J-account').val(),
        password = loginObj.find('.J-pwd').val();
    var is_legel_account = account;
    var is_legel_pwd = password.length > 0;


    if (!is_legel_pwd) {
        loginObj.find('.error-box').html('*请输入密码进行登录');
        loginObj.find('.error-box').css('display', 'block');
        return;
    }
    if (is_legel_account && is_legel_pwd) {
        loginObj.find('.error-box').css('display', 'none');
        showLoading();
        var post = {
            source: account,
            password: password
        };

        API.post('login', post, function (result) {
            if (result.error == 0) {
                hideLoading();
                setTimeout(function () {
                    alertImg('登录成功,即将返回首页.');
                }, 500);
                showLoading();
                //reload page
                location.reload();

            } else {
                hideLoading();
                loginObj.find('.error-box').text(result.message).show();
            }
        });
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
    var regUrl = /http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
    return regUrl.test(str);
}

function checkProjectName(str) {
    var bytesCount = 0;
    for (var i = 0; i < str.length; i++) {
        var c = str.charAt(i);
        if (/^[\u0000-\u00ff]$/.test(c)) { //匹配双字节
            bytesCount += 1;
        } else {
            bytesCount += 2;
        }
    }
    return bytesCount > 1 && bytesCount < 41;
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
        setError(input, '*请输入英文/中文，不支持特殊字符');
        return false;
    } else {
        removeError(input);
        return true;
    }
}

function judgeCompanyName(input) {
    if (!input.value.length) {
        setError(input, '*请输入公司名称');
        return false;
    } else {
        removeError(input);
    }
}

function judgeLinkUrl(input) {
    if (checkLinkUrl(input.value)) {
        removeError(input);
        return true;
    } else {
        setError(input, '*请输入正确的链接地址');
        return false;
    }
}

function judgeMoney(input) {
    if (checkMoney(parseFloat(input.value))) {
        removeError(input);
    } else {
        setError(input, '*请输入大于0的金额');
        return false;
    }
}

function judgePositiveInt(input) {
    if (checkPositiveInt(input.value)) {
        removeError(input);
        return true;
    } else {
        setError(input, '*请输入大于0的整数');
        return false;
    }
}

function judgeName(input) {
    if (!input.value.length) {
        setError(input, '*名称不能为空');
        return false;
    }
    if (checkName(input.value)) {
        removeError(input);
    } else {
        setError(input, '*请输入字母、数字、下划线组成，字母开头，4-16位的名称');
        return false;
    }
}

function judgePassword(input) {
    if (!input.value.length) {
        setError(input, '*密码不能为空');
        return false;
    }
    if (checkPassword(input.value)) {
        removeError(input);
        return true;
    } else {
        setError(input, '*请输入6-16位的密码');
        return false;
    }
}

function isCapslock(e) {

    e = (e) ? e : window.event;

    var charCode = false;
    if (e.which) {
        charCode = e.which;
    } else if (e.keyCode) {
        charCode = e.keyCode;
    }

    var shifton = false;
    if (e.shiftKey) {
        shifton = e.shiftKey;
    } else if (e.modifiers) {
        shifton = !!(e.modifiers & 4);
    }

    if (charCode >= 97 && charCode <= 122 && shifton) {
        return true;
    }

    if (charCode >= 65 && charCode <= 90 && !shifton) {
        return true;
    }

    return false;

}

function judgePasswordConfirm(input) {
    var pwd = $(input).parent().parent().find('.J-pwd').val();
    var str = input.value;

    if (pwd != str) {
        setError(input, '*两次密码输入不一致');
        return false;
    } else {
        removeError(input);
    }
}

function judgePhoneNum(input) {
    if (checkPhone(input.value)) {
        removeError(input);
        return true;
    } else {
        setError(input, '*请填写正确的手机号码');
        return false;
    }
}

function judgeEmail(input) {
    if (!input.value) {
        setError(input, '*邮箱不能为空');
        return false;
    }
    if (checkEmail(input.value)) {
        removeError(input);
        return true;
    } else {
        setError(input, '*请填写正确的电子邮箱');
        return false;
    }
}

function judgePhoneOrEmail(input) {
    if (!input.value) {
        setError(input, '*输入不能为空');
        return false;
    }
    if (checkEmail(input.value) || checkPhone(input.value)) {
        removeError(input);
        return true;
    } else {
        setError(input, '*请填写正确的手机号码或者邮箱');
        return false;
    }
}

function judgeLoginName(input) {
    if (checkPhone(input.value) || checkEmail(input.value)) {
        removeError(input);
    } else {
        setError(input, '*请填写正确的手机号码或电子邮箱');
        return false;
    }
}
function judgeLoginPassword(input) {
    if (input.value.length > 0) {
        removeError(input);
    } else {
        setError(input, '*请输入密码');
        return false;
    }
}

function setError(input, msg) {
    $(input).addClass('error');
    $(input).parent().find('.error-tip').remove();
    $(input).after('<div class="error-tip">' + msg + '</div>');
}

function setObjError(obj, msg) {
    obj.addClass('error');
    obj.parent().find('.error-tip').remove();
    obj.after('<div class="error-tip">' + msg + '</div>');
}

function removeError(input) {
    $(input).removeClass('error');
    $(input).parent().find('.error-tip').remove();
}

function getNowFormatDate() {
    var day = new Date();
    var Year = 0;
    var Month = 0;
    var Day = 0;
    var CurrentDate = "";
    //初始化时间
    Year = day.getFullYear(); //ie火狐下都可以
    Month = day.getMonth() + 1;
    Day = day.getDate();
    //Hour = day.getHours();
    // Minute = day.getMinutes();
    // Second = day.getSeconds();
    CurrentDate += Year + "-";
    if (Month >= 10) {
        CurrentDate += Month + "-";
    } else {
        CurrentDate += "0" + Month + "-";
    }
    if (Day >= 10) {
        CurrentDate += Day;
    } else {
        CurrentDate += "0" + Day;
    }
    return CurrentDate;
}

function getNowFormat2Date() {
    var day = new Date();
    var Year = 0;
    var Month = 0;
    var Day = 0;
    var CurrentDate = "";
    //初始化时间
    Year = day.getFullYear(); //ie火狐下都可以
    Month = day.getMonth() + 1;
    Day = day.getDate();
    Hour = day.getHours();
    Minute = day.getMinutes();
    // Second = day.getSeconds();
    CurrentDate += Year + "/";
    if (Month >= 10) {
        CurrentDate += Month + "/";
    } else {
        CurrentDate += "0" + Month + "/";
    }
    if (Day >= 10) {
        CurrentDate += Day;
    } else {
        CurrentDate += "0" + Day;
    }
    if (Hour >= 10) {
        CurrentDate += " " + Hour;
    } else {
        CurrentDate += " 0" + Hour;
    }
    if (Minute >= 10) {
        CurrentDate += ":" + Minute;
    } else {
        CurrentDate += ":0" + Minute;
    }
    return CurrentDate;
}

function settime(val) {
    if (countdown == 0) {
        val.removeAttr("disabled");
        val.val("获取验证码");
        countdown = 60;
        return;
    } else {
        val.attr("disabled", true);
        val.val(countdown + "秒后重试");
        countdown--;
    }
    setTimeout(function () {
        settime(val)
    }, 1000)
}

function alertImg(info) {
    $('#alertImgModel .modal-body').html('<p>' + info + '</p>');
    $('#alertImgModel').modal('show');
    setTimeout(function () {
        $('#alertImgModel').modal('hide');
    }, 2000)
}

function setCropImgSize(w, h) {
    var cropModalObj = $('.C-J-crop-modal');
    if (w > h) {
        cropModalObj.find('.image-box img').css('width', '400px').css('height', 'auto');
    } else {
        cropModalObj.find('.image-box img').css('height', '400px').css('width', 'auto');
    }
}

function reset_img_selector(img, select_w, select_h) {
    var isMac = navigator.userAgent.indexOf('Mac OS X') > 0;
    var time = isMac ? 500 : 1000;

    setTimeout(function () {
        var imgW = img.width();
        var imgH = img.height();
        var ratio = select_w / select_h;
        if (imgW < select_w) {
            select_w = imgW;
            select_h = select_w / ratio;
        }
        if (imgH < select_h) {
            select_h = imgH;
            select_w = select_h * ratio;
        }

        var X1 = (imgW - select_w) / 2,
            Y1 = (imgH - select_h) / 2,
            X2 = X1 + select_w,
            Y2 = Y1 + select_h;

        img.imgAreaSelect({x1: X1, y1: Y1, x2: X2, y2: Y2});
        localStorage.setItem('cropImgSize', JSON.stringify({'x1': X1, 'y1': Y1, 'w': select_w, 'h': select_h}));
    }, time)
}

function deleteBlank(str) {
    return str.replace(/\s+/g, "");
}

function hide(obj) {
    obj.css('display', 'none');
}

function show(obj) {
    obj.css('display', 'block');
}

function getNum(text) {
    var value = text.replace(/[^0-9]/ig, "");
    return parseInt(value);
}

function showAlertInfo(info, obj) {
    if (!obj) {
        obj = $('.J-alert');
    }
    obj.html(info).css('display', 'block');
    setTimeout(function () {
        obj.css('display', 'none');
    }, 2000)

}
function showLoading() {
    $('.J-loading').css('display', 'block');
}
function hideLoading() {
    $('.J-loading').css('display', 'none');
}

function checkWordNum(obj) {
    var maxLength = $(obj).data("max");
    var input = obj.value;
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

    removeError(obj);

    if (bytesCount > maxLength) {
        $(obj).parent().find('.J-had-input').addClass('warning');
    } else {
        $(obj).parent().find('.J-had-input').removeClass('warning')
    }
}

function initPwdCapsLock() {
    $('input[type="password"]').bind('keypress', function (e) {
        var is_capslock = isCapslock(e);
        if (is_capslock) {
            $(this).parent().find('.caps-lock-warning').removeClass('hide');
        }
        if (!is_capslock) {
            $(this).parent().find('.caps-lock-warning').addClass('hide')
        }
    });
}

var statusColor = {
    'preheating': STATIC_URL + '/v2/images/circle-preheating.png',
    'raising': STATIC_URL + '/v2/images/circle-raising.png',
    'raise-success': STATIC_URL + '/v2/images/circle-raise-success.png',
    'super-raise': STATIC_URL + '/v2/images/circle-super-raise.png'
};

function setProjectLineCircle(obj, percentValue, flag, emptyFill) {
    obj.circleProgress({
        value: percentValue,
        startAngle: -0.5 * Math.PI,
        size: 80,
        fill: {color: "lime", image: statusColor[flag]},
        emptyFill: emptyFill
    }).on('circle-animation-progress', function (event, progress, stepValue) {
        $(this).find('.line-desc .num').html(parseInt(100 * stepValue));
    });
}

function initloginEvent(){
    $('#login input').bind('keypress', function (event) {
        if (event.keyCode == "13") {
            $('#login .J-btn').click();
        }
    });
    initPwdCapsLock();
}

function initRegisterEvent(){
    $('#myRegisterTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });
    initFetchCheckCode();
    initPwdCapsLock();
}