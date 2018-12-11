/**
 * 后台通用的函数库
 * by zhaojpiing QQ: 17620286
 */

$(function() {
    //全选的实现
    $(".check-all").click(function () {
        $(".ids").prop("checked", this.checked);
    });
    $(".ids").click(function () {
        var option = $(".ids");
        option.each(function (i) {
            if (!this.checked) {
                $(".check-all").prop("checked", false);
                return false;
            } else {
                $(".check-all").prop("checked", true);
            }
        });
    });
});

/** 
 * 封装layer打开窗口的方法, 前台调用
 * @param string url 跳转弹窗 标题页面路径
 * @param string title 弹窗 标题
 * @param Array params layer需要的其他参数，一般传递null即可
 * @return null 没有返回值
 * 特殊情况需要传递params时，请参考layer的参数设置传递下面的各个参数
*/
function openLayerPopup(url, title, params){
    layParams = new Array();
    layParams['type']          = 2;
    layParams['shadeClose']    = true;
    layParams['shade']         = 0.8;
    layParams['width']         = '800px';
    layParams['height']        = '500px';
    layParams['titleColor']    = '#fff';
    layParams['titleBackground']    = '#aaa';
    if (typeof(params) != 'undefined' && params != '' && params != null) {
        layParams['type']          = params['type']         || layParams['type'];
        //单独处理一下shadeClose的数据
        if (params['shadeClose'] != null && params['shadeClose'] != undefined) {
            layParams['shadeClose'] = params['shadeClose'];
        }
        layParams['shade']         = params['shade']        || layParams['shade'];
        layParams['width']         = params['width']        || layParams['width'];
        layParams['height']        = params['height']       || layParams['height'];
        layParams['titleColor']    = params['titleColor']   || layParams['titleColor'];
        layParams['titleBackground'] = params['titleBackground'] || layParams['titleBackground'];
    }
    
    // console.log(layParams);
    parent.layer.open({
        type: layParams['type'], 
        shadeClose: layParams['shadeClose'],
        shade: layParams['shade'],
        area: [layParams['width'], layParams['height']],
        title:  [title, 'color: '+ layParams['titleColor'] +'; background:'+ layParams['titleBackground'] +';'],
        content: url
    });
}

/**
 * message
 * 弹出信息
 */
function toastr(msg) {
    parent.window.layer.msg(msg, {
        offset: 0,
        shift: 6
    });
}

/**
 * 按主键的值进行删除数据表中的记录
 * id:主键的值. 当值为checkbox时, 由checkbox生成
 * message:弹出确认对话框的信息;
 * isDelete: 是否为物理删除
 * RECYCLE_URL: 全局变量, 删除的处理URL
 */
function recycle(id, message, isDelete){
    if(id == 'chkbId'){
        id = checkedIds('chkbId');
        //console.log(id);return;
        if(id == false)  return false;
    }
    var url = '';
    if (isDelete === true) url = DELETE_URL + "/id/" + id;
    else url = RECYCLE_URL + "/id/" + id;

    // alert(url);// return false;
    if(confirm(message)){
         $.get(url, function(data){
            if(data.status == '1'){
                window.location.reload();
                toastr(data.info);
            }else{
            	if (typeof(data.info) != 'undefined') {
            		toastr(data.info);
            	} else {
            		console.log(data);
            	}
            }
        });
    }
}

/**
 * 按主键的值还原逻辑删除的数据
 * id:主键的值. 当值为checkbox时, 由checkbox生成
 * RESTORE_URL: 全局变量, 还原的处理URL
 */
function restore(id) {
    if(id == 'chkbId'){
        id = checkedIds('chkbId');
		//console.log(id);return;
        if(id == false)  return false;
    }

    var url = RESTORE_URL + "/id/" + id;

    // console.log(url);// return false;
    $.get(url, function(data){
        if(data.status == '1'){
            window.location.reload();
            toastr(data.info);
        }else{
        	if (typeof(data.info) != 'undefined') toastr(data.info);
			else console.log(data);
        }
    });
}

// 组织选中的复选框
function checkedIds(objName) {
    var ids = "";
    $("input[name='" + objName + "']:checked").each(function() {
        ids += $(this).val() + ",";
    });

    if (ids == "") {
        alert("没有选择要操作的数据!");
        return false;
    }
    return ids.substring(0, ids.length-1);
}

// 改变数据的可用状态 
function change_disabled(id, value){
	var url = CHANGE_STAUTS_URL + "/id/" + id + "/disabled/" + value;
    // console.log(url);// return false;
    $.get(url, function(data){
        if(data.status == '1'){
            window.location.reload();
            toastr(data.info);
        }else{
        	if (typeof(data.info) != 'undefined') toastr(data.info);
			else console.log(data);
        }
    });
}