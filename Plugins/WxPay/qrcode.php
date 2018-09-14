<?php
/**
 * 生成支付二维码
 */
error_reporting(E_ERROR);
require_once 'example/phpqrcode/phpqrcode.php';
$data = $_GET["data"];
$size = $_GET["size"]?$_GET["size"]:6;
$margin = $_GET["margin"]?$_GET["margin"]:1;
QRcode::png($data,false,QR_ECLEVEL_L,$size,$margin);