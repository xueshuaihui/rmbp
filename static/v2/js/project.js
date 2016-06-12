$(function(){
    initProjectLine();
    initSupportBtnStatus();
    initFeedLikeBtn();
    initDynamic();
    initDialog();
    window.onscroll = function(){
        setTopTabStatus();
    };
});

function initProjectLine(){
    var lineNum = parseInt($('.J-project').data('status-line'))/100 ;
    var flag = $('.J-project').data('flag');
    setProjectLineCircle($('.J-status-line'),lineNum, flag , '#666');
}

function initSupportBtnStatus(){
    var supportBtnObj = $('.J-support-btn');
    var statusID = supportBtnObj.data('status');
    var href = supportBtnObj.data('href');
    if(statusID == '3'){
        supportBtnObj.attr('disabled',false).removeClass('disabled-btn');
        supportBtnObj.click(function(){
            var id = $('.J-project').data('target');
            var isauth = $(this).data('isauth');
            var islogin = $('.J-logined').length;
            var that = this;

            if(!islogin){
                alertImg('您还未登录，请先登录后再操作');
                return;
            }

            if(isauth == -1 || isauth == 0){
                alertImg('您还未认证投资人，请先认证');
                setTimeout(function(){
                    location.href = '/user/certificate/'
                },1000);
                return;
            }

            if(isauth == 1){
                alertImg('您的认证投资人申请正在审核中，审核通过后，方可进行投资');
                return;
            }

            API.post('/get_unpay',{project_id: id},function(result){
                if(result.error == 0){
                    location.href = $(that).data('href');
                }else{
                    alertImg(result.message);
                    setTimeout(function(){
                        location.href = '/user/invested/'
                    },1000)
                }
            })
        })
    }else{
        supportBtnObj.attr('disabled',true).addClass('disabled-btn');
    }
}

function setTopTabStatus(){
    var scrollTop = document.body.scrollTop;
    var top_height = $('.J-project').height() + 65;
    var hasFixStyle = $('.J-detail-tab-box').hasClass('C-fixed');

    if(scrollTop < top_height && hasFixStyle){
        $('.J-detail-tab-box').removeClass('C-fixed');
        $('.J-detail-content').css('margin-top',0);
    }
    if(! (scrollTop < top_height) && !hasFixStyle){
        $('.J-detail-tab-box').addClass('C-fixed');
        $('.J-detail-content').css('margin-top','65px');
    }

    var supportBtn = $('.J-support-btn');
    if(supportBtn[0]){
        var hasSupportFixStyle = supportBtn.hasClass('C-support-fixed');
        var support_btn_top = $('.J-support-btn-box').offset().top;
        if(support_btn_top - scrollTop - 65 < 0 && !hasSupportFixStyle){
            $('.J-support-btn').addClass('C-support-fixed');
        }
        if(!(support_btn_top - scrollTop - 65 < 0) && hasSupportFixStyle){
            $('.J-support-btn').removeClass('C-support-fixed');
        }
    }
}

function initFeedLikeBtn() {
    setFeedStatus();
    $('.J-focus.like-box')
        .hover(function(){
            $(this).addClass('pulse');
        }, function(){
            $(this).removeClass('pulse');
        })
        .click(function () {
            var isLiked = $(this).hasClass('liked');
            var unfeed = isLiked ? 1 : '';
            var post = {
                id : $('.J-project').data('target'),
                type : 'project',
                unfeed : unfeed
            };
            var that = this;
            API.post('user_feed', post, function (result) {
                if (result.error == 0) {
                    if(isLiked){
                        $(that).removeClass('liked tada').addClass('unlike');
                    }else{
                        $(that).removeClass('unlike').addClass('liked tada');
                    }
                } else {
                    alertImg(result.message);
                }
            });
        })
}

function setFeedStatus(){
    var project_id = $('.J-project').data('target');
    API.post('project_feed', {id: project_id}, function (result) {
        if (result.error == 0) {
            $('.J-focus').addClass('liked').removeClass('unlike');
        } else {
            $('.J-focus').addClass('unlike').removeClass('liked');
        }
    });
}

function initDynamic(){
    initDynamicListID(); //初始化动态列表的id
    initNewDynamicBtn(); //初始化增加动态按钮
    initDynamicDelete(); //初始化删除动态
    initDynamicEdit(); //初始化动态编辑
}

function initDynamicListID() {
    var DynamicLists = $('.J-dynamic-box .line-block');
    for (var i = 0; i < DynamicLists.length; i++) {
        DynamicLists.eq(i).attr('id', 'dynamic' + i);
        DynamicLists.eq(i).find('.J-edit-btn').attr('id', 'dinamicEdit' + i);
        DynamicLists.eq(i).find('.J-delete-btn').attr('id', 'dinamicDelete' + i);
        DynamicLists.eq(i).find('.J-dynamic-title').attr('id', 'dinamicTitle' + i);
        DynamicLists.eq(i).find('.J-dynamic-content').attr('id', 'dinamicContent' + i);
    }
}

function initNewDynamicBtn() {
    $('.J-new-dynamic-btn').unbind('click').click(function () {
        var is_not_show = $('.J-dynamic-edit-box').css('display') == 'none';
        if (is_not_show) {
            $('.J-dynamic-edit-box').slideDown();
        }
    });

    $('.J-cancel-new-dynamic-btn').unbind('click').click(function () {
        var edit_status = judgeHasEdting();
        if (edit_status.status) {
            $('#dinamicEdit' + edit_status.index).removeClass('editing');
        }
        clearDynamicEditor();
        $('.J-dynamic-edit-box').slideUp('fast');
    });

    $('.J-save-new-dynamic-btn').unbind('click').click(function () {
        var title = $('.J-dynamic-edit-title').val();
        var content = $('.J-dynamic-edit-content').val();
        var edit_status = judgeHasEdting();
        var current_date = getNowFormatDate();
        var edit_date = $('#dynamic'+edit_status.index).find('.J-dynamic-time').text();
        var date = edit_status.status ? edit_date : '';
        var timeline_id = $('#dinamicEdit' + edit_status.index).data('id');
        var project_id = $('.J-project').data('target');
        var post = {
            id : timeline_id,
            project_id : project_id,
            title : title,
            message : content,
            time : date
        };
        if(!title.length){
            alertImg('请填写动态标题');
            $('.J-dynamic-edit-title').focus();
            return;
        }

        if($('.J-dynamic-edit-box .warning').length){
            alertImg('项目动态请在280个字符以内');
            $('.J-dynamic-edit-content').focus();
            return;
        }

        showLoading();
        API.post('project_timeline', post, function (result) {
            if (result.error == 0) {
                if (edit_status.status) {
                    $('#dinamicTitle' + edit_status.index).html(title);
                    $('#dinamicContent' + edit_status.index).html(content);
                    $('#dinamicEdit' + edit_status.index).removeClass('editing');
                    alertImg('修改成功');
                }
                if (!edit_status.status) {
                    var tplHtml = tpl('projectDynamicLine',{title:title, content:content, date:current_date, timeline_id: result.message});
                    $('.J-dynamic-box .box-line').append(tplHtml);
                    var is_no_content_show = !$('.J-dynamic-box .no-content').hasClass('hide');
                    if(is_no_content_show){
                        $('.J-dynamic-box .no-content').addClass('hide');
                    }

                    initDynamic();
                    alertImg('添加成功');
                }
                hideLoading();
                clearDynamicEditor();
                $('.J-dynamic-edit-box').slideUp('fast');
            } else {
                hideLoading();
                alertImg(result.message);
            }
        });
    })
}

function clearDynamicEditor() {
    $('.J-dynamic-edit-content').val('');
    $('.J-dynamic-edit-title').val('');
}

function judgeHasEdting() {
    var edit_btns = $('.J-edit-btn');
    var is_editing = false;
    var index = '';
    for (var i = 0; i < edit_btns.length; i++) {
        if (edit_btns.eq(i).hasClass('editing')) {
            is_editing = true;
            index = i;
        }
    }
    return {'status': is_editing, 'index': index};
}

function initDynamicEdit() {
    $('.J-edit-btn').unbind('click').click(function () {
        var is_editing = $(this).hasClass('editing'); //当前是否正在编辑
        var edit_status = judgeHasEdting(); //有无正在编辑的动态
        var num = getNum($(this).attr('id'));
        var title = $('#dinamicTitle' + num).html();
        var content = $('#dinamicContent' + num).text();

        //if 没有正在编辑的动态
        if (!edit_status.status) {
            $('.J-dynamic-edit-box').slideDown();
        }

        //if 有其他动态在编辑
        if (edit_status.status && !is_editing) {
            if (confirm('有其他动态正在编辑，是否保存？')) {
                $('.J-save-new-dynamic-btn').click(); //保存正在编辑的内容

            } else {
                $('#dinamicEdit' + edit_status.index).removeClass('editing');
            }
        }

        $(this).addClass('editing');
        $('.J-dynamic-edit-title').focus().val(title);
        $('.J-dynamic-edit-content').val(content);
    })
}

function initDynamicDelete() {
    $('.J-delete-btn').unbind('click').click(function () {
        var num = getNum($(this).prev().attr('id'));
        var edit_status = judgeHasEdting();
        var is_editing = edit_status.index == num;
        var timeline_id = $(this).data('id');
        var project_id = $('.J-project').data('target');

        var post = {
            id : timeline_id,
            project_id : project_id
        };
        //没有动态正在编辑 或者 本条动态不在编辑状态
        if (!edit_status.status || !is_editing) {
            if (confirm('确定要删除本条动态吗？')) {
                API.post('remove_project_timeline', post, function (result) {
                    if (result.error == 0) {
                        $('#dynamic' + num).remove();
                        initDynamic();
                    } else {
                        alertImg(result.message);
                    }
                });

                $('#dynamic' + num).remove();
                initDynamic();
            }
        }

        //该条动态正在编辑
        if (edit_status.status && is_editing) {
            if (confirm('当前动态正在编辑，确定删除？')) {
                API.post('remove_project_timeline', post, function (result) {
                    if (result.error == 0) {
                        clearDynamicEditor();
                        $('.J-dynamic-edit-box').slideUp('fast');
                        $('#dynamic' + num).remove();
                    } else {
                        alertImg(result.message);
                    }
                });
            }
        }
    })
}

function initDiscussBtn() {
    $('.J-q-btn-box .q-btn').click(function () {
        $('#myQuestionModel').modal('show');
    });

    $('.J-qa-list .J-edit').click(function(){
        var q_id = $(this).data('id1');
        var a_id = $(this).data('id2');
        var q = $(this).parent().parent().parent().parent().find('.q-content').html();
        var a = $(this).parent().parent().find('.content').html();
        $('.J-question-input').val(q);
        $('.J-question-input').attr('data-id',q_id);
        $('.J-answer-input').val(a);
        $('.J-answer-input').attr('data-id',a_id);
        $('#myQuestionModel').modal('show');
    });

    $('.J-qa-list .J-delete').click(function(){
        if(confirm('确定删除该条问答？')){
            var q_id = $(this).data('id');
            showLoading();
            var that = this;
            API.post('remove_qa', {id: q_id}, function (result) {
                if (result.error == 0) {
                    hideLoading();
                    $(that).parent().parent().parent().parent().remove();
                    alertImg('删除成功');
                } else {
                    hideLoading();
                    alertImg(result.message);
                }
            });
        }
    });

    $('#myQuestionModel .C-J-confirm').click(function () {
        var project_id = $('.J-project').data('target');
        var q_content = $('.J-question-input').val();
        var a_content = $('.J-answer-input').val();
        var q_id = $('.J-question-input').data('id');
        var a_id = $('.J-answer-input').data('id');

        if (!q_content.length) {
            setError('.J-question-input', '*请输入您要提问的问题');
            setTimeout(function () {
                removeError('#myQuestionModel .J-question-input');
            }, 2000);
            return;
        }

        if ($('#myQuestionModel .J-had-input').hasClass('warning')) {
            setError('.J-question-input', '*请输入小于100个字符的问题');
            setTimeout(function () {
                removeError('#myQuestionModel .J-question-input');
            }, 2000);
            return;
        }

        var comment = [
            {
                id: q_id,
                message: q_content
            },
            {
                id: a_id,
                message: a_content
            }
        ];
        var post = {
            project_id : project_id,
            comment: comment
        };
        showLoading();
        API.post('qa', post, function (result) {
            if (result.error == 0) {
                $('.J-question-input').val('');
                $('#myQuestionModel').modal('hide');
                hideLoading();
                setTimeout(function () {
                    alertImg('问题提交成功');
                    location.href = $('.J-qa-li a').attr('href');
                }, 500)
            } else {
                hideLoading();
                alertImg(result.message);
            }
        });
    })
}

function initDialog() {
    $('.C-J-dialog').click(function () {
        var project_id = $('.J-project').data('target');
        API.post('project_meet_exists',{project_id:project_id},function(result){
            if(result.error == 0){
                $('#myDialogModel').modal('show');
            }else{
                if(result.message.length){
                    alertImg(result.message);
                }else{
                    alertImg('您已发送过约见请求。');
                }
            }
        })

    });

    $('#myDialogModel .C-J-confirm').click(function () {
        var content = $('#myDialogModel textarea').val();
        var project_id = $('.J-project').data('target');
        if(!content.length){
            $('#myDialogModel .error-box').html('*约见内容不能为空哦').css('display','block');
            setTimeout(function(){
                hide($('#myDialogModel .error-box'));
            },2000);
            return;
        }

        if ($('#myDialogModel .J-had-input').hasClass('warning')) {
            $('#myDialogModel .error-box').html('*请输入不超过200个字符的内容').css('display','block');
            setTimeout(function () {
                hide($('#myDialogModel .error-box'));
            }, 2000);
            return;
        }

        var post = {
            project_id : project_id,
            message : content
        };
        showLoading();
        API.post('project_meet', post, function (result) {
            if (result.error == 0) {
                $('#myDialogModel').modal('hide');
                setTimeout(function(){
                    alertImg('约见申请提交成功！');
                },500);
            } else {
                alertImg(result.message);
            }
            hideLoading();
        });
    })
}

