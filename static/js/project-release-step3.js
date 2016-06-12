$(function () {

    // define invest component
    window.investComponent = (function () {
        var investList = [], investTpl = tpl('investTpl'), that = this;

        // bind event for single investment
        function bindEvent(ele) {
            // yes or no btn event
            ele.find('.J-yes').click(function () {
                $(this).addClass('select');
                $(this).parent().next().find('.J-no').removeClass('select');
                $(this).parent().parent().next().slideDown('fast');
            });
            ele.find('.J-no').click(function () {
                $(this).addClass('select');
                $(this).parent().prev().find('.J-yes').removeClass('select');
                $(this).parent().parent().next().slideUp('fast');
            });
            // bind img input
            ele.find('.J-gift-img-input').unbind('click').click(function () {
                var has_num = $(this).parent().find('.J-img').length;
                if (has_num > 1) {
                    alertImg('每挡投资最多只能选两张图片哦。');
                } else {
                    $(this).prev().unbind('click').click();
                }
                removeError($(this));
            });
        }

        // add a invest
        that.add = function (data) {
            // assign default value
            if (!data) {
                data = {
                    index: 0,
                    id: '',
                    price: '',
                    returnnum: '',
                    returntime: '',
                    maxnum: 0,
                    message: '',
                    pics: []
                };
            }
            var index = investList.push(data);
            investList[index - 1].index = index;
            // get element
            var investElement = $(investTpl(investList[index - 1]));
            // remember  element
            investList[index - 1].dom = investElement;
            // bind invest element event
            bindEvent(investElement);
            // append to dom
            $('#invest-wrap').append(investElement);
            setInvestmentBox();
        };

        // remove a invest
        that.remove = function (index) {
            if (investList[index]) {
                var invest = investList[index];
                if (confirm('确定删除该档投资吗？')) {
                    // remove dom when request over
                    window.API.post('remove_invest', {id: invest.id}, function () {
                        // todo remove data from investList
                        investList[index].dom.remove();
                        setInvestmentBox();
                    });
                }
            }
        };

        //remove a img
        that.removeImage = function (id) {
            window.API.post('remove_attachment', {id: id}, function () {
                $('#img' + id).remove();

            });
        };

        that.init = function (investData) {
            if (investData.length == 0) {
                investComponent.add();
            } else {
                for (var i in investData) {
                    investComponent.add(investData[i]);
                }
            }
        }
        return that;
    })();


    // bind event for data-action
    $(document).on('click', '*[data-action]', function () {
        switch ($(this).data('action')) {
            case 'add-invest':
                investComponent.add();
                break;
            case 'remove-invest':
                investComponent.remove($(this).data('index'));
                break;
            case 'remove-img':
                investComponent.removeImage($(this).data('id'));
        }
    });
});