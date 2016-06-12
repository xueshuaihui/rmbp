$(function(){

    //    define guy component
    window.guyComponent = (function () {
        var guyList = [], guyTpl = tpl('guyTpl'), that = this;

        // bind event for single guy
        function bindEvent(ele) {
            // bind img input
            ele.find('.J-img-input').unbind('click').click(function () {
                $(this).prev().unbind('click').click();
                removeError($(this));
            });
        }

        // add a guy
        that.add = function (data) {
            // assign default value
            if (!data) {
                data = {
                    index: 0,
                    id: '',
                    truename: '',
                    job: '',
                    intro: '',
                    pic: {}
                };
            }
            var index = guyList.push(data);
            guyList[index - 1].index = index;
            // get element
            var guyElement = $(guyTpl(guyList[index - 1]));
            // remember  element
            guyList[index - 1].dom = guyElement;
            // bind guy element event
            bindEvent(guyElement);
            // append to dom
            $('#guy-wrap').append(guyElement);
            setGuyBox();
        };

        // remove a guy
        that.remove = function (index) {
            if (guyList[index]) {
                var guy = guyList[index];
                if (confirm('确定删除这位小伙伴吗？')) {
                    // remove dom when request over
                    window.API.post('remove_guy', {id: guy.id}, function () {
                        // todo remove data from guyList
                        guyList[index].dom.remove();
                        setGuyBox();
                    });
                }
            }
        };

        that.init = function(guyData){
            if (guyData.length == 0) {
                guyComponent.add();
            } else {
                for (var i in guyData) {
                    guyComponent.add(guyData[i]);
                }
            }
        }
        return that;
    })();
    // bind event for data-action
    $(document).on('click', '*[data-action]', function () {
        switch ($(this).data('action')) {
            case 'add-guy':
                guyComponent.add();
                break;
            case 'remove-guy':
                guyComponent.remove($(this).data('index'));
                break;
        }
    });

});