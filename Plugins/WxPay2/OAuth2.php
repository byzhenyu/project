<?php
/**
 * Copyright (c) 山东六牛网络科技有限公司 https://liuniukeji.com
 * @Description    微信网页授权认证
 * @Author         yuedeguo/QQ:302044747
 * @Copyright      Copyright (c) 山东六牛网络科技有限公司 保留所有版权(https://www.liuniukeji.com)
 * @Date           2017-12-11
 * @CreateBy       PhpStorm
 */
class OAuth {
    /**
     * 验证登录
     */
    public function _initialize(){

        $wx = array(); //生成签名的时间戳
        $wx['timestamp'] = time(); //生成签名的随机串
        $wx['noncestr'] = 'jeWaOG8KNzVICwTh'; //jsapi_ticket是公众号用于调用微信JS接口的临时票据。正常情况下，jsapi_ticket的有效期为7200秒，通过access_token来获取。
        $wx['jsapi_ticket'] = $this->wx_get_jsapi_ticket(); //分享的地址，注意：这里是指当前网页的URL，不包含#及其后面部分，曾经的我就在这里被坑了，所以小伙伴们要小心了
        $wx['url'] = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
        $string = sprintf("jsapi_ticket=%s&noncestr=%s&timestamp=%s&url=%s", $wx['jsapi_ticket'], $wx['noncestr'], $wx['timestamp'], $wx['url']); //生成签名
        $wx['signature'] = sha1($string);

//        $this->assign('wx', $wx);

        if(session('WX_UID')){
        }else{
            $code = I('get.code','');
            $user = D('Users');
            $userInfo = getUserInfo($code);
            if($userInfo == false){
                return false;
            }
            $data['opend_id'] = $userInfo['openid'];
            $result = $user->field('id')->where(array('opend_id' => $data['opend_id']))->find();
            if($result){
                session('WX_UID',$result['id']);
            }else{
                $data['nick_name'] = $userInfo['nickname'];
                $data['head_portrait'] = $userInfo['headimgurl'];
                $data['gender'] = $userInfo['sex'];
                $data['add_time'] = time();
                $arr = $user->add($data);
                if($arr){
                    session('WX_UID',$arr);
                }else{
                }
            }
        }
    }

    //curl获取请求文本内容
    function get_curl_contents($url, $method ='GET', $data = array()) {
        if ($method == 'POST') {
            //使用crul模拟
            $ch = curl_init();
            //禁用htt<a href="/fw/photo.html" target="_blank">ps</a>
            // <a href="/tags.php/curl_setopt/" target="_blank">curl_setopt</a>($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
            //允许请求以文件流的形式返回
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 30);
            curl_setopt($ch, CURLOPT_DNS_CACHE_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_URL, $url);
            $result = curl_exec($ch); //执行发送
            curl_close($ch);
        }else {
            if (ini_get('allow_<a href="/tags.php/fopen/" target="_blank">fopen</a>_url') == '1') {
                $result = file_get_contents($url);
            }else {
                //使用crul模拟
                $ch = curl_init();
                //允许请求以文件流的形式返回
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
                //禁用https
                curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
                curl_setopt($ch, CURLOPT_URL, $url);
                $result = curl_exec($ch); //执行发送
                curl_close($ch);
            }
        }
        return $result;
    }
    //获取微信公从号access_token
    function wx_get_token() {
        $AppID = C('WxPay')['app_id'];//AppID(应用ID)
        $AppSecret = C('WxPay')['appsecret'];//AppSecret(应用密钥)
        $url = 'https://api.weixin.qq.com/cgi-bin/token?grant_type=client_credential&appid='.$AppID.'&secret='.$AppSecret;
        $res = $this->get_curl_contents($url);
        $res = json_decode($res, true);
        //这里应该把access_token缓存起来，至于要怎么缓存就看各位了，有效期是7200s
        $access_token = session($res['access_token']);
        return $res['access_token'];
    }
    //获取微信公从号ticket
    function wx_get_jsapi_ticket() {
        $url = sprintf("https://api.weixin.qq.com/cgi-bin/ticket/getticket?access_token=%s&type=jsapi", $this->wx_get_token());
        $res = $this->get_curl_contents($url);
        $res = json_decode($res, true);
        //这里应该把access_token缓存起来，至于要怎么缓存就看各位了，有效期是7200s
        $jsapi_ticket = session($res['ticket']);
        return $res['ticket'];
    }

}