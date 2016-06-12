$(function () {
    window.changeEvent = function (e) {
        var post = $(this).data('post'),
            reload = $(this).data('reload'),
            callback = $(this).data('callback');
        if (post == 'value') {
            post = $(this).val();
        } else if (post == 'form') {
            var data = $(this).serializeArray();
            post = '';
            for (var i in data) {
                post += '/' + data[i].name + '/' + data[i].value + '';
            }
        }
        post = post.split('\/');
        var postQuery = '';
        var url = $(this).attr('href') ? $(this).attr('href') : $(this).data('url');
        if (!url) {
            url = $(this).attr('action');
        }
        if (post.length == 1) {
            postQuery = post[0];
        } else {
            var start = 0;
            if (post[0] == '') {
                start = 1;
            }
            for (; start < post.length; start += 2) {
                if (start + 1 < post.length) {
                    postQuery += post[start] + '=' + post[start + 1] + '&';
                } else {
                    postQuery += post[start] + '&';
                }
            }
        }
        $.post(url, postQuery + '&ajax=1', function (data) {
            if (data) {
                eval('data=' + data);
                showAlertInfo(data.message);
                if (reload) {
                    location.reload();
                }
                callback && eval(callback+'(true)');
            } else {
                showAlertInfo('系统出错');
                callback && eval(callback+'(false)');
            }
        });
        e.preventDefault();
        e.stopPropagation();
        return false;
    };
    console.log(window.changeEvent);
    $('a[data-post]').on('click', changeEvent);
    $('form[data-post]').on('submit', changeEvent);
    $('select[data-post]').on('change', changeEvent);

});


function showAlertInfo(info) {
    $('.J-alert').html(info).css('display', 'block');
    setTimeout(function () {
        $('.J-alert').css('display', 'none');
    }, 2000)
}