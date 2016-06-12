$(function(){
	setPayWaySelect();

	setAddressInput();
	setAddressEdit();
	setInvestConfirmBtn();
	setInvestTotalMoney()

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
		var pre_num = parseInt($('.J-money-num-input').val()) ;
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

function setInvestConfirmBtn(){
	$('.J-confirm').click(function () {
		if ($('.J-checkbox').is(':checked')) {
			return true;
		} else {
			alertInfo('请先阅读相关条款');
			return false;
		}
	});
}

function setAddressInput(){
	$('.J-new-address').click(function(){
		$('.J-addr-info-input').removeClass('hide');
		$('.J-return-confirm-box').addClass('hide');
	});

	API.post('query_user_address', {id: "1,0,0"}, function (result) {
		if (result.error == 0) {
			var tmp_address = JSON.parse(result.message);
			addProvinceSelectOption(tmp_address[0]);
			addCitySelectOption(tmp_address[1]);

			setCountSelectStatus(tmp_address[2][0]);
		} else {
			alertInfo(result.message);
		}
	});
}

function changeAddressOption(targetID){
	var target = $('#'+targetID).data('target');
	if (target) {
		var province_id = $('#select-province').attr('data-id');
		var city_id = $('#select-city').attr('data-id');
		var id_str = target == 'select-city' ? province_id + "," + (parseInt(province_id) + 1) + ",0" : province_id + "," + city_id + ",0";

		API.post('query_user_address', {id: id_str}, function (result) {
			if (result.error == 0) {
				var tmp_address = JSON.parse(result.message);
				setCountSelectStatus(tmp_address[2][0]);

				if (target == 'select-city') {
					addCitySelectOption(tmp_address[1]);
					var firstCityOption = $('.J-city-options input').eq(0);
					$('#select-city').val(firstCityOption.val()).attr('data-id', firstCityOption.data('id'));
				}
				addCountySelectOption(tmp_address[2]);
				var firstCountyOption = $('.J-county-options input').eq(0);
				$('#select-county').val(firstCountyOption.val()).attr('data-id', firstCountyOption.data('id'));

				setAddressSelect();
			} else {
				alertInfo(result.message);
			}
		});
	}
}

function setAddressEdit(){
	$('.J-cancel-btn').click(function(){
		$('.J-return-confirm-box').removeClass('hide');
		$('.J-addr-info-input').addClass('hide');
	});

	$('.J-save-btn').click(function(){
		var name = $('.J-addr-info-input .J-name').val(),
			province_id = $('#select-province').data('id'),
			city_id = $('#select-city').data('id'),
			county_id = $('#select-county').data('id'),
			address_detail = $('.J-addr-info-input .J-address-detail').val(),
			phone = $('.J-addr-info-input .J-phone').val(),
			id = $('.J-addr-info-input').data('id');

		if (!name || !address_detail || !phone) {
			alertInfo('请将信息填写完整');
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
					alertInfo(result.message);
				}
			});
		}
	});

	initFixAddressBtn();
	initDeleteAddress();
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
				$('.J-addr-info-input .J-name').val($('.J-addr-info-box .J-name').text());
				$('.J-addr-info-input .J-phone').val($('.J-addr-info-box .J-phone').text());
				$('.J-addr-info-input .J-address-detail').val($('.J-addr-info-box .J-address-detail').text());

				$('.J-addr-info-input').data('id', id).removeClass('hide');
				$('.J-return-confirm-box').addClass('hide');
				$('.J-addr-info-input .J-cancel-btn').attr('data-target', 'edit');
			} else {
				alertInfo(result.message);
			}
		});
	});
}

function setAddressSelectInput(ids_arr) {
	var province_options = $('.J-province-options input');
	var city_options = $('.J-city-options input');
	var county_options = $('.J-county-options input');
	for (var i = 0; i < province_options.length; i++) {
		if (province_options.eq(i).attr('data-id') == ids_arr[0]) {
			$('#select-province').val(province_options.eq(i).val()).attr('data-id', province_options.eq(i).attr('data-id'))
		}
	}
	for (var j = 0; j < city_options.length; j++) {
		if (city_options.eq(j).attr('data-id') == ids_arr[1]) {
			$('#select-city').val(city_options.eq(j).val()).attr('data-id', city_options.eq(j).attr('data-id'))
		}
	}
	for (var k = 0; k < county_options.length; k++) {
		if (county_options.eq(k).attr('data-id') == ids_arr[2]) {
			$('#select-county').val(county_options.eq(k).val()).attr('data-id', county_options.eq(k).attr('data-id'))
		}
	}
}

function initDeleteAddress() {
	$('.J-addr-info-box .J-delete').click(function () {
		if(confirm('确定删除该地址吗？')){
			var id = $(this).data('id');
			API.post('remove_user_address', {id: id}, function (result) {
				if (result.error == 0) {
					$('.J-new-address').removeClass('hide');
					$('.J-addr-info-box').addClass('hide');
				} else {
					alertInfo(result.message);
				}
			})
		}
	});
}


function setAddressSelect(){
	$('.J-address-select').unbind('click').click(function(){
		var source = $(this).data('source');
		$('.'+source).css('display','block');
	});

	$('.J-address-options input').unbind('click').click(function(){
		var target = $(this).parent().data('target');
		var val = $(this).val();
		var id = $(this).data('id');
		var previousVal = $('#'+target).val();

		$('#'+target).val(val).attr('data-id',id);
		if(previousVal != val){
			changeAddressOption(target);
		}
		$(this).parent().css('display','none');
	})
}

function setPayWaySelect(){
	$('.J-select-btn').click(function(){
		$('.J-select-btn .status-flag').removeClass('select');

		$(this).find('.status-flag').addClass('select');
	})

	$('.J-pay-btn').click(function(){
		var selectObj = $('.J-select-btn .select');
		var is_select = selectObj[0];
		if(is_select){
			if(selectObj.data('target') == 'offline-pay'){
				location.href = "/order/account_info/";
			}
		}
		if(!is_select){
			alertInfo('请先选择支付方式');
		}
	})
}

function addProvinceSelectOption(province) {
	$('.J-province-options').html('');
	for (var i = 0; i < province.length; i++) {
		$('.J-province-options').append('<input type="button"  data-id="' + province[i].id + '"  value="' + province[i].name + '">')
	}
	setAddressSelect();
}

function addCitySelectOption(city) {

	$('.J-city-options').html('');
	for (var i = 0; i < city.length; i++) {
		$('.J-city-options').append('<input type="button"  data-id="' + city[i].id + '"  value="' + city[i].name + '">')
	}
	setAddressSelect();
}

function addCountySelectOption(county) {
	$('.J-county-options').html('');
	for (var i = 0; i < county.length; i++) {
		$('.J-county-options').append('<input type="button"  data-id="' + county[i].id + '"  value="' + county[i].name + '">')
	}
	setAddressSelect();
}

function setCountSelectStatus(county_id) {
	if (!county_id) {
		$(".J-county-box").css('display', 'none');
	}
	if (county_id) {
		$(".J-county-box").css('display', 'block');
	}
}

function reset_address() {
	$('.J-addr-info-input .J-name').val('');
	$('.J-addr-info-input .J-address-detail').val('');
	$('.J-addr-info-input .J-phone').val('');
}