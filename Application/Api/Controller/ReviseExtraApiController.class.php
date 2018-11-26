<?php
/**
 * @desc 绑定用户类型等非验证用户类型接口
 */
namespace Api\Controller;
use Common\Controller\ReviseUserCommonController;

class ReviseExtraApiController extends ReviseUserCommonController{

    /**
     * @desc 绑定用户类型
     * @param user_type 0:求职者 1、HR端
     */
    public function bindUserType(){
        $user_model = D('Admin/User');
        $user_id = UID;
        $user_where = array('user_id' => $user_id);
        $user_info = $user_model->getUserInfo($user_where);
        $user_type = I('user_type', -1, 'intval');
        if($user_info['user_type'] != 2) $this->apiReturn(V(0, '用户类型错误！'));
        if(!in_array($user_type, array(0, 1))) $this->apiReturn(V(0, '传入用户类型错误！'));
        $save = array('user_type' => $user_type);
        $result = $user_model->saveUserData($user_where, $save);
        if(false !== $result){
            $this->apiReturn(V(1, '用户类型更改成功！'));
        }
        else{
            $this->apiReturn(V(0, $user_model->getError()));
        }
    }
}