$(window).ready(function () {
    InvesterForm.init();
});

var InvesterForm = {
    init: function () {
        this.validate();
        this.cropImageBind();
        this.identityEvent();
        this.submitEvent();
        this.cityInput();
        this.initFieldInput();
        this.initCheckbox();
        initFetchCheckCode();
    },

    validate: function () {
        $('form').validate({
            rules: {
                "user[truename]": {
                    required: true
                },
                "user[phone]": {
                    required: true,
                    phone: true
                },
                "user[email]": {
                    required: true,
                    email: true
                },
                "user[region]": {
                    required: true
                },
                "user[company]": {
                    required: true
                },
                "user[job]": {
                    required: true
                },
                "user[account3nd][weixin]": {
                    required: true
                }
            }
        });
    },

    cropImageBind: function () {
        $('.J-upload-logo').unbind('click').click(function () {
            var target = $(this).data('target');
            $('#' + target).click();
        });
    },

    identityEvent: function () {
        $('.J-identity-box ul li .status-flag').unbind('click').bind('click', function () {
            var target = $(this).data('target');
            $('.J-identity-box ul li .status-flag').removeClass('select');
            $('.J-identity-box .content').hide();

            $(this).addClass('select');
            $('.J-identity-box .content[data-id=' + target + ']').show();
        });

        $('.J-identity-box .personal-content .status-flag').click(function () {
            $(this).parent().parent().find('.status-flag').removeClass('select');
            $(this).addClass('select');
        })
    },

    submitEvent: function () {
        var self = this;
        $('.J-submit').on('click', function () {
            $('form').submit(function () {
                return false;
            }).submit();

            var investorData = self.checkInvestorData();
            if (investorData.status) {
                API.post('certificate', investorData.data, function (result) {
                    if (result.error == 0) {
                        $('#certifySuccessModel').modal('show');
                    } else {
                        alertImg(result.message);
                    }
                })
            }
        })
    },

    checkInvestorData: function () {
        var certifyObj = $('#investorCertification');
        var img_src = $('a.J-upload-logo img').attr('src'),
            is_organization = certifyObj.find('.J-identity-box li .select').data('target') == 'organization-content',
            organization = certifyObj.find('.organization-content input').val(),
            focus_cities = certifyObj.find('.J-focus-city').find('.J-city'),
            focus_fields = certifyObj.find('.J-field').find('.select'),
            is_check_agree_list = $('.J-agree-list .status-flag').hasClass('select'),
            investor = '';

        var investorData = {
            status: false,
            data: {}
        };

        if (certifyObj.find('input.error').length ||
            ($('.J-check-code').length && !check.blank('.J-check-code'))) {
            return investorData;
        }

        if (img_src.match('no.png')) {
            alertImg('请选择个人头像');
            scrollToTop();
            return investorData;
        }

        if (is_organization) {
            investor = $('.organization-content input').val();
            if (!investor) {
                alertImg('请填写机构名称');
                return investorData;
            }
        }
        if (!is_organization) {
            var select = $('.personal-content .select');
            if (select[0]) {
                investor = select.data('value');
            }
            if (!select[0]) {
                alertImg('请选择您的投资身份');
                return investorData;
            }
        }

        if (!focus_cities.length) {
            alertImg('请选择您关注的城市');
            return investorData;
        }

        if (!focus_fields.length) {
            alertImg('请选择您关注的领域');
            return investorData;
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
        for (var i in userData) {
            investorData.data[userData[i].name] = userData[i].value;
        }
        investorData.status = true;
        return investorData;
    },

    cityInput: function () {
        var self = this;
        $('#city-input').click(function () {
            $('#citySelectModal').modal('show');
        });

        self.initCitySelect();
        self.initHotCity();
        self.initHotCityConfirm();
    },

    initCitySelect: function () {
        $('.J-select-city-box .J-city input').unbind('click').click(function () {
            var is_selected = $(this).hasClass('select');

            if (is_selected) {
                $(this).removeClass('select');
            }
            if (!is_selected) {
                $(this).addClass('select');
            }
        });
    },

    initCityDelete: function () {
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
    },

    initHotCity: function () {
        var self = this;
        var selectIds = $('#city-input').data('ids').toString();
        if (selectIds) {
            selectIds = selectIds.split(',');
        }
        API.post('region', {pid: 'hot'}, function (result) {
            if (result.error == 0) {
                var cities = JSON.parse(result.message);

                for (var i = 0; i < cities.length; i++) {
                    var tplHtml_1 = tpl('hotCityModal', {name: cities[i].name, code: cities[i].code, id: cities[i].id});
                    $('.J-select-city-box .J-city').append(tplHtml_1);

                    if (selectIds) {
                        for (var j = 0; j < selectIds.length; j++) {
                            if (cities[i].id == selectIds[j]) {
                                $('.J-select-city-box .J-city input').eq(i).addClass('select');
                                var tplHtml_2 = tpl('cityShowModal', {val: cities[i].name, id: selectIds[j]});
                                $('#city-input').before(tplHtml_2);
                            }
                        }
                    }
                }
                self.initCitySelect();
                self.initCityDelete();
            } else {
                alertImg(result.message);
            }
        })
    },

    initHotCityConfirm: function () {
        var self = this;
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
                    self.initCityDelete();
                    $('#citySelectModal').modal('hide');
                } else {
                    alertImg(result.message);
                }
            })
        });
    },

    initFieldInput: function () {
        $('.J-field .status-flag').click(function () {
            var selectField = $('.J-field .select');
            var is_select = $(this).hasClass('select');
            if (!is_select && selectField.length > 4) {
                alertImg('不要贪心哦，您关注的够多了');
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
    },

    initCheckbox: function () {
        $('.J-agree-list .status-flag').click(function () {
            var is_select = $(this).hasClass('select');
            if (is_select) {
                $(this).removeClass('select');
            }
            if (!is_select) {
                $(this).addClass('select');
                $('.error-box').hide();
            }
        });
    }

};