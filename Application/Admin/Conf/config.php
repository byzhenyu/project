<?php
/**
 * Created by PhpStorm.
 * User: jipingzhao
 * Date: 6/19/17
 * Time: 2:22 PM
 */
return array(
    // 定义定单状态
//    ORDER_STATUS => array(
//        0 => '未支付',
//        2 => '待发货',
//        3 => '已发货',
//        4 => '已完成',
//        1 => '已取消',
//    ),
    'URL_HTML_SUFFIX'       =>  '',  // URL伪静态后缀设置

    // 定义支付方式
    PAY_BANK => array(
        0 => '未付款',
        1 => '支付宝',
        2 => '微信',
        4 => '余额支付',
    ),

    //商品状态对应数据
    GOODS_STATUS => array(
        1 => '精品',
        2 => '新品',
        3 => '热销',
        4 => '推荐',
        5 => '上架',
        6 => '下架',
        7 => '库存警告',
    ),

    //商品属性录入方式
    ATTR_INPUT_TYPE => array(
        0 => '手工录入',
        1 => '从列表中选择',
        2 => '多行文本框'
    ),
    /* 模板相关配置 */
    /*'TMPL_PARSE_STRING' => array(
        '__UPLOADS__'   => __ROOT__ . '/'. APP_NAME . '/Uploads/',
        '__IMG__'       => __ROOT__ . '/'. APP_NAME . '/Admin/Static/images',
        '__CSS__'       => __ROOT__ . '/'. APP_NAME . '/Admin/Static/css',
        '__JS__'        => __ROOT__ . '/'. APP_NAME . '/Admin/Static/js',
        '__PUBLIC_IMG__' => __ROOT__ . '/Public/Static/images',
        '__COMMON__'     => __ROOT__ . '/'. APP_NAME . '/Common/Static',
        '__MAIN__'       => __ROOT__ . '/'. APP_NAME . '/Admin/Static/main',
    ),*/

    //退款退货状态
    // REFUND_STATUS => array(
    //     1 => '处理中',
    //     2 => '待处理',
    //     3 => '已完成'
    // ),

);