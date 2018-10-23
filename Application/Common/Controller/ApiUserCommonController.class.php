<?php
/**
 * 用户登录后, 需要继承的基类
 */
namespace Common\Controller;
use Common\Controller\ApiCommonController;
class ApiUserCommonController extends ApiCommonController {
    
    public function __construct(){
        parent::__construct();
        $token = I('post.token', '');
        // 判断token值是否正确并返回用户信息
        $uid = $this->checkTokenAndGetUid($token);
        if ($uid > 0) {
            define('UID', $uid);
        } else {
            $this->ajaxReturn(V('0', '用户已失效，请重新登录'));
        }
        
    }

    protected function checkTokenAndGetUid($token){
        
        $where['token'] = $token;
        $id = M('user_token')->where($where)->getField('user_id');
        return $id ? $id : 0;
    }
    public function index()
    {

    }
}
