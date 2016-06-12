$(function(){
	setIdentifySelect();
	setFeedHotCityStatus();
	setFeedFieldStatus();
	setFeedSlideStatus();
	setImgInput();

	initInputChange();
	initSubmit();
	initHotCity();
});

function initSubmit(){
	$('.J-certificate-submit').click(function(){
		var data = judgeCertificateData();
		if(data.status){
			API.post('certificate', data.data, function (result) {
				if (result.error == 0) {
					alertInfo('投资人申请提交成功，我们会抓紧对您的申请进行审核，审核通过即可进行投资');

					setTimeout(function(){
						alertInfo('即将跳到首页');

						window.location.href = 'http://m.rmbplus.cn/';
					},3000);
				} else {
					alertInfo(result.message);
				}
			})
		}
	})
}

function judgeCertificateData(){
	var truenameObj = $('.J-truename'),
		phoneObj = $('.J-phone'),
		codeObj = $('.J-check-code'),
		emailObj = $('.J-email'),
		atCityObj = $('.J-at-city'),
		imgObj = $('.J-user-box .J-user-img'),
		companyObj = $('.J-company'),
		positionObj = $('.J-position'),
		weixinObj = $('.J-weixin'),
		identifyObj = $('.identity-select-box .select'),
		personalIdentifyVal = $('.J-personal-content .select').data('value'),
		organizationIdentifyObj = $('.J-organization-content input'),
		feedCity = $('.J-feed-City .select'),
		feedField = $('.J-feed-field .select'),
		isCheck = $('.checkbox').is(':checked'),
		data = {
			status: false,
			data: {}
		};

	if(!checkName(truenameObj.val()) || !truenameObj.val().length){
		setWarning(truenameObj);
		alertInfo('请输入中文、英文、数字，不允许特殊字符。');
		return data;
	}else{
		removeWarning(truenameObj);
	}

	if(imgObj.attr('src').match('no.png')) {
		alertInfo('请选择头像。');
		return data;
	}else{
		$('#userAvatarInput').val(imgObj.attr('src'));
	}

	if(!phoneObj.attr('disabled') && !checkPhone(phoneObj.val())){
		setWarning(phoneObj);
		alertInfo('请输入正确的手机号码。');
		return data;
	}else{
		removeWarning(phoneObj);
	}

	if(!phoneObj.attr('disabled') && !codeObj.val().length){
		alertInfo('请输入手机验证码。');
		return data;
	}

	if(!emailObj.attr('disabled') && !checkEmail(emailObj.val())){
		setWarning(emailObj);
		alertInfo('请输入正确的邮箱地址。');
		return data;
	}else{
		removeWarning(emailObj);
	}

	if(!atCityObj.val().length){
		setWarning(atCityObj);
		return data;
	}else{
		removeWarning(atCityObj);
	}

	if(!companyObj.val().length){
		setWarning(companyObj);
		alertInfo('请输入您所在的公司地址。');
		return data;
	}else{
		removeWarning(emailObj);
	}

	if(!positionObj.val().length){
		setWarning(positionObj);
		alertInfo('请输入您的职位名称。');
		return data;
	}else{
		removeWarning(positionObj);
	}

	if(!weixinObj.val().length){
		setWarning(weixinObj);
		alertInfo('请输入您的微信号。');
		return data;
	}else{
		removeWarning(weixinObj);
	}

	if(!identifyObj[0]){
		alertInfo('请选择您的投资身份。');
		return data;
	}

	if(identifyObj.parent().parent().hasClass('J-personal-identity')){
		$('#investor').val(personalIdentifyVal);
	}

	if(identifyObj.parent().parent().hasClass('J-organization-identity') && !organizationIdentifyObj.val().length){
		alertInfo('请填写机构名称。');
		organizationIdentifyObj.focus();
		return data;
	}

	if(!feedCity.length){
		alertInfo('请选择您关注的城市。');
		return data;
	}

	if(!feedField.length){
		alertInfo('请选择您关注的领域。');
		return data;
	}

	if(!isCheck){
		alertInfo('请先阅读相关条款。');
		return data;
	}

	if(identifyObj.parent().parent().hasClass('J-organization-identity') && organizationIdentifyObj.val().length){
		$('#investor').val(organizationIdentifyObj.val());
	}

	var userData = $('#certificateForm').serializeArray();
	data.data = {};
	for(var i in userData){
		data.data[userData[i].name] = userData[i].value;
	}
	data.status = true;
	return data;

}

function setIdentifySelect(){

	initIdentityShow();

	$('.J-organization-identity').click(function(){
		var is_select = $(this).find('.select')[0];

		if(!is_select){
			showIdentify('organization', 'personal');
		}
	})

	$('.J-personal-content .J-content').click(function(){
		var is_select = $(this).find('.select')[0];
		if(!is_select){
			$('.J-personal-content').find('.status-flag').removeClass('select');
			$(this).find('.status-flag').addClass('select');
		}
	})
}

function showIdentify(showFlag, hideFlag){

	$('.J-' + showFlag + '-identity .status-flag').addClass('select');
	$('.J-' + hideFlag + '-identity .status-flag').removeClass('select')

	$('.J-' + hideFlag + '-content').slideUp('fast');
	$('.J-' + showFlag + '-content').slideDown('fast');
}

function initIdentityShow(){
	var isPersonal = $('.J-personal-identity .select')[0];
	var isOrganization = $('.J-organization-identity .select')[0];
	if(isPersonal){
		$('.J-organization-content').slideUp('fast');
		$('.J-personal-content').slideDown('fast');
	}
	if(isOrganization){
		$('.J-personal-content').slideUp('fast');
		$('.J-organization-content').slideDown('fast');
	}

	$('.J-personal-identity').click(function(){
		var is_select = $(this).find('.select')[0];

		if(!is_select){
			showIdentify('personal', 'organization');
		}
	})
}

function setFeedSlideStatus(){
	$('.J-control').click(function(){
		var is_up = $(this).find('.fold-status').hasClass('up');
		var targetObj = $('.' + $(this).data('target')) ;

		if(!is_up){
			$(this).find('.fold-status').removeClass('down').addClass('up');
			targetObj.slideUp('fast');
		}
		if(is_up){
			$(this).find('.fold-status').removeClass('up').addClass('down');
			targetObj.slideDown('fast');
		}
	})
}

function setFeedHotCityStatus(){
	initFeedHotCityStatus();

	$('.J-feed-City input').click(function(){
		var is_select = $(this).hasClass('select');
		var id = $(this).data('id');
		var unfeed = is_select ? 1 : '';
		var post = {
			id: id,
			unfeed: unfeed,
			type: 'region'
		};
		var that = this;

		API.post('user_feed', post, function (result) {
			if (result.error == 0) {
				if(is_select){$(that).removeClass('select');}
				if(!is_select){$(that).addClass('select');}
			} else {
				alertInfo(result.message);
			}
		});
	})
}

function initFeedHotCityStatus(){
	var is_hide = $('.J-feed-City').css('display') == 'none';
	if(is_hide){
		$('.J-city-control .fold-status').removeClass('down').addClass('up');
	}
	if(!is_hide){
		$('.J-city-control .fold-status').removeClass('up').addClass('down');
	}
}

function setFeedFieldStatus(){
	initFeedFieldStatus();

	$('.J-feed-field input').click(function(){
		var is_select = $(this).hasClass('select');
		var unfeed = is_select ? 1 : '';
		var post = {
			id: $(this).data('id'),
			type: 'area',
			unfeed: unfeed
		};
		var that = this;

		API.post('user_feed', post, function (result) {
			if (result.error == 0) {
				if(is_select){
					$(that).removeClass('select')
				}
				if(!is_select){
					$(that).addClass('select')
				}
			} else {
				alertInfo(result.message);
			}
		});
	})
}

function initFeedFieldStatus(){
	var is_hide = $('.J-feed-field').css('display') == 'none';
	if(is_hide){
		$('.J-field-control .fold-status').removeClass('down').addClass('up');
	}
	if(!is_hide){
		$('.J-field-control .fold-status').removeClass('up').addClass('down');
	}
}

function setImgInput(){
	$('.J-user-box .J-user-img').unbind('click').click(function(){
		var target = $(this).data('target');

		$('#'+target).click();
	})
}

function initInputChange(){
	$('.J-truename').change(function(){
		var val = $(this).val();
		if(!checkName(val)){
			setWarning($(this));
		}else{
			removeWarning($(this));
		}
	});

	$('.J-phone').change(function(){
		var val = $(this).val();
		if(!checkPhone(val)){
			setWarning($(this));
		}else{
			removeWarning($(this));
		}
	});

	$('.J-email').change(function(){
		var val = $(this).val();
		if(!checkEmail(val)){
			setWarning($(this));
		}else{
			removeWarning($(this));
		}
	})
}

function initHotCity() {
	var selectIds = $('.J-feed-City').data('ids').toString();
	if(selectIds){
		selectIds = selectIds.split(',');
	}

	API.post('region', {pid: 'hot'}, function (result) {
		if (result.error == 0) {
			var cities = JSON.parse(result.message);
			$('.J-feed-City').html('');
			for (var i = 0; i < cities.length; i++) {
				var tplHtml_1 = tpl('hotCityModal', {name: cities[i].name, code: cities[i].code, id: cities[i].id});
				$('.J-feed-City').append(tplHtml_1);

				if(selectIds){
					for(var j = 0;j<selectIds.length;j++){
						if(cities[i].id == selectIds[j]){
							$('.J-feed-City input').eq(i).addClass('select');
						}
					}
				}
			}

			setFeedHotCityStatus();
		} else {
			alertInfo(result.message);
		}
	})
}

function cropImg(o){
	//if (!o.value.match(/.jpg|.gif|.png|.bmp/i)) {
	//	alert('图片格式无效！');
	//	return false;
	//}
	var file = o.files[0];
	var reader = new FileReader();
	reader.onload = function() {
		var img = new Image();
		//img.src = reader.result;
		initCropImgBox(reader.result);
	};
	reader.readAsDataURL(file);
	$(o).replaceWith('<input type="file" class="hide" id="userHeadInput" onchange="cropImg(this)">');

	$('.J-user-box').css('display','none');
	$('.J-crop-box').css('display','block');
}

function initCropImgBox(imgSource){
	var boxWidth = $('#imgBoxSize').width()-20;
	var boxHeight = boxWidth;
	var imgObj = new Image();
	imgObj.src = imgSource;

	var cropzoom = $('#crop_container').cropzoom({
		width: boxWidth,
		height: boxHeight,
		bgColor: '#fff',
		overlayColor: '#333',
		enableRotation: false,
		enableZoom: true,
		zoomSteps: 10,
		selector: {
			centered: true,
			startWithOverlay: true,
			borderColor: 'blue',
			borderColorHover: 'red'
		},
		image: {
			source: imgSource,
			width: imgObj.width,
			height: imgObj.height,
			minZoom: 0,
			maxZoom: 200
		}
	});

	$('#zoomContainer.vertical').css('height', boxHeight);
	$('#zoomSlider.vertical').css('height', boxHeight - 42).css('margin','5px auto');

	$('#crop').off('click').click(function() {
		cropzoom.send(DIR+'mapi/user_avatar/', 'POST', {FORM_HASH:FORM_HASH}, function(rta) {
			var img_src = rta + '?t=' + Math.random();
			$('.J-user-img').attr('src', img_src);
			$('.J-crop-box').css('display','none');
			$('.J-user-box').css('display','block');
			setImgInput();
		});
	});
	$('.J-cancel-btn').off('click').click(function() {
		cropzoom.restore();
		$('.J-crop-box').css('display','none');
		$('.J-user-box').css('display','block');
	})
}