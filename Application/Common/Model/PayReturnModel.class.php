<?php
/**
 * 支付完成后的回调, 写入数据库
 */
namespace Common\Model;
use Think\Model;
class PayReturnModel extends Model{
    protected $tableName = 'Order'; 

    /**
     * 支付成功后回调
     * @param $out_trade_no string 自家定单号
     * @param $total_amount int 支付的金额
     * @param $trade_no string 支付平台交易号
     * @param $pay_bank int 支付的银行  支付方式 1：支付宝 2：微信 3：银联支付 4：余额支付 5：好友代付
     * @return array
     */
    public function paySuccess($out_trade_no='', $total_amount=0, $trade_no='', $pay_bank=-1){
        // LL($out_trade_no .'|'. $total_amount .'|'. $trade_no .'|'. $pay_bank );
        if ($out_trade_no == '') return V(0, '参数错误, 需要自家定单号: $out_trade_no');
        if ($total_amount < 0) return V(0, '参数错误, 支付金额不正确: $total_amount');
        if ($trade_no == '') return V(0, '参数错误, 需要支付平台交易号: $trade_no');
        if (intval($pay_bank) == -1) return V(0, '参数错误, 支付方式不正确: $pay_bank');

        //根据订单号查看支付类型
        $pay_type = substr($out_trade_no, 0, 1);
        //if ($pay_type == 'C') { //余额充值
            return $this->rechargePay($out_trade_no, $total_amount, $trade_no, $pay_bank);
        //}

    }
    /**
     * 余额充值成功支付处理
     */
    public function rechargePay($out_trade_no, $total_amount, $trade_no, $pay_bank) {
        $trade_no_array = explode('-', $out_trade_no);
        $user_id = $trade_no_array[1];
        $result = D('Common/PayRecharge')->paySuccess($total_amount, $user_id, $trade_no, $pay_bank);
        return $result;
    }

}