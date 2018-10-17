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
            $mobile_info = $model->getUserInfo(array('mobile' => $data['mobile'], 'status' => 1, 'user_type' => 1, 'user_id' => array('neq', HR_ID)));
            if($mobile_info) $this->ajaxReturn(V(0, '手机号已经被绑定！'));
            $res = $model->saveUserData($where,$data);
            if(false !== $res){
                $this->ajaxReturn(V(1, '保存成功！'));
            }
            else{
                $this->ajaxReturn(V(0, '保存失败！'));
            }
        }
        $info = D('Admin/User')->getUserInfo($where, '*');
        $like_tags = D('Admin/Tags')->getTagsList(array('id' => array('in', $info['like_tags'])));
        $tags = '';
        foreach($like_tags as &$val){
            $tags .= $val['tags_name'].',';
        }
        unset($val);
        $info['like_tags'] = rtrim($tags, ',');
        if(!$info['like_tags']) $info['like_tags'] = '尚未填写';
        $this->info = $info;
        $this->display();
    }

}