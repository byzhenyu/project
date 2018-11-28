//多图上传
$(".goods-pic-ids input[type=file]").fileupload({
    url: GOODS_IMG_UPLAOD_URL,
    dataType: 'json',
    done: function (e, data) {

        $('.upload-img-box-ids').append('<div class="upload-item"><img src="'+data.result.data.name+'" style="max-width: 150px;">'+
            '<div class="file-del" onclick="delPicIds(this)" style="color:red;cursor:pointer">删除</div>'+
            '<input type="hidden" name="goods_img_ids[]" value="'+data.result.data.nameosspath+'">'+
            '<input type="hidden" name="local_goods_img_ids[]" value="'+data.result.data.name+'"></div>');
    }
}).prop('disabled', !$.support.fileInput)
    .parent().addClass($.support.fileInput ? undefined : 'disabled');

function delPicIds(obj) {
    $(obj).parent().remove();
}