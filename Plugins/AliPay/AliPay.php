<?php

/**
 * 支付宝插件 使用方法请查看同文件夹下的demo
 * 目前已经支持电脑网站支付，手机APP支付，支付回调校验，用户提现等功能，如需拓展请联系作者
 * @author Jack_YanTC <627495692@qq.com>
 */
class AliPay {

    private $appId;
    private $rsaPrivateKey;
    private $signType;
    private $alipayrsaPublicKey;
    private $notifyUrl;
    private $returnUrl;

    /**
     * 初始化参数
     *
     * @param array $options
     * @param $options ['appId']  应用ID，在支付宝上获取
     * @param $options ['rsaPrivateKey'] 应用密钥，与应用公钥一组，公钥填写到支付宝
     * @param $options ['signType'] 签名方式
     * @param $options ['alipayrsaPublicKey'] 支付宝公钥，在支付宝上获取
     * @param $options ['notifyUrl'] 支付宝回调地址
     * @param $options ['returnUrl'] 用于web支付返回地址
     */
    public function __construct($options = null) {
        $this->appId = isset ($options ['appId']) ? $options ['appId'] : C('AliPay')['appId'];
        $this->rsaPrivateKey = isset ($options ['rsaPrivateKey']) ? $options ['rsaPrivateKey'] : C('AliPay')['rsaPrivateKey'];
        $this->signType = isset ($options ['signType']) ? $options ['signType'] : C('AliPay')['signType'];
        $this->notifyUrl = isset ($options ['notifyUrl']) ? $options ['notifyUrl'] : C('AliPay')['notifyUrl'];
        $this->alipayrsaPublicKey = isset ($options ['alipayrsaPublicKey']) ? $options ['alipayrsaPublicKey'] : C('AliPay')['alipayrsaPublicKey'];
        $this->returnUrl = isset ($options ['returnUrl']) ? $options ['returnUrl'] : C('AliPay')['returnUrl'];
    }

    /**
     * 支付宝app支付 需要签约 APP支付
     * @param string $data 业务参数 body subject out_trade_no total_amount
     * @param string $data['out_trade_no'] 订单号  必填
     * @param string $data['total_amount'] 订单金额  必填
     * @param string $data['subject'] 订单标题  必填
     * @param string $data['body'] 订单详情  可选
     * @return $response 返回app所需字符串
     */
    public function AliPayApp($data) {
        if (empty($this->appId))
            return false;
        $aliPayPath = './Plugins/AliPay/alipay-sdk/';
        require_once($aliPayPath . "aop/AopClient.php");
        require_once($aliPayPath . 'aop/request/AlipayTradeAppPayRequest.php');
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = $this->signType;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeAppPayRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode([
            'body' => $data['body'],
            'subject' => $data['subject'],
            'out_trade_no' => $data['out_trade_no'],//此订单号为商户唯一订单号
            'total_amount' => $data['total_amount'],//保留两位小数
            'timeout_express' => '30m',
            'product_code' => 'QUICK_MSECURITY_PAY'
        ]);
        $request->setNotifyUrl($this->notifyUrl);
        $request->setBizContent($bizcontent);
        //这里和普通的接口调用不同，使用的是sdkExecute
        $response = $aop->sdkExecute($request);
        //htmlspecialchars是为了输出到页面时防止被浏览器将关键参数html转义，实际打印到日志以及http传输不会有这个问题
        //return htmlspecialchars($response);//就是orderString 可以直接给客户端请求，无需再做处理。
        //返回app所需字符串
        return $response;
    }

    /**
     * 支付宝web支付 需要签约 电脑网站支付
     * @param string $data 业务参数
     * @param string $data['out_trade_no'] 订单号  必填
     * @param string $data['total_amount'] 订单金额  必填
     * @param string $data['subject'] 订单标题  必填
     * @param string $data['body'] 订单详情  可选
     * @return $result 返回form表单，插入到当前网页即跳转到支付宝付款界面
     */
    public function AliPayWeb($data) {
        if (empty($this->appId))
            return false;
        $aliPayPath = './Plugins/AliPay/alipay-sdk/';
        require_once($aliPayPath . "aop/AopClient.php");
        require_once($aliPayPath . 'aop/request/AlipayTradePagePayRequest.php');
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->signType = $this->signType;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        $aop->apiVersion = '1.0';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new \AlipayTradePagePayRequest();
        $bizcontent = json_encode([
            'body' => $data['body'],
            'subject' => $data['subject'],
            'out_trade_no' => $data['out_trade_no'],//此订单号为商户唯一订单号
            'total_amount' => $data['total_amount'],//保留两位小数
            'product_code' => 'FAST_INSTANT_TRADE_PAY'
        ]);
        $request->setNotifyUrl($this->notifyUrl);
        $request->setReturnUrl($this->returnUrl);
        $request->setBizContent($bizcontent);
        $result = $aop->pageExecute($request);
        //返回form提交表单
        return $result;
    }

    /**
     * 支付宝MobileWeb支付 需要签约 手机网站支付
     * @param string $data 业务参数
     * @param string $data['out_trade_no'] 订单号  必填
     * @param string $data['total_amount'] 订单金额  必填
     * @param string $data['subject'] 订单标题  必填
     * @param string $data['body'] 订单详情  可选
     * @return $result 返回form表单，插入到当前网页即跳转到支付宝付款界面
     */
    public function AliPayMobileWeb($data) {
        if (empty($this->appId))
            return false;
        $aliPayPath = './Plugins/AliPay/alipay-sdk/';
        require_once($aliPayPath . "aop/AopClient.php");
        require_once($aliPayPath . 'aop/request/AlipayTradeWapPayRequest.php');
        $aop = new \AopClient();
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->signType = $this->signType;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        $aop->apiVersion = '1.0';
        $aop->postCharset = 'UTF-8';
        $aop->format = 'json';
        $request = new \AlipayTradeWapPayRequest ();
        $bizcontent = json_encode([
            'body' => $data['body'],
            'subject' => $data['subject'],
            'out_trade_no' => $data['out_trade_no'],//此订单号为商户唯一订单号
            'total_amount' => $data['total_amount'],//保留两位小数
            'timeout_express' => '90m',
            'product_code' => 'QUICK_WAP_WAY'
        ]);
        $request->setNotifyUrl($this->notifyUrl);
        $request->setReturnUrl($this->returnUrl);
        $request->setBizContent($bizcontent);
        $result = $aop->pageExecute($request);
        //返回form提交表单
        return $result;
    }

    /**
     * 支付宝支付回调签名验证
     * @param string $data 业务参数
     * @return bool
     */
    public function AliPayNotifyCheck() {
        $aliPayPath = './Plugins/AliPay/alipay-sdk/';
        require_once($aliPayPath . "aop/AopClient.php");
        $aop = new \AopClient;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        //此处验签方式必须与下单时的签名方式一致
        $flag = $aop->rsaCheckV1($_POST, NULL, $this->signType);
        return $flag;
    }

    /**
     * 支付宝提现转账 需要签约 单笔转账到支付宝账户接口
     * @param string $data 业务参数
     * @param $data['out_biz_no'] 订单号  必填
     * @param $data['amount'] 提现金额 必填 金额不小于0.1元,单日转出累计额度为100万元,转账给个人支付宝账户，单笔最高5万元；转账给企业支付宝账户，单笔最高10万元。
     * @param $data['payee_account'] 收款支付宝账号  必填
     * @param $data['payee_real_name'] 收款支付宝账号真实姓名  最好填上 填上会验证账号是否正确
     * @param $data['payer_show_name'] 付款方姓名  可选
     * @param $data['remark'] 转账提现备注  可选
     * @return bool
     */
    public function AliPayWithdraw($data) {
        if (empty($this->appId))
            return false;
        if ($data['amount']<0.1)
            return false;
        $aliPayPath = './Plugins/AliPay/alipay-sdk/';
        require_once($aliPayPath . "aop/AopClient.php");
        require_once($aliPayPath . 'aop/request/AlipayFundTransToaccountTransferRequest.php');
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = $this->signType;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayFundTransToaccountTransferRequest ();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode([
            'out_biz_no' => $data['out_biz_no'],//此订单号为商户唯一订单号
            'payee_type' => 'ALIPAY_LOGONID',//默认登录账号，后期可拓展
            'payee_account' => $data['payee_account'],
            'amount' => $data['amount'],
            'payee_real_name' => $data['payee_real_name'],
            'payer_show_name' => $data['payer_show_name'],
            'remark' => $data['remark'],
        ]);
        $request->setBizContent($bizcontent);
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            //echo "成功"; 提现成功
            return true;
        } else {
            //echo "失败";
            return false;
        }
    }

    /**
     * 支付宝订单退款
     * @param string $data 业务参数
     * @param $data['out_trade_no'] 订单号  必填
     * @param $data['refund_amount'] 退款金额 必填
     * @return bool
     */
    public function AliPayRefund($data) {
        if (empty($this->appId))
            return false;
        $aliPayPath = './Plugins/AliPay/alipay-sdk/';
        require_once($aliPayPath . "aop/AopClient.php");
        require_once($aliPayPath . 'aop/request/AlipayTradeRefundRequest.php');
        $aop = new \AopClient;
        $aop->gatewayUrl = "https://openapi.alipay.com/gateway.do";
        $aop->appId = $this->appId;
        $aop->rsaPrivateKey = $this->rsaPrivateKey;
        $aop->format = "json";
        $aop->charset = "UTF-8";
        $aop->signType = $this->signType;
        $aop->alipayrsaPublicKey = $this->alipayrsaPublicKey;
        //实例化具体API对应的request类,类名称和接口名称对应,当前调用接口名称：alipay.trade.app.pay
        $request = new \AlipayTradeRefundRequest();
        //SDK已经封装掉了公共参数，这里只需要传入业务参数
        $bizcontent = json_encode([
            'out_trade_no' => $data['out_trade_no'],//此订单号为商户唯一订单号
            'refund_amount' => $data['refund_amount'],
        ]);
        $request->setBizContent($bizcontent);
        $result = $aop->execute($request);
        $responseNode = str_replace(".", "_", $request->getApiMethodName()) . "_response";
        $resultCode = $result->$responseNode->code;
        if(!empty($resultCode)&&$resultCode == 10000){
            //echo "成功"; 提现成功
            return true;
        } else {
            //echo "失败";
            return false;
        }
    }
}