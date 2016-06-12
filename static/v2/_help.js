/*[tplStatic 1.0 - 1511]*/

$(function(){$("fieldset[data-type] legend").click(function(){var is_up=$(this).hasClass('up');if(is_up){$(this).parent().animate({height:'40px'},200);$(this).removeClass('up');}else{$(this).parent().animate({height:'100%'},200);$(this).addClass('up');}});});;