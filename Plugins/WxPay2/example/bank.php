<?php
header("Access-Control-Allow-Origin:*");
header("Access-Control-Allow-Method:POST,GET");
class Bank {

    public function withdraw($orderInfo=array()){
        $url = "https://api.mch.weixin.qq.com/mmpaysptrans/pay_bank";
        $data['mch_id'] = C('WxPay')['mch_id']; //商户号
        $data['partner_trade_no'] = $orderInfo['order_no']; //订单
        $data['nonce_str'] = $this->getRandChar(32); //随机字符串
        $data['enc_bank_no'] = $this->rsa_encrypt($orderInfo['bank_no']); //银行卡号
        $data['enc_true_name'] = $this->rsa_encrypt($orderInfo['bank_master']); //收款人姓名
        $data['bank_code'] = $orderInfo['bank_id']; //开户行
        $data['amount'] = $orderInfo['practical_money']*100; //付款金额
        $data['sign'] = $this->getSign($data); //签名
        $xml=$this->arrayToXml($data);
        $result = $this->url($xml,$url);
        return $result;
    }

    //获取rsa加密公钥
    private function getrsaapi(){
        $url = "https://fraud.mch.weixin.qq.com/risk/getpublickey";
        $params = [
            'mch_id' => trim(C('WxPay')['mch_id']),
            'nonce_str' => trim($this->getRandChar(32)),
            'sign_type' => MD5
        ];
        $params['sign'] = $this->getSign($params); //生成sign

        $xml = $this->arrayToXml($params); //创建xml，
        $response = $this->url($xml, $url);  //提交获取rsa的请求
        return $response;
    }

    //rsa加密响应字段
    private function rsa_encrypt($str){
        $url = dirname(dirname(__FILE__))."/cert/rsa8.pem";
        $pu_key = openssl_pkey_get_public(file_get_contents("$url"));  //读取公钥内容

        $encrypted = '';
        // 用标准的RSA加密库对敏感信息进行加密，选择RSA_PKCS1_OAEP_PADDING填充模式
        // 得到进行rsa加密并转base64之后的密文
        openssl_public_encrypt($str,$encryptedBlock,$pu_key,OPENSSL_PKCS1_OAEP_PADDING);
        $str_base64  = base64_encode($encrypted.$encryptedBlock);
        return $str_base64;
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
    private function arrayToXml($arr) {
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
}

