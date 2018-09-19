<?php
/**
 * 账单控制器
 */
namespace Admin\Controller;
use Think\Controller;
class UserAccountController extends CommonController {

    //账单列表
    public function listUserAccount(){
        $type = I('type', -1, 'intval');
        $keywords = I('keyword', '', 'trim');
        $model = D('Admin/UserAccount');
        $where = array();
        if($keywords) $where['u.mobile|u.user_name'] = array('like', '%'.$keywords.'%');
        if($type >= 0) $where['ua.type'] = $type;
        $list = $model->getUserAccountList($where);
        $this->keyword = $keywords;
        $this->type = $type;
        $this->info = $list['info'];
        $this->page = $list['page'];
        $this->display();
    }

    // 放入回收站
    public function del(){
        $this->_del('UserAccount', 'id');
    }
}