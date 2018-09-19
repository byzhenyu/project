<?php
namespace Hr\Controller;
use Common\Controller\HrCommonController;
class HrController extends HrCommonController {

    public function setHr(){
        $user_id = HR_ID;
        $where = array('user_id' => $user_id);
        if(IS_POST){
            $data = I('post.');
            $model = D('Admin/User');
            if(!isMobile($data['mobile'])) $this->ajaxReturn(V(0, '请输入合法的手机号码！'));
            if($data['password']){
                if(!$data['new_password']) $this->ajaxReturn(V(0, '请再次输入新密码'));
                if($data['new_password'] != $data['password']) $this->ajaxReturn(V(0, '新密码两次输入不一致！'));
                if(strlen($data['password']) < 6 || strlen($data['password']) > 18) $this->ajaxReturn(V(0, '密码长度控制在6-18位！'));
            }
            $res = $model->saveUserData($where,$data);
            if(false !== $res){
                $this->ajaxReturn(V(1, '保存成功！'));
            }
            else{
                $this->ajaxReturn(V(0, '保存失败！'));
            }
        }
        $info = D('Admin/User')->getUserInfo($where, '*');
        $this->info = $info;
        $this->display();
    }
}