/**
 * Created by jipingzhao on 6/17/17.
 * 表单ajax提交, 相关函数
 */

var _IS_SUBMIT_SUCCESS = false; // 全局变量, 提交是否成功
function enbaleSubmitButton(){
    $("button:submit").attr("disabled", false);
}

$(document)
    .ajaxStart(function(){
        $("button:submit").attr("disabled", true);
    })
    .ajaxStop(function(){
        setTimeout("enbaleSubmitButton();", 3000);//延时5s执行
    });

function bindAjaxForm(){
    // 需要ajax提交的表单, 加上css类名 ajaxForm
    $(".ajaxForm").submit(function(){
        var self = $(this);

        // 验证函数
        if (typeof(validate) != 'undefined' && $.isFunction(validate)) {
            if ( eval(validate)() == false ) {
                return false;
            }
        }

        $.ajax({
            type : "POST",
            cache: false,
            url  : self.attr("action"),
            data : self.serialize(),
            datatype : "json",
            success : success,
            error: function(){
                alert("程序错误!")
            }
        });
        return false;

        function success(data){
            // console.log(data);

            //当有自定义回调函数时, 执行并执行回调函数
            if (typeof(callback) != 'undefined' && $.isFunction(callback)) {
                eval(callback)(data);

                return true;
            }

            //如果没有回调函数, 默认执行
            if(data.status == 1){
                _IS_SUBMIT_SUCCESS = true;

                if (data.info != '' && typeof(data.info) != 'undefined')  toastr(data.info);;
                //跳转页面
                if ( typeof(_TARGET_URL) != 'undefined' && _TARGET_URL != '') {
                    window.location.href = _TARGET_URL;
                }
                //刷新页面
                if ( typeof(_NEED_REFRESH) != 'undefined' && _NEED_REFRESH == true) {
                    location.reload();
                }
            } else {
                if (data.info != '' && typeof(data.info) != 'undefined') toastr(data.info);
                else  toastr('未定义错误!');
            }
        }
    });

}
bindAjaxForm();

// 返回按钮事件
function goback(){

    if (_IS_SUBMIT_SUCCESS) {
        self.location=document.referrer;
    } else {
        history.back();
    }
}
