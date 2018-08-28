<?php

require './mysql.class.php';
require './WxPayConf.php';
require './WxJsPay.php';

$config['appid']     = 'wx2ec1492fa0d063ce'; // 微信公众号身份的唯一标识
$config['appsecret'] = 'c46c59f48922049c8fc0020c6f0b6dcd'; // JSAPI接口中获取openid
$config['mchid']     = '1367054602'; // 受理商ID
$config['key']       = '31k2pez4aafhuru3xevtwipuoqumfa2u'; // 商户支付密钥Key

// 初始化WxPayConf_pub
$wxpaypubconfig = new \WxPayConf ( $config );

// 使用通用通知接口
$WxJsPay = new \WxJsPay ();

// 存储微信的回调
$xml = $GLOBALS ['HTTP_RAW_POST_DATA'];        
$WxJsPay->saveData ( $xml );

if ($WxJsPay->checkSign () == TRUE) {

	if ($WxJsPay->data ["return_code"] == "FAIL") {
	  	exit('fail');
	} elseif ($WxJsPay->data ["result_code"] == "FAIL") {
	  	exit('fail');
	} else {

		$order     = $WxJsPay->getData ();	
		$trade_no  = $order ["transaction_id"];
		$total_fee = $order ["total_fee"] / 100;
		$pkey      = $order ["attach"] ;
		$pkeys     = explode ( "@", $pkey );
		$orderId   = $pkeys [0];
		$userId    = $pkeys [1];         
		$gas_no    = $pkeys [2];

		if(!empty($orderId) && !empty($userId) && !empty($gas_no)) {

			$mysql = new MySQL('tempForGas', 'root', 'LIUniukeji12#$', 'localhost');

			$where            = array();
			$where['id']      = $orderId;
			$where['user_id'] = $userId;
			$where['gas_no']  = $gas_no;

			$order = $mysql->Select('ln_gas_order', $where, '',1);

			if(!empty($order) && $order['pay_status'] == 0) {

				$data['real_pay_money'] = $total_fee;
				$data['transid']        = $trade_no;
				$data['pay_time']       = time();
				$data['pay_status']     = 1;
				$data['order_status']   = 1;

				$mysql->update('ln_gas_order', $data, $where);
				$mysql->closeConnection();

			    echo "SUCCESS";
			    exit;
			    // 请不要修改或删除                  
			} else {
				$mysql->closeConnection();
				exit('fail');
			}             
		}              
	}
}
exit('fail');
?>