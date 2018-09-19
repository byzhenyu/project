//根据类型获取商品属性
$('#goods_type').change(function(){
    var goods_id = $("input[name='goods_id']").val();
    var type_id = $(this).val();
    $("input[name='model_id']").val(type_id);
    getGoodsAttr(goods_id, type_id);
});

function getGoodsAttr(goods_id, type_id) {
    $.ajax({
        type: 'GET',
        data: {goods_id: goods_id, type_id: type_id},
        url: ATTR_INPUT_URL,
        success: function (data) {
            $("#goods_attr_table tr:gt(0)").remove()
            $("#goods_attr_table").append(data);
        }
    });
}

//根据类型获取商品规格
$('#goods_spec_type_id').change(function(){
    $('.is_spec_selected').val($(this).val());
    var goods_id = $("input[name='goods_id']").val();
    var type_id = $(this).val();
    $("input[name='is_spec']").val(type_id);
    getGoodsSpec(goods_id, type_id);
});
function getGoodsSpec(goods_id, type_id) {
    if(1 == parseInt(type_id)){
        $.ajax({
            type: 'GET',
            data: {goods_id: goods_id, type_id: type_id},
            url: SPEC_INPUT_URL,
            success: function (data) {
                $("#ajax_spec_data").html('')
                $("#ajax_spec_data").append(data);
                ajaxGetSpecInput();
            }
        });
    }
    else{
        $("#ajax_spec_data").html('');
    }
}


//单图上传
$('#upload').fileupload({
    url: GOODS_IMG_UPLAOD_URL,
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