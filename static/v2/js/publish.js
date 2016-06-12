$(function(){
    if(publishGlobalEvents.length >0) {
        for (var i in publishGlobalEvents) {
            try {
                publishGlobalEvents[i]();
            } catch (e) {
                console.log('publishGlobalEvents[' + i + ']', e);
            }
        }
    }

    $('input[type="text"]').focus(function(){
       var val = parseFloat($(this).val());
        if(val == 0){
            $(this).val('');
        }
    });
    submitCheck.form();
});

var block = {
    setIndex : function(flag){
        var blocks = $('.block-box');

        for(var i = 0; i < blocks.length; i++){
            blocks.eq(i).addClass('J-block-'+(i+1));

            if(flag == 'guy'){
                blocks.eq(i).find('.J-img-input')
                    .attr('id','guyImgInput'+(i+1))
                    .attr('data-previd','guyImgPrev'+(i+1));

                blocks.eq(i).find('.J-upload-img-btn')
                    .attr('id','guyImgPrev'+(i+1))
                    .attr('data-target','guyImgInput'+(i+1));

                imgCrop.init();
            }

        }
    }
};

var calculate = {
    //计算众筹方占股
    raiseShare : function(o){
        var num = parseFloat($(o).val());
        var projectShare = isNaN(num) ? 0 : num;
        if(projectShare<0 || projectShare>100){
            projectShare = 0;
        }
        if(projectShare>100){
            projectShare = 100;
        }

        $('.J-raise-party').val(100-projectShare)
    },

    //计算出让股份
    sellShare :function(){
        var minFinanceObj = $('.J-min-finance') ,
            maxFinanceObj = $('.J-max-finance');
        var valuation = parseFloat($('.J-valuation').val()) ,
            minFinance = parseFloat(minFinanceObj.val() ),
            maxFinance = parseFloat(maxFinanceObj.val());
        if(!(valuation > 0)){
            $('.J-min-share').html(0);
            $('.J-max-share').html(0);
            return;
        }

        if(!(minFinance > 0)){
            $('.J-min-share').html(0);
        }else{
            var minShare = (minFinance / valuation * 100).toFixed(2);
            $('.J-min-share').html(minShare);
        }

        if(!(maxFinance > 0)){
            $('.J-max-share').html(0);
        }else{
            var maxShare = (maxFinance / valuation * 100).toFixed(2);
            $('.J-max-share').html(maxShare);
        }

    },

    //计算创业众筹份数
    raiseNum : function(){
        var perInvest = parseFloat($('.J-per-invest').val());
        var minFinance = parseFloat($('.J-min-finance').val() ),
            maxFinance = parseFloat($('.J-max-finance').val());

        var minNumObj = $('.J-raise-min-num'),
            maxNumObj = $('.J-raise-max-num');

        if(perInvest > 0 && minFinance > 0 && maxFinance > 0){
            minNumObj.val(parseInt(minFinance/perInvest));
            maxNumObj.val(parseInt(maxFinance/perInvest));
        }else{
            minNumObj.val(0);
            maxNumObj.val(0);
        }
    },

    //计算众筹金额
    raiseAmount : function(){
        var existFinanceObj = $('.J-existfinancing'),
            raiseFinanceObj = $('.J-crowd-money'),
            raiseFinanceUnitObj = $('.J-crowd-money-unit');
        var valuation = parseFloat($('.J-valuation').val());
        var existFinance = parseFloat(existFinanceObj.val());

        if(valuation > 0 && existFinance > 0 && !(valuation < existFinance)){
            var money = (valuation-existFinance).toFixed(4);
            if(money > 1 || money == 1){
                money = parseFloat(money).toFixed(2);
                raiseFinanceObj.val(parseFloat(money));
                raiseFinanceUnitObj.html('万元');
            }else{
                money *= 10000;
                money = parseFloat(money).toFixed(2);
                raiseFinanceObj.val(parseFloat(money));
                raiseFinanceUnitObj.html('元');
            }
            this.raiseNumStore();
        }else{
            raiseFinanceObj.val(0);
            raiseFinanceUnitObj.html('元');
        }
    },

    //计算创业众筹份数
    raiseNumStore : function(){
        var valuation = parseFloat($('.J-valuation').val());
        var existFinancing = parseFloat($('.J-existfinancing').val());
        var returnNum = parseFloat($('.J-returnnum').val());
        var singleMoneyObj = $('.J-single-money');
        var singleMoneyUnitObj = $('.J-single-money-unit');

        if(valuation>0 && returnNum>0 && existFinancing>0){
            var raiseFinance = (valuation - existFinancing).toFixed(4);
            var per = parseFloat(raiseFinance/returnNum);
            if( per > 1 || per == 1 ){
                per = parseFloat(per).toFixed(2);
                singleMoneyObj.val(parseFloat(per));
                singleMoneyUnitObj.html('万元');
            }else{
                per *= 10000;
                per = parseFloat(per).toFixed(2);
                singleMoneyObj.val(parseFloat(per));
                singleMoneyUnitObj.html('元');
            }

        }else{
            singleMoneyObj.val(0);
            singleMoneyUnitObj.html('元');
        }
    }

};

var submitCheck = {
    validate : function(){
        $('.J-form-box').validate({
            rules: {
                "project[title]":{
                    required: true,
                    rangelength:[2,20]
                },
                "project[intro]":{
                    required: true,
                    maxlength : 120
                },
                "project[company]":{
                    required: true,
                    rangelength:[2,20]
                },
                "project[email]": {
                    required: true,
                    email: true
                },
                "project[phone]":{
                    required: true,
                    phone: true
                },
                "project[3ndinfo][website]":{
                    required:false,
                    url:true
                },
                "project[3ndinfo][weibo]":{
                    required:false,
                    url:true
                },
                "project[urls][android]":{
                    required:false,
                    url:true
                },
                "project[urls][ios]":{
                    required:false,
                    url:true
                },
                "project[valuation]":{
                    required:true,
                    number : true,
                    pNum : true
                },
                "project[minfinancing]":{
                    required:true,
                    number : true,
                    finance: true,
                    pNum:true
                },
                "project[maxfinancing]":{
                    required:true,
                    number : true,
                    finance: true,
                    pNum:true,
                    maxFinance : true
                },
                "invests[price]":{
                    required:true,
                    number : true,
                    pNum : 0,
                    perInvest : true
                },
                "invests[maxnum]":{
                    digits : true,
                    min : 0,
                    singleMax : true
                },
                "project[financingday]":{
                    required:true,
                    digits : true,
                    range:[1,90]
                },
                "project['region']":{
                    required:true
                },
                "project[existpercent]":{
                    required: true,
                    number : true,
                    range:[0,100]
                },
                "project[existfinancing]":{
                    required: true,
                    number : true,
                    min : 0,
                    finance : true
                },
                "invests[returnnum]":{
                    required: true,
                    digits : true,
                    min : 0
                },
                "invests[id]":{
                    required: true
                },
                "invests[bonuscondition]":{
                    required: true
                },
                "invests[bonuscircle]":{
                    required: true,
                    digits:true,
                    pNum: true
                },
                "invests[bonusexpect]":{
                    required: true,
                    number:true,
                    pNum: true
                },
                "invests[message]":{
                    required: true
                },
                "project[region]":{
                    required: true
                },
                "area_ids":{
                    select: true
                },
                "invests[bonusrate]":{
                    select: true
                },
                "project[status]":{
                    select: true
                },
                "project[balance]":{
                    select: true
                },
                "project[stage]":{
                    select: true
                }
            }
        });
    },

    form : function(){
        var self = this;
        self.validate();

        $('.J-form-box').on('submit',function(){
            return self.stepSubmit(parseInt($('.J-steps-ul').data('currentstep')), $('.J-steps-ul').data('type'));
        });
    },

    stepSubmit : function(step, type){
        switch (step){
            case 1 :
                return this.step1(type);
                break;
            case 2 :
                return this.step2();
                break;
            case 3 :
                return this.step3(type);
                break;
            case 4 :
                return this.step4(type);
                break;
            case 5 :
                return this.step5(type);
                break;
        }
    },

    step1 : function(type){
        var self = this;
        var intro = $('.J-project-intro')[0];
        if(
            check.image('projectBasicImgPrev')
        ){
            if($('.J-weixin-input').val().length){
                if(!check.image('projectWeixinImgPrev')){
                    return false;
                }
            }
            $('.J-basic-img .J-img').val($('#projectBasicImgPrev img').attr('src'));
            $('.J-weixin-img .J-img').val($('#projectWeixinImgPrev img').attr('src'));

            if(type=='tmt'){
                if(self.checkError()){
                    var fieldSelectObj = $('.J-field .select');
                    if(!fieldSelectObj.length){
                        alertImg('请选择所属领域');
                        return false;
                    }
                    var area_ids = '';
                    fieldSelectObj.each(function () {
                        area_ids += $(this).data('id') + ',';
                    });
                    $('#area_ids').val(area_ids);
                }else{
                    return false;
                }
            }
            if(type=='store'){
                var dateYearObj = $('.J-sel-year'),
                    dateMonthObj = $('.J-sel-month'),
                    dateDayObj = $('.J-sel-day');
                var addressProvince = $('#select-province'),
                    addressCity = $('#select-city'),
                    addressCounty = $('#select-county');
                if( !check.select(dateYearObj) ||
                    !check.select(dateMonthObj) ||
                    !check.select(dateDayObj) ||
                    !check.select(addressProvince) ||
                    !check.select(addressCity)||
                    (addressCounty.css('display')!='none' && !check.select(addressCounty))){
                    return false;
                }
            }
            return true;
        }
        return false;

    },
    step2 : function(){
        var desc_detail = UE.getEditor('detailEditor').getContent();
        if(desc_detail.length){
            $(".J-description").val(desc_detail);
            return true;
        }else{
            alertImg('请填写详情介绍');
            return false;
        }

    },
    step3 : function(type){

    },
    step4 : function(type){
        if(type == 'store'){
            var shopBlocks = $('.J-stores-box .J-shop-box');
            for(var i=0; i<shopBlocks.length; i++){
                var shopIndex = i+1;
                if( !check.blank('.J-block-' + shopIndex + ' .J-name') ||
                    !check.blank('.J-block-' + shopIndex + ' .J-address') ||
                    !check.regCheck('.J-block-' + shopIndex + ' .J-totalfinancing') ||
                    !check.regCheck('.J-block-' + shopIndex + ' .J-opcircle') ||
                    !check.regCheck('.J-block-' + shopIndex + ' .J-payoffday') ||
                    !check.regCheck('.J-block-' + shopIndex + ' .J-shopsize') ||
                    !check.regCheck('.J-block-' + shopIndex + ' .J-passengers') ||
                    !check.regCheck('.J-block-' + shopIndex + ' .J-perconsumption') ||
                    !check.regCheck('.J-block-' + shopIndex + ' .J-permonth') ||
                    !check.regCheck('.J-block-' + shopIndex + ' .J-perprofits') ){
                    return false;
                }
            }
            return true;
        }
        if(type == 'tmt'){
            var guyBlocks = $('.J-guys-box .J-guy-box');
            for(var j=0; j<guyBlocks.length; j++){
                var index = j+1;
                if(
                    !check.wordNumCheck('.J-block-' + index + ' .J-name') ||
                    !check.regCheck('.J-block-' + index + ' .J-name') ||
                    !check.wordNumCheck('.J-block-' + index + ' .J-position') ||
                    !check.regCheck('.J-block-' + index + ' .J-position') ||
                    !check.wordNumCheck('.J-block-' + index + ' .J-intro') ||
                    !check.image('guyImgPrev'+index)){
                    return false;
                }
                guyBlocks.eq(j).find('.J-img').val(guyBlocks.eq(j).find('.J-upload-img-btn img').attr('src'));
            }
            var confirm_status = parseInt($('.J-submit-confirm').val());
            if(confirm_status==0){
                return true;
            }
            if(confirm_status==1){
                this.verify();
                return false;
            }
            return false;
        }
    },
    step5 : function(type){
        var self = this;
        if(type=='store'){
            var investMessage = UE.getEditor('detailEditor').getContent();
            if(investMessage.length){
                $(".J-description").val(investMessage);
            }else{
                alertImg('请详细填写其他回报');
                return false;
            }

            var confirm_status = parseInt($('.J-submit-confirm').val());
            if(confirm_status==0){
                return true;
            }
            if(confirm_status==1){
                if(self.checkError()){
                    self.verify();
                    return false;
                }else{
                    return false;
                }
            }

        }
    },

    checkError : function(){
        var errors = $('.J-form-box .error');
        for(var i=0; i<errors.length; i++){
            if(errors.eq(i).css('display') == 'block'){
                return false;
            }
        }
        return true;
    },

    verify : function(){
        $('#myProjectReleaseModel').modal('show');

        $('#myProjectReleaseModel .C-J-confirm').click(function(){
            $('.J-isverify').val(1);
            $('#myProjectReleaseModel').modal('hide');
            $('.J-submit-confirm').click();
        })
    }
};

var selectTime = {
    init : function(){
        var date = [
            parseInt($('.J-sel-year').data('value')),
            parseInt($('.J-sel-month').data('value')),
            parseInt($('.J-sel-day').data('value'))
        ];

        var d = new Date(),
            nowYear = +d.getFullYear();

        $(".J-time-select").date_select({
            "year_from" : 2000,           // 开始年份
            "year_to" : nowYear + 3,             // 结束年份
            "set_date" : date[0] ? date.join('/') : '-1/-1/-1',     // 默认日期
            "default_date" : "On"       // 默认设置开关 [On|Off]
        });
    }
};

var selectAddress = {
    init : function(){
        var self = this;
        var region_ids_arr = [
            $('#select-province').data('id')==0 ? 1 : $('#select-province').data('id'),
            $('#select-city').data('id') ,
            $('#select-county').data('id')
        ];
        var region_ids = region_ids_arr.join(',');

        console.log('region_ids->'+region_ids);
        API.post('query_user_address', {id: region_ids}, function (result) {
            if (result.error == 0) {
                var tmp_address = JSON.parse(result.message);
                self.setProvinceOption(tmp_address[0]);
                self.setCityOption(tmp_address[1]);
                self.setCountyOption(tmp_address[2]);
                if(region_ids!='1,0,0'){
                    self.setCountyStatus(tmp_address[2][0]);
                    self.setSelectInput(region_ids_arr);
                }
            } else {
                alertImg(result.message);
            }
        });

        $('.J-address-select').change(function () {
            check.removeError($(this).parent().parent().find('.J-tip'),$(this));
            var target = $(this).data('target');
            if (target) {
                var province_id = $('#select-province').val();
                var city_id = $('#select-city').val();
                var id_str = target == 'select-city' ? province_id + "," + (parseInt(province_id) + 1) + ",0" : province_id + "," + city_id + ",0";

                API.post('query_user_address', {id: id_str}, function (result) {
                    if (result.error == 0) {
                        var tmp_address = JSON.parse(result.message);
                        self.setCountyStatus(tmp_address[2][0]);

                        if (target == 'select-city') {
                            self.setCityOption(tmp_address[1]);
                        }
                        self.setCountyOption(tmp_address[2]);
                    } else {
                        alertImg(result.message);
                    }
                });
            }
        })
    },

    setSelectInput: function(ids_arr){
        var province_options = $('#select-province option');
        var city_options = $('#select-city option');
        var county_options = $('#select-county option');
        for (var i = 0; i < province_options.length; i++) {
            if (province_options[i].value == ids_arr[0]) {
                province_options[i].selected = true;
            }
        }
        for (var j = 0; j < city_options.length; j++) {
            if (city_options[j].value == ids_arr[1]) {
                city_options[j].selected = true;
            }
        }
        for (var k = 0; k < county_options.length; k++) {
            if (county_options[k].value == ids_arr[2]) {
                county_options[k].selected = true;
            }
        }
    },

    setProvinceOption: function(province){
        $('#select-province').html('')
            .append('<option  value="-1">请选择</option>');
        for (var i = 0; i < province.length; i++) {
            $('#select-province').append('<option  value="' + province[i].id + '">' + province[i].name + '</option>')
        }
    },

    setCityOption: function(city){
        $('#select-city').html('')
            .append('<option  value="-1">请选择</option>');
        for (var j = 0; j < city.length; j++) {
            $('#select-city').append('<option  value="' + city[j].id + '">' + city[j].name + '</option>')
        }
    },

    setCountyOption: function(county){
        $('#select-county').html('')
            .append('<option  value="-1">请选择</option>');
        for (var i = 0; i < county.length; i++) {
            $('#select-county').append('<option  value="' + county[i].id + '">' + county[i].name + '</option>')
        }
    },

    setCountyStatus: function(county_id){
        if (!county_id) {
            $("#select-county").css('display', 'none');
        }
        if (county_id) {
            $("#select-county").css('display', 'block');
        }
    }
};

function initSelectField() {

    $('.J-field .status-flag').bind('click', function () {
        var selected_input = $('.J-field .select');
        var is_selected = $(this).hasClass('select');

        if (is_selected) {
            if (selected_input.length == 1) {
                alertImg('最后一个不能删哦！');
            }else{
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

function checkWordNum(obj) {
    var maxLength = $(obj).data("max");
    var input = obj.value;
    var bytesCount = input.toString().length;
    //var bytesCount = 0;
    //for (var i = 0; i < input.length; i++) {
    //    var c = input.charAt(i);
    //    if (/^[\u0000-\u00ff]$/.test(c)){ //匹配双字节
    //        bytesCount += 1;
    //    }else {
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