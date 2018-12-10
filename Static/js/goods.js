//单图上传
$('#upimg1').fileupload({
    url: 'companyUpload',
    dataType: 'json',
    done: function (e, data) {
        $('#uplogo').attr('src', data.result.src);
        $('#img').val(data.result.src);
    }
}).prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled');

function delPic() {
    $('#goods_img').val('');
    $('#goods-main-img').html('');
}

$("#upimg2").fileupload({
    url: "companyUpload",
    dataType: 'json',
    done: function (e, data) {
        var upload_item = $('.upimg').length;
        if(upload_item == 8){
            $('.upimg_button').hide();
        }
        console.log(data);
        $('.company_imgs').append('<div class="upimg"><img src="'+data.result.src+'" />'+
            '<div class="file-del" onclick="delPicIds(this)" style="color:red;cursor:pointer">删除</div>'+
            '<input type="hidden" name="company_img_ids[]" value="'+data.result.src+'"></div>');
    }
}).prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled');

function delPicIds(obj) {
    $(obj).parent().remove();
    var upload_item = $('.upimg').length;
    if(upload_item < 9){
        $('.upimg_button').show();
    }
}