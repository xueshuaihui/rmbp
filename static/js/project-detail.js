$(function () {
    // 初始化编辑器
    if($('#projectDetailEditor')[0]){
        var ue = UE.getEditor('projectDetailEditor');
    }
    initInvestListStyle(); //初始化投资列表样式
    initDynamic(); //初始化项目动态
    initLike();    //初始化关注按钮
    initDialog();  //初始化 “约约约” 
    setPartnerBanner();  //初始化团队成员轮播效果
    initDiscussBtn(); //初始化讨论模块的提问按钮
    initProjectStatusStyle();
    initSupportBtnStatus();
    initWeixinShare();
    window.onscroll = function () {
        setTopTabStatus();
    };

    $('.J_zoom_img_box').hc_zoom();

});

function initWeixinShare(){
    $('.J-weixin').hover(function(){
        var src = $(this).data('src');
        $('.J-weixin-img img').attr('src',src);
        $('.J-weixin-img').css('display','block');
    },function(){
        $('.J-weixin-img').css('display','none');
    })
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
            var islogin = $('.J-unlogin')[0].length;
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

function setPartnerBanner() {
    $('.J-partner-bxslider').bxSlider({
        minSlides: 3,
        maxSlides: 3,
        // auto:true,
        adaptiveHeight: true,
        slideWidth: 207,
        slideMargin: 20
    });
}

function initProjectStatusStyle(){
    var status_5_span = $('.J-status-5 span');
    if(status_5_span.length == 1){
        status_5_span.css('line-height','75px');
    }
    if(status_5_span.length == 2){
        status_5_span.css('line-height','37.5px');
    }

    var status_6_span = $('.J-status-6 span');
    if(status_6_span.length == 1){
        status_6_span.css('line-height','75px');
    }
    if(status_6_span.length == 2){
        status_6_span.css('line-height','37.5px');
    }

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
                    location.href = location.href;
                }, 500)
            } else {
                hideLoading();
                alertImg(result.message);
            }
        });
    })
}

function scrollToContentTab(){
    var top_height = $('.C-project-detail .title-box').height() + 85;
    var scrollTop = document.body.scrollTop;
    if(scrollTop > top_height){
        $(window).scrollTop(top_height);
    }
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

function initDynamicEdit() {
    $('.J-edit-btn').unbind('click').click(function () {
        var is_editing = $(this).hasClass('editing'); //当前是否正在编辑
        var edit_status = judgeHasEdting(); //有无正在编辑的动态
        var num = getNum($(this).attr('id'));
        var title = $('#dinamicTitle' + num).html();
        var content = $('#dinamicContent' + num).html();

        //if 没有正在编辑的动态
        if (!edit_status.status) {
            $('.C-J-edit-box').slideDown();
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
        $('.C-J-edit-box input').focus().val(title);
        UE.getEditor('projectDetailEditor').setContent(content);
    })
}

function initDynamicDelete() {
    $('.J-delete-btn').unbind('click').click(function () {
        var num = getNum($(this).prev().attr('id'));
        var edit_status = judgeHasEdting();
        var is_editing = edit_status.index == num;
        var timeline_id = $(this).data('id');
        var project_id = $(this).data('target');

        var post = {
            id : timeline_id,
            project_id : project_id
        };
        //没有动态正在编辑 或者 本条动态不在编辑状态
        if (!edit_status.status || !is_editing) {
            if (confirm('确定要删除本条动态吗？')) {
                showLoading();
                API.post('remove_project_timeline', post, function (result) {
                    if (result.error == 0) {
                        $('#dynamic' + num).remove();
                        initDynamic();
                    } else {
                        alertImg(result.message);
                    }
                    hideLoading();
                });
            }
        }

        //该条动态正在编辑
        if (edit_status.status && is_editing) {
            if (confirm('当前动态正在编辑，确定删除？')) {
                showLoading();
                API.post('remove_project_timeline', post, function (result) {
                    if (result.error == 0) {
                        clearDynamicEditor();
                        $('.C-J-edit-box').slideUp('fast');
                        $('#dynamic' + num).remove();
                    } else {
                        alertImg(result.message);
                    }
                    hideLoading();
                });

            }
        }
    })
}

function initDynamic() {
    initDynamicListID(); //初始化动态列表的id
    initNewDynamicBtn(); //初始化增加动态按钮
    initDynamicDelete(); //初始化删除动态


    initDynamicEdit(); //初始化动态编辑
    initDynamicDetailStatus(); //初始化查看动态详情
}

function initDynamicListID() {
    var DynamicLists = $('#dynamicList .cd-timeline-block');
    for (var i = 0; i < DynamicLists.length; i++) {
        DynamicLists.eq(i).attr('id', 'dynamic' + i);
        DynamicLists.eq(i).find('.J-edit-btn').attr('id', 'dinamicEdit' + i);
        DynamicLists.eq(i).find('.J-delete-btn').attr('id', 'dinamicDelete' + i);
        DynamicLists.eq(i).find('.J-title').attr('id', 'dinamicTitle' + i);
        DynamicLists.eq(i).find('.J-content').attr('id', 'dinamicContent' + i);
        DynamicLists.eq(i).find('.J-detail-control').attr('id', 'dinamicDetailControl' + i);
    }
}

function initNewDynamicBtn() {
    $('.C-J-new-dynamic').unbind('click').click(function () {
        var is_not_show = $('.C-J-edit-box').css('display') == 'none';
        if (is_not_show) {
            $('.C-J-edit-box').slideDown();
        }
    })

    $('.J-cancel-new-dynamic-btn').unbind('click').click(function () {
        var edit_status = judgeHasEdting();
        if (edit_status.status) {
            $('#dinamicEdit' + edit_status.index).removeClass('editing');
        }
        clearDynamicEditor();
        $('.C-J-edit-box').slideUp('fast');
    })

    $('.J-save-new-dynamic-btn').unbind('click').click(function () {
        var title = $('.C-J-edit-box input').val();
        var content = UE.getEditor('projectDetailEditor').getContent();
        var edit_status = judgeHasEdting();
        var current_date = getNowFormatDate();
        var edit_date = $('#dynamic'+edit_status.index).find('.cd-date').text();
        var date = edit_status.status ? edit_date : '';
        var timeline_id = $('#dinamicEdit' + edit_status.index).data('id');
        var project_id = $('#dynamicList').data('target');
        var post = {
            id : timeline_id,
            project_id : project_id,
            title : title,
            message : content,
            time : date
        };
        if(!title.length){
            alertImg('请填写动态标题');
            $('.C-J-edit-box input').focus();
            return;
        }
        if(!content.length){
            alertImg('请填写动态内容');
            UE.getEditor('projectDetailEditor').focus();
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
                   var tplHtml = tpl('projectTimeline',{title:title, content:content, date:current_date, timeline_id: result.message});
                   $('#cd-timeline').append(tplHtml);
                   var is_no_content_show = $('.J-dynamicList').hasClass('hide');
                   if(is_no_content_show){
                       $('.no-content').addClass('hide');
                       $('.J-dynamicList').removeClass('hide');
                   }


                   initDynamic();
                   alertImg('添加成功');
                }
                hideLoading();
                scrollToContentTab();
                clearDynamicEditor();
                $('.C-J-edit-box').slideUp('fast');
            } else {
                hideLoading();
                alertImg(result.message);
            }
        });
    })
}

function clearDynamicEditor() {
    UE.getEditor('projectDetailEditor').setContent('');
    $('.C-J-edit-box input').val('');
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

function initDynamicDetailStatus() {
    $('.J-detail-control').unbind('click').click(function () {
        var num = getNum($(this).attr('id'));
        var is_up = $(this).hasClass('up');
        var dynamicID = 'dynamic' + num;
        var is_editing = $('#dinamicEdit' + num).hasClass('editing');

        //if 详情展开 并且 该动态正在编辑中
        if (is_up && is_editing) {
            if (confirm('该动态正在编辑中，是否保存？')) {
                $('.J-save-new-dynamic-btn').click(); //保存正在编辑的内容
            }
            $('#dinamicEdit' + num).removeClass('editing');
            clearDynamicEditor();
            $('.C-J-edit-box').slideUp('fast');

            $(this).removeClass('up');
            $('#' + dynamicID).find('.J-content-box').slideUp('fast');
        }

        //if 详情展开 并且 该动态没在编辑
        if (is_up && !is_editing) {
            $(this).removeClass('up');
            $('#' + dynamicID).find('.J-content-box').slideUp('fast');
        }

        //if 详情收起
        if (!is_up) {
            $(this).addClass('up');
            $('#' + dynamicID).find('.J-content-box').slideDown('fast');
        }
    })

    $('.J-dynamicList .J-title').click(function () {
        var num = getNum($(this).attr('id'));
        $('#dynamic'+num).find('.J-detail-control').click();
    })
}

function initInvestListStyle() {
    var lists = $('#investList li');
    for (var i = 0; i < lists.length; i++) {
        if (i % 2) {
            lists.eq(i).addClass('even');
        } else {
            lists.eq(i).addClass('odd');
        }
    }
}

function initLike() {
    setFeedStatus();
    $('.C-J-focus').click(function () {
        var isLiked = $(this).hasClass('liked');
        var num = $(this).find('.J-like-num').html();
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
                    var new_num_1 = parseInt(num) - 1;
                    $(that).addClass('unlike');
                    $(that).removeClass('liked');
                    $(that).find('.desc').html('关注(<span class="J-like-num">' + new_num_1 + '</span>)');
                }else{
                    var new_num_2 = parseInt(num) + 1;
                    $(that).addClass('liked');
                    $(that).removeClass('unlike');
                    $(that).find('.desc').html('已关注(<span class="J-like-num">' + new_num_2 + '</span>)');
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
            var num = $('.C-J-focus .J-like-num').text();
            $('.C-J-focus').addClass('liked').removeClass('unlike');
            $('.C-J-focus .desc').html('已关注(<span class="J-like-num">' + num + '</span>)');
        } else {

        }
    });
}

function setTopTabStatus() {
    var scrollTop = document.body.scrollTop;
    var top_height = $('.C-project-detail .title-box').height() + 85;
    if (scrollTop < top_height) {

        $('.J-tab-box').removeClass('C-fixed');
    }
    if (!(scrollTop < top_height)) {
        $('.J-tab-box').addClass('C-fixed');
    }
}
      

   
