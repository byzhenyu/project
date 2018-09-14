<?php
/**
 * 余额充值
 */
namespace Common\Model;
use Think\Model;
class PayRechargeModel extends Model{
    protected $tableName = 'user_account'; 

    /**
     * 支付/微信充值
     * @param $money string 支付金额
     * @param $user_id int 会员id
     * @param $out_trade_no string 支付流水号
     * @return array
     */
    public function paySuccess($money, $user_id, $out_trade_no, $pay_bank){
        M()->startTrans(); // 开启事务
        //增加店铺保证金 并改完已缴纳保证金
        $money = yuan_to_fen($money);
        $map['user_id'] = $user_id;
        $changeMoney = M('User')->where($map)->setInc('user_money', $money);
        if ($changeMoney === false) {
            M()->rollback(); // 事务回滚
            return V(0, '充值失败');
        }
        //充值记录
        $recharge_log = $this->user_account_log($user_id, $money, $pay_bank, $out_trade_no);
        if ($recharge_log === false) {
            M()->rollback(); // 事务回滚
            return V(0, '充值失败');
        }
        $account_log = account_log($user_id, $money, 0, '充值');
        if ($account_log === false) {
            M()->rollback(); // 事务回滚
            return V(0, '充值失败');
        }
        M()->commit(); // 事务提交
        return V(1, '充值成功');
    }

    
    /**
     * 充值记录
     * @param $user_id int 会员id
     * @param $pay_bank int 支付类型 1支付宝 2微信 
     * @param $money string 金额
     * @param $trade_no string 支付流水号
     * @return array
     */
    private function user_account_log($user_id, $money, $pay_bank, $trade_no = '') {
        $data['user_id'] = $user_id;
        $data['type'] = 0;
        $data['money'] = $money;
        $data['payment'] = $pay_bank;
        $data['trade_no'] = $trade_no;
        $data['add_time'] = NOW_TIME;
        return $this->add($data);
    }

}