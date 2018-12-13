<?php

namespace NewHr\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->get_global_config();
    }

    public function index(){
        $hr_auth = session('hr_auth');
        $hr_id = $hr_auth['hr_id'];
        if($hr_id) {
            redirect(U('/NewHr/Index'));
        }
        $this->display();
    }
    /**
    * @desc  微信扫码登录
    * @param
    * @return mixed
    */
    public function weiChatDoLogin(){
        $code = $_GET['code'];
        if (empty($code)) {
            $this->redirect('Login/Login');
        }
        /*引入微信登录类*/
        require_once("./Plugins/WxLogin/WxLogin.php");
        $WxLogin = new \WxLogin();
        $weiChat_token = $WxLogin->getWeiChat($code);
        $weiChatData = $WxLogin->getWeiChatInfo($weiChat_token['access_token'], $weiChat_token['openid']);
        $loginInfo = D('Admin/User')->weiChatLogin($weiChatData['unionid']);
        if( $loginInfo['status'] == 1 ){ //登录成功
            /* 存入session */
            $this->_autoSession($loginInfo['data']);
            $this->display(('Index/index'));
        } else {
            $this->ajaxReturn($loginInfo);
        }
    }
       /**
       * @desc 手机验证码登录
       * @param   phone
       * @param   chkcode
       * @return mixed
       */
       public  function phoneLogin(){
           $user = D('Admin/User');
           if (! $user->create(I('post.'), 2)){
               $this->ajaxReturn(V(0, $user->getError()));
           }
           $mobile = I('post.mobile', '');
           $chkcode = I('chkcode');
           $valid = D('Admin/SmsMessage')->checkSmsMessage($chkcode, $mobile, 1, 7);
           if (!$valid['status']) $this->ajaxReturn($valid);
           $loginInfo = D('Admin/User')->phoneLogin($mobile);
           if( $loginInfo['status'] == 1 ){ //登录成功
               /* 存入session */
               $this->_autoSession($loginInfo['data']);
               $this->ajaxReturn($loginInfo);
           } else {
               $this->ajaxReturn($loginInfo);
           }
       }

    public function dologin(){
        $user = D('Admin/User');
        if (! $user->create(I('post.'), 5)){
            $this->ajaxReturn(V(0, $user->getError()));
        }
        $chkcode = I('chkcode');
        if(!$this->check_verify($chkcode)){
            $this->ajaxReturn(V(0, '验证码错误'));
        }
        $mobile = I('post.mobile', '');
        $password = I('post.password', '');
        $loginInfo = D('Admin/User')->doLogin($mobile, $password, '', 1, true);
        if( $loginInfo['status'] == 1 ){ //登录成功
            unset($loginInfo['password']);
            /* 存入session */
            $this->_autoSession($loginInfo['data']);
            $this->ajaxReturn(V(1, '登录成功'));
        } else {
            $this->ajaxReturn(V(0, $loginInfo['info']));
        }
    }
    /**
     * 检测验证码
     * @param  integer $id 验证码ID
     * @return boolean     检测结果
     */
    private function check_verify($code, $id = 1){
        $verify = new \Think\Verify();
        return $verify->check($code, $id);
    }


    
    public function logout(){
        session(null);
        $this->redirect('index');
    }
    
    /* 记录登录SESSION和COOKIES */
    private function _autoSession($user){
        
        if ($user['user_id'] == 0) {
            session(null);
            $this->error('您不是hr，不能登录！' );
        }
        $auth = array(
            'hr_id'             => $user['user_id'],
            'hr_name'       =>   $user['user_name'],
            'last_login_time' => $user['last_login_time'],
        );

        session('hr_auth', $auth);
    }
    

    /**
     * [get_global_config 获取配置]
     * @return [type] [description]
     */
    public function get_global_config()
    {
        /* 读取数据库中的配置 */
        $config =   S('DB_CONFIG_DATA');
        if(!$config){
            $configParse = new \Common\Tools\ConfigParse();
            $config      =   $configParse->lists();
            S('DB_CONFIG_DATA',$config,60);
        }
        C($config); //添加配置

    }

    //生成验证码图片
    public function chkcode(){
        $Verify = new \Think\Verify(array(
            'length' => 4,
            'useNoise' => FALSE,
            'imageH' =>40,
            'imageW' => 100,
            'fontSize'=>14,
            'useCurve'=>false
        ));
        $Verify->entry(1);
    }


}