$(document).ready(function () {

    //初始化项目发布页面元素
    initProjectReleasePage();

    //初始化表单提交按钮
    initSubmit();

    //设置编辑器
    //获取  UE.getEditor('descDetailEditor').getContent();
    if ($('#descDetailEditor').length > 0) {
        setTimeout(function(){
            var descDetaiUE = UE.getEditor('descDetailEditor');
            setTimeout(function(){
                descDetaiUE.setContent($('#description').val());
            },500);
        },0);
    }

    if ($('#financingPlanEditor').length > 0) {
        setTimeout(function(){
            var financingPlanUE = UE.getEditor('financingPlanEditor');
            setTimeout(function(){
                financingPlanUE.setContent($('#financingplan').val());
            },500);
        },0);
    }
});

//日期初始化
function initDateInput() {
    $.ms_DatePicker({
        YearSelector: ".sel_year",
        MonthSelector: ".sel_month",
        DaySelector: ".sel_day"
    });
}

function initSubmit() {
    $('.J-CreateProjectForm').on('submit', function () {
        var data_status = false;
        var current_step = getNum($(this).attr('action')) - 1;

        if (current_step == 1) {
            data_status = judgeStep1Data();

            var desc_detail = UE.getEditor('descDetailEditor').getContent();
            var financing_plan_desc = UE.getEditor('financingPlanEditor').getContent();
            $(".J-description").val(desc_detail);
            $(".J-financingplan").val(financing_plan_desc);
            var area_ids = '';
            $('.J-field .select').each(function () {
                area_ids += $(this).data('id') + ',';
            });
            $('#area_ids').val(area_ids);
            var img_url = $('#projectBasicImgPrev img').attr('src');
            $('.J-img').val(img_url);
        }
        if (current_step == 2) {
            data_status = judgeStep2Data();
        }
        if (current_step == 3) {
            data_status = judgeStep3Data();
        }
        if (current_step == 4) {
            data_status = judgeStep4Data();
        }
        if (current_step == 5) {
            data_status = judgeStep5Data();
        }
        if (!data_status) {
            return false;
        }
        showLoading();
        return true;
    });

    $('#myProjectReleaseModel .C-J-confirm').click(function () {
        $('.J-isverify').val(1);
        $('.J-submit').click();
        $('#myProjectReleaseModel').modal('hide');
    })
}

function setReturnNum(){
    $('.J-return-money').val( isNaN(parseFloat($('.J-return-money').val())) ? 1 : parseFloat($('.J-return-money').val()) );

    var min = parseFloat($('#invest-wrap').data('minfinancing'))*10000 ;
    var max = parseFloat($('#invest-wrap').data('maxfinancing'))*10000 ;
    var return_money = $('.J-return-money').val();
    var per = parseFloat(return_money);
    var return_min_num = min/per;
    var return_max_num = max/per;
    if(isNaN(return_money) || !return_money.length){
        $('.J-return-min-num').val(0);
        $('.J-return-max-num').val(0);
    }else{
        $('.J-return-min-num').val(return_min_num);
        $('.J-return-max-num').val(return_max_num);
    }
}

function setInvestNumShow(){
    var investments = $('.investment-box');
    var total = 0;
    var min = parseFloat($('#invest-wrap').data('minfinancing'))*10000 ;
    var max = parseFloat($('#invest-wrap').data('maxfinancing'))*10000;
    for(var i = 0; i < investments.length; i++){
        var money = investments.eq(i).find('.J-return-money').val() ? parseFloat(investments.eq(i).find('.J-return-money').val()) : 0;
        var num = investments.eq(i).find('.J-return-num').val() ? parseInt(investments.eq(i).find('.J-return-num').val()) : 0;
        total += money*num;
    }
    var relax_min = min - total;
    var relax_max = max - total;

    if(relax_min > 0){
        $('.J-invest-num-show').html('所有档位总计融资'+total+' 元，距最低计划融资额还差 <span class="warning">'+relax_min+'</span> 元。');
        return false
    }
    if(!(relax_min > 0) && !(relax_max < 0)){
        $('.J-invest-num-show').html('所有档位总计融资 '+total+' 元，满足计划的融资要求。');
        return true;
    }
    if(relax_max < 0){
        $('.J-invest-num-show').html('所有档位总计融资 '+total+' 元，超出最高计划融资额 <span class="warning">' + Math.abs(relax_max) + '</span> 元。');
        return false;
    }
}

function checkConfirm() {
    var data_status = judgeStep5Data(),
        is_check = $('.J-checkbox').attr('checked');
    if (data_status) {
        if (!is_check) {
            $('.error-box').html('*请先同意条约').css('display', 'block');
        } else {
            $('.error-box').css('display', 'none');
            $('#myProjectReleaseModel').modal('show');
        }
    }
}

function initSampleImgShow() {
    $('.J-short-desc').focus(function () {
        $('.J-short-desc-sample').show();
    })
    $('.J-short-desc').blur(function () {
        $('.J-short-desc-sample').hide();
    })

    $('.J-return-content').focus(function () {
        $(this).parent().parent().find('.J-short-return-sample').show();
    })
    $('.J-return-content').blur(function () {
        $(this).parent().parent().find('.J-short-return-sample').hide();
    })
}

function initProjectReleasePage() {
    //初始化step label的状态
    initStepDescStatus();

    //初始化示例图片显示
    initSampleImgShow();

    //投资档、创始人显示初始化
    setInvestmentBox();
    setGuyBox();

    //所属领域初始化
    initSelectField();
    select_investment_partner();

    //插入图片初始化
    basic_img_input();
    gift_img_input();
    partner_img_input();

    //日期初始化
    initDateInput();
}

function initStepDescStatus() {
    var step_label = $('#steps');
    var current_step = parseInt(step_label.attr('data-currentstep'));
    var step_lis = step_label.find('li');

    step_lis.removeClass('current');
    for (var i = 0; i < step_lis.length; i++) {
        var step_num = getNum(step_lis.eq(i).attr('id'));
        if (!(step_num > current_step)) {
            step_lis.eq(i).addClass('current');
        }
    }
}

function basic_img_input() {
    $('.J-CreateProjectForm .J-img-select').click(function () {
        removeError('.J-img-select');
        $('#projectBasicImgInput').click();
    });

    $('#projectBasicImgCropModal .C-J-confirm').click(function () {
        var size = JSON.parse(localStorage.getItem('cropImgSize'));//尺寸在localstorage的cropImgSize里存着
        var primary_width = $("#projectBasicImgCropModal .image-box img").width();
        var sourseImg = new Image();
        sourseImg.src = $("#projectBasicImgCropModal .image-box img").attr('src');

        var R = sourseImg.width / primary_width;
        var canvas = $("#projectBasicImgCropModal .J-canvas")[0];
        var context = canvas.getContext("2d");
        context.drawImage(sourseImg, size.x1 * R, size.y1 * R, size.w * R, size.h * R, 0, 0, canvas.width, canvas.height);
        $('#projectBasicImgCropModal .C-J-cancel').click();

        $('#projectBasicImgPrev').html('');
        $('#projectBasicImgPrev').append("<a class='img-a J-img'><img src='" + canvas.toDataURL('image/jpeg',0.8) + "'/></a>");
    })
}

function gift_img_input() {

    $('#giftImgCropModal .C-J-confirm').unbind('click').click(function () {
        var size = JSON.parse(localStorage.getItem('cropImgSize'));//尺寸在localstorage的cropImgSize里存着
        var primary_width = $("#giftImgCropModal .image-box img").width();
        var sourseImg = new Image();
        sourseImg.src = $("#giftImgCropModal .image-box img").attr('src');
        var R = sourseImg.width / primary_width;
        var canvas = $("#giftImgCropModal .J-canvas")[0];
        var context = canvas.getContext("2d");
        context.drawImage(sourseImg, size.x1 * R, size.y1 * R, size.w * R, size.h * R, 0, 0, canvas.width, canvas.height);
        $('#giftImgCropModal .C-J-cancel').click();
        var current_input_id = localStorage.getItem('currentGiftInputID');

        var tplHtml = tpl("modelImgInput", {src: canvas.toDataURL("image/jpeg",0.8), index: getNum(current_input_id)});
        $('#' + current_input_id).before(tplHtml);
        $('.J-CreateProjectForm .close').unbind('click').click(function () {
            $(this).parent().remove();
        });
    })
}

function partner_img_input() {

    $('#partnerImgCropModal .C-J-confirm').unbind('click').click(function () {
        var size = JSON.parse(localStorage.getItem('cropImgSize'));//尺寸在localstorage的cropImgSize里存着
        var primary_width = $("#partnerImgCropModal .image-box img").width();
        var sourseImg = new Image();
        sourseImg.src = $("#partnerImgCropModal .image-box img").attr('src');
        var R = sourseImg.width / primary_width;
        var canvas = $("#partnerImgCropModal .J-canvas")[0];
        var context = canvas.getContext("2d");
        context.drawImage(sourseImg, size.x1 * R, size.y1 * R, size.w * R, size.h * R, 0, 0, canvas.width, canvas.height);
        $('#partnerImgCropModal .C-J-cancel').click();

        var current_input_id = localStorage.getItem('currentPartnerInputID');
        var input_num = getNum(current_input_id);
        $('.J-img-box-' + input_num).html('');

        var tplHtml = tpl("modelImgInput", {src: canvas.toDataURL("image/jpeg",0.8), index: input_num});
        $('.J-img-box-' + input_num).append(tplHtml);
    })
}

function initSelectField() {

    $('.J-CreateProjectForm .J-field .status-flag').bind('click', function () {
        var selected_input = $('.J-CreateProjectForm .field .select');
        var is_selected = $(this).hasClass('select');

        removeError($('.J-field'));
        if (is_selected) {
            if (selected_input.length == 1) {
                alertImg('最后一个不能删哦！');
            } else {
                $(this).removeClass('select');
            }
        }
        if (!is_selected) {
            if (selected_input.length > 2) {
                alertImg('最多只能选三个哦！');
            } else {
                $(this).addClass('select');
            }
        }
    });
}

function select_investment_partner() {
    $('.J-CreateProjectForm .title .status-flag').unbind('click').bind('click', function () {
        var is_selected = $(this).hasClass('select');

        if (is_selected) {
            $(this).removeClass('select');
        }
        if (!is_selected) {
            $(this).addClass('select');
        }
    });
}


function click_delete_investment() {
    var investment_box = $('.investment-box');
    if (investment_box.length == 1) {
        alertImg('最后一个档位不可以删哦！');
    }
    if (investment_box.length > 1) {
        investment_box.find('.action-btns').css('display', 'block');
    }
}

function click_delete_partner() {
    var partner_box = $('.partner-box');
    if (partner_box.length == 1) {
        alertImg('最后一个小伙伴不可以删哦！');
    }
    if (partner_box.length > 1) {
        partner_box.find('.action-btns').css('display', 'block');
    }
}

function cancel_delete_investment() {
    $('.investment-box .action-btns').css('display', 'none');
}

function cancel_delete_partner() {
    $('.partner-box .action-btns').css('display', 'none');
}

function setInvestmentBox() {
    var investments = $('.investment-box');
    for (var i = 0; i < investments.length; i++) {
        var capital_num = num_to_capital(i + 1);
        investments.eq(i).find('.J-invest-num').html(capital_num);
    }
    $('.action-btns').css('display', 'none');
    $('.error-box').css('display', 'none');

    //setInvestNumShow();

    $('.J-return-question-mark').unbind('hover').hover(function(){
        $('.J-question-mark-content').css('display','block');
    },function(){
        $('.J-question-mark-content').css('display','none');
    });
}

function setGuyBox() {
    var partners = $('.partner-box');
    partners.find('.partner-sample').css('display', 'none').eq(0).css('display', 'block');
    for (var i = 0; i < partners.length; i++) {
        var num = i + 1;
        var capital_num = num_to_capital(num);
        partners.eq(i).find('.partner-num').html(capital_num);
        partners.eq(i).find('.img-prev-box').addClass('J-img-box-' + num);
    }
    $('.action-btns').css('display', 'none');
    $('.error-box').css('display', 'none');
}

function num_to_capital(n) {
    var cnum = ['零', '一', '二', '三', '四', '五', '六', '七', '八', '九'];
    var s = '';
    n = '' + n; // 数字转为字符串
    for (var i = 0; i < n.length; i++) {
        s += cnum[parseInt(n.charAt(i))];
    }
    return s;
}

function cropBasicImg(o) {
    var isIE = navigator.userAgent.indexOf('MSIE') >= 0;
    if (!o.value.match(/.jpg|.gif|.png|.bmp/i)) {
        alert('图片格式无效！');
        return false;
    }
    if (isIE) { //IE浏览器
        $('#projectBasicImgCropModal .image-box img').attr('src', o.value);
        $('#projectBasicImgCropModal .preview-box img').attr('src', o.value);
    }
    if (!isIE) {
        var file = o.files[0];
        var reader = new FileReader();
        reader.onload = function () {
            var img = new Image();
            img.src = reader.result;
            setCropImgSize(img.width, img.height);
            $('#projectBasicImgCropModal .image-box img').attr('src', reader.result);
            $('#projectBasicImgCropModal .preview-box img').attr('src', reader.result);
        };
        reader.readAsDataURL(file);
    }

    $('#projectBasicImgCropModal').modal('show');
    $('#projectBasicImgCropModal .image-box img').imgAreaSelect({
        aspectRatio: '54:30',
        handles: true,
        fadeSpeed: 200,
        onSelectChange: basicImgPreview
    });

    reset_img_selector($('#projectBasicImgCropModal .image-box img'), 150, 83);

    $(o).replaceWith('<input style="display:none" type="file" id="projectBasicImgInput" onchange="cropBasicImg(this)" alt="projectBasicImgCropModal">');
}

function basicImgPreview(img, selection) {
    if (!selection.width || !selection.height)
        return;

    var scaleX = 150 / selection.width;
    var scaleY = 83 / selection.height;

    $('#projectBasicImgCropModal .preview-box img').css({
        width: Math.round(scaleX * img.width),
        height: Math.round(scaleY * img.height),
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

function cropGiftImg(o) {
    var isIE = navigator.userAgent.indexOf('MSIE') >= 0;
    localStorage.setItem('currentGiftInputID', $(o).attr('id'));
    if (!o.value.match(/.jpg|.gif|.png|.bmp/i)) {
        alert('图片格式无效！');
        return false;
    }
    if (isIE) { //IE浏览器
        $('#giftImgCropModal .image-box img').attr('src', o.value);
        $('#giftImgCropModal .preview-box img').attr('src', o.value);
    }
    if (!isIE) {
        var file = o.files[0];
        var reader = new FileReader();
        reader.onload = function () {
            var img = new Image();
            img.src = reader.result;
            setCropImgSize(img.width, img.height);
            $('#giftImgCropModal .image-box img').attr('src', reader.result);
            $('#giftImgCropModal .preview-box img').attr('src', reader.result);

        };
        reader.readAsDataURL(file);
    }

    $('#giftImgCropModal').modal('show');
    $('#giftImgCropModal .image-box img').imgAreaSelect({
        aspectRatio: '1:1',
        handles: true,
        fadeSpeed: 20,
        onSelectChange: giftImgPreview
    });

    reset_img_selector($('#giftImgCropModal .image-box img'), 100, 100);
    var id = $(o).attr('id');
    $(o).replaceWith('<input type="file" class="img-input-file" onchange="cropGiftImg(this)" id="' + id + '">');
}

function giftImgPreview(img, selection) {
    if (!selection.width || !selection.height)
        return;

    var scaleX = 150 / selection.width;
    var scaleY = 150 / selection.height;

    $('#giftImgCropModal .preview-box img').css({
        width: Math.round(scaleX * img.width),
        height: Math.round(scaleY * img.height),
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

function cropPartnerImg(o) {
    var isIE = navigator.userAgent.indexOf('MSIE') >= 0;
    var cropModal = $('#partnerImgCropModal');
    localStorage.setItem('currentPartnerInputID', $(o).attr('id'));
    if (!o.value.match(/.jpg|.gif|.png|.bmp/i)) {
        alert('图片格式无效！');
        return false;
    }
    if (isIE) { //IE浏览器
        cropModal.find('.image-box img').attr('src', o.value);
        cropModal.find('.preview-box img').attr('src', o.value);
    }
    if (!isIE) {
        var file = o.files[0];
        var reader = new FileReader();
        reader.onload = function () {
            var img = new Image();
            img.src = reader.result;
            setCropImgSize(img.width, img.height);
            cropModal.find('.image-box img').attr('src', reader.result);
            cropModal.find('.preview-box img').attr('src', reader.result);
        };
        reader.readAsDataURL(file);
    }
    cropModal.modal('show');
    cropModal.find('.image-box img').imgAreaSelect({
        aspectRatio: '1:1',
        handles: true,
        fadeSpeed: 20,
        onSelectChange: partnerImgPreview
    });

    reset_img_selector(cropModal.find('.image-box img'), 100, 100);
    var id = $(o).attr('id');
    $(o).replaceWith('<input type="file" class="img-input-file" onchange="cropPartnerImg(this)" id="' + id + '">');
}

function partnerImgPreview(img, selection) {
    if (!selection.width || !selection.height)
        return;

    var scaleX = 150 / selection.width;
    var scaleY = 150 / selection.height;

    $('#partnerImgCropModal .preview-box img').css({
        width: Math.round(scaleX * img.width),
        height: Math.round(scaleY * img.height),
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

function judgeProjectName(input) {
    if (checkProjectName(input.value)) {
        removeError(input);
    } else {
        setError(input, '*请输入2-40位字符的名称');
    }
}

function judgePersonalDesc(input) {
    var text = input.value;
    if (!text.length) {
        setError(input, '*请输入210个字符以内的简介')
    }
    if (text.length) {
        removeError(input);
    }
}

function judgeCityInput(input) {
    var city = input.value;
    if (city.length) {
        removeError(input);
    } else {
        setError(input, '请输入城市');
    }
}

function judgeStep1Data() {
    checkWordNum($('.J-short-desc')[0]);
    var project_name = $('.J-name').val(),
        project_company = $('.J-company-name').val(),
        project_short_desc = $('.J-short-desc').val(),
        desc_max_num = parseInt($('.J-short-desc').data('max')),
        desc_had_num = parseInt($('.J-had-input').html()),
        project_img = $('#projectBasicImgPrev img').attr('src'),
        project_city = $('.J-city').val(),
        project_website = $('.J-website-link').val(),
        project_ios = $('.J-iOS-link').val(),
        project_android = $('.J-Android-link').val(),
        project_fields = $('.J-field .select'),
        project_detail_desc = UE.getEditor('descDetailEditor').getContent(),
        financing_plan_desc = UE.getEditor('financingPlanEditor').getContent();
    if (!checkProjectName(project_name)) {
        setError('.J-name', '*请输入2-40个字符的名称');
        $('.J-name').focus();
        return false;
    } else {
        removeError('.J-name');
    }
    if (!project_company.length) {
        setError('.J-company-name', '*请输入公司名称');
        $('.J-company-name').focus();
        return false;
    } else {
        removeError('.J-company-name');
    }

    if (!project_short_desc.length || desc_had_num > desc_max_num) {
        setError('.J-short-desc', '*请输入不超过210个字符的简介');
        $('.J-short-desc').focus();
        return false;
    } else {
        removeError('.J-short-desc');
    }

    if (!project_img.length) {
        setError('.J-img-select', '*请选择图片进行上传');
        $('.J-img-select').focus();
        return false;
    } else {
        removeError('.J-img-select');
    }

    if (!project_city) {
        setError('.J-city', '*请输入城市');
        $('.J-city').focus();
        return false;
    } else {
        removeError('.J-city');
    }

    if(project_website && !judgeLinkUrl($('.J-website-link')[0])){
        $('.J-website-link').focus();
        return false;
    }
    if(project_ios && !judgeLinkUrl($('.J-iOS-link')[0])){
        $('.J-iOS-link').focus();
        return false;
    }
    if(project_android && !judgeLinkUrl($('.J-Android-link')[0])){
        $('.J-Android-link').focus();
        return false;
    }

    if (!project_fields[0]) {
        setError('.J-field', '*请选择所属领域');
        $('.J-field').focus();
        return false;
    } else {
        removeError('.J-field');
    }

    if (!project_detail_desc.length) {
        $('.error-box').html("*请填写项目的详细介绍").css('display', 'block');
        var editor = UE.getEditor('descDetailEditor');
        editor.focus();
        setTimeout(function () {
            $('.error-box').css('display', 'none');
        }, 1500);
        return false;
    } else {
        $('.error-box').css('display', 'none');
    }

    if (!financing_plan_desc.length) {
        $('.error-box').html("*请填写项目的融资计划").css('display', 'block');
        var plan_editor = UE.getEditor('financingPlanEditor');
        plan_editor.focus();
        setTimeout(function () {
            $('.error-box').css('display', 'none');
        }, 1500);
        return false;
    } else {
        $('.error-box').css('display', 'none');
    }
    return true;
}

function judgeStep2Data() {
    var current_valuation = parseFloat($('.J-current-valuation').val()),
        val_min = parseFloat($('#valuationMin').val()),
        val_max = parseFloat($('#valuationMax').val()),
        deadline = parseFloat($('#deadline').val()),
        valuation = parseFloat($('#valuation').val());

    if (!current_valuation) {
        setError('.J-current-valuation', '*请输入大于0的数字');
        $('.J-current-valuation').focus();
        return false;
    } else {
        removeError('.J-current-valuation');
    }

    if (!checkMoney(val_min)) {
        setError('#valuationMin', '*请输入大于0的数字');
        $('#valuationMin').focus();
        return false;
    } else {
        removeError('#valuationMin');
    }

    if (valuation < val_max) {
        setError('#valuationMax', '*最大融资不能超过估值');
        $('#valuationMax').focus();
        return false;
    } else {
        removeError('#valuationMax');
    }

    if (!checkMoney(val_max)) {
        setError('#valuationMax', '*请输入大于0的数字');
        $('#valuationMax').focus();
        return false;
    } else {
        removeError('#valuationMax');
    }

    if (!(val_min < val_max)) {
        setError('#valuationMax', '*请输入大于最小融资的数字');
        $('#valuationMax').focus();
        return false;
    } else {
        removeError('#valuationMax');
    }

    if (!checkPositiveInt(parseInt(deadline)) || parseInt(deadline) > 90) {
        setError('#deadline', '*不超过90天');
        $('#deadline').focus();
        return false;
    }

    return true;
}

function judgeStep3Data() {
    var investments = $('.investment-box');
    var data_status = false;
    for (var i = 0; i < investments.length; i++) {
        var return_money = investments.eq(i).find('.J-return-money').val(),
            has_return = investments.eq(i).find('.J-yes').hasClass('select'),
            return_content = investments.eq(i).find('.J-return-content').val(),
            return_time = investments.eq(i).find('.J-provideDate').val(),
            return_img = investments.eq(i).find('.gift-imgs .J-img'),
            max_num = investments.eq(i).find('.J-max-num').val();

        if (!checkMoney(return_money)) {
            setObjError(investments.eq(i).find('.J-return-money'), '*请输入大于0的金额');
            investments.eq(i).find('.J-return-money').focus();
            return;
        }

        if (!checkPositiveInt(max_num)) {
            setObjError(investments.eq(i).find('.J-max-num'), '*请输入大于0的整数');
            investments.eq(i).find('.J-max-num').focus();
            return;
        }

        if(has_return){
            if(!return_content){
                setError(investments.eq(i).find('.J-return-content'),'*请填写回报信息');
                investments.eq(i).find('.J-return-content').focus();
                return;
            }else{
                remove(investments.eq(i).find('.J-return-content'));
            }

            if( !judgePositiveInt(investments.eq(i).find('.J-provideDate')[0])){
                setError(investments.eq(i).find('.J-provideDate'),'*请填写大于0的整数');
                investments.eq(i).find('.J-provideDate').focus();
                return;
            }else{
                investments.eq(i).find('.J-provideDate').val(parseInt(return_time));
                remove(investments.eq(i).find('.J-provideDate'));
            }

            if(!return_img.length){
                setError(investments.eq(i).find('.J-gift-img-input'),'*请上传图片');
                investments.eq(i).find('.J-gift-img-input').focus();
                return;
            }else{
                remove(investments.eq(i).find('.J-gift-img-input'));
            }
        }

        if (i == investments.length - 1) {
            $('.error-box').css('display','none');
            data_status = true;
        }
    }
    return data_status;
}

function judgeStep4Data() {
    var partners = $('.partner-box');
    var partner_data = false;
    for (var i = 0; i < partners.length; i++) {
        var name = partners.eq(i).find('.J-name').val(),
            position = partners.eq(i).find('.J-position').val(),
            partner_short_desc = partners.eq(i).find('.J-short-desc').val(),
            partner_img = partners.eq(i).find('.img-prev-box img').attr('src');

        if (!checkOnlyChineseOrEnlish(name)) {
            setObjError(partners.eq(i).find('.J-name'), '*请输入英文/中文，不支持特殊字符');
            partners.eq(i).find('.J-name').focus();
            return false;
        }
        if (!checkOnlyChineseOrEnlish(position)) {
            setObjError(partners.eq(i).find('.J-position'), '*请输入英文/中文，不支持特殊字符');
            partners.eq(i).find('.J-position').focus();
            return false;
        }
        if (!partner_short_desc.length) {
            setObjError(partners.eq(i).find('.J-short-desc'), '*请输入英文/中文，不支持特殊字符');
            partners.eq(i).find('.J-short-desc').focus();
            return false;
        }
        if (!partner_img) {
            setObjError(partners.eq(i).find('.J-img-input'), '*请选择图片进行上传');
            partners.eq(i).find('.J-img-input').focus();
            return false;
        }
        if (i == partners.length - 1) {
            partner_data = true;
        }
    }
    return partner_data;
}

function judgeStep5Data() {
    var financer_name = $('.J-name').val(),
        financer_phone = $('.J-phone').val(),
        financer_email = $('.J-email').val();

    if (!checkOnlyChineseOrEnlish(financer_name)) {
        setError('.J-name', '*请输入中文或英文字符，不允许特殊字符');
        $('.J-name').focus();
        return false;
    }
    if (!checkPhone(financer_phone)) {
        setError('.J-phone', '*请输入正确的手机号码');
        $('.J-phone').focus();
        return false;
    }
    if (!checkEmail(financer_email)) {
        setError('.J-email', '*请输入正确的邮箱地址');
        $('.J-email').focus();
        return false;
    }
    return true;
}

function judgeMinValuation(input) {
    var min = input.value,
        max = $('#valuationMax').val();
    if (!checkMoney(min)) {
        setError(input, '*请输入大于0的数字');
        return;
    }
    removeError(input);
}

function judgeMaxValuation(input) {
    var max = input.value,
        min = $('#valuationMin').val(),
        valuation = $('#valuation').val();
    if (!checkMoney(max)) {
        setError(input, '*请输入大于0的数字');
        return;
    }
    if (checkMoney(max) && checkMoney(min) && min != '' && parseInt(min) > parseInt(max)) {
        setError(input, '*请输入大于最小融资的的值');
        return;
    }
    if (parseInt(valuation) < parseInt(max)) {
        setError(input, '*最大融资不能超过估值');
        return;
    }
    removeError('#valuationMin');
    removeError(input);
}

function judgeTime(input) {
    var time = input.value;
    if (!checkPositiveInt(parseInt(time)) || parseInt(time) > 90) {
        setError(input, '*不超过90天');
    } else {
        input.value = parseInt(time);
        removeError(input);
    }
}
