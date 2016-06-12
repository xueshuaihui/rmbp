/*[tplStatic 1.0 - 1511]*/

$(function(){window.guyComponent=(function(){var guyList=[],guyTpl=tpl('guyTpl'),that=this;function bindEvent(ele){ele.find('.J-img-input').unbind('click').click(function(){$(this).prev().unbind('click').click();removeError($(this));});}
that.add=function(data){if(!data){data={index:0,id:'',truename:'',job:'',intro:'',pic:{}};}
var index=guyList.push(data);guyList[index-1].index=index;var guyElement=$(guyTpl(guyList[index-1]));guyList[index-1].dom=guyElement;bindEvent(guyElement);$('.J-guys-box').append(guyElement);block.setIndex('guy');};that.remove=function(index){if(guyList[index]){var guy=guyList[index];if(guyList.length==1){alertImg('最后一位小伙伴不能删哦');return;}
if(confirm('确定删除这位小伙伴吗？')){window.API.post('remove_guy',{id:guy.id},function(){guyList[index].dom.remove();block.setIndex('guy');});}}};that.init=function(guyData){if(guyData.length==0){guyComponent.add();}else{for(var i in guyData){guyComponent.add(guyData[i]);}}}
return that;})();$(document).unbind('click').on('click','*[data-action]',function(){switch($(this).data('action')){case'add-guy':guyComponent.add();break;case'remove-guy':guyComponent.remove($(this).data('index'));break;}});});;