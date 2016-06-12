$(function () {
    $('.J-confirm').on('click', function () {
        if ($('.check-box input').is(':checked')) {
            $(this).prev().css('display', 'none');

            return true;
        } else {
            $(this).parent().find('.error-box').html('请先阅读相关条款').css('display', 'block');
            return false;
        }
    });

    initAddressBox();

    setInvestTotalMoney();

});

function setInvestTotalMoney(){
    setInvestMoneyNum();
    setMoneyNumBtnStatus();
    initAddNum();
    initDescNum();

    $('.J-money-num-input').change(function(){
        var num = parseInt($(this).val());
        if(num < 1 || isNaN(num)){
            $(this).val(1);
        }else{
            $(this).val(num);
        }
        setInvestMoneyNum();
    });
}

function initAddNum(){
    $('.J-add').click(function(){
        var pre_num = parseInt($('.J-money-num-input').val());
        var max = parseInt($('.J-money-num-input').data('maxnum'));
        if( (max!=0 && pre_num < max) || max == 0 ){
            var num = pre_num + 1;
            $('.J-money-num-input').val(num);
            setInvestMoneyNum();
        }
        setMoneyNumBtnStatus();
    })
}

function initDescNum(){
    $('.J-desc').click(function(){
        var pre_num = parseInt($('.J-money-num-input').val()) ;
        if(pre_num>1){
            var num = pre_num - 1;
            $('.J-money-num-input').val(num);
            setInvestMoneyNum();
        }
        setMoneyNumBtnStatus();
    })
}

function setMoneyNumBtnStatus(){
    var num = parseInt($('.J-money-num-input').val()) ;
    var max = parseInt($('.J-money-num-input').data('maxnum'));

    if((max != 0 && num < max) || max == 0 ){
        $('.J-add').removeClass('add-disabled').addClass('add-active');
    }else{
        $('.J-add').removeClass('add-active').addClass('add-disabled');
    }

    if(num < 2){
        $('.J-desc').removeClass('desc-active').addClass('desc-disabled');
    }else{
        $('.J-desc').removeClass('desc-disabled').addClass('desc-active');
    }
}

function setInvestMoneyNum(){
    var per = parseFloat( $('.J-per-money').html());
    var num = parseFloat($('.J-money-num-input').val());
    var total = parseFloat( per * num);

    var per_gufen = parseFloat($('.J-gufen').data('pershare'));
    var totle_gufen = per_gufen*num;

    $('.J-user-invest-money').html(total);
    $('.J-gufen').html(totle_gufen.toFixed(2));
}

function initAddressBox() {
    setAddressInput();
    initDeleteAddress();
    initNewAddress();
    initFixAddressBtn();
}

function initNewAddress() {
    $('.J-new-address').click(function () {
        $(this).css('display', 'none');
        reset_address();
        setAddressInput();
        $('.J-addr-info-input').css('display', 'block');
        $('.J-addr-info-input .C-J-cancel-btn').attr('data-target', 'new');
    });

    $('.J-addr-info-input .C-J-cancel-btn').click(function () {
        var target = $(this).data('target');
        if (target == 'edit') {
            $('.J-addr-info-input').css('display', 'none');
            $('.J-addr-info-box').css('display', 'block');
        }
        if (target == 'new') {
            $('.J-addr-info-input').css('display', 'none');
            $('.J-new-address').css('display', 'block');
        }
    });

    $('.C-J-save-btn').click(function () {
        var name = $('.J-addr-info-input .J-name').val(),
            province_id = $('#select-province').val(),
            city_id = $('#select-city').val(),
            county_id = $('#select-county').val(),
            address_detail = $('.J-addr-info-input .J-address-detail').val(),
            phone = $('.J-addr-info-input .J-phone').val(),
            id = $('.J-addr-info-input').data('id');

        if (!name || !address_detail || !phone) {
            $('.J-addr-info-input .error-box').html('请将信息填写完整').css('display', 'block');
        } else {
            var address = {
                county_id: county_id,
                province_id: province_id,
                city_id: city_id,
                name: name,
                phone: phone,
                address: address_detail
            };
            API.post('user_address', {id: id, address: address}, function (result) {
                if (result.error == 0) {
                    location.reload(true);
                } else {
                    alertImg(result.message);
                }
            });
        }
    });
}

function initFixAddressBtn() {
    $('.J-addr-info-box .J-fix').click(function () {
        var id = $(this).data('id');
        var region_ids = $(this).data('region');
        var region_ids_arr = region_ids.split(',');

        reset_address();

        API.post('query_user_address', {id: region_ids}, function (result) {
            if (result.error == 0) {
                var tmp_address = JSON.parse(result.message);
                addProvinceSelectOption(tmp_address[0]);
                addCitySelectOption(tmp_address[1]);
                addCountySelectOption(tmp_address[2]);
                setCountSelectStatus(tmp_address[2][0]);

                setAddressSelectInput(region_ids_arr);
                $('.J-addr-info-input').attr('data-id', id);
                $('.J-addr-info-input .J-name').val($('.J-addr-info-box .J-name').text());
                $('.J-addr-info-input .J-phone').val($('.J-addr-info-box .J-phone').text());
                $('.J-addr-info-input .J-address-detail').val($('.J-addr-info-box .J-address-detail').text());

                $('.J-address-box .J-addr-info-input').css('display', 'block');
                $('.J-address-box .J-addr-info-box').css('display', 'none');
                $('.J-addr-info-input .C-J-cancel-btn').attr('data-target', 'edit');
            } else {
                alertImg(result.message);
            }
        });
    });
}

function initDeleteAddress() {
    $('.J-addr-info-box .J-delete').click(function () {
        if(confirm('确定删除该地址吗？')){
            var id = $(this).data('id');
            API.post('remove_user_address', {id: id}, function (result) {
                if (result.error == 0) {
                    $('.J-address-box .J-new-address').css('display', 'block');
                    $('.J-address-box .J-addr-info-box').css('display', 'none');
                } else {
                    alertImg(result.message);
                }
            })
        }
    });
}

function setAddressSelectInput(ids_arr) {
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

}

function setAddressInput() {
    API.post('query_user_address', {id: "1,0,0"}, function (result) {
        if (result.error == 0) {
            var tmp_address = JSON.parse(result.message);
            addProvinceSelectOption(tmp_address[0]);
            addCitySelectOption(tmp_address[1]);

            setCountSelectStatus(tmp_address[2][0]);
        } else {
            alertImg(result.message);
        }
    });

    $('.J-address-select').change(function () {
        var target = $(this).data('target');
        if (target) {
            var province_id = $('#select-province').val();
            var city_id = $('#select-city').val();
            var id_str = target == 'select-city' ? province_id + "," + (parseInt(province_id) + 1) + ",0" : province_id + "," + city_id + ",0";

            API.post('query_user_address', {id: id_str}, function (result) {
                if (result.error == 0) {
                    var tmp_address = JSON.parse(result.message);
                    setCountSelectStatus(tmp_address[2][0]);

                    if (target == 'select-city') {
                        addCitySelectOption(tmp_address[1]);
                    }
                    addCountySelectOption(tmp_address[2]);
                } else {
                    alertImg(result.message);
                }
            });
        }
    })
}

function addProvinceSelectOption(province) {
    $('#select-province').html('');
    for (var i = 0; i < province.length; i++) {
        $('#select-province').append('<option  value="' + province[i].id + '">' + province[i].name + '</option>')
    }
}

function addCitySelectOption(city) {
    $('#select-city').html('');
    for (var j = 0; j < city.length; j++) {
        $('#select-city').append('<option  value="' + city[j].id + '">' + city[j].name + '</option>')
    }
}

function addCountySelectOption(county) {
    $('#select-county').html('');
    for (var i = 0; i < county.length; i++) {
        $('#select-county').append('<option  value="' + county[i].id + '">' + county[i].name + '</option>')
    }
}

function setCountSelectStatus(county_id) {
    if (!county_id) {
        $("#select-county").css('display', 'none');
    }
    if (county_id) {
        $("#select-county").css('display', 'block');
    }
}

function reset_address() {
    $('.J-addr-info-input .error-box').css('display', 'none');

    $('.J-addr-info-input .J-name').val('');
    $('.J-addr-info-input .J-address-detail').val('');
    $('.J-addr-info-input .J-phone').val('');
}