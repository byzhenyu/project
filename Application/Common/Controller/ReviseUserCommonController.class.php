<?php
/**
 * 闪荐二期用户登录不验证用户类型基类
 */
namespace Common\Controller;
use Common\Controller\ApiCommonController;
class ReviseUserCommonController extends ApiCommonController {
    
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
}
