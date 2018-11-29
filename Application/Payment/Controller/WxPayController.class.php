<?php
/**
 * 微信支付回调
 * by zhoaojiping liuniukeji.com <QQ: 17620286>
 */
namespace Payment\Controller;
use Common\Controller\CommonController;

class WxPayController extends CommonController {

    // 定单支付回调
    public function wxNotify() {
        require_once("Plugins/WxPay/WxPay.php");
        $wxPay = new \WxPay();

        //验证是否是支付宝发送
        $flag = $wxPay->WxPayNotifyCheck();
        LL($flag,'./log1.txt');
        //验证成功
        if ($flag['status']) {
            if ($flag['data']['return_code'] == 'SUCCESS' && $flag['data']['result_code'] == 'SUCCESS') {
                $out_trade_no = trim($flag['data']['out_trade_no']);//商家订单号
                LL($out_trade_no,'./log1.txt');
                $total_amount = trim($flag['data']['total_fee']); //支付的金额
                $trade_no = trim($flag['data']['transaction_id']); //商户订单号
                //成功后的业务逻辑处理
                $trade_no_array = explode('-', $out_trade_no);
                $user_id = $trade_no_array[1];
                $type = substr($out_trade_no, 0 , 1);
                if($type == 'W'){
                    $pay_bank = 2;
                }else{
                    $pay_bank = 1;
                }
                $result = D('Common/PayRecharge')->paySuccess(fen_to_yuan($total_amount), $user_id, $trade_no, $pay_bank);
                if ($result['status'] == 1) {
                    $r_arr['return_code'] = 'SUCCESS';
                    $r_arr['return_msg'] = '回调成功';
                    echo $wxPay->arrayToXml($r_arr);
                    die;
                }
            }
        }
        $r_arr['return_code'] = 'FAIL';
        $r_arr['return_msg'] = '回调失败';
        echo $wxPay->arrayToXml($r_arr);
        die;
    }
}