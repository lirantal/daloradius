var ajax = new Array();

function ajaxGeneric(remoteAjaxFile,remoteAjaxFunc,remoteAjaxDiv,remoteAjaxExtra) {
        var index = ajax.length;
        ajax[index] = new sack();

        ajax[index].requestFile = remoteAjaxFile + '?' + remoteAjaxFunc + '&divContainer=' + remoteAjaxDiv + '&' + remoteAjaxExtra;
        ajax[index].onCompletion = function(){ evalAjax(index) };
        ajax[index].runAJAX();
}


function evalAjax(index) {
        eval(ajax[index].response);
}


