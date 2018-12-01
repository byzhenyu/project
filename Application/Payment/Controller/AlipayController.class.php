<?php
/**
 * 支付相关
 * by zhoaojiping liuniukeji.com <QQ: 17620286>
 */
namespace Payment\Controller;
use Common\Controller\CommonController;

class AlipayController extends CommonController {

    // alipay 定单支付  type  0 余额充值 1 保证金充值
    public function alipay(){
        $type =  I('type', 0, 'intval');
        $data['user_id'] = UID;
        $data['recharge_money'] = I('recharge_money',0 , 'intval');
        $order_sn = makeOrderSn($data['user_id']);
        if($type == 0){
            $data['order_sn'] = 'T'.$order_sn;
            $data['recharge_type'] = 0;
        }else{
            $data['order_sn'] = 'B'.$order_sn;
            $data['recharge_type'] = 1;
        }
        $data['add_time'] = NOW_TIME;
        M('recharge')->add($data);
        $data['body'] = C('APP_NAME').'网页充值';
        $data['subject'] = C('APP_NAME').'网页充值';
        $data['out_trade_no'] =  $data['order_sn'];
        $data['total_amount'] = '0.01';
        header("Content-type: text/html; charset=utf-8");
        require_once("./Plugins/AliPay/AliPay.php");
        $alipay = new \AliPay();
        echo '页面跳转中, 请稍后...';
        echo $alipay->AliPayWeb($data);
    }
    //移动web支付 示例
    public function mobileWebPay() {
        $type =  I('type', 0, 'intval');
        $data['user_id'] = UID;
        $recharge_money = I('recharge_money',0 , 'intval');

        $data['recharge_money'] = yuan_to_fen($recharge_money);
        $order_sn = makeOrderSn($data['user_id']);
        if($type == 0){
            $data['order_sn'] = 'T'.$order_sn;
            $data['recharge_type'] = 0;
        }else{
            $data['order_sn'] = 'B'.$order_sn;
            $data['recharge_type'] = 1;
        }
        $data['add_time'] = NOW_TIME;
        M('recharge')->add($data);
        $data['body'] = C('APP_NAME').'H5充值';
        $data['subject'] = C('APP_NAME').'H5充值';
        $data['out_trade_no'] =  $data['order_sn'];
        $data['total_amount'] = '0.01';

        require_once("./Plugins/AliPay/AliPay.php");
        $alipay =new \AliPay();
        echo '页面跳转中, 请稍后...';
        echo $alipay->AliPayMobileWeb($data);

    }
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
                LL($total_amount,'./log/log1.txt');
                //成功后的业务逻辑处理
                $result = D('Common/Recharge')->paySuccess($out_trade_no, $total_amount, $trade_no, 1);
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