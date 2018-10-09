<?php

class H5 {

    public function withdraw($orderInfo=array()){
        $url = "https://api.mch.weixin.qq.com/pay/unifiedorder";
        $data['appid'] = C('WxPay')['app_id'];
        $data['mch_id'] = C('WxPay')['mch_id']; //商户号
        $data['nonce_str'] = $this->getRandChar(32); //随机字符串
        $data['body'] = "数维财富链-用户支付";   //商品描述
        $data['out_trade_no'] = $orderInfo['order_no']; // 订单号
        $data['total_fee'] = $orderInfo['payment_money']*100; //订单金额
        $data['spbill_create_ip'] = get_client_ip();  //终端IP
        $data['notify_url'] = "https://".$_SERVER['HTTP_HOST']."/Api/Wxpay/notify_url";//回调地址
        $data['trade_type'] = "MWEB"; //交易类型
        $data['scene_info'] = 3;//交易场景
        $data['sign'] = $this->getSign($data); //签名
        $xml=$this->arrayToXml($data);
        $result = $this->url($xml,$url);
        return $result;
    }

    // 获取指定长度的随机字符串
    private function getRandChar($length) {
        $str = null;
        $strPol = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789abcdefghijklmnopqrstuvwxyz";
        $max = strlen($strPol) - 1;

        for ($i = 0; $i < $length; $i++) {
            $str .= $strPol [rand(0, $max)]; // rand($min,$max)生成介于min和max两个数之间的一个随机整数
        }

        return $str;
    }

    /**
     * 	作用：生成签名
     */
    private function getSign($Obj){
        foreach ($Obj as $k => $v) {
            $Parameters[$k] = $v;
        }
        //签名步骤一：按字典序排序参数
        ksort($Parameters);
        $String = $this->formatBizQueryParaMap($Parameters, false);
        //签名步骤二：在string后加入KEY
        $String = $String."&key=".C('WxPay')['key'];
        //签名步骤三：MD5加密
        $String = md5($String);
        //签名步骤四：所有字符转为大写
        $result_ = strtoupper($String);
        return $result_;
    }
    /**
     * 	作用：格式化参数，签名过程需要使用
     */
    private function formatBizQueryParaMap($paraMap, $urlencode){
        $buff = "";
        ksort($paraMap);
        foreach ($paraMap as $k => $v) {
            if($urlencode) {
                $v = urlencode($v);
            }
            $buff .= $k . "=" . $v . "&";
        }
        $reqPar = "";
        if (strlen($buff) > 0) {
            $reqPar = substr($buff, 0, strlen($buff)-1);
        }
        return $reqPar;
    }

    /**
     * curl
     */
    private function url($xml,$url ,$second=30){
        $ch=curl_init();
        curl_setopt($ch,CURLOPT_URL,$url);
        curl_setopt($ch,CURLOPT_HEADER,FALSE);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,TRUE);
        curl_setopt($ch,CURLOPT_SSL_VERIFYPEER,TURE);//证书检查
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_SSLCERT,getcwd().'/Plugins/WxPay/cert/apiclient_cert.pem');
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_SSLKEY,getcwd().'/Plugins/WxPay/cert/apiclient_key.pem');
        curl_setopt($ch,CURLOPT_SSLCERTTYPE,'pem');
        curl_setopt($ch,CURLOPT_CAINFO,getcwd().'/Plugins/WxPay/cert/rootca.pem');
        curl_setopt($ch,CURLOPT_POST,TRUE);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$xml);

        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // 要求结果为字符串且输出到屏幕上

        $data=curl_exec($ch);
        if($data){
            curl_close($ch);
            $re = $this->xmlstr_to_array($data);
            return $re;
        }else{
            $error=curl_errno($ch);
            echo "curl出错，错误代码：$error"."<br/>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurs.html'>;错误原因查询</a><br/>";
            curl_close($ch);
            echo false;
        }

        // 初始化curl
        $ch = curl_init();
        // 超时时间
        curl_setopt($ch, CURLOPT_TIMEOUT, $second);
        // 这里设置代理，如果有的话
        // curl_setopt($ch,CURLOPT_PROXY, '8.8.8.8');
        // curl_setopt($ch,CURLOPT_PROXYPORT, 8080);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        // 设置header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
        // 要求结果为字符串且输出到屏幕上
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
        // post提交方式
        curl_setopt($ch, CURLOPT_POST, TRUE);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        // 运行curl
        $data = curl_exec($ch);
        // 返回结果
        if ($data) {
            curl_close($ch);
            return $data;
        } else {
            $error = curl_errno($ch);
            echo "curl出错，错误码:$error" . "<br>";
            echo "<a href='http://curl.haxx.se/libcurl/c/libcurl-errors.html'>错误原因查询</a></br>";
            curl_close($ch);
            return false;
        }
    }

    //xml转成数组
    private function xmlstr_to_array($xmlstr) {
        $doc = new \DOMDocument ();
        $doc->loadXML($xmlstr);
        return $this->domnode_to_array($doc->documentElement);
    }

    //dom转成数组
    private function domnode_to_array($node) {
        $output = array();
        switch ($node->nodeType) {
            case XML_CDATA_SECTION_NODE :
            case XML_TEXT_NODE :
                $output = trim($node->textContent);
                break;
            case XML_ELEMENT_NODE :
                for ($i = 0, $m = $node->childNodes->length; $i < $m; $i++) {
                    $child = $node->childNodes->item($i);
                    $v = $this->domnode_to_array($child);
                    if (isset ($child->tagName)) {
                        $t = $child->tagName;
                        if (!isset ($output [$t])) {
                            $output [$t] = array();
                        }
                        $output [$t] [] = $v;
                    } elseif ($v) {
                        $output = ( string )$v;
                    }
                }
                if (is_array($output)) {
                    if ($node->attributes->length) {
                        $a = array();
                        foreach ($node->attributes as $attrName => $attrNode) {
                            $a [$attrName] = ( string )$attrNode->value;
                        }
                        $output ['@attributes'] = $a;
                    }
                    foreach ($output as $t => $v) {
                        if (is_array($v) && count($v) == 1 && $t != '@attributes') {
                            $output [$t] = $v [0];
                        }
                    }
                }
                break;
        }
        return $output;
    }

    // 数组转xml
    public function arrayToXml($arr) {
        $xml = "<xml>";
        foreach ($arr as $key => $val) {
            if (is_numeric($val)) {
                $xml .= "<" . $key . ">" . $val . "</" . $key . ">";
            } else
                $xml .= "<" . $key . "><![CDATA[" . $val . "]]></" . $key . ">";
        }
        $xml .= "</xml>";
        return $xml;
    }

    /**
     * 微信签名验证
     * @param string $data 业务参数
     * @return array
     */
    public function WxPayNotifyCheck() {
        $postStr = $GLOBALS['HTTP_RAW_POST_DATA'];
        $postObj = simplexml_load_string($postStr, 'SimpleXMLElement', LIBXML_NOCDATA);
        if ($postObj === false) {
            error_log('parse xml error', 3, './wechat_errorlog.txt');
        }
        if ($postObj->return_code != 'SUCCESS') {
            error_log($postObj->return_msg, 3, './wechat_errorlog.txt');
        }

        $arr = (array)$postObj;
        unset($arr['sign']);
        if ($this->getSign($arr) == $postObj->sign) {
            return array('status' => true, 'data' => $arr);
        } else
            return array('status' => false);
    }

}

