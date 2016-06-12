$(function () {
    initFeedProject();
});

function initFeedProject() {
    setFeedStatus();

    $('.J-focus').click(function () {
        var isLiked = $(this).find('.J-focus-flag').hasClass('liked');
        var unfeed = isLiked ? 1 : '';
        var post = {
            id: $('.J-project').data('target'),
            type: 'project',
            unfeed: unfeed
        };
        var that = this;
        var pre_num = parseInt($('.J-like-num').html());
        API.post('user_feed', post, function (result) {
            if (result.error == 0) {
                var feedNum = parseInt($('.J-feednum').text(), 10);
                if (isLiked) {
                    $(that).find('.J-focus-flag').addClass('unlike').removeClass('liked');
                    $('.J-focus-desc').html('关注我们');

                    feedNum--;
                } else {
                    $(that).find('.J-focus-flag').addClass('liked').removeClass('unlike');
                    $('.J-focus-desc').html('已关注');
                    feedNum++;
                }
                $('.J-feednum').html(feedNum);
            } else {
                alertInfo(result.message);
                if (result.error == 999) {
                    setTimeout(function () {
                        location.href = DIR + 'user/login/';
                    }, 1000);
                }
            }
        });
    })
}

function setFeedStatus() {
    var project_id = $('.J-project').data('target');
    API.post('project_feed', {id: project_id}, function (result) {
        if (result.error == 0) {
            $('.J-focus .J-focus-flag').addClass('liked').removeClass('unlike');
            $('.J-focus-desc').html('已关注');
        }
    });
}