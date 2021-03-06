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
            $user_type = M('User')->where(array('user_id' => $uid))->getField('user_type');
            if(!in_array($user_type, array(0, 1))) $this->apiReturn(V(0, '请先绑定用户类型'));
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
}
