<?php
/**
 * 用户登录后, 需要继承的基类
 * create by zhaojiping <QQ: 17620286>
 */
namespace Common\Controller;
use Common\Controller\ApiCommonController;
class ApiUserCommonController extends ApiCommonController {
    
    public function __construct(){
        parent::__construct();
        $token = I('post.token', '');
        $token = '397332857351537649';
        // 判断token值是否正确并返回用户信息
        $uid = $this->checkTokenAndGetUid($token);
        if ($uid > 0) {
            define('UID', $uid);
            //根据uid获取会员类型
            $userType = D('Home/User')->getUserField(array('user_id'=>UID), 'user_type');
            //根据会员类型取出相关店铺id
            $where['user_id'] = UID;
            $where['audit_status'] = array('in', '-1,0,1,2');
            if ($userType == 2) {
                $shop_id = M('shop')->where($where)->getField('shop_id');
            }
            elseif ($userType == 1) {
                unset($where['audit_status']);
                $rider_id = M('Rider')->where($where)->getField('id');
            }
            $shop_id = $shop_id ? $shop_id : 0;
            $rider_id = $rider_id ? $rider_id : 0;
            define('SHOP_ID', $shop_id);
            define('RIDER_ID', $rider_id);
            unset($where);
            
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
