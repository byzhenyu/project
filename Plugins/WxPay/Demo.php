<?php

/**
 * 微信插件Demo
 * @author Jack_YanTC <627495692@qq.com>
 */
class Demo {

    //config 示例
    public $config = array(

        /* 微信支付相关配置 */
        'WxPay' => array(
            #微信商户平台应用APPID
            'app_id' => 'wx278f0cfe8cf5be1c',
            #商户号
            'mch_id' => '1482937212',
            //api密钥
            'key' => 'yiwenjiaoyuyiwenjiaoyuyiwenjiaoy',
            #异步回调地址
            'notify_url' => 'http://yiwen.host3.liuniukeji.com/index.php/Home/Payment/wechatNotify',
            //公众帐号secert（仅JSAPI支付的时候需要配置)
            'appsecret' => 'e2970084a53543160d8e93a865d94621',
        )

    );


    //app支付 示例
    public function appPay() {
        $data['body'] = '六牛科技';//订单详情
        $data['out_trade_no'] = '201705201314';//订单号
        $data['total_fee'] = '0.01';//订单金额元
        require_once("Plugins/WxPay/WxPay.php");
        $wxPay = new \WxPay();
        //返回微信app支付所需字符串
        $result = $wxPay->WxPayApp($data);

        return $result;
    }

    //web支付 示例
    public function webPay() {
        $data['body'] = '六牛科技';//订单详情
        $data['out_trade_no'] = '201705201314';//订单号
        $data['total_fee'] = '0.01';//订单金额元
        require_once("Plugins/WxPay/WxPay.php");
        $wxPay = new \WxPay();
        //返回支付二维码图片的url地址，网页里直接如下调用
        //<img alt="扫描二扫码支付" src="{$result}"/>;
        $result = $wxPay->WxPayWeb($data);
        redirect($result);
//        $this->result =$result;
//        $this->display('index');
    }

    //微信回调地址 示例
    public function WxNotify() {
        require_once("Plugins/WxPay/WxPay.php");
        $wxPay = new \WxPay();
        //验证是否是微信发送且数据完整
        $flag = $wxPay->WxPayNotifyCheck();
        if ($flag['status']) {
            if ($flag['data']['return_code'] == 'SUCCESS' && $flag['data']['result_code'] == 'SUCCESS') {
                $out_trade_no = $flag['data']['out_trade_no'];//订单号

                //业务逻辑处理
                $result = D('Common/Recharge')->notify($out_trade_no);

                if ($result) {
                    $r_arr['return_code'] = 'SUCCESS';
                    $r_arr['return_msg'] = '回调成功';
                    echo $this->arrayToXml($r_arr);
                    die;
                }
            }
        }
        $r_arr['return_code'] = 'FAIL';
        $r_arr['return_msg'] = '回调失败';
        echo $this->arrayToXml($r_arr);
        die;

    }
}