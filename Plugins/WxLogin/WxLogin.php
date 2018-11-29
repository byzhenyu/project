<?php

/**
 * 微信扫码登录
 * @author byzhenyu <byzhenyu@qq.com>
 */
class WxLogin{

    /**
     * 初始化参数
     *
     * @param array $options
     * @param $options ['app_id']  APPID：绑定支付的APPID（必须配置，开户邮件中可查看）
     * @param $options ['mch_id'] MCHID：商户号（必须配置，开户邮件中可查看）
     * @param $options ['key'] KEY：商户支付密钥，参考开户邮件设置（必须配置，登录商户平台自行设置）
     * @param $options ['appsecret'] 公众帐号secert（仅JSAPI支付的时候需要配置)，
     * @param $options ['notify_url'] 支付宝回调地址
     */
    public function __construct($options = array())
    {
        $this->config = !empty($options) ? $options : C('WxLogin');
    }
    /**
     * @desc   微信网页授权返回值
     * @param  $code open_id
     * @return mixed
     */
    public function getWeiChat($code){
        $wxConfig =  $this->config;
        //通过下面url获取access_t和 openid，具体看代码
        $url = 'https://api.weixin.qq.com/sns/oauth2/access_token?appid='.$wxConfig['appId'].'&secret='.$wxConfig['appSecret'].'&code='.$code.'&grant_type=authorization_code';
        $data = json_decode($this->curl($url), true);//调取function.php封装的CURL函数  return array
        return $data;
    }
    /**
     * @desc  根据微信code获取用户的信息
     * @param access_token
     * @param openid  https://api.weixin.qq.com/sns/userinfo?access_token=ACCESS_TOKEN&openid=OPENID
     * @return mixed
     */
    function getWeiChatInfo($access_token,$openid){
        $url = 'https://api.weixin.qq.com/sns/userinfo?access_token='.$access_token.'&openid='.$openid;
        $data = json_decode($this->curl($url), true);//调取function.php封装的CURL函数  return array
        return $data;
    }
    /**
     * @desc  curl 处理
     * @param   url
     * @return mixed
     */
    public function curl($url){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, $url);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        $data = curl_exec($curl);
        curl_close($curl);
        return $data;
    }

}