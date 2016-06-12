$.validator.addMethod("phone",function(value,element,params){
    return check.reg['phone'].test(value);
},"请输入正确的手机号码");

$.validator.addMethod("finance",function(value,element,params){
    var valuation = parseFloat($('.J-valuation').val());
    return !(valuation < parseFloat(value) );
},"请输入一个不大于融资总额的值");

$.validator.addMethod("maxFinance",function(value,element,params){
    var minFinance = parseFloat($('.J-min-finance').val());
    return minFinance < parseFloat(value);
},"请输入一个大于最低融资额的值");

$.validator.addMethod("perInvest",function(value,element,params){
    var minFinance = parseFloat($('.J-min-finance').val());
    return minFinance > parseFloat(value);
},"请输入一个小于最低融资额的值");

$.validator.addMethod("singleMax",function(value,element,params){
    var isTmt = $('.J-steps-ul').data('type') == 'tmt';
    var minRaiseNum = isTmt ? parseFloat($('.J-raise-min-num').val()) : parseFloat($('.J-returnnum').val());
    return minRaiseNum > parseFloat(value);
},"请输入一个小于众筹份数的值");

$.validator.addMethod("pNum",function(value,element,params){
    return parseFloat(value) > 0;
},"请输入一个大于 0 的值");

$.validator.addMethod("select",function(value,element,params){
    return parseFloat(value) > -1;
},"请选择");

jQuery.extend(jQuery.validator.messages, {
    required: "不能为空",
    email: "请输入正确格式的电子邮件",
    url: "请输入合法的网址",
    number: "请输入合法的数字",
    digits: "请输入整数",
    maxlength: jQuery.validator.format("请输入一个 长度最多是 {0} 的内容"),
    minlength: jQuery.validator.format("请输入一个 长度最少是 {0} 的内容"),
    rangelength: jQuery.validator.format("请输入一个长度介于 {0} 和 {1} 之间的内容"),
    range: jQuery.validator.format("请输入一个介于 {0} 和 {1} 之间的值"),
    max: jQuery.validator.format("请输入一个最大为 {0} 的值"),
    min: jQuery.validator.format("请输入一个最小为 {0} 的值")
});

var check = {
    message : {
        only_chinese_english : "请输入中文或英文字符，不允许特殊字符",
        blank : "不能为空",
        p_int : "请输入正整数",
        p_num : "请输入大于0的数",
        select : "请选择"
    },
    reg : {
        phone : /^1[3|4|5|7|8]\d{9}$/,
        double_byte : /^[\u0000-\u00ff]$/,
        only_chinese_english : /^\+?[a-zA-z\u4e00-\u9fa5 ]*$/,
        p_int : /^[1-9]*[1-9][0-9]*$/,
        p_num : /^\d+(?=\.{0,1}\d+$|$)/
    },

    setError : function(error_obj , val_obj, msg){
        error_obj.removeClass('warning-tip')
            .addClass('error-tip')
            .find('.J-tip-content').html(msg);

        val_obj.addClass('error')
            .focus();
    },

    removeError : function(error_obj , val_obj){
        error_obj.removeClass('warning-tip')
            .removeClass('error-tip')
            .find('.J-tip-content').html('');

        val_obj.removeClass('error');
    },

    regCheck : function(o){
        var reg_type = $(o).data('regtype');
        var reg = this.reg[reg_type ];
        var error_obj = $(o).parent().parent().find('.J-tip');
        if(reg_type=='p_num'){
            if(!isNaN($(o).val()) && parseFloat($(o).val())>0){
                this.removeError(error_obj, $(o));
                return true;
            }
            this.setError(error_obj, $(o), this.message[reg_type]);
            return false;
        }

        if(reg.test($(o).val())){
            this.removeError(error_obj, $(o));
            return true;
        }else{
            this.setError(error_obj, $(o), this.message[reg_type]);
            return false;
        }
    },

    wordNumCheck : function(o){
        var error_obj = $(o).parent().parent().find('.J-tip');
        var val = $(o).val();
        var min = parseInt($(o).data('min'));
        var max = parseInt($(o).data('max'));
        var bytesCount = val.toString().length;

        if(bytesCount==0){
            this.setError(error_obj, $(o), this.message.blank);
            return false;
        }

        if(bytesCount < min || bytesCount > max){
            this.setError(error_obj, $(o), '请输入' + min + '-' + max + '个字的内容');
            return false;
        }else{
            this.removeError(error_obj, $(o));
            return true;
        }
    },

    image : function(id){
        var o = '#' + id;
        var error_obj = $(o).parent().parent().find('.J-tip');
        var hasImg = $(o).find('img').length;

        if(!hasImg){
            this.setError(error_obj, $(o), '请选择图片');
            return false;
        }else{
            this.removeError(error_obj, $(o));
            return true;
        }
    },

    blank : function(o){
        var val = $(o).val();
        var error_obj = $(o).parent().parent().find('.J-tip');
        if(val.length){
            this.removeError(error_obj, $(o));
            return true;
        }else{
            this.setError(error_obj, $(o), this.message['blank']);
            return false;
        }
    },

    select : function(o){
        var val = $(o).val();
        var error_obj = $(o).parent().parent().find('.J-tip');
        if(val > -1){
            this.removeError(error_obj, $(o));
            return true;
        }else{
            this.setError(error_obj, $(o), this.message['select']);
            return false;
        }
    }
};