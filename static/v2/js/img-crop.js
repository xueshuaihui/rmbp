prevID = '';
cropImgSize = {};
resultImgSize = {};
var imgCrop = {
    init : function () {
        $('.J-upload-img-btn').unbind('click').click(function(){
            var error_obj = $(this).parent().parent().find('.J-tip');
            check.removeError(error_obj, $(this));
            $('#'+$(this).data('target')).click();
        })
    } ,

    change : function(o){
        var self = this;
        var imgModalObj = $('.J-crop-modal');
        prevID = $(o).data('previd');

        resultImgSize = {
            w : $(o).data('width'),
            h : $(o).data('height'),
            r : $(o).data('width') / $(o).data('height'),
            aspectRatio : $(o).data('width') + ':' + $(o).data('height')
        };
        console.log(resultImgSize);

        imgModalObj.find('.J-canvas')
            .attr('width',resultImgSize.w)
            .attr('height',resultImgSize.h);
        imgModalObj.find('.preview-box')
            .css('height',150/resultImgSize.r);

        console.log(o.value);
        if(!o.value){
            return;
        }

        var isIE = navigator.userAgent.indexOf('MSIE') >= 0;
        if (!o.value.match(/.jpg|.gif|.png|.bmp/i)) {
            alert('图片格式无效！');
            return false;
        }
        if (isIE) { //IE浏览器
            imgModalObj.find('.image-box img').attr('src', o.value);
            imgModalObj.find('.preview-box img').attr('src', o.value);
        }
        if (!isIE) {
            var file = o.files[0];
            var reader = new FileReader();
            reader.onload = function () {
                var img = new Image();
                img.src = reader.result;
                self.setCropImgSize(img.width, img.height);
                imgModalObj.find('.image-box img').attr('src', reader.result);
                imgModalObj.find('.preview-box img').attr('src', reader.result);
            };
            reader.readAsDataURL(file);
        }

        imgModalObj.modal('show');
        imgModalObj.find('.image-box img').imgAreaSelect({
            aspectRatio: resultImgSize.aspectRatio,
            handles: true,
            fadeSpeed: 200,
            onSelectChange: this.prev
        });

        self.resetSelector(imgModalObj.find('.image-box img'), 150, 150/resultImgSize.r);

        $(o).replaceWith('<input style="display:none" type="file" class="J-img-input" id="'
            + $(o).attr('id')
            + '" onchange="imgCrop.change(this)" data-width="'
            + $(o).data('width')
            + '" data-height="'
            + $(o).data('height')
            + '" data-previd="'
            + $(o).data('previd')
            + '">');
        self.init();
    },

    resetSelector : function(img, select_w, select_h){
        var isMac = navigator.userAgent.indexOf('Mac OS X') > 0;
        var time = isMac ? 500 : 1000;

        setTimeout(function () {
            var imgW = img.width();
            var imgH = img.height();
            var ratio = select_w/select_h;
            if(imgW < select_w){
                select_w = imgW;
                select_h = select_w/ratio;
            }
            if(imgH < select_h){
                select_h = imgH;
                select_w = select_h*ratio;
            }

            var X1 = (imgW - select_w) / 2,
                Y1 = (imgH - select_h) / 2,
                X2 = X1 + select_w,
                Y2 = Y1 + select_h;

            img.imgAreaSelect({x1: X1, y1: Y1, x2: X2, y2: Y2});
            cropImgSize = {
                'x1': X1,
                'y1': Y1,
                'w': select_w,
                'h': select_h
            };
        }, time)
    },

    setCropImgSize : function(w, h){
        var cropModalObj = $('.J-crop-modal');
        if(w > h){
            cropModalObj.find('.image-box img').css('width','400px').css('height','auto');
        }else{
            cropModalObj.find('.image-box img').css('height','400px').css('width','auto');
        }
    } ,

    prev : function(img, selection){
        if (!selection.width || !selection.height)
            return;
        var scaleX = 150 / selection.width;
        var scaleY = (150/resultImgSize.r) / selection.height;

        $('.J-crop-modal .preview-box img').css({
            width: Math.round(scaleX * img.width),
            height: Math.round(scaleY * img.height),
            marginLeft: -Math.round(scaleX * selection.x1),
            marginTop: -Math.round(scaleY * selection.y1)
        });
        cropImgSize = {
            'x1': selection.x1,
            'y1': selection.y1,
            'w': selection.width,
            'h': selection.height
        };
    },

    confirm : function(){
        var size = cropImgSize;
        var cropModalObj = $(".J-crop-modal");
        var primary_width = cropModalObj.find(".image-box img").width();
        var sourseImg = new Image();
        sourseImg.src = cropModalObj.find(".image-box img").attr('src');

        var R = sourseImg.width / primary_width;
        var canvas = cropModalObj.find(".J-canvas")[0];
        var context = canvas.getContext("2d");
        context.drawImage(sourseImg, size.x1 * R, size.y1 * R, size.w * R, size.h * R, 0, 0, canvas.width, canvas.height);

        $('#'+prevID)
            .html('')
            .append("<img src='" + canvas.toDataURL('image/jpeg',0.8) + "'/>");

        this.cancel();
        console.log('crop success');
    },

    cancel : function(){
        $(".J-crop-modal").modal('hide');
        this.clearCrop();
    },

    clearCrop : function(){
        $('.imgareaselect-outer').hide();
        $('.imgareaselect-handle').parent().hide();
        this.init();
    }
};