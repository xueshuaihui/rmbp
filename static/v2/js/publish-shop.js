$(function () {

    // define shop component
    window.shopComponent = (function () {
        var shopList = [], shopTpl = tpl('shopTpl'), that = this;

        // add a shop
        that.add = function (data) {
            // assign default value
            if (!data) {
                data = {
                    index: 0,
                    id: '',
                    name: '',
                    address: '',
                    totalfinancing: '',
                    opcircle: '',
                    payoffday: '',
                    shopsize: '',
                    passengers: '',
                    perconsumption: '',
                    permonth: '',
                    perprofits: ''
                };
            }
            var index = shopList.push(data);
            shopList[index - 1].index = index;
            // get element
            var shopElement = $(shopTpl(shopList[index - 1]));
            // remember  element
            shopList[index - 1].dom = shopElement;
            // bind shop element event
            // append to dom
            $('.J-stores-box').append(shopElement);
            block.setIndex("shop");
        };

        // remove a shop
        that.remove = function (index) {
            if (shopList[index]) {
                var shop = shopList[index];
                if (confirm('确定删除该店铺吗？')) {
                    // remove dom when request over
                    window.API.post('remove_shop', {id: shop.id}, function () {
                        // todo remove data from shopList
                        shopList[index].dom.remove();
                        block.setIndex("shop");
                    });
                }
            }
        };

        that.init = function (shopData) {
            if (shopData.length == 0) {
                //shopComponent.add();
            } else {
                for (var i in shopData) {
                    shopComponent.add(shopData[i]);
                }
            }
        }
        return that;
    })();


    // bind event for data-action
    $(document).on('click', '*[data-action]', function () {
        switch ($(this).data('action')) {
            case 'add-shop':
                shopComponent.add();
                break;
            case 'remove-shop':
                shopComponent.remove($(this).data('index'));
                break;
        }
    });
});