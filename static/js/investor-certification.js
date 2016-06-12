$(window).ready(function () {
    initFetchCheckCode();
    initCityInput();
    initCropImage();
    set_select_element();
    select_investor_indentiter();
    set_identiter_content_btn_status();
    initSubmitBtn();
    initHotCity();
});

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

        API.post('user_feed_region', {id: ids.join(',')}, function (result) {
            if (result.error == 0) {
                initCityDelete();
                $('#citySelectModal').modal('hide');
            } else {
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

function initCropImage() {
    $('#investorImgInput').click(function () {
        $(this).prev().click();
        removeError($(this));
    });

    $('#cropInvestorIconModal .C-J-confirm').click(function () {
        var size = JSON.parse(localStorage.getItem('cropImgSize'));//尺寸及图片的base64在localstorage的cropImgSize里存着
        var primary_width = $("#cropInvestorIconModal .image-box img").width();
        var sourseImg = new Image();
        sourseImg.src = $("#cropInvestorIconModal .image-box img").attr('src');

        var R = sourseImg.width / primary_width;
        var canvas = $("#cropInvestorIconModal .J-canvas")[0];
        var context = canvas.getContext("2d");
        context.drawImage(sourseImg, size.x1 * R, size.y1 * R, size.w * R, size.h * R, 0, 0, canvas.width, canvas.height);

        $('#investorImg').attr('src', canvas.toDataURL("image/png"));
        $('#cropInvestorIconModal .C-J-cancel').click();
    })
}


function cropImg(o) {
    var isIE = navigator.userAgent.indexOf('MSIE') >= 0;
    if (!o.value.match(/.jpg|.gif|.png|.bmp/i)) {
        alert('图片格式无效！');
        return false;
    }
    if (isIE) { //IE浏览器
        $('#cropInvestorIconModal .image-box img').attr('src', o.value);
        $('#cropInvestorIconModal .preview-box img').attr('src', o.value);
    }
    if (!isIE) {
        var file = o.files[0];
        var reader = new FileReader();
        reader.onload = function () {
            var img = new Image();
            img.src = reader.result;
            $('#cropInvestorIconModal .image-box img').attr('src', reader.result);
            $('#cropInvestorIconModal .preview-box img').attr('src', reader.result);

        };
        reader.readAsDataURL(file);
    }

    $('#cropInvestorIconModal').modal('show');
    $('#cropInvestorIconModal .image-box img').imgAreaSelect({
        aspectRatio: '1:1',
        handles: true,
        fadeSpeed: 200,
        onSelectChange: preview
    });

    reset_img_selector($('#cropInvestorIconModal .image-box img'), 100, 100);
    $(o).replaceWith('<input type="file" style="display:none" onchange="cropImg(this)">');
}

function preview(img, selection) {
    if (!selection.width || !selection.height)
        return;

    var scaleX = 150 / selection.width;
    var scaleY = 150 / selection.height;

    $('#cropInvestorIconModal .preview-box img').css({
        width: Math.round(scaleX * img.width),
        height: Math.round(scaleY * img.height),
        marginLeft: -Math.round(scaleX * selection.x1),
        marginTop: -Math.round(scaleY * selection.y1)
    });
    localStorage.setItem('cropImgSize', JSON.stringify({
        'img': $('#cropInvestorIconModal .image-box img').attr('src'),
        'x1': selection.x1,
        'y1': selection.y1,
        'w': selection.width,
        'h': selection.height
    }));
}

function set_select_element() {
    $('#investorCertification .fields .status-flag').click(function () {
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

    $('#investorCertification .agree-list .status-flag').click(function () {
        var is_select = $(this).hasClass('select');
        if (is_select) {
            $(this).removeClass('select');
        }
        if (!is_select) {
            $(this).addClass('select');
        }
    })
}

function select_investor_indentiter() {
    $('#investorCertification .identity-box .status-flag').unbind('click').bind('click', function () {
        var is_personal = $(this).parent().hasClass('J-personal-identity');
        var is_selected = $(this).hasClass('select');
        removeError($('.J-identity-box'));
        removeError($('.organization-content input'));

        if (is_personal && is_selected) {
            $(this).removeClass('select');
            $('#investorCertification .J-organization-identity .status-flag').addClass('select');
        }
        if (is_personal && !is_selected) {
            $(this).addClass('select');
            $('#investorCertification .J-organization-identity .status-flag').removeClass('select');
        }
        if (!is_personal && is_selected) {
            $(this).removeClass('select');
            $('#investorCertification .J-personal-identity .status-flag').addClass('select');
        }
        if (!is_personal && !is_selected) {
            $(this).addClass('select');
            $('#investorCertification .J-personal-identity .status-flag').removeClass('select');
        }
        set_investor_indentiter_content();
    });
}

function set_investor_indentiter_content() {
    var is_personal_show = $('#investorCertification .J-personal-identity .status-flag').hasClass('select');

    $('#investorCertification .content').css('display', 'none');
    if (is_personal_show) {
        $('#investorCertification .personal-content').css('display', 'block');
    }
    if (!is_personal_show) {
        $('#investorCertification .organization-content').css('display', 'block');
    }
}

function set_identiter_content_btn_status() {
    $('.personal-content .status-flag').click(function () {
        $('.personal-content .status-flag').removeClass('select');
        $(this).addClass('select');
        removeError($('.J-identity-box'));
    })
}

function initHotCity() {
    var selectIds = $('#city-input').data('ids').toString();
    if(selectIds){
        selectIds = selectIds.split(',');
    }

    API.post('region', {pid: 'hot'}, function (result) {
        if (result.error == 0) {
            var cities = JSON.parse(result.message);

            for (var i = 0; i < cities.length; i++) {
                var tplHtml_1 = tpl('hotCityModal', {name: cities[i].name, code: cities[i].code, id: cities[i].id});
                $('.J-select-city-box .J-city').append(tplHtml_1);

                if(selectIds){
                    for(var j = 0;j<selectIds.length;j++){
                        if(cities[i].id == selectIds[j]){
                            $('.J-select-city-box .J-city input').eq(i).addClass('select');
                            var tplHtml_2 = tpl('cityShowModal', {val: cities[i].name, id: selectIds[j]});
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

function initSubmitBtn() {
    $('#investorCertification .J-submit').on('click', function () {
        var investorData = judgeInvestorData();

        if (investorData.status) {
            $('#investorCertification').find('.error-box').css('display', 'none');
            showLoading();

            investorData.data['isauth'] = $('.J-isauth select').val();
            investorData.data['message'] = $('.J-message textarea').val();

            API.post('certificate', investorData.data, function (result) {
                if (result.error == 0) {
                    $('#certifySuccessModel').modal('show');
                    hideLoading();
                } else {
                    hideLoading();
                    alertImg(result.message);
                }
            })
        }
    })
}

function judgeInvestorData() {
    var certifyObj = $('#investorCertification');
    var name = certifyObj.find('.J-name').val(),
        img_src = $('#investorImg').attr('src'),
        phone = certifyObj.find('.J-phone').val(),
        phone_check_code = certifyObj.find('.J-check-code').val(),
        email = certifyObj.find('.J-email').val(),
        at_city = certifyObj.find('.J-at-city').val(),
        company = certifyObj.find('.J-company').val(),
        position = certifyObj.find('.J-position').val(),
        weixin = certifyObj.find('.J-weixin').val(),
        is_organization = certifyObj.find('.J-organization-identity .status-flag').hasClass('select'),
        organization = certifyObj.find('.organization-content input').val(),
        focus_cities = certifyObj.find('.J-focus-city').find('.J-city'),
        focus_fields = certifyObj.find('.J-focus-fields').find('.select'),
        is_check_agree_list = $('.J-agree-list input').attr('checked'),
        investor = '';

    var investorData = {
        status: false,
        data: {}
    };
    if (!name || !judgeOnlyChineseOrEnlish(certifyObj.find('.J-name')[0])) {
        setError(certifyObj.find('.J-name'), '*请填写姓名');
        certifyObj.find('.J-name').focus();
        return investorData;
    } else {
        removeError(certifyObj.find('.J-name'));
    }

    if (img_src.match('no.png')) {
        setError($('#investorImgInput'), '*请选择图片');
        $('.investor-img-box .error-tip').css('margin-left','110px');
        $('#investorImgInput').focus();
        return investorData;
    } else {
        removeError($('#investorImgInput'));
    }

    if (!judgePhoneNum(certifyObj.find('.J-phone')[0])) {
        certifyObj.find('.J-phone').focus();
        return investorData;
    }

    if (!$('.J-phone').attr('disabled') && !phone_check_code) {
        setError(certifyObj.find('.J-phone-check-code'), '*请填写手机验证码');
        certifyObj.find('.J-phone-check-code').focus();
        return investorData;
    } else {
        removeError(certifyObj.find('.J-phone-check-code'));
    }

    if (!judgeEmail(certifyObj.find('.J-email')[0])) {
        setError(certifyObj.find('.J-email'), '*请填写正确的邮箱');
        certifyObj.find('.J-email').focus();
        return investorData;
    }else{
        removeError(certifyObj.find('.J-email'));
    }
    if (!at_city) {
        setError(certifyObj.find('.J-at-city'), '*请填写您的所在城市');
        certifyObj.find('.J-at-city').focus();
        return investorData;
    } else {
        removeError(certifyObj.find('.J-at-city'));
    }

    if (!company) {
        setError(certifyObj.find('.J-company'), '*请填写您的所在公司');
        certifyObj.find('.J-company').focus();
        return investorData;
    } else {
        removeError(certifyObj.find('.J-company'));
    }

    if (!position) {
        setError(certifyObj.find('.J-position'), '*请填写您的职位');
        certifyObj.find('.J-position').focus();
        return investorData;
    } else {
        removeError(certifyObj.find('.J-position'));
    }

    if (!weixin) {
        setError(certifyObj.find('.J-weixin'), '*请填写您的微信号');
        certifyObj.find('.J-weixin').focus();
        return investorData;
    } else {
        removeError(certifyObj.find('.J-weixin'));
    }

    if (is_organization) {
        investor = $('.organization-content input').val();
        if (!investor) {
            setError($('.organization-content input')[0], '*请填写机构名称');
            $('.organization-content input').focus();
            return investorData;
        } else {
            removeError($('.organization-content input'));
        }
    }
    if (!is_organization) {
        var select = $('.personal-content .select');
        if (select[0]) {
            investor = select.data('value');
        }
        if (!select[0]) {
            setError($('.J-identity-box'), '*请选择您的投资身份');
            $('.J-identity-box').parent().find('.error-tip').css({
                "margin-top": $('.J-identity-box').height(),
                "margin-left": '100px'
            });
            return investorData;
        }
        removeError($('.J-identity-box'));
    }

    if (!focus_cities.length) {
        setError($('#city-input'), '*请选择您关注的城市');
        $('#city-input').focus();
        return investorData;
    } else {
        removeError($('#city-input'));
    }

    if (!focus_fields.length) {
        setError($('.J-focus-fields'), '*请选择您关注的领域');
        $('.J-focus-fields').focus();
        $('.J-focus-fields').parent().find('.error-tip').css({
            "margin-top": $('.J-focus-fields').height(),
            "margin-left": '100px'
        });
        return investorData;
    } else {
        removeError($('.J-focus-fields'));
    }
    if (!is_check_agree_list) {
        $('.error-box').html('*请先阅读相关条款').css('display', 'block');
        return investorData;
    } else {
        $('.error-box').css('display', 'none');
    }

    $('#userAvatarInput').val(img_src);
    $('#investor').val(investor);
    var userData = certifyObj.serializeArray();
    investorData.data = {};
    for(var i in userData){
        investorData.data[userData[i].name] = userData[i].value;
    }
    investorData.status = true;


    return investorData;
}
