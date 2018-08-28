<?php
/**
 * Created by PhpStorm.
 * User: jipingzhao
 * Date: 6/17/17
 * Time: 8:39 AM
 * 管理帐号登陆操作类
 */

namespace Admin\Controller;
use Think\Controller;
class LoginController extends Controller {
    public function __construct(){
        parent::__construct();
        $this->get_global_config();
    }

    //登陆界面
    public function index(){
        $this->display();
    }

    // 登录操作
    public function dologin(){
        $adminModel = D('Admin');
        if($adminModel->validate($adminModel->_login_validate)->create(I('post.'),6)){
            if($adminModel->doLogin()){
                admin_log('登录');
                $this->ajaxReturn(V(1, '登录成功,正在跳转'));
            }
        }
        $this->ajaxReturn(V(0, $adminModel->getError()));
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
    //退出操作
    Public function logout(){
        session('admin_id',null);
        session('admin_name', null);
        redirect(U('Login/index'));
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