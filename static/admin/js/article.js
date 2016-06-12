$(function(){
    $('#bannerQuickTab a').click(function (e) {
        e.preventDefault();
        $(this).tab('show');
    });

    //if ($('#articleListContentEditor').length > 0) {
    //    var articleUe = UE.getEditor('articleListContentEditor');
    //}
    if ($('#articleListContentEditor').length > 0) {
        setTimeout(function(){
            var ue = UE.getEditor('articleListContentEditor');
            setTimeout(function(){
                ue.setContent($('#articleList').val());
            },500);
        },0);
    }

    //if ($('#answerContentEditor').length > 0) {
    //    var helpUe = UE.getEditor('answerContentEditor');
    //}
    if ($('#answerContentEditor').length > 0) {
        setTimeout(function(){
            var ue = UE.getEditor('answerContentEditor');
            setTimeout(function(){
                ue.setContent($('#answer').val());
            },500);
        },0);
    }



    window.API = (function () {
        var that = this;
        that.post = function (api, post, callback) {
            //add form hash
            post.FORM_HASH = FORM_HASH;
            $.post(DIR + '/api/' + api + '/', post, function (result) {
                var data;
                try {
                    eval('data=' + result);
                } catch (e) {
                }
                callback && callback(data);
            });
        };
        return that;
    })();
});

function setTargetStatus(str){
    var target = str;
    var is_checked = $(this).attr('checked');
    if(is_checked){
        $(this).attr('checked',false);
        $('#'+target).attr('disabled',true);
    }
    if(!is_checked){
        $(this).attr('checked',true);
        $('#'+target).attr('disabled',false);
    }
}

function deleteItem(deleteUrl, deleteId){
    if(confirm('确定要删除该条内容吗？')){
        API.post(deleteUrl, {id: deleteId}, function (result) {
            if(result.error == 0){
                location.reload();
            }else{
                alert(result.message);
            }
        })
    }

}

function Img_show(o) {
    var file = o.files[0];
    var reader = new FileReader();
    reader.onload = function () {
        //console.log(reader.result);
        $('.img').attr('src',reader.result);
        $('#path').val(reader.result);
    }
    reader.readAsDataURL(file);
    $(o).replaceWith('<input type="file" onchange="Img_show(this)">');
}
