/**
 * 上传图片
 * btnUpload: 上传按钮的ID
 * inputImg: 保存图片路径的input ID
 * savePath: 指定存放在服务器上的路径
 * item: 一个页面上传多个图片时的标识
 * by zhaojiping
 **/
function ajaxUpload(btnUpload, inputImg, savePath, item, up_area){
    var filename = ""; // 旧文件的文件名
    var oldImg = $(inputImg).val();
    if($.trim(oldImg) != "" && oldImg.indexOf('/') != -1){
        var arr = oldImg.split('/');
        var file = arr[arr.length - 1];
        filename = file.split('.')[0];
    }

    if(!savePath) savePath = "";  // 要存放的路径
    
    new AjaxUpload($(btnUpload), {
        action: UPLOAD_IMG_URL,  
        name: 'photo',   //这相当于<input type = "file" name = "photo"/> 
        data:{},  //附加参数值
        dataType : 'text',
        onSubmit : function(file, ext){
            if(!(ext && /^(jpg|png|gif|jpeg)$/.test(ext.toLowerCase()))){
                toastr('图片格式不支持, 请上传jpg, png, gif, jpeg格式图片');
                return false;
            }
            this.setData({'oldImg':filename, 'savePath':savePath});
            this.disable();
        },
        onComplete: function(file, response){
            console.log(response);
            json = $.parseJSON($(response).text());
            if(json['status'] == true || json['status'] == 1 || json['status'].toString() == '1'){
                $(up_area + item).attr('src', json['src']);
                $("#img" + item).val(json['src']);
            }else{
                toastr(json['msg'])
            }
            this.enable(); 
        }  
    }); 
}


/**
 * 删除文件
 * file: 删除的文件路径
 * item: 删除的文件编号
 * message: 删除的提示信息
 * by zhaojiping
 **/
function delFile(file, item, message){
    if(! message) message = "确认删除吗? 此步骤无法恢复!"
    if(! confirm(message)) return false;
    var url;
    if (DELETE_FILE_URL.indexOf('?') > -1) {
        url = DELETE_FILE_URL + "&file="+ file;
    } else {
        url = DELETE_FILE_URL + "?file="+ file;
    }
    
    $.get(url, function(data){
        if(data.status == 1){
            $('#img_' + item).attr('src', '').hide();
            $('#img' + item).val('').hide();
            $('#btn_delete_' + item).hide();
            toastr(data.msg);
        }else{
            toastr(data.msg)
        }
    })
}