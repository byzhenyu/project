<?php
/**
 * 支付相关
 * by zhoaojiping liuniukeji.com <QQ: 17620286>
 */
namespace Payment\Controller;
use Common\Controller\CommonController;

class AlipayController extends CommonController {

    // 定单支付回调
    public function alipayNotify() {
        require_once("./Plugins/AliPay/AliPay.php");
        $alipay = new \AliPay();
        //p($_POST);
        //验证是否是支付宝发送
        $flag = $alipay->AliPayNotifyCheck();
        LL($_POST,'./log/log2.txt');
        if ($flag) {
            if ($_POST['trade_status'] == 'TRADE_FINISHED' || $_POST['trade_status'] == 'TRADE_SUCCESS') {
                $out_trade_no = trim($_POST['out_trade_no']); //商户订单号
                $total_amount = trim($_POST['total_amount']); //支付的金额
                $trade_no = trim($_POST['trade_no']); //商户订单号
                //成功后的业务逻辑处理
                $trade_no_array = explode('-', $out_trade_no);
                $user_id = $trade_no_array[1];
                $result = D('Common/PayRecharge')->paySuccess($total_amount, $user_id, $trade_no, 1, $out_trade_no);
                if ($result['status'] == 1) {
                    echo "success"; //  告诉支付宝支付成功 请不要修改或删除
                    die;
                } else {
                    LL($result);
                }
            }
        }
        echo "fail"; //验证失败
        die;
    }
}