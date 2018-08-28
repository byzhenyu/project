<?php
/**
 * @Description      快递鸟电子面单接口
 * @Author     	     gejun mail@gejun.net
 */
use Think\Controller;
class KdApiEOrder extends Controller {

    /**
     * 快递鸟商户号
     *
     * @var string
     */
    private $eBusinessID;
    
    /**
     * 快递鸟秘钥
     *
     * @var string
     */
    private $appKey;

    /**
     * 初始化参数
     *
     * @param array $options
     * @param $options ['eBusinessID'] EBusinessID：商户id
     * @param $options ['appKey'] AppKey：appkey
     */

    public function __construct($options) {
        $this->eBusinessID = $options['eBusinessID'];
        $this->appKey = $options['appKey'];
    }
    
    /**
     * 处理接收参数
     *
     * @param array $data
     * @return json
     */
    function kdOrder($data) {
 
        //构造电子面单提交信息
        $eorder = [];
        $eorder["ShipperCode"] = $data['shipping']['shipperCode'];
        if ($data['shipping']['customerName']) {
            $eorder['CustomerName'] = $data['shipping']['customerName'];
        }
        if ($data['shipping']['customerPwd']) {
            $eorder['CustomerPwd'] = $data['shipping']['customerPwd'];
        }
        $eorder["OrderCode"] = $data['order']['order_sn'];
        $eorder["PayType"] = 1;
        $eorder["ExpType"] = 1;
        
        $receiver = [];
        $receiver["Name"] = $data['order']['consignee'];
        $receiver["Mobile"] = $data['order']['mobile'];
        $receiver["ProvinceName"] =$data['order']['province'];
        $receiver["CityName"] = $data['order']['city'];
        $receiver["ExpAreaName"] = $data['order']['district'];
        $receiver["Address"] = $data['order']['address'];
        
        $sender = [];
        $sender["Name"] = $data['shop']['contacts_name'];
        $sender["Mobile"] = $data['shop']['user_mobile'];
        $sender["ProvinceName"] = $data['shop']['province'];
        $sender["CityName"] = $data['shop']['city'];
        $sender["Address"] = $data['shop']['address'];
        
        $commodity = [];
        if (!empty($data['order_goods'])) {
            foreach ($data['order_goods'] as $key => $value) {
                $commodity[$key]["GoodsName"] = $value['goods_name'];
                $commodity[$key]["Goodsquantity"] = $value['buy_number'];
                //$commodity[$key]["GoodsPrice"] = $value['price'];
            }
        } else {
            $commodity["GoodsName"] = '其它';
        }

        $eorder["Sender"] = $sender;
        $eorder["Receiver"] = $receiver;
        $eorder["Commodity"] = $commodity;
        $eorder['IsReturnPrintTemplate']  = "1";
        
        //调用电子面单
        $jsonParam = json_encode($eorder, JSON_UNESCAPED_UNICODE);
        $jsonResult =$this->submitEOrder($jsonParam);
        return $jsonResult;
    }

    /**
     * Json方式 调用电子面单接口
     */
    function submitEOrder($requestData){
         $Kdniaoconf = C('Kdniao');
         $datas = array(
            'EBusinessID' => $this->eBusinessID,
            'RequestType' => '1007',
            'RequestData' => urlencode($requestData) ,
            'DataType' => '2',
        );
        $datas['DataSign'] = $this->encrypt($requestData, $this->appKey);
        $result = $this->sendPost($Kdniaoconf['request_url'], $datas);
        //LL($result,'./log12.txt');
        //根据公司业务处理返回的信息......
        return $result;
    }
    
    
    /**
     *  post提交数据
     * @param  string $url 请求Url
     * @param  array $datas 提交的数据
     * @return url响应返回的html
     */
    function sendPost($url, $datas) {
        $temps = array();
        foreach ($datas as $key => $value) {
            $temps[] = sprintf('%s=%s', $key, $value);
        }
        $post_data = implode('&', $temps);
        $url_info = parse_url($url);
        if(empty($url_info['port']))
        {
            $url_info['port']=80;
        }
        $httpheader = "POST " . $url_info['path'] . " HTTP/1.0\r\n";
        $httpheader.= "Host:" . $url_info['host'] . "\r\n";
        $httpheader.= "Content-Type:application/x-www-form-urlencoded\r\n";
        $httpheader.= "Content-Length:" . strlen($post_data) . "\r\n";
        $httpheader.= "Connection:close\r\n\r\n";
        $httpheader.= $post_data;
        $fd = fsockopen($url_info['host'], $url_info['port']);
        fwrite($fd, $httpheader);
        $gets = "";
        $headerFlag = true;
        while (!feof($fd)) {
            if (($header = @fgets($fd)) && ($header == "\r\n" || $header == "\n")) {
                break;
            }
        }
        while (!feof($fd)) {
            $gets.= fread($fd, 128);
        }
        fclose($fd);
    
        return $gets;
    }
    
    /**
     * 电商Sign签名生成
     * @param data 内容
     * @param appkey Appkey
     * @return DataSign签名
     */
    function encrypt($data, $appkey) {
        return urlencode(base64_encode(md5($data.$appkey)));
    }
    /**************************************************************
     *
     *  使用特定function对数组中所有元素做处理
     *  @param  string  &$array     要处理的字符串
     *  @param  string  $function   要执行的函数
     *  @return boolean $apply_to_keys_also     是否也应用到key上
     *  @access public
     *
     *************************************************************/
    function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }
             
            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
    
    
    /**************************************************************
     *
     *  将数组转换为JSON字符串（兼容中文）
     *  @param  array   $array      要转换的数组
     *  @return string      转换得到的json字符串
     *  @access public
     *
     *************************************************************/
    function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
    
}
     
?>