//单图上传
$('#upload').fileupload({
    url: SHOP_IMG_UPLAOD_URL,
    dataType: 'json',
    done: function (e, data) {
        $('#shop-main-img').html('<img src="'+data.result.data+'" style="max-width: 150px;"><div class="file-del"><a href="javascript:void(0)" onclick="delPic()" style="color:red">删除</a></div>');
        $('#shop_logo').val(data.result.data);
    }
}).prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled');

function delPic() {
    $('#shop_logo').val('');
    $('#shop-main-img').html('');
}
//单图上传1
$('#upload1').fileupload({
    url: SHOP_IMG_UPLAOD_URL,
    dataType: 'json',
    done: function (e, data) {
        $('#shop-main-img1').html('<img src="'+data.result.data+'" style="max-width: 150px;"><div class="file-del"><a href="javascript:void(0)" onclick="delPic()" style="color:red">删除</a></div>');
        $('#shop_real_pic').val(data.result.data);
    }
}).prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled');

function delPic1() {
    $('#shop_real_pic').val('');
    $('#shop-main-img1').html('');
}
//多图上传
$(".goods-pic-ids input[type=file]").fileupload({
    url: GOODS_IMG_UPLAOD_URL,
    dataType: 'json',
    done: function (e, data) {
        $('.upload-img-box-ids').append('<div class="upload-item"><img src="'+data.result.data+'" style="max-width: 150px;">'+
            '<div class="file-del" onclick="delPicIds(this)" style="color:red;cursor:pointer">删除</div>'+
            '<input type="hidden" name="goods_img_ids[]" value="'+data.result.data+'"></div>');
    }
}).prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled');

function delPicIds(obj) {
    $(obj).parent().remove();
}