function CURD_delAjax(url,primary_key, id) {
    $.post(url, {primary_key: id}, CURD_callback, 'json');
    $('#CURL_list_' + id).remove();
}
function CURD_callback(data) {
    primary_key = data.content.primary_key;
    if(data.content.msg){
        alert(data.content.msg);
    }else if(data.content.exception_code && data.content.exception_msg){
        alert(data.content.exception_code+'>>'+data.content.exception_msg);
    }
//    alert(data.msg);
    location.reload();
}

function CURD_submitForm(formid, ajaxurl) {
    $.ajax({
        cache: true,
        type: "POST",
        url: ajaxurl,
        data: $('#' + formid).serialize(), // 浣犵殑formid
        async: false,
        dataType: 'json',
        error: function (request) {
            alert("操作错误");
        },
        success: function (data) {
            CURD_ajaxsubmitformcallback(data);
        }
    });
}
function CURD_ajaxsubmitformcallback(data) {
    primary_key = data.content.primary_key;
    if (!$('#'+primary_key).val()) {
        $('#'+primary_key).val(data.content.curd_primary_key);
    }
    if(data.content.msg){
        alert(data.content.msg);
    }else if(data.content.exception_code && data.content.exception_msg){
        alert(data.content.exception_code+'>>'+data.content.exception_msg);
    }

    //form 表单AJAX提交 回调函数
}

