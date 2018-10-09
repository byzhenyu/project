<?php
ini_set('date.timezone','Asia/Shanghai');
error_reporting(E_ERROR);

require_once dirname(dirname(__FILE__))."/lib/WxPay.Api.php";
require_once dirname(dirname(__FILE__)).'/lib/WxPay.Notify.php';
require_once 'log.php';

//初始化日志
$logHandler= new CLogFileHandler("../logs/".date('Y-m-d').'.log');
$log = Log::Init($logHandler, 15);

class PayNotifyCallBack extends WxPayNotify
{
	//查询订单
	public function Queryorder($transaction_id)
	{
		$input = new WxPayOrderQuery();
		$input->SetTransaction_id($transaction_id);
		$result = WxPayApi::orderQuery($input);
		Log::DEBUG("query:" . json_encode($result));
		if(array_key_exists("return_code", $result)
			&& array_key_exists("result_code", $result)
			&& $result["return_code"] == "SUCCESS"
			&& $result["result_code"] == "SUCCESS")
		{
			return true;
		}
		return false;
	}
	
	//重写回调处理函数
	public function NotifyProcess($data, &$msg)
	{
		Log::DEBUG("call back:" . json_encode($data));
		$notfiyOutput = array();
		if(!array_key_exists("transaction_id", $data)){
			$msg = "输入参数不正确";
			return false;
		}
		//查询订单，判断订单真实性
		if(!$this->Queryorder($data["transaction_id"])){
			$msg = "订单查询失败";
			return false;
		}
        $out_trade_no = $data['out_trade_no'];//订单号
        //业务逻辑处理
        $orderModel = D('Order');
        $moneyLogModel = D('MoneyLog');
        $userModel = D('Users');
        //获取订单信息
        $orderInfo = $orderModel->getOrderInfo(array('order_no'=>$out_trade_no));
        M()->startTrans(); //开启事务
        // 修改订单状态，减去用户余额，记录相关日志
        $orderResult = $orderModel->balancePayOrder($orderInfo);
       // $userResult = $userModel->editUserMoney($orderInfo['user_id'],$orderInfo['payment_money']);
        $orderInfo['type'] = 1;
        $moneyLogResult = $moneyLogModel->addMoneyLog($orderInfo);
        //记录待奖励日志
        $daiMoney['payment_money'] = C('CAPITAL_GIVE_MONEY')/100*$orderInfo['money'];
        $daiMoney['user_id'] = $orderInfo['user_id'];
        $daiMoney['admin_id'] = $orderInfo['admin_id'];
        $daiMoney['id'] = $orderInfo['id'];
        $daiMoney['type'] = 2;
        $moneyLogResult1 = $moneyLogModel->addMoneyLog($daiMoney);
        //给平台返现
        D('Admin')->editAdminMoney($orderInfo['admin_id'],$orderInfo['money'],$orderInfo['id']);
        //资金池返现
        $orderModel->fanxianbaOper($orderInfo['admin_id']);
        //如果使用积分  减去积分 记录积分日志
        if($orderInfo['use_integral'] > 0){
            D('IntegralLog')->addIntegralLog(UID,$orderInfo['admin_id'],'-'.$orderInfo['use_integral']);
            $userModel->editUserIntegral($orderInfo['use_integral'],2);
        }
        if($orderResult &&  $moneyLogResult && $moneyLogResult1){
            M()->commit(); // 提交
            $msg = "支付成功";
            return true;
        }else{
            M()->rollback(); // 回滚
            $msg = "支付失败";
           return false;
        }
		return true;
	}
}

Log::DEBUG("begin notify");
//$notify = new PayNotifyCallBack();
//$notify->Handle(false);
