//单图上传
$('#upload').fileupload({
    url: "{:U('Hr/Hr/companyUpload')}",
    dataType: 'json',
    done: function (e, data) {
        $('#goods-main-img').html('<img src="'+data.result.data.name+'" style="max-width: 150px;"><div class="file-del"><a href="javascript:void(0)" onclick="delPic()" style="color:red">删除</a></div>');
        $('#goods_img').val(data.result.data.nameosspath);
        $('#local_goods_img').val(data.result.data.name);
    }
}).prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled');

function delPic() {
    $('#goods_img').val('');
    $('#goods-main-img').html('');
}

//多图上传
$(".goods-pic-ids input[type=file]").fileupload({
    url: "companyUpload",
    dataType: 'json',
    done: function (e, data) {
        var upload_item = $('.upload-item').length;
        if(upload_item == 8){
            $('.goods-pic-ids').hide();
        }
        $('.upload-img-box-ids').append('<div class="upload-item"><img src="'+data.result.src+'" style="max-width: 150px;">'+
            '<div class="file-del" onclick="delPicIds(this)" style="color:red;cursor:pointer">删除</div>'+
            '<input type="hidden" name="company_img_ids[]" value="'+data.result.src+'">'+
            '<input type="hidden" name="local_company_img_ids[]" value="'+data.result.src+'"></div>');
    }
}).prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled');

function delPicIds(obj) {
    $(obj).parent().remove();
    var upload_item = $('.upload-item').length;
    if(upload_item < 9){
        $('.goods-pic-ids').show();
    }
}