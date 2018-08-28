/**
 * 前台通用的函数库
 * by zhaojpiing QQ: 17620286
 */

$(function() {

});

/**
 * toastr message
 * 弹出信息
 */
function toastr(msg) {
    $('.tips').html(msg);
}

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
    layParams['shadeClose']    = false;
    layParams['shade']         = 0.8;
    layParams['width']         = '650px';
    layParams['height']        = '500px';
    layParams['titleColor']    = '#666';
    layParams['titleBackground']    = '#f2f2f2';
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
  * 判断手机号码是否正确
  */
function isMobile(value) {
    var length = value.length;
    var mobile = /^(1(([23578][0-9])|[4][57]))\d{8}$/;
    if (length == 11 && mobile.test(value)) return true
    else return false;
}


/**
  * 商品数量加减，改变购物车数量
  */
function setCartGoodsNum() {
    $('.setAmount').on('click', '.reduce', function () {
        var input = $(this).next().find('input'),
            val = parseInt(input.val()),
            min = parseInt(input.attr('data-min'));
        if (val > min) {
            val--;
            input.val(val);
            input.attr('data-num', val);
        }
        return false;
    }).on('click', '.add', function () {
        var input = $(this).prev().find('input'),
            val = parseInt(input.val()),
            max = parseInt(input.attr('data-max'));
        if (val < max) {
            val++;
            input.val(val);
            input.attr('data-num', val);
        }
        return false;
    }).on('blur', 'input[name="num"]', function () {
        testVal($(this));
    }).on('keyup', 'input[name="num"]', function () {
        testVal($(this));
    });
    function testVal(elm) {
        var val = elm.val(),
            num = elm.attr('data-num'),
            min = elm.attr('data-min'),
            max = elm.attr('data-max');
        if (!/^[0-9]*$/.test(val)) {
            elm.val(num);
            return;
        }
        var a = parseInt(val, 10);
        return "" == elm ? !1 : isNaN(a) || a < min || a > max ? (elm.val(num), !1) : (elm.attr('data-num', a));
    }
}

/**
 * 商品价格转换 分-》元
 * @param fen
 * @return string
 */
function fen_to_yuan(fen) {
    yuan = parseInt(fen)/100;
    return yuan.toFixed(2);
}