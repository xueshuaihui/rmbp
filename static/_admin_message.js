/*[tplStatic 1.0 - 1511]*/

(function(window){window.changeMessage=function(o){var type=$(o).data('type'),target=$('#'+$(o).data('target')),value=parseInt($(o).val(),10),message='';if(target.length==0){return false;}
switch(type){case'project_verify':switch(value){case-1:message='很抱歉的通知您，您在Plus平台上发起的众筹项目[%project%]未通过审核，原因如下：\n'+'\n'+'\n您可以根据未通过的原因进行修改，欢迎再次提交申请，符合条件后我们会立刻审核通过。';break;case 1:break;case 2:message='您发起的项目[%project%]已经进入预热阶段，我们将会对您的项目在平台进行推广预热，让更多的投资人了解您的项目。';break;case 3:message='您发起的项目[%project%]正式进入众筹阶段，投资者可以对您的项目进行投资了。请保持关注，并祝您的项目早日众筹成功。';break;case 4:message='您发起的项目[%project%]在Plus平台众筹成功，我们会于24小时之内联系您，沟通众筹成功后的事宜，请保持沟通渠道的畅通。';break;case 5:message='您发起的项目[%project%]在设定的期限内没能达到预期目标，很遗憾的通知您本次众筹失败了。\m建议您继续完善产品，期待看到您带着更成熟的产品来到Plus平台进行众筹。';break;case 6:message='您发起的项目[%project%]已经通过，稍后我们将会有专员和您联系进一步事宜。';break;}
break;case'person_auth':switch(value){case-1:message='很遗憾的通知您，您的投资人资格申请未通过，原因如下：\n%1%\n若您符合条件之后，欢迎再次申请认证投资人。';break;case 1:break;case 2:message='您的投资人资格申请刚刚通过审核，正式成为了Plus平台的认证投资人，赶紧点击查看正在平台上众筹的优质项目吧。';break;}
break;case'invest':switch(value){case-1:message='很遗憾的通知您，您的退款申请未能通过审核，如果有任何疑问，请联系我们。';break;case 2:message='恭喜您，您的退款申请已经通过审核，退款将在一段时间内打回您支付的银行卡中。';break;}
default:break;}
if(message){target.val(message);}}})(window);;